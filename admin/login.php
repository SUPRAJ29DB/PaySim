<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/services/AuthService.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (!empty($_SESSION['ADMIN_LOGGED_IN'])) { header('Location: dashboard.php'); exit; }
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['admin_login'])) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $result = (new AuthService())->adminLogin($username, $password);
            if (!empty($result['success'])) { header('Location: dashboard.php'); exit; }
            $error = $result['message'] ?? 'Invalid administrator credentials.';
        } catch (Throwable $e) {
            error_log('[PaySim admin login] ' . $e->getMessage());
            $error = 'Unable to authenticate right now. Check database configuration.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login — PaySim</title>
    <meta name="description" content="Secure Administrator Authentication Portal for PaySim UPI Platform.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-primary:     #050811;
            --bg-card:        rgba(13, 17, 30, 0.75);
            --border:         rgba(239, 68, 68, 0.2);
            --border-focus:   rgba(239, 68, 68, 0.6);
            --text-primary:   #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted:     #64748b;
            --admin-accent:   #ef4444;
            --admin-glow:     rgba(239, 68, 68, 0.35);
            --indigo:         #6366f1;
            --radius:         16px;
        }

        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; padding: 20px; }

        .bg-orb { position:fixed; border-radius:50%; filter:blur(130px); opacity:0.3; z-index:0; pointer-events:none; }
        .bg-orb--1 { width:550px;height:550px;background:radial-gradient(circle,var(--admin-accent) 0%,transparent 70%);top:-150px;right:-150px; }
        .bg-orb--2 { width:450px;height:450px;background:radial-gradient(circle,var(--indigo) 0%,transparent 70%);bottom:-150px;left:-150px; }

        .login-card { width:100%;max-width:440px;background:var(--bg-card);backdrop-filter:blur(30px);-webkit-backdrop-filter:blur(30px);border:1px solid var(--border);border-radius:var(--radius);padding:40px 36px;box-shadow:0 20px 60px rgba(0,0,0,0.6);position:relative;z-index:1; }
        
        .brand-badge { display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:28px; }
        .brand-icon { width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--admin-accent),#dc2626);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px var(--admin-glow); }
        .brand-icon svg { width:26px;height:26px;color:#fff; }
        .brand-title h2 { font-size:1.4rem;font-weight:800;letter-spacing:-0.02em; }
        .brand-title span { font-size:0.72rem;color:var(--admin-accent);text-transform:uppercase;letter-spacing:0.12em;font-weight:700; }

        .alert-error { background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:var(--admin-accent);padding:12px 16px;border-radius:10px;font-size:0.85rem;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:10px; }

        .form-group { margin-bottom:20px; }
        .form-label { display:block;font-size:0.8rem;color:var(--text-secondary);font-weight:600;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.04em; }
        .form-input { width:100%;background:rgba(0,0,0,0.4);border:1.5px solid rgba(255,255,255,0.1);border-radius:12px;padding:14px 16px;color:var(--text-primary);font-size:0.95rem;outline:none;transition:all 0.2s; }
        .form-input:focus { border-color:var(--admin-accent);box-shadow:0 0 16px var(--admin-glow); }

        .btn-submit { width:100%;padding:14px;border-radius:12px;background:linear-gradient(135deg,var(--admin-accent),#b91c1c);color:#fff;font-size:0.95rem;font-weight:700;border:none;cursor:pointer;box-shadow:0 6px 20px var(--admin-glow);transition:all 0.2s;margin-top:10px; }
        .btn-submit:hover { transform:translateY(-1px);box-shadow:0 8px 25px var(--admin-glow); }

        .demo-box { margin-top:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.08);text-align:center; }
        .demo-btn { display:inline-flex;align-items:center;gap:8px;color:var(--text-secondary);font-size:0.82rem;text-decoration:none;font-weight:600;background:rgba(255,255,255,0.05);padding:8px 16px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);transition:all 0.2s; }
        .demo-btn:hover { color:var(--text-primary);border-color:var(--admin-accent);background:rgba(239,68,68,0.1); }
    </style>
</head>
<body>
    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>

    <div class="login-card">
        <div class="brand-badge">
            <div class="brand-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="brand-title">
                <h2>PaySim</h2>
                <span>Admin Control Panel</span>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Admin Username</label>
                <input type="text" name="username" class="form-input" placeholder="admin" autocomplete="username" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Master Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter administrator password" autocomplete="current-password" required>
            </div>

            <button type="submit" name="admin_login" class="btn-submit">
                Authenticate & Enter Dashboard
            </button>
        </form>

    </div>
</body>
</html>
