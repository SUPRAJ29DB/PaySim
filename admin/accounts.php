<?php
session_start();

if (!isset($_SESSION['ADMIN_LOGGED_IN']) || $_SESSION['ADMIN_LOGGED_IN'] !== true) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['ADMIN_NAME'] ?? 'Administrator';
$adminRole = $_SESSION['ADMIN_ROLE'] ?? 'Super Admin';

$pools = [
    ['name' => 'State Bank of India — Primary Escrow Pool', 'acc' => '•••• 9901', 'bal' => 12050000.00, 'node' => 'ONLINE (80ms)', 'status' => 'Balanced'],
    ['name' => 'HDFC Bank — Instant Liquidity Reserve',      'acc' => '•••• 4410', 'bal' => 4520000.00,  'node' => 'ONLINE (95ms)', 'status' => 'Balanced'],
    ['name' => 'ICICI Bank — Merchant Clearing Vault',     'acc' => '•••• 8821', 'bal' => 1920000.00,  'node' => 'ONLINE (110ms)','status' => 'Balanced'],
    ['name' => 'Axis Bank — RBI NEFT Reserve Account',      'acc' => '•••• 1009', 'bal' => 5000000.00,  'node' => 'ONLINE (105ms)','status' => 'Balanced'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Escrow Pools — PaySim Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">

    <style>
        .pools-grid { display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:24px; }
        .pool-card { background:var(--bg-card);backdrop-filter:blur(30px);border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:0 10px 30px rgba(0,0,0,0.4); }
        .pool-title { font-size:1.05rem;font-weight:700;color:var(--text-primary);margin-bottom:4px; }
        .pool-acc { font-family:'JetBrains Mono',monospace;font-size:0.82rem;color:var(--text-muted); }
        .pool-bal { font-size:1.9rem;font-weight:800;color:var(--success);font-family:'JetBrains Mono',monospace;margin:18px 0; }
        .pool-node { font-size:0.82rem;color:var(--text-secondary);display:flex;align-items:center;justify-content:space-between;padding-top:14px;border-top:1px solid rgba(255,255,255,0.06); }
    </style>
</head>
<body>
    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="sidebar-brand-text">
                    <h2>PaySim Admin</h2>
                    <span>Super Admin Portal</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-label">Core Operations</div>
                <a href="dashboard.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="users.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users & KYC
                </a>
                <a href="transactions.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    System Transactions
                </a>
                <a href="accounts.php" class="nav-item active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Bank Escrow Pools
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-info">
                        <div class="name"><?php echo htmlspecialchars($adminName); ?></div>
                        <div class="role"><?php echo htmlspecialchars($adminRole); ?></div>
                    </div>
                    <a href="logout.php" class="logout-btn" title="Logout">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </a>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-title" style="margin-bottom:28px;">
                <h1>System Bank Escrow & Settlement Pools</h1>
                <p>Real-time reserve balances held across partner banking nodes</p>
            </div>

            <div class="pools-grid">
                <?php foreach ($pools as $p): ?>
                    <div class="pool-card">
                        <div class="pool-title"><?php echo htmlspecialchars($p['name']); ?></div>
                        <div class="pool-acc">Account <?php echo htmlspecialchars($p['acc']); ?></div>
                        <div class="pool-bal">₹<?php echo number_format($p['bal'], 2); ?></div>
                        <div class="pool-node">
                            <span><?php echo $p['node']; ?></span>
                            <span style="color:var(--success);font-weight:700;"><?php echo $p['status']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
