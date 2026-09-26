<?php
/**
 * PaySim - Transaction Ledger Model
 */

require_once dirname(__DIR__) . '/config/database.php';

class Transaction {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO transactions (
                txn_id, ref_no, utr, sender_id, receiver_id, sender_upi, receiver_upi, 
                sender_name, receiver_name, amount, fee, cashback, txn_type, status, 
                note, category, payment_method, device_info, ip_address, created_at
            ) VALUES (
                :txn_id, :ref_no, :utr, :sender_id, :receiver_id, :sender_upi, :receiver_upi,
                :sender_name, :receiver_name, :amount, :fee, :cashback, :txn_type, :status,
                :note, :category, :payment_method, :device_info, :ip_address, :created_at
            )
        ");

        $stmt->execute([
            'txn_id'         => $data['txn_id'],
            'ref_no'         => $data['ref_no'],
            'utr'            => $data['utr'],
            'sender_id'      => $data['sender_id'] ?? null,
            'receiver_id'    => $data['receiver_id'] ?? null,
            'sender_upi'     => $data['sender_upi'],
            'receiver_upi'   => $data['receiver_upi'],
            'sender_name'    => $data['sender_name'],
            'receiver_name'  => $data['receiver_name'],
            'amount'         => $data['amount'],
            'fee'            => $data['fee'] ?? 0.00,
            'cashback'       => $data['cashback'] ?? 0.00,
            'txn_type'       => $data['txn_type'] ?? 'transfer',
            'status'         => $data['status'] ?? 'completed',
            'note'           => $data['note'] ?? '',
            'category'       => $data['category'] ?? 'Transfer',
            'payment_method' => $data['payment_method'] ?? 'UPI',
            'device_info'    => $data['device_info'] ?? 'PaySim Web',
            'ip_address'     => $data['ip_address'] ?? '127.0.0.1',
            'created_at'     => $data['created_at'] ?? date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTxnId(string $txnId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE LOWER(txn_id) = LOWER(:txn) LIMIT 1");
        $stmt->execute(['txn' => trim($txnId)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUserTransactions(int $userId, int $limit = 20, int $offset = 0, array $filters = []): array {
        $sql = "
            SELECT * FROM transactions 
            WHERE (sender_id = :uid OR receiver_id = :uid2)
        ";
        $params = ['uid' => $userId, 'uid2' => $userId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'debit') {
                $sql .= " AND sender_id = :filter_uid";
                $params['filter_uid'] = $userId;
            } elseif ($filters['type'] === 'credit') {
                $sql .= " AND receiver_id = :filter_uid";
                $params['filter_uid'] = $userId;
            }
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (txn_id LIKE :s1 OR sender_name LIKE :s2 OR receiver_name LIKE :s3 OR note LIKE :s4)";
            $s = "%{$filters['search']}%";
            $params['s1'] = $s;
            $params['s2'] = $s;
            $params['s3'] = $s;
            $params['s4'] = $s;
        }

        $sql .= " ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countUserTransactions(int $userId, array $filters = []): int {
        $sql = "SELECT COUNT(*) as count FROM transactions WHERE (sender_id = :uid OR receiver_id = :uid2)";
        $params = ['uid' => $userId, 'uid2' => $userId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetch()['count'] ?? 0);
    }

    public function getAllTransactions(int $limit = 50, int $offset = 0, array $filters = []): array {
        $sql = "SELECT * FROM transactions WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (txn_id LIKE :s1 OR sender_name LIKE :s2 OR receiver_name LIKE :s3 OR sender_upi LIKE :s4 OR receiver_upi LIKE :s5)";
            $s = "%{$filters['search']}%";
            $params['s1'] = $s;
            $params['s2'] = $s;
            $params['s3'] = $s;
            $params['s4'] = $s;
            $params['s5'] = $s;
        }

        $sql .= " ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAllTransactions(array $filters = []): int {
        $sql = "SELECT COUNT(*) as count FROM transactions WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetch()['count'] ?? 0);
    }

    public function getMetrics(): array {
        $totalVolStmt = $this->db->query("SELECT SUM(amount) as total_volume, COUNT(*) as total_txns FROM transactions WHERE status = 'completed'");
        $volData = $totalVolStmt->fetch();

        $successStmt = $this->db->query("SELECT COUNT(*) as completed_txns FROM transactions WHERE status = 'completed'");
        $completed = (int) ($successStmt->fetch()['completed_txns'] ?? 0);

        $totalTxns = (int) ($volData['total_txns'] ?? 0);
        $successRate = $totalTxns > 0 ? round(($completed / $totalTxns) * 100, 2) : 100.0;

        return [
            'total_volume' => (float) ($volData['total_volume'] ?? 0.0),
            'total_txns'   => $totalTxns,
            'success_rate' => $successRate,
        ];
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE transactions SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
