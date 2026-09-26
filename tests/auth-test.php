<?php
/**
 * PaySim - Authentication & User Registration Test Suite
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/services/AuthService.php';
require_once dirname(__DIR__) . '/services/UserService.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Account.php';

class AuthTest {
    private AuthService $authService;
    private UserService $userService;
    private User $userModel;
    private Account $accountModel;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct() {
        $this->authService  = new AuthService();
        $this->userService  = new UserService();
        $this->userModel     = new User();
        $this->accountModel = new Account();
    }

    private function assert(bool $condition, string $testName): void {
        if ($condition) {
            echo "  [PASS] {$testName}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$testName}\n";
            $this->failed++;
        }
    }

    public function run(): bool {
        echo "=== Running Authentication & User Lifecycle Test Suite ===\n";

        // 1. Password Hashing & Verification
        $plainPass = 'Secret@9876';
        $hashed = Security::hashPassword($plainPass);
        $this->assert(Security::verifyPassword($plainPass, $hashed), "Password hashing & verification working");
        $this->assert(!Security::verifyPassword('WrongPass123', $hashed), "Password verification rejects wrong password");

        // 2. User Registration
        $uniq = time() . '_' . random_int(100, 999);
        $testUser = [
            'full_name' => 'Test User ' . $uniq,
            'username'  => 'tuser_' . $uniq,
            'email'     => 'tuser_' . $uniq . '@test.com',
            'phone'     => '+91 9' . random_int(100000000, 999999999),
            'password'  => 'TestPass@123',
            'pin'       => '654321',
        ];

        $regResult = $this->authService->register($testUser);
        $this->assert($regResult['success'] === true, "User registration succeeds with valid data");
        $newUserId = (int) ($regResult['user']['id'] ?? 0);
        $this->assert($newUserId > 0, "Registered user created with valid ID ({$newUserId})");

        // 3. Verify Wallet Auto-Creation & Balance
        $wallet = $this->accountModel->findByUserId($newUserId, 'wallet');
        $this->assert($wallet !== null, "User wallet auto-created upon registration");
        $this->assert((float)$wallet['balance'] === 5000.00, "Promotional ₹5,000 welcome balance credited to new wallet");

        // 4. Duplicate Registration Prevention
        $dupResult = $this->authService->register($testUser);
        $this->assert($dupResult['success'] === false, "Duplicate username/email registration rejected");

        // 5. User Login with valid credentials
        $loginRes = $this->authService->login($testUser['username'], 'TestPass@123');
        $this->assert($loginRes['success'] === true, "Login succeeds with valid username and password");
        $this->assert(isset($_SESSION['USER_ID']) && (int)$_SESSION['USER_ID'] === $newUserId, "Session USER_ID set correctly");

        // 6. User Login with email identifier
        $emailLogin = $this->authService->login($testUser['email'], 'TestPass@123');
        $this->assert($emailLogin['success'] === true, "Login succeeds using registered email as identifier");

        // 7. Login with invalid password
        $failLogin = $this->authService->login($testUser['username'], 'WrongPassword999');
        $this->assert($failLogin['success'] === false, "Login rejected with incorrect password");

        // 8. UPI PIN Verification
        $this->assert($this->userService->verifyUpiPin($newUserId, '654321'), "UPI PIN verifies correctly");
        $this->assert(!$this->userService->verifyUpiPin($newUserId, '000000'), "Invalid UPI PIN rejected");

        // 9. Account Freeze Prevention
        $this->userModel->updateStatus($newUserId, 'frozen');
        $frozenLogin = $this->authService->login($testUser['username'], 'TestPass@123');
        $this->assert($frozenLogin['success'] === false, "Frozen account login successfully blocked");
        // Restore account to active
        $this->userModel->updateStatus($newUserId, 'active');

        // 10. Password Reset
        $resetRes = $this->authService->resetPassword($testUser['username'], 'BrandNewPass@456');
        $this->assert($resetRes['success'] === true, "Password reset succeeds");
        $newLogin = $this->authService->login($testUser['username'], 'BrandNewPass@456');
        $this->assert($newLogin['success'] === true, "Login succeeds with new password after reset");

        // 11. Admin Authentication
        $adminRes = $this->authService->adminLogin('nonexistent_admin_test', 'admin@123');
        $this->assert($adminRes['success'] === false, 'Unknown admin account and hardcoded password must be rejected.');

        // 12. Logout
        $this->authService->logout();
        $this->assert(!isset($_SESSION['USER_ID']), "Logout destroys user session successfully");

        echo "\nAuth Tests: {$this->passed} Passed, {$this->failed} Failed\n\n";
        return ($this->failed === 0);
    }
}

// Direct execution from CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new AuthTest();
    exit($test->run() ? 0 : 1);
}
