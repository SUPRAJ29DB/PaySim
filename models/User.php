<?php
/**
 * PaySim - User Model
 */

require_once dirname(__DIR__) . '/config/database.php';

class User {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(:username) LIMIT 1");
        $stmt->execute(['username' => trim($username)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
        $stmt->execute(['email' => trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByPhone(string $phone): ?array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE REPLACE(REPLACE(phone, ' ', ''), '+', '') LIKE :phone LIMIT 1");
        $stmt->execute(['phone' => '%' . substr($cleanPhone, -10)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUpiId(string $upiId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(upi_id) = LOWER(:upi_id) LIMIT 1");
        $stmt->execute(['upi_id' => trim($upiId)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, username, email, phone, password_hash, upi_id, upi_pin_hash, role, status)
            VALUES (:full_name, :username, :email, :phone, :password_hash, :upi_id, :upi_pin_hash, :role, :status)
        ");

        $stmt->execute([
            'full_name'     => $data['full_name'],
            'username'      => strtolower(trim($data['username'])),
            'email'         => strtolower(trim($data['email'])),
            'phone'         => trim($data['phone']),
            'password_hash' => $data['password_hash'],
            'upi_id'        => strtolower(trim($data['upi_id'])),
            'upi_pin_hash'  => $data['upi_pin_hash'] ?? null,
            'role'          => $data['role'] ?? 'user',
            'status'        => $data['status'] ?? 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $val) {
            if ($key !== 'id') {
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $val;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updatePin(int $id, string $pinHash): bool {
        $stmt = $this->db->prepare("UPDATE users SET upi_pin_hash = :pin_hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['pin_hash' => $pinHash, 'id' => $id]);
    }

    public function updatePassword(int $id, string $passwordHash): bool {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['hash' => $passwordHash, 'id' => $id]);
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE users SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function getAll(int $limit = 50, int $offset = 0, string $search = ''): array {
        if (!empty($search)) {
            $stmt = $this->db->prepare("
                SELECT u.*, a.balance as wallet_balance
                FROM users u
                LEFT JOIN accounts a ON a.user_id = u.id AND a.account_type = 'wallet'
                WHERE u.full_name LIKE :s1 OR u.username LIKE :s2 OR u.email LIKE :s3 OR u.upi_id LIKE :s4
                ORDER BY u.id DESC
                LIMIT :limit OFFSET :offset
            ");
            $like = "%{$search}%";
            $stmt->bindValue(':s1', $like);
            $stmt->bindValue(':s2', $like);
            $stmt->bindValue(':s3', $like);
            $stmt->bindValue(':s4', $like);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("
                SELECT u.*, a.balance as wallet_balance
                FROM users u
                LEFT JOIN accounts a ON a.user_id = u.id AND a.account_type = 'wallet'
                ORDER BY u.id DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int {
        if (!empty($search)) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM users
                WHERE full_name LIKE :s1 OR username LIKE :s2 OR email LIKE :s3 OR upi_id LIKE :s4
            ");
            $like = "%{$search}%";
            $stmt->execute([':s1' => $like, ':s2' => $like, ':s3' => $like, ':s4' => $like]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        }
        return (int) ($stmt->fetch()['count'] ?? 0);
    }
}
