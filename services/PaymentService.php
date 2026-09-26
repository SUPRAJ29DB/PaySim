<?php
/**
 * PaySim - Core UPI Payment Processing Service
 */

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/PaymentRequest.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';
require_once dirname(__DIR__) . '/services/UserService.php';
require_once dirname(__DIR__) . '/utils/TransactionID.php';
require_once dirname(__DIR__) . '/utils/UPIValidator.php';
require_once dirname(__DIR__) . '/config/constants.php';

class PaymentService {
    protected User $userModel;
    protected Account $accountModel;
    protected Transaction $txnModel;
    protected PaymentRequest $requestModel;
    protected Notification $notifModel;
    protected AuditLog $auditModel;
    protected UserService $userService;

    public function __construct() {
        $this->userModel    = new User();
        $this->accountModel = new Account();
        $this->txnModel     = new Transaction();
        $this->requestModel = new PaymentRequest();
        $this->notifModel   = new Notification();
        $this->auditModel   = new AuditLog();
        $this->userService  = new UserService();
    }

    public function sendMoney(
        int $senderId, 
        string $receiverUpi, 
        float $amount, 
        string $pin, 
        string $note = '', 
        string $category = 'Transfer', 
        string $paymentMethod = 'UPI'
    ): array {
        // 1. Validate Amount
        if (!is_finite($amount) || $amount <= 0 || round($amount, 2) !== $amount) {
            return ['success' => false, 'message' => 'Enter a valid amount with up to two decimal places.'];
        }
        if ($amount < MIN_TXN_AMOUNT) {
            return ['success' => false, 'message' => 'Minimum transfer amount is ₹' . MIN_TXN_AMOUNT];
        }
        if ($amount > MAX_SINGLE_TXN_LIMIT) {
            return ['success' => false, 'message' => 'Transfer amount exceeds single transaction limit of ₹' . number_format(MAX_SINGLE_TXN_LIMIT, 2)];
        }

        // 2. Validate UPI ID
        $cleanReceiverUpi = strtolower(trim($receiverUpi));
        if (!UPIValidator::validate($cleanReceiverUpi)) {
            return ['success' => false, 'message' => 'Invalid beneficiary UPI ID format.'];
        }

        // 3. Sender Verification
        $sender = $this->userModel->findById($senderId);
        if (!$sender) {
            return ['success' => false, 'message' => 'Sender account not found.'];
        }
        if ($sender['status'] === 'frozen') {
            return ['success' => false, 'message' => 'Your account is frozen. Outgoing transactions are suspended.'];
        }

        // 4. Verify PIN
        if (!$this->userService->verifyUpiPin($senderId, $pin)) {
            $this->auditModel->log('INVALID_PIN', 'users', (string)$senderId, $senderId, ['amount' => $amount, 'to' => $cleanReceiverUpi]);
            return ['success' => false, 'message' => 'Incorrect UPI PIN. Transaction cancelled for security.'];
        }

        // Prevent paying self
        if (strtolower($sender['upi_id']) === $cleanReceiverUpi) {
            return ['success' => false, 'message' => 'Cannot transfer funds to your own UPI ID.'];
        }

        // 5. Check Balance
        $senderWallet = $this->accountModel->findByUserId($senderId, 'wallet');
        if (!$senderWallet || (float)$senderWallet['balance'] < $amount) {
            return ['success' => false, 'message' => 'Insufficient wallet balance to complete this transfer.'];
        }

        // 6. Resolve Beneficiary Info
        $resolved = $this->userService->resolveUpi($cleanReceiverUpi);
        $receiverName = $resolved['name'];
        $receiverUser = $this->userModel->findByUpiId($cleanReceiverUpi);
        // This application is a closed-loop simulator, not connected to external UPI rails.
        if (!$receiverUser) {
            return ['success' => false, 'message' => 'This UPI ID is not registered on PaySim. External UPI transfers are not supported in this simulator.'];
        }
        $receiverId = (int)$receiverUser['id'];
        $receiverWallet = $this->accountModel->findByUserId($receiverId, 'wallet');
        if (!$receiverWallet || ($receiverWallet['status'] ?? 'active') !== 'active') {
            return ['success' => false, 'message' => 'Recipient wallet is unavailable. Payment was not processed.'];
        }

        // 7. Execute Atomic Transfer
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            // Debit sender
            $debited = $this->accountModel->debit((int)$senderWallet['id'], $amount);
            if (!$debited) {
                $db->rollBack();
                return ['success' => false, 'message' => 'Failed to debit sender account. Check available balance.'];
            }

            // Credit the verified internal recipient within the same database transaction.
            $credited = $this->accountModel->credit((int)$receiverWallet['id'], $amount);
            if (!$credited) {
                throw new RuntimeException('Recipient wallet could not be credited.');
            }

            // No random cashback is issued; promotional credits should be explicitly configured.
            $cashback = 0.00;

            // Create Transaction Record
            $txnId = TransactionID::generateTxnId();
            $refNo = TransactionID::generateRefNo();
            $utr   = TransactionID::generateUtr();

            $insertId = $this->txnModel->create([
                'txn_id'         => $txnId,
                'ref_no'         => $refNo,
                'utr'            => $utr,
                'sender_id'      => $senderId,
                'receiver_id'    => $receiverId,
                'sender_upi'     => $sender['upi_id'],
                'receiver_upi'   => $cleanReceiverUpi,
                'sender_name'    => $sender['full_name'],
                'receiver_name'  => $receiverName,
                'amount'         => $amount,
                'fee'            => 0.00,
                'cashback'       => $cashback,
                'txn_type'       => ($paymentMethod === 'Dynamic QR Pay' ? 'qr_pay' : 'transfer'),
                'status'         => 'completed',
                'note'           => $note ?: 'Payment via PaySim',
                'category'       => $category,
                'payment_method' => $paymentMethod,
                'ip_address'     => Security::getClientIp(),
            ]);

            $db->commit();

            // 8. Send Notifications
            $this->notifModel->create([
                'user_id' => $senderId,
                'title'   => 'Sent ₹' . number_format($amount, 2) . ' to ' . $receiverName,
                'message' => "Transaction {$txnId} completed successfully.",
                'type'    => 'success',
                'action_url' => 'transaction-details.php?id=' . $txnId,
            ]);

            if ($cashback > 0) {
                $this->notifModel->create([
                    'user_id' => $senderId,
                    'title'   => '🎉 Cashback Won: ₹' . number_format($cashback, 2),
                    'message' => 'Promotional cashback credited to your wallet for transaction ' . $txnId,
                    'type'    => 'reward',
                    'action_url' => 'dashboard.php',
                ]);
            }

            if ($receiverUser && $receiverId) {
                $this->notifModel->create([
                    'user_id' => $receiverId,
                    'title'   => 'Received ₹' . number_format($amount, 2) . ' from ' . $sender['full_name'],
                    'message' => "Funds credited to your PaySim wallet. Ref: {$refNo}",
                    'type'    => 'success',
                    'action_url' => 'transaction-details.php?id=' . $txnId,
                ]);
            }

            // 9. Audit Log
            $this->auditModel->log('SEND_MONEY_SUCCESS', 'transactions', $txnId, $senderId, [
                'amount' => $amount,
                'to'     => $cleanReceiverUpi,
                'utr'    => $utr,
            ]);

            $updatedWallet = $this->accountModel->findByUserId($senderId, 'wallet');

            return [
                'success'       => true,
                'message'       => 'Payment successful!',
                'transaction'   => [
                    'id'          => $insertId,
                    'txn_id'      => $txnId,
                    'ref_no'      => $refNo,
                    'utr'         => $utr,
                    'amount'      => $amount,
                    'cashback'    => $cashback,
                    'to_name'     => $receiverName,
                    'to_upi'      => $cleanReceiverUpi,
                    'status'      => 'completed',
                    'date'        => date('d M Y, h:i A'),
                    'new_balance' => (float)$updatedWallet['balance'],
                ],
            ];
        } catch (Throwable $e) {
            if ($db->inTransaction()) { $db->rollBack(); }
            $this->auditModel->log('SEND_MONEY_ERROR', 'transactions', null, $senderId, ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Transaction failed due to a processing error. No payment was completed.'];
        }
    }

    public function requestMoney(int $requesterId, string $payerUpi, float $amount, string $note = ''): array {
        if ($amount < MIN_TXN_AMOUNT) {
            return ['success' => false, 'message' => 'Minimum request amount is ₹' . MIN_TXN_AMOUNT];
        }

        $cleanPayerUpi = strtolower(trim($payerUpi));
        if (!UPIValidator::validate($cleanPayerUpi)) {
            return ['success' => false, 'message' => 'Invalid payer UPI ID format.'];
        }

        $requester = $this->userModel->findById($requesterId);
        if (!$requester) {
            return ['success' => false, 'message' => 'Requester account not found.'];
        }

        if (strtolower($requester['upi_id']) === $cleanPayerUpi) {
            return ['success' => false, 'message' => 'Cannot request money from your own UPI ID.'];
        }

        $payerUser = $this->userModel->findByUpiId($cleanPayerUpi);
        $payerId   = $payerUser ? (int)$payerUser['id'] : null;

        $requestCode = TransactionID::generateRequestCode();
        $expiresAt   = date('Y-m-d H:i:s', strtotime('+3 days'));

        $reqId = $this->requestModel->create([
            'request_code'  => $requestCode,
            'requester_id'  => $requesterId,
            'payer_id'      => $payerId,
            'requester_upi' => $requester['upi_id'],
            'payer_upi'     => $cleanPayerUpi,
            'amount'        => $amount,
            'note'          => $note ?: 'Payment collect request',
            'status'        => 'pending',
            'expires_at'    => $expiresAt,
        ]);

        if ($payerUser && $payerId) {
            $this->notifModel->create([
                'user_id' => $payerId,
                'title'   => 'Payment Request: ₹' . number_format($amount, 2),
                'message' => $requester['full_name'] . ' requested ₹' . number_format($amount, 2) . ' (' . ($note ?: 'Collect Request') . ')',
                'type'    => 'alert',
                'action_url' => 'request-money.php',
            ]);
        }

        $this->auditModel->log('CREATE_PAYMENT_REQUEST', 'payment_requests', $requestCode, $requesterId, [
            'amount' => $amount,
            'payer'  => $cleanPayerUpi,
        ]);

        return [
            'success'      => true,
            'message'      => 'Payment request sent successfully.',
            'request_code' => $requestCode,
            'amount'       => $amount,
            'id'           => $reqId,
        ];
    }

    public function cancelRequest(int $requestId, int $userId): array {
        $req = $this->requestModel->findById($requestId);
        if (!$req) {
            return ['success' => false, 'message' => 'Payment request not found.'];
        }
        if ((int)$req['requester_id'] !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized to cancel this request.'];
        }
        if ($req['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Request is already ' . $req['status'] . '.'];
        }

        $this->requestModel->updateStatus($requestId, 'declined');
        $this->auditModel->log('CANCEL_PAYMENT_REQUEST', 'payment_requests', $req['request_code'], $userId);

        return ['success' => true, 'message' => 'Payment request cancelled successfully.'];
    }

    public function payRequest(int $requestId, int $payerId, string $pin): array {
        $req = $this->requestModel->findById($requestId);
        if (!$req) {
            return ['success' => false, 'message' => 'Payment request not found.'];
        }
        if ($req['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Request is no longer active (status: ' . $req['status'] . ').'];
        }

        $result = $this->sendMoney(
            $payerId,
            $req['requester_upi'],
            (float)$req['amount'],
            $pin,
            'Payment for request ' . $req['request_code'] . ': ' . $req['note'],
            'Request Pay'
        );

        if ($result['success']) {
            $this->requestModel->updateStatus($requestId, 'accepted');
        }

        return $result;
    }
}
