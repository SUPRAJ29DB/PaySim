<?php
/**
 * PaySim - Forgot & Reset Password
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/middleware/guest.php';

checkGuest();

$authService = new AuthService();
$error = '';
$success = '';
$step = 1; // 1: Identifier, 2: Simulated OTP, 3: New Password

$identifier = trim($_POST['identifier'] ?? ($_SESSION['reset_identifier'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'request_otp';

    if ($action === 'request_otp') {
        if (empty($identifier)) {
            $error = 'Please enter your username, email, phone, or UPI ID.';
        } else {
            // Find user
            $userModel = new User();
            $user = $userModel->findByUsername($identifier)
                ?: $userModel->findByEmail($identifier)
                ?: $userModel->findByPhone($identifier)
                ?: $userModel->findByUpiId($identifier);

            if (!$user) {
                $error = 'No PaySim account found matching that identifier.';
            } else {
                $_SESSION['reset_identifier'] = $identifier;
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['simulated_otp'] = (string) random_int(100000, 999999);
                $_SESSION['otp_expires_at'] = time() + 300;
                unset($_SESSION['otp_verified']);
                $step = 2;
                $success = 'A simulated one-time code was generated for this demo. It expires in 5 minutes.';
                if (defined('APP_ENV') && APP_ENV === 'development') { $success .= ' Development OTP: ' . $_SESSION['simulated_otp']; }
            }
        }
    } elseif ($action === 'verify_otp') {
        $enteredOtp = trim($_POST['otp'] ?? '');
        $expectedOtp = (string)($_SESSION['simulated_otp'] ?? '');

        if (empty($expectedOtp) || time() > (int)($_SESSION['otp_expires_at'] ?? 0)) {
            unset($_SESSION['simulated_otp'], $_SESSION['otp_verified']);
            $error = 'The one-time code expired. Request a new code.';
            $step = 1;
        } elseif (empty($enteredOtp)) {
            $error = 'Please enter the 6-digit OTP code.';
            $step = 2;
        } elseif (!hash_equals($expectedOtp, $enteredOtp)) {
            $error = 'Invalid one-time code. Please try again.';
            $step = 2;
        } else {
            $_SESSION['otp_verified'] = true;
            $step = 3;
            $success = 'OTP verified successfully! Please choose a new secure password.';
        }
    } elseif ($action === 'reset_password') {
        if (empty($_SESSION['otp_verified']) || empty($_SESSION['reset_user_id']) || (int)$_SESSION['reset_user_id'] < 1) {
            $error = 'Verify the one-time code before resetting your password.';
            $step = 1;
        } else {
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($newPassword) || strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters long.';
            $step = 3;
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match.';
            $step = 3;
        } else {
            $res = $authService->resetPassword($identifier, $newPassword);
            if ($res['success']) {
                $step = 4; // Complete
                $success = 'Your password has been reset successfully! You can now log in with your new credentials.';
                unset($_SESSION['reset_identifier'], $_SESSION['reset_user_id'], $_SESSION['simulated_otp'], $_SESSION['otp_expires_at'], $_SESSION['otp_verified']);
            } else {
                $error = $res['message'];
                $step = 3;
            }
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — PaySim</title>
    <meta name="description" content="Recover and reset your PaySim account password securely.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
        .step-progress-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .step-pill {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-muted);
        }
        .step-pill.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 0 12px var(--accent-glow);
        }
        .step-pill.done {
            background: var(--success);
            color: #fff;
            border-color: var(--success);
        }
        .step-line-mini {
            width: 30px;
            height: 2px;
            background: rgba(255,255,255,0.1);
        }
        .step-line-mini.done {
            background: var(--success);
        }
        .otp-display-badge {
            background: rgba(99, 102, 241, 0.12);
            border: 1px dashed var(--accent);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .otp-display-badge strong {
            color: var(--accent-hover);
            font-family: var(--font-mono);
            font-size: 1.15rem;
            letter-spacing: 0.15em;
        }
    </style>
</head>
<body>
    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="auth-title">Account Recovery</h1>
                <p class="auth-subtitle">Reset your PaySim account password safely</p>
            </div>

            <!-- Steps Progress Indicator -->
            <div class="step-progress-row">
                <div class="step-pill <?php echo $step > 1 ? 'done' : ($step === 1 ? 'active' : ''); ?>">1</div>
                <div class="step-line-mini <?php echo $step > 1 ? 'done' : ''; ?>"></div>
                <div class="step-pill <?php echo $step > 2 ? 'done' : ($step === 2 ? 'active' : ''); ?>">2</div>
                <div class="step-line-mini <?php echo $step > 2 ? 'done' : ''; ?>"></div>
                <div class="step-pill <?php echo $step >= 4 ? 'done' : ($step === 3 ? 'active' : ''); ?>">3</div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-error">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success) && $step !== 4): ?>
                <div class="auth-success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: Identifier Input -->
                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="action" value="request_otp">
                    <div class="form-group">
                        <label class="form-label" for="identifier">Username, Email, Phone, or UPI ID</label>
                        <input type="text" id="identifier" name="identifier" class="form-control" placeholder="e.g. suprakash or suprakash@paysim" value="<?php echo htmlspecialchars($identifier); ?>" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                        Send Verification Code
                    </button>
                </form>

            <?php elseif ($step === 2): ?>
                <!-- Step 2: OTP Verification -->
                <div class="otp-display-badge">
                    Demo OTP is generated dynamically in development mode.
                </div>

                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="action" value="verify_otp">
                    <div class="form-group">
                        <label class="form-label" for="otp">Enter 6-Digit OTP</label>
                        <input type="text" id="otp" name="otp" class="form-control" placeholder="6-digit code" maxlength="6" style="letter-spacing: 0.25em; font-family: var(--font-mono); font-size: 1.25rem; text-align: center;" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                        Verify Code & Continue
                    </button>
                </form>

            <?php elseif ($step === 3): ?>
                <!-- Step 3: Set New Password -->
                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="At least 6 characters" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                        Save New Password
                    </button>
                </form>

            <?php elseif ($step === 4): ?>
                <!-- Step 4: Completion -->
                <div style="text-align: center; padding: 20px 0;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--success); color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.8rem; box-shadow: 0 0 25px rgba(16, 185, 129, 0.4);">
                        ✓
                    </div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 10px; color: var(--text-primary);">Password Reset Complete</h3>
                    <p style="color: var(--text-secondary); font-size: 0.92rem; margin-bottom: 24px;">Your password has been changed. You can now log into your PaySim account.</p>
                    <a href="login.php" class="btn btn-primary" style="width: 100%;">Proceed to Login</a>
                </div>
            <?php endif; ?>

            <div class="auth-footer">
                Remembered your password? <a href="login.php" class="auth-link">Return to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
