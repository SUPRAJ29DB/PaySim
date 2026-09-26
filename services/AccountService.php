<?php
/**
 * PaySim - Account & Wallet Management Service
 */

require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/utils/TransactionID.php';

class AccountService {
    protected Account $accountModel;
    protected Transaction $txnModel;
    protected Notification $notifModel;
    protected AuditLog $auditModel;
    protected User $userModel;

    public function __construct() {
        $this->accountModel = new Account();
        $this->txnModel     = new Transaction();
        $this->notifModel   = new Notification();
        $this->auditModel   = new AuditLog();
        $this->userModel    = new User();
    }

    public function getWallet(int $userId): ?array {
        return $this->accountModel->findByUserId($userId, 'wallet');
    }

    public function getBalance(int $userId): float {
        $wallet = $this->getWallet($userId);
        return (float) ($wallet['balance'] ?? 0.00);
    }

    public function addMoney(int $userId, float $amount, string $paymentMethod = 'Bank Transfer', string $note = 'Wallet Top-up'): array {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero.'];
        }
        if ($amount > 100000) {
            return ['success' => false, 'message' => 'Maximum add money limit is ₹1,00,000 per transaction.'];
        }

        $wallet = $this->getWallet($userId);
        if (!$wallet) {
            return ['success' => false, 'message' => 'Wallet account not found.'];
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        // Credit the wallet
        $this->accountModel->credit((int)$wallet['id'], $amount);

        // Record Transaction
        $txnId = TransactionID::generateTxnId();
        $refNo = TransactionID::generateRefNo();
        $utr   = TransactionID::generateUtr();

        $this->txnModel->create([
            'txn_id'         => $txnId,
            'ref_no'         => $refNo,
            'utr'            => $utr,
            'sender_id'      => null,
            'receiver_id'    => $userId,
            'sender_upi'     => 'escrow@paysimbank',
            'receiver_upi'   => $user['upi_id'],
            'sender_name'    => $paymentMethod,
            'receiver_name'  => $user['full_name'],
            'amount'         => $amount,
            'fee'            => 0.00,
            'cashback'       => 0.00,
            'txn_type'       => 'add_money',
            'status'         => 'completed',
            'note'           => $note,
            'category'       => 'Wallet Topup',
            'payment_method' => $paymentMethod,
        ]);

        $this->notifModel->create([
            'user_id' => $userId,
            'title'   => 'Wallet Loaded: ₹' . number_format($amount, 2),
            'message' => "Successfully added ₹" . number_format($amount, 2) . " via {$paymentMethod}.",
            'type'    => 'success',
            'action_url' => 'dashboard.php',
        ]);

        $this->auditModel->log('ADD_MONEY', 'accounts', (string)$wallet['id'], $userId, ['amount' => $amount, 'method' => $paymentMethod]);

        $updatedWallet = $this->getWallet($userId);

        return [
            'success'     => true,
            'message'     => 'Money added to wallet successfully.',
            'txn_id'      => $txnId,
            'utr'         => $utr,
            'new_balance' => (float) $updatedWallet['balance'],
        ];
    }

    public function linkBankAccount(int $userId, array $data): array {
        if (empty($data['bank_name']) || empty($data['account_number']) || empty($data['ifsc_code'])) {
            return ['success' => false, 'message' => 'Bank name, Account number, and IFSC code are required.'];
        }

        $id = $this->accountModel->addBankAccount([
            'user_id'        => $userId,
            'bank_name'      => trim($data['bank_name']),
            'account_number' => '•••• ' . substr(trim($data['account_number']), -4),
            'ifsc_code'      => strtoupper(trim($data['ifsc_code'])),
            'branch'         => trim($data['branch'] ?? 'Digital Branch'),
            'account_type'   => $data['account_type'] ?? 'Savings Account',
            'balance'        => (float) ($data['initial_balance'] ?? 50000.00),
            'is_primary'     => !empty($data['is_primary']) ? 1 : 0,
            'color_gradient' => $data['color_gradient'] ?? 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
        ]);

        $this->auditModel->log('LINK_BANK', 'bank_accounts', (string)$id, $userId, ['bank' => $data['bank_name']]);

        return ['success' => true, 'message' => 'Bank account linked successfully.', 'id' => $id];
    }
}
