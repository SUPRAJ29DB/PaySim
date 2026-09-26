<?php
session_start();

// ─── Redirect if already logged in ─────────────────────────────────────────
if (isset($_SESSION['USER_ID'])) {
    header("Location: dashboard.php");
    exit();
}

// ─── Handle Registration ────────────────────────────────────────────────────
$error   = '';
$success = '';
$formData = [
    'full_name' => '',
    'username'  => '',
    'email'     => '',
    'phone'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $agree    = isset($_POST['agree']);

    $formData = compact('full_name', 'username', 'email', 'phone');
    $formData['full_name'] = $fullName;

    // Validation
    if (empty($fullName) || empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = 'Username must be 3-20 characters (letters, numbers, underscores).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!$agree) {
        $error = 'You must agree to the Terms & Privacy Policy.';
    } else {
        require_once __DIR__ . '/config/app.php';
        require_once __DIR__ . '/services/AuthService.php';

        $authService = new AuthService();
        $regRes = $authService->register([
            'full_name' => $fullName,
            'username'  => $username,
            'email'     => $email,
            'phone'     => $phone,
            'password'  => $password,
            'pin'       => '123456',
        ]);

        if ($regRes['success']) {
            $user = $regRes['user'];
            $_SESSION['USER_ID']  = (int) $user['id'];
            $_SESSION['USERNAME'] = $user['username'];
            $_SESSION['NAME']     = $user['full_name'];
            $_SESSION['UPI_ID']   = $user['upi_id'];
            if (!headers_sent()) {
                session_regenerate_id(true);
            }
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $regRes['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — PaySim</title>
    <meta name="description" content="Create your PaySim account — the UPI Payment Simulator. Start sending and receiving money instantly.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

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
            --success-bg:    rgba(16, 185, 129, 0.1);
            --success-border:rgba(16, 185, 129, 0.3);
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
            overflow-x: hidden;
            position: relative;
            padding: 40px 20px;
        }

        /* ── Animated background orbs ──────────────────────────────────── */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.4;
            z-index: 0;
            animation: float 8s ease-in-out infinite alternate;
        }

        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            top: -150px; right: -100px;
        }

        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #8b5cf6 0%, transparent 70%);
            bottom: -100px; left: -100px;
            animation-delay: 4s;
        }

        @keyframes float {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -20px) scale(1.1); }
        }

        /* ── Register Container ───────────────────────────────────────── */
        .register-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
        }

        .register-card {
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
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Brand ─────────────────────────────────────────────────────── */
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 56px; height: 56px;
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

        .brand-icon svg { width: 28px; height: 28px; color: #fff; }

        .brand h1 {
            font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-hover));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        .brand p { color: var(--text-secondary); font-size: 0.875rem; margin-top: 6px; }

        /* ── Alerts ────────────────────────────────────────────────────── */
        .alert {
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .alert svg { flex-shrink: 0; width: 18px; height: 18px; }

        .alert--error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
            animation: shake 0.4s ease-in-out;
        }

        .alert--error svg { color: #ef4444; }

        .alert--success {
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            color: #34d399;
        }

        .alert--success svg { color: var(--success); }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }

        /* ── Form ──────────────────────────────────────────────────────── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper svg.input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px; height: 18px;
            color: var(--text-muted);
            transition: color 0.25s;
            pointer-events: none;
        }

        .input-wrapper input {
            width: 100%;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 14px 12px 42px;
            font-family: inherit;
            font-size: 0.9rem;
            color: var(--text-primary);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
        }

        .input-wrapper input::placeholder { color: var(--text-muted); }

        .input-wrapper input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .input-wrapper input:focus ~ svg.input-icon,
        .input-wrapper input:focus + svg.input-icon {
            color: var(--accent-hover);
        }

        /* UPI Preview */
        .upi-preview {
            margin-top: 6px;
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.25s;
        }

        .upi-preview span {
            color: var(--accent-hover);
            font-weight: 600;
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

        .toggle-password:hover { color: var(--text-secondary); }
        .toggle-password svg { width: 18px; height: 18px; }

        /* Password strength */
        .password-strength {
            margin-top: 8px;
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .strength-bar {
            flex: 1;
            height: 3px;
            border-radius: 2px;
            background: rgba(99,102,241,0.1);
            overflow: hidden;
            transition: background 0.3s;
        }

        .strength-bar.active-1 { background: #ef4444; }
        .strength-bar.active-2 { background: #f59e0b; }
        .strength-bar.active-3 { background: #f59e0b; }
        .strength-bar.active-4 { background: var(--success); }

        .strength-label {
            font-size: 0.68rem;
            font-weight: 600;
            margin-left: 8px;
            min-width: 50px;
        }

        /* ── Checkbox ─────────────────────────────────────────────────── */
        .agree-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 24px;
            font-size: 0.8rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .agree-row input[type="checkbox"] {
            appearance: none;
            width: 18px; height: 18px;
            border: 1.5px solid var(--border);
            border-radius: 5px;
            background: var(--bg-secondary);
            cursor: pointer;
            position: relative;
            transition: background 0.2s, border-color 0.2s;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .agree-row input[type="checkbox"]:checked {
            background: var(--accent);
            border-color: var(--accent);
        }

        .agree-row input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            left: 5px; top: 2px;
            width: 5px; height: 9px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .agree-row a {
            color: var(--accent-hover);
            text-decoration: none;
            font-weight: 500;
        }

        .agree-row a:hover { text-decoration: underline; }

        /* ── Submit Button ─────────────────────────────────────────────── */
        .btn-register {
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

        .btn-register::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.15) 50%, transparent 60%);
            transform: translateX(-100%);
            transition: transform 0.5s;
        }

        .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 30px var(--accent-glow);
        }

        .btn-register:hover::before { transform: translateX(100%); }
        .btn-register:active { transform: translateY(0); }

        .btn-register.loading { pointer-events: none; opacity: 0.8; }
        .btn-register .spinner {
            display: none;
            width: 20px; height: 20px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin: 0 auto;
        }
        .btn-register.loading .btn-text { display: none; }
        .btn-register.loading .spinner { display: block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Divider ───────────────────────────────────────────────────── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Footer ────────────────────────────────────────────────────── */
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

        .footer-text a:hover { color: var(--accent); text-decoration: underline; }

        /* ── Features Pills ───────────────────────────────────────────── */
        .features {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            background: rgba(99,102,241,0.08);
            border: 1px solid rgba(99,102,241,0.12);
            border-radius: 20px;
            font-size: 0.7rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .feature-pill svg { width: 12px; height: 12px; color: var(--accent-hover); }

        /* ── Responsive ────────────────────────────────────────────────── */
        @media (max-width: 520px) {
            .register-card { padding: 32px 24px; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>
<body>

<div class="register-wrapper">
    <div class="register-card">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            </div>
            <h1>Create Account</h1>
            <p>Join PaySim and start transacting</p>
        </div>

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
        <div class="alert alert--error" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="register.php" id="registerForm" autocomplete="on">

            <!-- Full Name -->
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <div class="input-wrapper">
                    <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required autocomplete="name" value="<?php echo htmlspecialchars($formData['full_name']); ?>">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
            </div>

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Choose a username" required autocomplete="username" pattern="[a-zA-Z0-9_]{3,20}" value="<?php echo htmlspecialchars($formData['username']); ?>" oninput="updateUPIPreview()">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" />
                    </svg>
                </div>
                <div class="upi-preview" id="upiPreview">
                    Your UPI ID: <span id="upiIdPreview">username@paysim</span>
                </div>
            </div>

            <!-- Email & Phone -->
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" placeholder="you@email.com" required autocomplete="email" value="<?php echo htmlspecialchars($formData['email']); ?>">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted);">(optional)</span></label>
                    <div class="input-wrapper">
                        <input type="tel" id="phone" name="phone" placeholder="+91 XXXXX XXXXX" autocomplete="tel" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Min 6 characters" required minlength="6" autocomplete="new-password" oninput="checkStrength()">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')" aria-label="Toggle password">
                        <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
                <div class="password-strength" id="strengthIndicator" style="display:none;">
                    <div class="strength-bar" id="bar1"></div>
                    <div class="strength-bar" id="bar2"></div>
                    <div class="strength-bar" id="bar3"></div>
                    <div class="strength-bar" id="bar4"></div>
                    <span class="strength-label" id="strengthLabel"></span>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required autocomplete="new-password">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')" aria-label="Toggle confirm password">
                        <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Agree -->
            <label class="agree-row">
                <input type="checkbox" name="agree" required>
                <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
            </label>

            <!-- Submit -->
            <button type="submit" class="btn-register" id="registerBtn">
                <span class="btn-text">Create Account</span>
                <div class="spinner"></div>
            </button>
        </form>

        <div class="divider">or</div>

        <p class="footer-text">
            Already have an account? <a href="login.php">Sign in</a>
        </p>

        <div class="features">
            <span class="feature-pill">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
                Instant UPI
            </span>
            <span class="feature-pill">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                Secure
            </span>
            <span class="feature-pill">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                Cashback
            </span>
        </div>
    </div>
</div>

<script>
    // ─── UPI Preview ────────────────────────────────────────────────
    function updateUPIPreview() {
        const username = document.getElementById('username').value.trim().toLowerCase();
        const preview = document.getElementById('upiIdPreview');
        preview.textContent = (username || 'username') + '@paysim';
    }

    // ─── Password Strength ──────────────────────────────────────────
    function checkStrength() {
        const password = document.getElementById('password').value;
        const indicator = document.getElementById('strengthIndicator');
        const label = document.getElementById('strengthLabel');

        if (password.length === 0) {
            indicator.style.display = 'none';
            return;
        }

        indicator.style.display = 'flex';

        let score = 0;
        if (password.length >= 6)  score++;
        if (password.length >= 10) score++;
        if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) score++;

        const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['', '#ef4444', '#f59e0b', '#f59e0b', '#10b981'];

        for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById('bar' + i);
            bar.className = 'strength-bar';
            if (i <= score) bar.classList.add('active-' + score);
        }

        label.textContent = labels[score] || '';
        label.style.color = colors[score] || '';
    }

    // ─── Toggle Password Visibility ─────────────────────────────────
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    // ─── Loading State ──────────────────────────────────────────────
    document.getElementById('registerForm').addEventListener('submit', function () {
        document.getElementById('registerBtn').classList.add('loading');
    });
</script>

</body>
</html>
