<?php
/**
 * PaySim - Administration & Compliance Business Service
 */

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/PaymentRequest.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';

class AdminService {
    protected User $userModel;
    protected Account $accountModel;
    protected Transaction $txnModel;
    protected PaymentRequest $requestModel;
    protected Notification $notifModel;
    protected AuditLog $auditModel;

    public function __construct() {
        $this->userModel    = new User();
        $this->accountModel = new Account();
        $this->txnModel     = new Transaction();
        $this->requestModel = new PaymentRequest();
        $this->notifModel   = new Notification();
        $this->auditModel   = new AuditLog();
    }

    public function getDashboardStatistics(): array {
        $metrics = $this->txnModel->getMetrics();
        $totalUsers = $this->userModel->countAll();

        $db = Database::getInstance()->getConnection();
        $frozenUsersStmt = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'frozen'");
        $frozenUsers = (int) ($frozenUsersStmt->fetch()['count'] ?? 0);

        $pendingReqStmt = $db->query("SELECT COUNT(*) as count FROM payment_requests WHERE status = 'pending'");
        $pendingReq = (int) ($pendingReqStmt->fetch()['count'] ?? 0);

        return [
            'total_volume'     => $metrics['total_volume'],
            'total_users'      => $totalUsers,
            'total_txns'       => $metrics['total_txns'],
            'success_rate'     => $metrics['success_rate'],
            'pending_requests' => $pendingReq,
            'flagged_risk'     => $frozenUsers,
        ];
    }

    public function getUsers(int $page = 1, int $perPage = 50, string $search = ''): array {
        $offset = ($page - 1) * $perPage;
        $users = $this->userModel->getAll($perPage, $offset, $search);
        $total = $this->userModel->countAll($search);

        return [
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_records'=> $total,
                'total_pages'  => ceil($total / max(1, $perPage)),
            ],
        ];
    }

    public function getUserDetails(int $userId): ?array {
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return null;
        }

        $wallet = $this->accountModel->findByUserId($userId, 'wallet');
        $banks  = $this->accountModel->getBankAccounts($userId);
        $txns   = $this->txnModel->getUserTransactions($userId, 10, 0);

        unset($user['password_hash'], $user['upi_pin_hash']);

        return [
            'user'         => $user,
            'wallet'       => $wallet,
            'bank_accounts'=> $banks,
            'recent_txns'  => $txns,
        ];
    }

    public function freezeUser(int $userId, string $reason = 'Suspicious activity detected'): bool {
        $this->userModel->updateStatus($userId, 'frozen');
        $this->notifModel->create([
            'user_id' => $userId,
            'title'   => 'Account Suspended',
            'message' => 'Your account has been temporarily frozen by PaySim Security: ' . $reason,
            'type'    => 'security',
        ]);
        $this->auditModel->log('FREEZE_USER', 'users', (string)$userId, null, ['reason' => $reason]);
        return true;
    }

    public function unfreezeUser(int $userId): bool {
        $this->userModel->updateStatus($userId, 'active');
        $this->notifModel->create([
            'user_id' => $userId,
            'title'   => 'Account Restored',
            'message' => 'Your PaySim account has been reactivated. You can now resume transactions.',
            'type'    => 'success',
        ]);
        $this->auditModel->log('UNFREEZE_USER', 'users', (string)$userId, null);
        return true;
    }

    public function getAllTransactions(int $page = 1, int $perPage = 50, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $txns = $this->txnModel->getAllTransactions($perPage, $offset, $filters);
        $total = $this->txnModel->countAllTransactions($filters);

        return [
            'transactions' => $txns,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_records'=> $total,
                'total_pages'  => ceil($total / max(1, $perPage)),
            ],
        ];
    }

    public function getAuditLogs(int $page = 1, int $perPage = 50): array {
        $offset = ($page - 1) * $perPage;
        $logs = $this->auditModel->getAll($perPage, $offset);
        $total = $this->auditModel->countAll();

        return [
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_records'=> $total,
                'total_pages'  => ceil($total / max(1, $perPage)),
            ],
        ];
    }
}
