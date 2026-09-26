<?php
/**
 * PaySim - Notification Model
 */

require_once dirname(__DIR__) . '/config/database.php';

class Notification {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type, is_read, action_url)
            VALUES (:user_id, :title, :message, :type, :is_read, :action_url)
        ");

        $stmt->execute([
            'user_id'    => $data['user_id'],
            'title'      => $data['title'],
            'message'    => $data['message'],
            'type'       => $data['type'] ?? 'info',
            'is_read'    => $data['is_read'] ?? 0,
            'action_url' => $data['action_url'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getUserNotifications(int $userId, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = :uid 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markAsRead(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid");
        return $stmt->execute(['id' => $id, 'uid' => $userId]);
    }

    public function markAllAsRead(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0");
        return $stmt->execute(['uid' => $userId]);
    }

    public function countUnread(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = :uid AND is_read = 0");
        $stmt->execute(['uid' => $userId]);
        return (int) ($stmt->fetch()['unread'] ?? 0);
    }

    public function broadcast(string $title, string $message, string $type = 'info', ?string $url = null): int {
        $usersStmt = $this->db->query("SELECT id FROM users WHERE status = 'active'");
        $users = $usersStmt->fetchAll();

        $count = 0;
        foreach ($users as $u) {
            $this->create([
                'user_id'    => $u['id'],
                'title'      => $title,
                'message'    => $message,
                'type'       => $type,
                'action_url' => $url,
            ]);
            $count++;
        }
        return $count;
    }
}
