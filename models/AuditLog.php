<?php
/**
 * PaySim - Audit Log Model for Security & Admin Telemetry
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/utils/Security.php';

class AuditLog {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function log(string $action, string $entityType, ?string $entityId = null, ?int $userId = null, $payload = null): int {
        $ip = Security::getClientIp();
        $ua = Security::getUserAgent();
        $payloadJson = is_array($payload) || is_object($payload) ? json_encode($payload, JSON_UNESCAPED_SLASHES) : (string)$payload;

        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, payload)
            VALUES (:uid, :action, :etype, :eid, :ip, :ua, :payload)
        ");

        $stmt->execute([
            'uid'     => $userId,
            'action'  => strtoupper($action),
            'etype'   => $entityType,
            'eid'     => $entityId,
            'ip'      => $ip,
            'ua'      => substr($ua, 0, 500),
            'payload' => $payloadJson,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getAll(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT a.*, u.full_name, u.username 
            FROM audit_logs a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.created_at DESC, a.id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM audit_logs");
        return (int) ($stmt->fetch()['count'] ?? 0);
    }
}
