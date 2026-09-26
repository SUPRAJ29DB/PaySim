<?php
/**
 * PaySim - Transaction Ledger, History & Reporting Test Suite
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/services/TransactionService.php';
require_once dirname(__DIR__) . '/utils/TransactionID.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/User.php';

class TransactionTest {
    private TransactionService $txnService;
    private Transaction $txnModel;
    private User $userModel;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct() {
        $this->txnService = new TransactionService();
        $this->txnModel   = new Transaction();
        $this->userModel  = new User();
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
        echo "=== Running Transaction Ledger & Reporting Test Suite ===\n";

        // 1. TransactionID Generator Tests
        $txnId = TransactionID::generateTxnId();
        $this->assert(str_starts_with($txnId, 'TXN'), "Generated transaction ID has 'TXN' prefix ({$txnId})");
        $this->assert(strlen($txnId) >= 12, "Transaction ID has sufficient entropy length");

        $utr = TransactionID::generateUtr();
        $this->assert(str_starts_with($utr, 'UTR'), "Generated UTR has 'UTR' prefix ({$utr})");
        $this->assert(strlen($utr) >= 12, "UTR is at least 12 characters");

        $refNo = TransactionID::generateRefNo();
        $this->assert(str_starts_with($refNo, 'PSM'), "PaySim internal reference starts with 'PSM' ({$refNo})");

        // 2. Fetch User and History
        $user = $this->userModel->findByUsername('suprakash');
        $this->assert($user !== null, "Test user retrieved for history tests");
        $userId = (int)$user['id'];

        $historyData = $this->txnService->getHistory($userId, 1, 10);
        $this->assert(isset($historyData['transactions']), "History returns transactions array");
        $this->assert(isset($historyData['pagination']), "History returns pagination metadata");
        $this->assert(is_array($historyData['transactions']), "Transactions list is an array");

        // 3. Status Filtering
        $completedTxns = $this->txnService->getHistory($userId, 1, 10, ['status' => 'completed']);
        $allCompleted = true;
        foreach ($completedTxns['transactions'] as $tx) {
            if ($tx['status'] !== 'completed') {
                $allCompleted = false;
                break;
            }
        }
        $this->assert($allCompleted, "Filter by status 'completed' returns only completed transactions");

        // 4. Direction Filtering
        $debitTxns = $this->txnService->getHistory($userId, 1, 10, ['type' => 'debit']);
        $allDebit = true;
        foreach ($debitTxns['transactions'] as $tx) {
            if ($tx['direction'] !== 'debit') {
                $allDebit = false;
                break;
            }
        }
        $this->assert($allDebit, "Filter by type 'debit' returns only outgoing transactions");

        // 5. Search Filtering
        $sampleTxn = $historyData['transactions'][0] ?? null;
        if ($sampleTxn) {
            $searchKeyword = substr($sampleTxn['counterparty'], 0, 4);
            $searchResult = $this->txnService->getHistory($userId, 1, 10, ['search' => $searchKeyword]);
            $this->assert(count($searchResult['transactions']) > 0, "Search query finds matching transaction by counterparty");
        }

        // 6. Transaction Details & Timeline
        if ($sampleTxn) {
            $details = $this->txnService->getDetails($sampleTxn['id'], $userId);
            $this->assert($details !== null, "Transaction details retrieved successfully by ID");
            $this->assert(isset($details['timeline']) && count($details['timeline']) === 4, "Transaction details include 4-step NPCI timeline");
            $this->assert(!empty($details['utr']), "Details contains valid UTR");
            $this->assert(!empty($details['payment_method']), "Details contains payment method");
        }

        // 7. Security: Unauthorized User Cannot Access Details
        if ($sampleTxn) {
            $unauthorizedDetails = $this->txnService->getDetails($sampleTxn['id'], 99999);
            $this->assert($unauthorizedDetails === null, "Transaction access blocked for unrelated user");
        }

        // 8. Monthly Summary Calculation
        $summary = $this->txnService->getMonthlySummary($userId);
        $this->assert(isset($summary['month_income']), "Summary calculates monthly income");
        $this->assert(isset($summary['month_expense']), "Summary calculates monthly expense");
        $this->assert(isset($summary['total_cashback']), "Summary calculates total cashback");
        $this->assert(is_float($summary['month_income']) || is_int($summary['month_income']), "Income is numeric");

        echo "\nTransaction Tests: {$this->passed} Passed, {$this->failed} Failed\n\n";
        return ($this->failed === 0);
    }
}

// Direct execution from CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new TransactionTest();
    exit($test->run() ? 0 : 1);
}
