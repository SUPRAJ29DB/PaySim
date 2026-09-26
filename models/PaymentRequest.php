<?php
/**
 * PaySim - Payment Request (UPI Collect) Model
 */

require_once dirname(__DIR__) . '/config/database.php';

class PaymentRequest {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO payment_requests (
                request_code, requester_id, payer_id, requester_upi, payer_upi, amount, note, status, expires_at
            ) VALUES (
                :request_code, :requester_id, :payer_id, :requester_upi, :payer_upi, :amount, :note, :status, :expires_at
            )
        ");

        $stmt->execute([
            'request_code'  => $data['request_code'],
            'requester_id'  => $data['requester_id'],
            'payer_id'      => $data['payer_id'] ?? null,
            'requester_upi' => $data['requester_upi'],
            'payer_upi'     => $data['payer_upi'],
            'amount'        => $data['amount'],
            'note'          => $data['note'] ?? '',
            'status'        => $data['status'] ?? 'pending',
            'expires_at'    => $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+3 days')),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM payment_requests WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByRequestCode(string $code): ?array {
        $stmt = $this->db->prepare("SELECT * FROM payment_requests WHERE request_code = :code LIMIT 1");
        $stmt->execute(['code' => trim($code)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUserRequests(int $userId, string $type = 'all'): array {
        if ($type === 'received') {
            $stmt = $this->db->prepare("
                SELECT r.*, u.full_name as requester_name 
                FROM payment_requests r
                LEFT JOIN users u ON u.id = r.requester_id
                WHERE r.payer_id = :uid 
                ORDER BY r.created_at DESC
            ");
            $stmt->execute(['uid' => $userId]);
        } elseif ($type === 'sent') {
            $stmt = $this->db->prepare("
                SELECT r.*, u.full_name as payer_name 
                FROM payment_requests r
                LEFT JOIN users u ON u.id = r.payer_id
                WHERE r.requester_id = :uid 
                ORDER BY r.created_at DESC
            ");
            $stmt->execute(['uid' => $userId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT r.*, u1.full_name as requester_name, u2.full_name as payer_name
                FROM payment_requests r
                LEFT JOIN users u1 ON u1.id = r.requester_id
                LEFT JOIN users u2 ON u2.id = r.payer_id
                WHERE r.requester_id = :uid1 OR r.payer_id = :uid2
                ORDER BY r.created_at DESC
            ");
            $stmt->execute(['uid1' => $userId, 'uid2' => $userId]);
        }

        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE payment_requests SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function getAll(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u1.full_name as requester_name, u2.full_name as payer_name
            FROM payment_requests r
            LEFT JOIN users u1 ON u1.id = r.requester_id
            LEFT JOIN users u2 ON u2.id = r.payer_id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
