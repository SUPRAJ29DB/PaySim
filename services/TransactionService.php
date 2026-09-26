<?php
/**
 * PaySim - Transaction Queries & Reporting Service
 */

require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/utils/Helpers.php';

class TransactionService {
    protected Transaction $txnModel;
    protected User $userModel;
    protected Account $accountModel;

    public function __construct() {
        $this->txnModel     = new Transaction();
        $this->userModel    = new User();
        $this->accountModel = new Account();
    }

    public function getHistory(int $userId, int $page = 1, int $perPage = 20, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $txns = $this->txnModel->getUserTransactions($userId, $perPage, $offset, $filters);
        $total = $this->txnModel->countUserTransactions($userId, $filters);

        $formatted = [];
        foreach ($txns as $t) {
            $isDebit = ((int)$t['sender_id'] === $userId);
            $formatted[] = [
                'id'             => $t['txn_id'],
                'db_id'          => $t['id'],
                'ref_no'         => $t['ref_no'],
                'utr'            => $t['utr'],
                'amount'         => (float)$t['amount'],
                'direction'      => $isDebit ? 'debit' : 'credit',
                'counterparty'   => $isDebit ? $t['receiver_name'] : $t['sender_name'],
                'counterparty_upi'=> $isDebit ? $t['receiver_upi'] : $t['sender_upi'],
                'status'         => $t['status'],
                'category'       => $t['category'],
                'payment_method' => $t['payment_method'],
                'note'           => $t['note'],
                'cashback'       => (float)$t['cashback'],
                'date'           => date('d M Y, h:i A', strtotime($t['created_at'])),
                'time_ago'       => Helpers::timeAgo($t['created_at']),
                'avatar'         => Helpers::getInitials($isDebit ? $t['receiver_name'] : $t['sender_name']),
                'avatar_color'   => Helpers::getAvatarColor($isDebit ? $t['receiver_name'] : $t['sender_name']),
            ];
        }

        return [
            'transactions' => $formatted,
            'pagination'   => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_records'=> $total,
                'total_pages'  => ceil($total / max(1, $perPage)),
            ],
        ];
    }

    public function getDetails(string $txnId, ?int $userId = null): ?array {
        $t = $this->txnModel->findByTxnId($txnId);
        if (!$t) {
            return null;
        }

        if ($userId !== null) {
            // Ensure user was either sender or receiver
            if ((int)$t['sender_id'] !== $userId && (int)$t['receiver_id'] !== $userId) {
                return null;
            }
        }

        $isDebit = ($userId !== null && (int)$t['sender_id'] === $userId);

        return [
            'id'             => $t['txn_id'],
            'ref'            => $t['ref_no'],
            'utr'            => $t['utr'],
            'amount'         => (float)$t['amount'],
            'fee'            => (float)$t['fee'],
            'cashback'       => (float)$t['cashback'],
            'type'           => $isDebit ? 'debit' : 'credit',
            'status'         => $t['status'],
            'sender_name'    => $t['sender_name'],
            'sender_upi'     => $t['sender_upi'],
            'receiver_name'  => $t['receiver_name'],
            'receiver_upi'   => $t['receiver_upi'],
            'note'           => $t['note'],
            'category'       => $t['category'],
            'payment_method' => $t['payment_method'],
            'device_info'    => $t['device_info'],
            'ip_address'     => $t['ip_address'],
            'date'           => date('d M Y, h:i A', strtotime($t['created_at'])),
            'raw_date'       => $t['created_at'],
            'timeline'       => [
                ['title' => 'Payment Initiated',  'time' => date('h:i:01 A', strtotime($t['created_at'])), 'desc' => 'Order received by PaySim Switch', 'status' => 'done'],
                ['title' => 'Bank Authorization', 'time' => date('h:i:03 A', strtotime($t['created_at'])), 'desc' => 'Funds authenticated & held', 'status' => 'done'],
                ['title' => 'NPCI Settlement',    'time' => date('h:i:04 A', strtotime($t['created_at'])), 'desc' => 'Inter-bank UPI clearing settled', 'status' => 'done'],
                ['title' => 'Beneficiary Credit', 'time' => date('h:i:05 A', strtotime($t['created_at'])), 'desc' => 'Funds deposited to destination', 'status' => 'done'],
            ],
        ];
    }

    public function getMonthlySummary(int $userId): array {
        $db = Database::getInstance()->getConnection();

        // Total Income (credits) this month
        $incomeStmt = $db->prepare("
            SELECT SUM(amount) as income 
            FROM transactions 
            WHERE receiver_id = :uid AND status = 'completed'
        ");
        $incomeStmt->execute(['uid' => $userId]);
        $income = (float) ($incomeStmt->fetch()['income'] ?? 0.0);

        // Total Expense (debits) this month
        $expenseStmt = $db->prepare("
            SELECT SUM(amount) as expense 
            FROM transactions 
            WHERE sender_id = :uid AND status = 'completed'
        ");
        $expenseStmt->execute(['uid' => $userId]);
        $expense = (float) ($expenseStmt->fetch()['expense'] ?? 0.0);

        // Total Cashback earned
        $cbStmt = $db->prepare("
            SELECT SUM(cashback) as cb 
            FROM transactions 
            WHERE sender_id = :uid AND status = 'completed'
        ");
        $cbStmt->execute(['uid' => $userId]);
        $cashback = (float) ($cbStmt->fetch()['cb'] ?? 0.0);

        return [
            'month_income'  => $income,
            'month_expense' => $expense,
            'total_cashback'=> $cashback,
        ];
    }
}
