<?php
session_start();

// ─── Redirect if already logged in ─────────────────────────────────────────
if (isset($_SESSION['USER_ID'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// ─── Handle POST Login ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        require_once __DIR__ . '/config/app.php';
        require_once __DIR__ . '/services/AuthService.php';

        try {
            $authService = new AuthService();
            $authRes = $authService->login($username, $password);
        } catch (Throwable $e) {
            error_log('[PaySim login] ' . $e->getMessage());
            $authRes = ['success' => false, 'message' => 'Login service is temporarily unavailable. Check database configuration.'];
        }

        if ($authRes['success']) {
            header("Location: dashboard.php");
            exit();
        }

        $authenticated = false;
        if (!$authenticated && empty($error)) {
            $error = $authRes['message'] ?? 'Account not found. Please check your username.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PaySim</title>
    <meta name="description" content="Log in to PaySim, the UPI Payment Simulator. Manage your digital payments securely.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & Base ──────────────────────────────────────────────── */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary:    #0a0e1a;
            --bg-secondary:  #111827;
            --bg-card:       rgba(17, 24, 39, 0.7);
            --border:        rgba(99, 102, 241, 0.2);
            --border-focus:  rgba(99, 102, 241, 0.6);
            --text-primary:  #f1f5f9;
            --text-secondary:#94a3b8;
            --text-muted:    #64748b;
            --accent:        #6366f1;
            --accent-hover:  #818cf8;
            --accent-glow:   rgba(99, 102, 241, 0.35);
            --success:       #10b981;
            --error-bg:      rgba(239, 68, 68, 0.12);
            --error-border:  rgba(239, 68, 68, 0.4);
            --error-text:    #fca5a5;
            --radius:        12px;
            --radius-lg:     20px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ── Animated background orbs ──────────────────────────────────── */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.4;
            z-index: 0;
            animation: float 8s ease-in-out infinite alternate;
        }

        body::before {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            top: -150px;
            right: -100px;
        }

        body::after {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #8b5cf6 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            animation-delay: 4s;
        }

        @keyframes float {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -20px) scale(1.1); }
        }

        /* ── Login Container ───────────────────────────────────────────── */
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .login-card {
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 40px 36px;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.03),
                0 25px 50px -12px rgba(0,0,0,0.5);
            animation: cardEntry 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(24px);
        }

        @keyframes cardEntry {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Brand ─────────────────────────────────────────────────────── */
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px var(--accent-glow);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 8px 24px var(--accent-glow); }
            50%      { box-shadow: 0 8px 40px rgba(99,102,241,0.5); }
        }

        .brand-icon svg {
            width: 28px;
            height: 28px;
            color: #fff;
        }

        .brand h1 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-hover));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand p {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-top: 6px;
        }

        /* ── Error Alert ───────────────────────────────────────────────── */
        .error-alert {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s ease-in-out;
        }

        .error-alert svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            color: #ef4444;
        }

        .error-alert span {
            color: var(--error-text);
            font-size: 0.85rem;
            font-weight: 500;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }

        /* ── Form ──────────────────────────────────────────────────────── */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            transition: color 0.25s;
            pointer-events: none;
        }

        .input-wrapper input {
            width: 100%;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 13px 14px 13px 44px;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--text-primary);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
        }

        .input-wrapper input::placeholder {
            color: var(--text-muted);
        }

        .input-wrapper input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .input-wrapper input:focus ~ svg,
        .input-wrapper input:focus + svg {
            color: var(--accent-hover);
        }

        /* Password toggle */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.25s;
            padding: 4px;
            display: flex;
        }

        .toggle-password:hover {
            color: var(--text-secondary);
        }

        .toggle-password svg {
            position: static;
            transform: none;
            pointer-events: auto;
        }

        /* ── Utilities Row ─────────────────────────────────────────────── */
        .form-utils {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 0.825rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .remember-me input[type="checkbox"] {
            appearance: none;
            width: 16px;
            height: 16px;
            border: 1.5px solid var(--border);
            border-radius: 4px;
            background: var(--bg-secondary);
            cursor: pointer;
            position: relative;
            transition: background 0.2s, border-color 0.2s;
        }

        .remember-me input[type="checkbox"]:checked {
            background: var(--accent);
            border-color: var(--accent);
        }

        .remember-me input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            left: 4.5px;
            top: 1.5px;
            width: 5px;
            height: 9px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .forgot-link {
            color: var(--accent-hover);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        /* ── Submit Button ─────────────────────────────────────────────── */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.01em;
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.15) 50%, transparent 60%);
            transform: translateX(-100%);
            transition: transform 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 30px var(--accent-glow);
        }

        .btn-login:hover::before {
            transform: translateX(100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* ── Loading State ─────────────────────────────────────────────── */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin: 0 auto;
        }

        .btn-login.loading .btn-text { display: none; }
        .btn-login.loading .spinner  { display: block; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ── Divider ───────────────────────────────────────────────────── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Footer Link ───────────────────────────────────────────────── */
        .footer-text {
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .footer-text a {
            color: var(--accent-hover);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-text a:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        /* ── Demo Credentials Chip ─────────────────────────────────────── */
        .demo-hint {
            margin-top: 20px;
            text-align: center;
            padding: 10px 16px;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: var(--radius);
            font-size: 0.78rem;
            color: var(--text-secondary);
        }

        .demo-hint strong {
            color: var(--accent-hover);
        }

        /* ── Responsive ────────────────────────────────────────────────── */
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            </div>
            <h1>PaySim</h1>
            <p>Sign in to your UPI wallet</p>
        </div>

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
        <div class="error-alert" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" id="loginForm" autocomplete="on">

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        required
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-utils">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
                <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <span class="btn-text">Sign In</span>
                <div class="spinner"></div>
            </button>
        </form>

        <div class="divider">or</div>

        <p class="footer-text">
            Don't have an account? <a href="register.php">Create one</a>
        </p>

    </div>
</div>

<script>
    // Toggle password visibility
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            `;
        } else {
            input.type = 'password';
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            `;
        }
    }

    // Loading state on form submit
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('loading');
    });
</script>

</body>
</html>
