<?php
/**
 * PaySim - UPI Payment & Ledger Transfer Test Suite
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/services/PaymentService.php';
require_once dirname(__DIR__) . '/services/AccountService.php';
require_once dirname(__DIR__) . '/services/UserService.php';
require_once dirname(__DIR__) . '/services/QRService.php';
require_once dirname(__DIR__) . '/utils/UPIValidator.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/PaymentRequest.php';

class PaymentTest {
    private PaymentService $paymentService;
    private AccountService $accountService;
    private UserService $userService;
    private User $userModel;
    private Account $accountModel;
    private Transaction $txnModel;
    private PaymentRequest $requestModel;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct() {
        $this->paymentService = new PaymentService();
        $this->accountService = new AccountService();
        $this->userService    = new UserService();
        $this->userModel       = new User();
        $this->accountModel   = new Account();
        $this->txnModel       = new Transaction();
        $this->requestModel   = new PaymentRequest();
    }

    private function assert(bool $condition, string $testName): void {
        if ($condition) {
            echo "  [PASS] {$testName}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$testName}\n";
            $this->failed++;
        }
    }

    public function run(): bool {
        echo "=== Running Payment & UPI Settlement Test Suite ===\n";

        // 1. UPI ID Format Validation
        $this->assert(UPIValidator::validate('suprakash@paysim'), "UPIValidator accepts valid internal UPI ID");
        $this->assert(UPIValidator::validate('ravi.kumar@oksbi'), "UPIValidator accepts valid dot-separated UPI ID");
        $this->assert(UPIValidator::validate('store_99@paytm'), "UPIValidator accepts underscore in UPI ID");
        $this->assert(!UPIValidator::validate('invalid_no_at'), "UPIValidator rejects string missing @ symbol");
        $this->assert(!UPIValidator::validate('invalid @handle'), "UPIValidator rejects spaces in UPI ID");
        $this->assert(!UPIValidator::validate('@onlyhandle'), "UPIValidator rejects missing username prefix");

        // 2. Setup Test Sender and Receiver
        $sender = $this->userModel->findByUsername('suprakash');
        $receiver = $this->userModel->findByUsername('demo');
        $this->assert($sender !== null && $receiver !== null, "Test sender & receiver accounts exist");

        $senderId = (int)$sender['id'];
        $receiverId = (int)$receiver['id'];

        $initialSenderBal = $this->accountService->getBalance($senderId);
        $initialReceiverBal = $this->accountService->getBalance($receiverId);

        // 3. Self-Transfer Prevention
        $selfRes = $this->paymentService->sendMoney($senderId, $sender['upi_id'], 100.00, '123456');
        $this->assert($selfRes['success'] === false, "Self-transfer prevented");

        // 4. Invalid PIN Prevention
        $pinRes = $this->paymentService->sendMoney($senderId, $receiver['upi_id'], 100.00, '000000');
        $this->assert($pinRes['success'] === false, "Transfer rejected on incorrect UPI PIN");

        // 5. Insufficient Balance Protection
        $hugeAmt = $initialSenderBal + 1000000.00;
        $overRes = $this->paymentService->sendMoney($senderId, $receiver['upi_id'], $hugeAmt, '123456');
        $this->assert($overRes['success'] === false, "Transfer rejected when amount exceeds wallet balance");

        // 6. Successful Transfer
        $transferAmt = 150.00;
        $sendRes = $this->paymentService->sendMoney(
            $senderId, 
            $receiver['upi_id'], 
            $transferAmt, 
            '123456', 
            'Test Dinner Split', 
            'Food & Dining'
        );

        $this->assert($sendRes['success'] === true, "Send money succeeds with valid parameters");
        $txn = $sendRes['transaction'] ?? [];
        $this->assert(!empty($txn['txn_id']), "Transaction ID generated ({$txn['txn_id']})");
        $this->assert(!empty($txn['utr']), "NPCI UTR reference generated ({$txn['utr']})");

        // 7. Verify Balances after Transfer
        $newSenderBal = $this->accountService->getBalance($senderId);
        $newReceiverBal = $this->accountService->getBalance($receiverId);

        // Accounting for any cashback that might have been awarded to sender
        $cashback = (float)($txn['cashback'] ?? 0.0);
        $expectedSenderBal = $initialSenderBal - $transferAmt + $cashback;
        $expectedReceiverBal = $initialReceiverBal + $transferAmt;

        $this->assert(abs($newSenderBal - $expectedSenderBal) < 0.01, "Sender balance accurately debited (including cashback adjustment)");
        $this->assert(abs($newReceiverBal - $expectedReceiverBal) < 0.01, "Receiver balance accurately credited");

        // 8. Add Money / Wallet Top-Up
        $topupAmt = 500.00;
        $topupRes = $this->accountService->addMoney($senderId, $topupAmt, 'SBI Bank Account', 'Test Add Money');
        $this->assert($topupRes['success'] === true, "Add money to wallet succeeds");
        $postTopupBal = $this->accountService->getBalance($senderId);
        $this->assert(abs($postTopupBal - ($newSenderBal + $topupAmt)) < 0.01, "Wallet balance reflects topup accurately");

        // 9. Payment Collect Request Flow
        $reqAmt = 250.00;
        $reqRes = $this->paymentService->requestMoney($receiverId, $sender['upi_id'], $reqAmt, 'Split for Uber');
        $this->assert($reqRes['success'] === true, "Payment request created successfully");
        $reqCode = $reqRes['request_code'] ?? '';
        $this->assert(!empty($reqCode), "Payment request code generated ({$reqCode})");

        // Retrieve and pay the request
        $savedReq = $this->requestModel->findByRequestCode($reqCode);
        $this->assert($savedReq !== null && $savedReq['status'] === 'pending', "Payment request persisted with pending status");

        $payReqRes = $this->paymentService->payRequest((int)$savedReq['id'], $senderId, '123456');
        $this->assert($payReqRes['success'] === true, "Payment of collect request succeeds");

        $paidReq = $this->requestModel->findById((int)$savedReq['id']);
        $this->assert($paidReq['status'] === 'accepted', "Payment request status transitioned to 'accepted'");

        // 10. QR Service URI Generation and Parsing
        $qrUri = QRService::generateUpiUri('merchant@paysim', 'PaySim Cafe', 320.50, 'Coffee and pastry');
        $this->assert(str_starts_with($qrUri, 'upi://pay?'), "QR Service generates valid upi://pay URI");

        $parsed = QRService::parseUpiUri($qrUri);
        $this->assert($parsed !== null, "QR Service accurately parses generated URI");
        $this->assert($parsed['upi_id'] === 'merchant@paysim', "Parsed UPI ID matches original");
        $this->assert($parsed['amount'] === 320.50, "Parsed amount matches original");
        $this->assert($parsed['note'] === 'Coffee and pastry', "Parsed note matches original");

        echo "\nPayment Tests: {$this->passed} Passed, {$this->failed} Failed\n\n";
        return ($this->failed === 0);
    }
}

// Direct execution from CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new PaymentTest();
    exit($test->run() ? 0 : 1);
}
