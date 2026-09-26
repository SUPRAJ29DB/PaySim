<?php
/**
 * PaySim - Authentication Business Service
 */

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/utils/Security.php';
require_once dirname(__DIR__) . '/utils/TransactionID.php';

class AuthService {
    protected User $userModel;
    protected Account $accountModel;
    protected AuditLog $auditModel;
    protected Notification $notifModel;

    public function __construct() {
        $this->userModel    = new User();
        $this->accountModel = new Account();
        $this->auditModel   = new AuditLog();
        $this->notifModel   = new Notification();
    }

    public function register(array $data): array {
        $username = strtolower(trim($data['username'] ?? ''));
        $email    = strtolower(trim($data['email'] ?? ''));
        $phone    = trim($data['phone'] ?? '');
        $fullName = trim($data['full_name'] ?? '');
        $password = $data['password'] ?? '';
        $pin      = $data['pin'] ?? '123456';

        if ($this->userModel->findByUsername($username)) {
            return ['success' => false, 'message' => "Username '{$username}' is already taken."];
        }
        if ($this->userModel->findByEmail($email)) {
            return ['success' => false, 'message' => "Email '{$email}' is already registered."];
        }
        if ($this->userModel->findByPhone($phone)) {
            return ['success' => false, 'message' => "Phone number '{$phone}' is already registered."];
        }

        $upiId = $username . '@paysim';
        $passwordHash = Security::hashPassword($password);
        $pinHash = Security::hashPin($pin);

        $userId = $this->userModel->create([
            'full_name'     => $fullName,
            'username'      => $username,
            'email'         => $email,
            'phone'         => $phone,
            'password_hash' => $passwordHash,
            'upi_id'        => $upiId,
            'upi_pin_hash'  => $pinHash,
            'role'          => 'user',
            'status'        => 'active',
        ]);

        // Create default wallet with initial promotional balance of ₹5,000.00
        $accNo = TransactionID::generateAccountNumber();
        $this->accountModel->create([
            'user_id'        => $userId,
            'account_number' => $accNo,
            'account_type'   => 'wallet',
            'balance'        => 5000.00,
            'currency'       => 'INR',
            'status'         => 'active',
        ]);

        // Link default bank account
        $this->accountModel->addBankAccount([
            'user_id'        => $userId,
            'bank_name'      => 'State Bank of India',
            'account_number' => '•••• ' . random_int(1000, 9999),
            'ifsc_code'      => 'SBIN0001092',
            'branch'         => 'Digital Branch, Mumbai',
            'account_type'   => 'Savings Account',
            'balance'        => 75000.00,
            'is_primary'     => 1,
            'color_gradient' => 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
        ]);

        // Notifications & Audit Log
        $this->notifModel->create([
            'user_id' => $userId,
            'title'   => 'Welcome to PaySim!',
            'message' => 'Your virtual UPI ID ' . $upiId . ' is now active with ₹5,000.00 welcome balance.',
            'type'    => 'success',
        ]);

        $this->auditModel->log('USER_REGISTER', 'users', (string)$userId, $userId, ['username' => $username, 'upi' => $upiId]);

        $user = $this->userModel->findById($userId);
        return ['success' => true, 'message' => 'Registration successful.', 'user' => $user];
    }

    public function login(string $username, string $password): array {
        $user = $this->userModel->findByUsername($username);
        if (!$user) {
            // Check if user entered email or phone instead
            $user = $this->userModel->findByEmail($username) ?: $this->userModel->findByPhone($username);
        }

        if (!$user) {
            return ['success' => false, 'message' => 'Account not found. Please check your credentials.'];
        }

        if ($user['status'] === 'frozen') {
            return ['success' => false, 'message' => 'Your account has been frozen by security administration.'];
        }
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is currently ' . $user['status'] . '.'];
        }

        if (!Security::verifyPassword($password, $user['password_hash'])) {
            $this->auditModel->log('LOGIN_FAILED', 'users', (string)$user['id'], $user['id'], ['username' => $username]);
            return ['success' => false, 'message' => 'Invalid password. Please try again.'];
        }

        // Set Session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['USER_ID']  = (int) $user['id'];
        $_SESSION['USERNAME'] = $user['username'];
        $_SESSION['NAME']     = $user['full_name'];
        $_SESSION['UPI_ID']   = $user['upi_id'];
        $_SESSION['ROLE']     = $user['role'];
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $this->auditModel->log('USER_LOGIN', 'users', (string)$user['id'], $user['id']);

        return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
    }

    public function adminLogin(string $username, string $password): array {
        $user = $this->userModel->findByUsername(strtolower(trim($username)));
        if (!$user
            || !in_array(($user['role'] ?? ''), ['admin', 'superadmin'], true)
            || ($user['status'] ?? '') !== 'active'
            || !Security::verifyPassword($password, (string)($user['password_hash'] ?? ''))) {
            return ['success' => false, 'message' => 'Invalid administrator credentials.'];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $adminData = [
            'id' => (int)$user['id'],
            'name' => (string)$user['full_name'],
            'email' => (string)$user['email'],
            'role' => ($user['role'] === 'superadmin') ? 'Super Admin' : 'Admin',
        ];
        $_SESSION['ADMIN_LOGGED_IN'] = true;
        $_SESSION['ADMIN_ID'] = $adminData['id'];
        $_SESSION['ADMIN_NAME'] = $adminData['name'];
        $_SESSION['ADMIN_EMAIL'] = $adminData['email'];
        $_SESSION['ADMIN_ROLE'] = $adminData['role'];
        $this->auditModel->log('ADMIN_LOGIN', 'admin', (string)$adminData['id'], $adminData['id']);
        return ['success' => true, 'message' => 'Admin authentication successful.', 'admin' => $adminData];
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['USER_ID'] ?? null;
        if ($userId) {
            $this->auditModel->log('USER_LOGOUT', 'users', (string)$userId, $userId);
        }
        unset($_SESSION['USER_ID'], $_SESSION['USERNAME'], $_SESSION['NAME'], $_SESSION['UPI_ID'], $_SESSION['ROLE']);
        session_destroy();
    }

    public function adminLogout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['ADMIN_LOGGED_IN'], $_SESSION['ADMIN_ID'], $_SESSION['ADMIN_NAME'], $_SESSION['ADMIN_EMAIL'], $_SESSION['ADMIN_ROLE']);
    }

    public function resetPassword(string $identifier, string $newPassword): array {
        $user = $this->userModel->findByUsername($identifier)
            ?: $this->userModel->findByEmail($identifier)
            ?: $this->userModel->findByPhone($identifier)
            ?: $this->userModel->findByUpiId($identifier);

        if (!$user) {
            return ['success' => false, 'message' => 'Account not found for provided identifier.'];
        }

        $hash = Security::hashPassword($newPassword);
        $this->userModel->updatePassword($user['id'], $hash);
        $this->auditModel->log('PASSWORD_RESET', 'users', (string)$user['id'], $user['id']);

        $this->notifModel->create([
            'user_id' => $user['id'],
            'title'   => 'Password Changed',
            'message' => 'Your PaySim account password was updated successfully.',
            'type'    => 'security',
        ]);

        return ['success' => true, 'message' => 'Password reset successfully. You can now login.'];
    }
}
