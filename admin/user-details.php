<?php
session_start();

if (!isset($_SESSION['ADMIN_LOGGED_IN']) || $_SESSION['ADMIN_LOGGED_IN'] !== true) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['ADMIN_NAME'] ?? 'Administrator';
$adminRole = $_SESSION['ADMIN_ROLE'] ?? 'Super Admin';
$userId    = $_GET['id'] ?? 101;

$user = [
    'id'           => $userId,
    'name'         => 'Suprakash Ghosh',
    'email'        => 'suprakash@paysim.com',
    'phone'        => '+91 98765 43210',
    'upi'          => 'suprakash@paysim',
    'kyc_status'   => 'Verified',
    'pan_no'       => 'ABCDE1234F',
    'aadhaar_no'   => '•••• •••• 8912',
    'account_state'=> 'Active',
    'balance'      => 24580.75,
    'registered'   => '10 Jan 2026, 10:15 AM',
    'last_ip'      => '103.211.54.12 (Kolkata, WB)',
    'banks'        => [
        ['name' => 'State Bank of India', 'acc' => '•••• 4829', 'primary' => true],
        ['name' => 'HDFC Bank',           'acc' => '•••• 9102', 'primary' => false],
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Audit Details — #<?php echo $user['id']; ?> — PaySim Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">

    <style>
        .grid-2 { display:grid;grid-template-columns:repeat(2, 1fr);gap:20px; }

        .meta-item { display:flex;flex-direction:column;gap:4px; }
        .meta-label { font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700; }
        .meta-val { font-size:0.95rem;font-weight:600;color:var(--text-primary); }

        .btn-control { padding:12px 20px;border-radius:12px;font-size:0.88rem;font-weight:700;border:none;cursor:pointer;margin-right:10px;transition:all 0.2s; }
        .btn-danger { background:rgba(239,68,68,0.18);color:var(--admin-hover);border:1px solid var(--border); }
        .btn-danger:hover { background:rgba(239,68,68,0.3);box-shadow:0 0 16px var(--admin-glow); }
        .btn-success { background:var(--success-bg);color:var(--success);border:1px solid var(--success-border); }
        .btn-success:hover { background:rgba(16,185,129,0.25); }
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
                <a href="users.php" class="nav-item active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users & KYC
                </a>
                <a href="transactions.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    System Transactions
                </a>
                <a href="accounts.php" class="nav-item">
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
            <div style="margin-bottom:24px;">
                <a href="users.php" class="btn-secondary" style="margin-bottom:12px;">&larr; Back to Users List</a>
                <div class="page-title">
                    <h1><?php echo htmlspecialchars($user['name']); ?> <span style="font-size:0.85rem;color:var(--text-muted);">(ID #<?php echo $user['id']; ?>)</span></h1>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        KYC & Account Audit Profile
                    </div>
                </div>
                
                <div class="grid-2">
                    <div class="meta-item">
                        <span class="meta-label">Primary UPI ID</span>
                        <span class="meta-val" style="font-family:'JetBrains Mono',monospace;color:var(--admin-hover);"><?php echo htmlspecialchars($user['upi']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Email Address</span>
                        <span class="meta-val"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Mobile Number</span>
                        <span class="meta-val"><?php echo htmlspecialchars($user['phone']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">KYC Verification</span>
                        <span class="meta-val" style="color:var(--success);"><?php echo htmlspecialchars($user['kyc_status']); ?> (PAN: <?php echo $user['pan_no']; ?>)</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Current Wallet Balance</span>
                        <span class="meta-val" style="font-family:'JetBrains Mono',monospace;font-size:1.3rem;">₹<?php echo number_format($user['balance'], 2); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Registration IP & Origin</span>
                        <span class="meta-val"><?php echo htmlspecialchars($user['last_ip']); ?></span>
                    </div>
                </div>

                <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--border);">
                    <button class="btn-control btn-danger" onclick="alert('Account frozen successfully.')">Freeze User Account</button>
                    <button class="btn-control btn-success" onclick="alert('Wallet balance updated by admin.')">Adjust Wallet Balance</button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
