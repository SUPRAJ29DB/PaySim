<?php
/**
 * PaySim - Notification Dispatcher Service
 */

require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/utils/Helpers.php';

class NotificationService {
    protected Notification $notifModel;

    public function __construct() {
        $this->notifModel = new Notification();
    }

    public function getUserNotifications(int $userId, int $limit = 50): array {
        $notifs = $this->notifModel->getUserNotifications($userId, $limit);
        $formatted = [];
        foreach ($notifs as $n) {
            $formatted[] = [
                'id'         => (int)$n['id'],
                'title'      => $n['title'],
                'message'    => $n['message'],
                'type'       => $n['type'],
                'is_read'    => (bool)$n['is_read'],
                'action_url' => $n['action_url'],
                'date'       => date('d M Y, h:i A', strtotime($n['created_at'])),
                'time_ago'   => Helpers::timeAgo($n['created_at']),
            ];
        }
        return $formatted;
    }

    public function markRead(int $id, int $userId): bool {
        return $this->notifModel->markAsRead($id, $userId);
    }

    public function markAllRead(int $userId): bool {
        return $this->notifModel->markAllAsRead($userId);
    }

    public function getUnreadCount(int $userId): int {
        return $this->notifModel->countUnread($userId);
    }

    public function send(int $userId, string $title, string $message, string $type = 'info', ?string $url = null): int {
        return $this->notifModel->create([
            'user_id'    => $userId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'action_url' => $url,
        ]);
    }

    public function broadcast(string $title, string $message, string $type = 'info', ?string $url = null): int {
        return $this->notifModel->broadcast($title, $message, $type, $url);
    }
}
