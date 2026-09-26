<?php
session_start();

if (!isset($_SESSION['ADMIN_LOGGED_IN']) || $_SESSION['ADMIN_LOGGED_IN'] !== true) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['ADMIN_NAME'] ?? 'Administrator';
$adminRole = $_SESSION['ADMIN_ROLE'] ?? 'Super Admin';

$usersList = [
    [
        'id'        => 101,
        'name'      => 'Suprakash Ghosh',
        'email'     => 'suprakash@paysim.com',
        'phone'     => '+91 98765 43210',
        'upi'       => 'suprakash@paysim',
        'kyc'       => 'Verified',
        'status'    => 'Active',
        'balance'   => 24580.75,
        'joined'    => '10 Jan 2026',
    ],
    [
        'id'        => 102,
        'name'      => 'Ravi Kumar',
        'email'     => 'ravi@paysim.com',
        'phone'     => '+91 98123 90481',
        'upi'       => 'ravi@paysim',
        'kyc'       => 'Verified',
        'status'    => 'Active',
        'balance'   => 8420.00,
        'joined'    => '14 Feb 2026',
    ],
    [
        'id'        => 103,
        'name'      => 'Priya Sharma',
        'email'     => 'priya@gmail.com',
        'phone'     => '+91 97711 00293',
        'upi'       => 'priya@paysim',
        'kyc'       => 'Pending',
        'status'    => 'Active',
        'balance'   => 1200.00,
        'joined'    => '01 Mar 2026',
    ],
    [
        'id'        => 104,
        'name'      => 'Amit Verma',
        'email'     => 'amit@icici.com',
        'phone'     => '+91 99002 44810',
        'upi'       => 'amit@icici',
        'kyc'       => 'Rejected',
        'status'    => 'Suspended',
        'balance'   => 0.00,
        'joined'    => '12 Aug 2026',
    ],
    [
        'id'        => 105,
        'name'      => 'Anita Roy',
        'email'     => 'anita@roy.in',
        'phone'     => '+91 98300 11928',
        'upi'       => 'anita@paysim',
        'kyc'       => 'Verified',
        'status'    => 'Active',
        'balance'   => 45900.50,
        'joined'    => '20 Aug 2026',
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — PaySim Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">

    <style>
        .user-cell { display:flex;align-items:center;gap:12px; }
        .avatar-circle { width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--admin-accent),#b91c1c);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:0.85rem;box-shadow:0 4px 12px var(--admin-glow); }
        .user-name { font-weight:700;color:var(--text-primary); }
        .user-sub  { font-size:0.75rem;color:var(--text-muted);font-family:'JetBrains Mono',monospace; }

        .badge-status.active { color:var(--success);font-weight:700; }
        .badge-status.suspended { color:var(--admin-accent);font-weight:700; }

        .btn-link { color:var(--admin-hover);text-decoration:none;font-weight:700;font-size:0.82rem; }
        .btn-link:hover { text-decoration:underline; }
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
            <div class="page-title" style="margin-bottom:28px;">
                <h1>User Directory & KYC Compliance</h1>
                <p>Manage customer accounts, KYC verification status, and security states</p>
            </div>

            <div class="filter-bar">
                <div class="search-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="Search by user name, phone, email, or UPI ID...">
                </div>
            </div>

            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User Profile</th>
                            <th>Contact Phone</th>
                            <th>KYC Status</th>
                            <th>Account Status</th>
                            <th>Wallet Balance</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usersList as $u): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="avatar-circle"><?php echo strtoupper(substr($u['name'],0,1)); ?></div>
                                        <div>
                                            <div class="user-name"><?php echo htmlspecialchars($u['name']); ?></div>
                                            <div class="user-sub"><?php echo htmlspecialchars($u['upi']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($u['phone']); ?></td>
                                <td><span class="badge <?php echo strtolower($u['kyc']); ?>"><?php echo $u['kyc']; ?></span></td>
                                <td><span class="badge-status <?php echo strtolower($u['status']); ?>"><?php echo $u['status']; ?></span></td>
                                <td style="font-family:'JetBrains Mono',monospace;font-weight:700;">₹<?php echo number_format($u['balance'], 2); ?></td>
                                <td style="color:var(--text-secondary);font-size:0.82rem;"><?php echo $u['joined']; ?></td>
                                <td>
                                    <a href="user-details.php?id=<?php echo $u['id']; ?>" class="btn-link">Audit User &rarr;</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
