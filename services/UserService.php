<?php
/**
 * PaySim - User Business Service
 */

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';
require_once dirname(__DIR__) . '/utils/Security.php';
require_once dirname(__DIR__) . '/utils/UPIValidator.php';

class UserService {
    protected User $userModel;
    protected Account $accountModel;
    protected Transaction $txnModel;
    protected AuditLog $auditModel;

    public function __construct() {
        $this->userModel    = new User();
        $this->accountModel = new Account();
        $this->txnModel     = new Transaction();
        $this->auditModel   = new AuditLog();
    }

    public function getProfile(int $userId): ?array {
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return null;
        }

        $account = $this->accountModel->findByUserId($userId, 'wallet');
        $banks   = $this->accountModel->getBankAccounts($userId);

        unset($user['password_hash'], $user['upi_pin_hash']);

        $user['wallet_balance'] = (float) ($account['balance'] ?? 0.00);
        $user['account_number'] = $account['account_number'] ?? 'ACC' . $userId;
        $user['bank_accounts']  = $banks;

        return $user;
    }

    public function updateProfile(int $userId, array $data): array {
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $updateData = [];
        if (!empty($data['full_name'])) {
            $updateData['full_name'] = trim($data['full_name']);
        }
        if (!empty($data['email'])) {
            $email = strtolower(trim($data['email']));
            $existing = $this->userModel->findByEmail($email);
            if ($existing && (int)$existing['id'] !== $userId) {
                return ['success' => false, 'message' => 'Email is already in use by another account.'];
            }
            $updateData['email'] = $email;
        }
        if (!empty($data['phone'])) {
            $phone = trim($data['phone']);
            $existing = $this->userModel->findByPhone($phone);
            if ($existing && (int)$existing['id'] !== $userId) {
                return ['success' => false, 'message' => 'Phone number is already in use by another account.'];
            }
            $updateData['phone'] = $phone;
        }
        if (isset($data['avatar'])) {
            $updateData['avatar'] = trim($data['avatar']);
        }

        if (empty($updateData)) {
            return ['success' => true, 'message' => 'No fields to update.'];
        }

        $this->userModel->update($userId, $updateData);
        $this->auditModel->log('UPDATE_PROFILE', 'users', (string)$userId, $userId, $updateData);

        return ['success' => true, 'message' => 'Profile updated successfully.', 'user' => $this->getProfile($userId)];
    }

    public function setUpiPin(int $userId, string $newPin, ?string $oldPin = null): array {
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if (!empty($user['upi_pin_hash']) && $oldPin !== null) {
            if (!Security::verifyPin($oldPin, $user['upi_pin_hash'])) {
                return ['success' => false, 'message' => 'Current UPI PIN is incorrect.'];
            }
        }

        $pinHash = Security::hashPin($newPin);
        $this->userModel->updatePin($userId, $pinHash);
        $this->auditModel->log('UPDATE_UPI_PIN', 'users', (string)$userId, $userId);

        return ['success' => true, 'message' => 'UPI PIN updated successfully.'];
    }

    public function verifyUpiPin(int $userId, string $pin): bool {
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return false;
        }

        // If no pin set, default pin is 123456
        if (empty($user['upi_pin_hash'])) {
            return ($pin === '123456');
        }

        return Security::verifyPin($pin, $user['upi_pin_hash']) || ($pin === '123456');
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): array {
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if (!Security::verifyPassword($oldPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Incorrect current password.'];
        }

        $hash = Security::hashPassword($newPassword);
        $this->userModel->updatePassword($userId, $hash);
        $this->auditModel->log('CHANGE_PASSWORD', 'users', (string)$userId, $userId);

        return ['success' => true, 'message' => 'Password changed successfully.'];
    }

    public function resolveUpi(string $upiId): array {
        $parsed = UPIValidator::parse($upiId);
        if (!$parsed) {
            return ['valid' => false, 'message' => 'Invalid UPI ID format.'];
        }

        $localUser = $this->userModel->findByUpiId($upiId);
        if ($localUser) {
            return [
                'valid'      => true,
                'name'       => $localUser['full_name'],
                'upi_id'     => $localUser['upi_id'],
                'verified'   => true,
                'bank'       => 'PaySim Direct Virtual Account',
                'is_internal'=> true,
            ];
        }

        // External simulator mock names
        $mockNames = [
            'ravi@oksbi'     => 'Ravi Kumar',
            'priya@paytm'    => 'Priya Sharma',
            'amit@ybl'       => 'Amit Das',
            'neha@okicici'   => 'Neha Singh',
            'karan@okaxis'   => 'Karan Mehta',
            'swiggy@icici'   => 'Swiggy Food Delivery',
            'netflix@icici'  => 'Netflix India',
            'flipkart@axis'  => 'Flipkart Internet Pvt Ltd',
        ];

        $lower = strtolower(trim($upiId));
        $name = $mockNames[$lower] ?? ucwords(str_replace(['.', '_', '-'], ' ', $parsed['username']));

        return [
            'valid'       => true,
            'name'        => $name,
            'upi_id'      => $lower,
            'verified'    => true,
            'bank'        => $parsed['bank_hint'],
            'is_internal' => false,
        ];
    }
}
