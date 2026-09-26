<?php
session_start();

if (!isset($_SESSION['ADMIN_LOGGED_IN']) || $_SESSION['ADMIN_LOGGED_IN'] !== true) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['ADMIN_NAME'] ?? 'Administrator';
$adminRole = $_SESSION['ADMIN_ROLE'] ?? 'Super Admin';

$transactions = [
    [
        'id'        => 'TXN001',
        'ref'       => 'PSM926814750001',
        'sender'    => 'Suprakash Ghosh (suprakash@paysim)',
        'receiver'  => 'Ravi Kumar (ravi@paysim)',
        'type'      => 'UPI Transfer',
        'amount'    => 500.00,
        'status'    => 'completed',
        'date'      => '18 Sep 2026, 02:32 PM',
    ],
    [
        'id'        => 'TXN002',
        'ref'       => 'PSM926814750002',
        'sender'    => 'Acme Technologies (payroll@acmetech)',
        'receiver'  => 'Suprakash Ghosh (suprakash@paysim)',
        'type'      => 'NEFT Salary',
        'amount'    => 12400.00,
        'status'    => 'completed',
        'date'      => '17 Sep 2026, 09:00 AM',
    ],
    [
        'id'        => 'TXN003',
        'ref'       => 'PSM926814750003',
        'sender'    => 'Suprakash Ghosh (suprakash@paysim)',
        'receiver'  => 'Netflix India (netflix@razorpay)',
        'type'      => 'UPI Autopay',
        'amount'    => 649.00,
        'status'    => 'completed',
        'date'      => '16 Sep 2026, 06:45 PM',
    ],
    [
        'id'        => 'TXN005',
        'ref'       => 'PSM926814750005',
        'sender'    => 'Suprakash Ghosh (suprakash@paysim)',
        'receiver'  => 'Flipkart Ltd (flipkart@paytm)',
        'type'      => 'UPI QR Scan',
        'amount'    => 2499.00,
        'status'    => 'pending',
        'date'      => '19 Sep 2026, 09:15 PM',
    ],
    [
        'id'        => 'TXN006',
        'ref'       => 'PSM926814750006',
        'sender'    => 'Suprakash Ghosh (suprakash@paysim)',
        'receiver'  => 'Amit Verma (amit@icici)',
        'type'      => 'UPI Transfer',
        'amount'    => 15000.00,
        'status'    => 'failed',
        'date'      => '14 Sep 2026, 04:10 PM',
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global System Transactions — PaySim Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">

    <style>
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
                <a href="users.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users & KYC
                </a>
                <a href="transactions.php" class="nav-item active">
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
                <h1>Global System Transactions Register</h1>
                <p>Complete audit ledger of all NPCI UPI settlements and transfers</p>
            </div>

            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>TXN ID & Ref</th>
                            <th>Sender Party</th>
                            <th>Receiver Party</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Timestamp</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td>
                                    <div style="font-family:'JetBrains Mono',monospace;font-weight:700;"><?php echo $t['id']; ?></div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);font-family:'JetBrains Mono',monospace;"><?php echo $t['ref']; ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($t['sender']); ?></td>
                                <td><?php echo htmlspecialchars($t['receiver']); ?></td>
                                <td style="color:var(--text-secondary);"><?php echo $t['type']; ?></td>
                                <td style="font-family:'JetBrains Mono',monospace;font-weight:700;">₹<?php echo number_format($t['amount'], 2); ?></td>
                                <td><span class="status-pill <?php echo $t['status']; ?>"><?php echo ucfirst($t['status']); ?></span></td>
                                <td style="color:var(--text-secondary);font-size:0.82rem;"><?php echo $t['date']; ?></td>
                                <td>
                                    <a href="transaction-details.php?id=<?php echo $t['id']; ?>" class="btn-link">Full Audit &rarr;</a>
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
