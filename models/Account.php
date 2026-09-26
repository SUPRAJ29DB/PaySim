<?php
/**
 * PaySim - Account & Bank Accounts Model
 */

require_once dirname(__DIR__) . '/config/database.php';

class Account {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUserId(int $userId, string $type = 'wallet'): ?array {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE user_id = :user_id AND account_type = :type LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'type' => $type]);
        $acc = $stmt->fetch();
        return $acc ?: null;
    }

    public function findByAccountNumber(string $accountNumber): ?array {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE account_number = :acc LIMIT 1");
        $stmt->execute(['acc' => trim($accountNumber)]);
        $acc = $stmt->fetch();
        return $acc ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO accounts (user_id, account_number, account_type, balance, currency, status)
            VALUES (:user_id, :account_number, :account_type, :balance, :currency, :status)
        ");

        $stmt->execute([
            'user_id'        => $data['user_id'],
            'account_number' => $data['account_number'],
            'account_type'   => $data['account_type'] ?? 'wallet',
            'balance'        => $data['balance'] ?? 0.00,
            'currency'       => $data['currency'] ?? 'INR',
            'status'         => $data['status'] ?? 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function credit(int $accountId, float $amount): bool {
        $stmt = $this->db->prepare("
            UPDATE accounts 
            SET balance = balance + :amount, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND status = 'active'
        ");
        return $stmt->execute(['amount' => $amount, 'id' => $accountId]) && $stmt->rowCount() > 0;
    }

    public function debit(int $accountId, float $amount): bool {
        $stmt = $this->db->prepare("
            UPDATE accounts 
            SET balance = balance - :amount, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND balance >= :check_amount AND status = 'active'
        ");
        $stmt->execute(['amount' => $amount, 'check_amount' => $amount, 'id' => $accountId]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatus(int $accountId, string $status): bool {
        $stmt = $this->db->prepare("UPDATE accounts SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $accountId]);
    }

    public function getBankAccounts(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM bank_accounts WHERE user_id = :uid ORDER BY is_primary DESC, id ASC");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function addBankAccount(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO bank_accounts (user_id, bank_name, account_number, ifsc_code, branch, account_type, balance, is_primary, color_gradient)
            VALUES (:user_id, :bank_name, :account_number, :ifsc_code, :branch, :account_type, :balance, :is_primary, :color_gradient)
        ");

        $stmt->execute([
            'user_id'        => $data['user_id'],
            'bank_name'      => $data['bank_name'],
            'account_number' => $data['account_number'],
            'ifsc_code'      => $data['ifsc_code'],
            'branch'         => $data['branch'] ?? '',
            'account_type'   => $data['account_type'] ?? 'Savings Account',
            'balance'        => $data['balance'] ?? 50000.00,
            'is_primary'     => $data['is_primary'] ?? 0,
            'color_gradient' => $data['color_gradient'] ?? 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getAllAccounts(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT a.*, u.full_name, u.username, u.upi_id, u.email
            FROM accounts a
            JOIN users u ON u.id = a.user_id
            ORDER BY a.id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
