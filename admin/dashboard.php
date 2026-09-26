<?php
session_start();

if (!isset($_SESSION['ADMIN_LOGGED_IN']) || $_SESSION['ADMIN_LOGGED_IN'] !== true) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['ADMIN_NAME'] ?? 'Administrator';
$adminRole = $_SESSION['ADMIN_ROLE'] ?? 'Super Admin';

// Simulated Metrics
$totalVolume    = 18450200.50; // ₹1.84 Cr
$totalUsers     = 14250;
$totalTxns      = 89410;
$successRate    = 99.98;
$pendingRefunds = 4;
$flaggedRisk    = 2;

// Recent System Audit Stream
$latestTxns = [
    ['id' => 'TXN90481', 'user' => 'Ravi Kumar',     'amount' => 500.00,   'type' => 'UPI Transfer',  'status' => 'completed', 'time' => '10 mins ago'],
    ['id' => 'TXN90480', 'user' => 'Priya Sharma',   'amount' => 12400.00, 'type' => 'NEFT Salary',   'status' => 'completed', 'time' => '25 mins ago'],
    ['id' => 'TXN90479', 'user' => 'Flipkart Ltd',   'amount' => 2499.00,  'type' => 'Shopping QR',   'status' => 'pending',   'time' => '40 mins ago'],
    ['id' => 'TXN90478', 'user' => 'Amit Verma',     'amount' => 15000.00, 'type' => 'UPI Transfer',  'status' => 'failed',    'time' => '1 hour ago'],
    ['id' => 'TXN90477', 'user' => 'Netflix India',  'amount' => 649.00,   'type' => 'UPI Autopay',   'status' => 'completed', 'time' => '2 hours ago'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — PaySim Control Center</title>
    <meta name="description" content="PaySim UPI Platform System Administration Command Center.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">

    <style>
        .top-banner { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px; }
        .status-strip { background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);border-radius:12px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;font-size:0.88rem; }
        .node-status { display:flex;align-items:center;gap:10px;font-weight:700;color:var(--success); }
        .node-pulse { width:8px;height:8px;border-radius:50%;background:var(--success);box-shadow:0 0 10px var(--success);animation:pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{transform:scale(1);} 50%{transform:scale(1.4);} }

        .kpi-grid { display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;margin-bottom:28px; }
        .kpi-card { background:var(--bg-card);backdrop-filter:blur(30px);border:1px solid var(--border);border-radius:var(--radius);padding:24px;display:flex;flex-direction:column;gap:8px;box-shadow:0 10px 30px rgba(0,0,0,0.4); }
        .kpi-label { font-size:0.75rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:0.06em; }
        .kpi-val { font-size:1.75rem;font-weight:800;color:var(--text-primary);font-family:'JetBrains Mono',monospace; }
        .kpi-sub { font-size:0.78rem;color:var(--success);font-weight:600;display:flex;align-items:center;gap:4px; }

        .admin-grid { display:grid;grid-template-columns:1fr 360px;gap:28px; }

        .ctrl-btn { width:100%;padding:14px 16px;border-radius:12px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:var(--text-primary);font-size:0.88rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:12px;margin-bottom:12px;transition:all 0.2s; }
        .ctrl-btn:hover { background:var(--bg-hover);border-color:var(--border-focus);color:var(--admin-hover);transform:translateX(2px); }

        .btn-action { color:var(--admin-hover);text-decoration:none;font-weight:700;font-size:0.82rem; }
        .btn-action:hover { text-decoration:underline; }

        @media (max-width: 1024px) {
            .admin-grid { grid-template-columns:1fr; }
            .sidebar { transform:translateX(-100%); }
            .main-content { margin-left:0;padding:20px; }
        }
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
                <a href="dashboard.php" class="nav-item active">
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
                <a href="accounts.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Bank Escrow Pools
                </a>

                <div class="nav-section-label">Risk & Compliance</div>
                <a href="payment-requests.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Disputes & Payouts
                </a>
                <a href="audit-logs.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Audit Logs
                </a>

                <div class="nav-section-label">Management</div>
                <a href="notifications.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Broadcast Engine
                </a>
                <a href="settings.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    System Rules
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

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-banner">
                <div class="page-title">
                    <h1>PaySim Platform Control Center</h1>
                    <p>Real-time telemetry, NPCI node metrics, and administrative governance</p>
                </div>
            </div>

            <!-- NPCI Node Status Strip -->
            <div class="status-strip">
                <div class="node-status">
                    <span class="node-pulse"></span>
                    NPCI UPI Gateway Switch: ONLINE (Latency: 118ms)
                </div>
                <div style="color:var(--text-secondary);font-size:0.8rem;">
                    Last Settlement Sweep: Today, 09:00 PM • All 4 Escrow Pools Balanced
                </div>
            </div>

            <!-- KPI Metric Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <span class="kpi-label">Total System Volume</span>
                    <span class="kpi-val">₹1.84 Cr</span>
                    <span class="kpi-sub">+14.2% vs last week</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Active Registered Users</span>
                    <span class="kpi-val"><?php echo number_format($totalUsers); ?></span>
                    <span class="kpi-sub">+128 new today</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Total Transactions</span>
                    <span class="kpi-val"><?php echo number_format($totalTxns); ?></span>
                    <span class="kpi-sub">Success Rate <?php echo $successRate; ?>%</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Flagged Risk Alerts</span>
                    <span class="kpi-val" style="color:var(--warning);"><?php echo $flaggedRisk; ?></span>
                    <span class="kpi-sub" style="color:var(--text-muted);">AML Engine Triggered</span>
                </div>
            </div>

            <div class="admin-grid">
                <!-- Left Column -->
                <div>
                    <!-- Real-time Audit Stream -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Real-Time Transaction Stream
                            </div>
                            <a href="transactions.php" class="btn-action">View All Records &rarr;</a>
                        </div>

                        <table class="stream-table">
                            <thead>
                                <tr>
                                    <th>TXN ID</th>
                                    <th>Account / User</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestTxns as $t): ?>
                                    <tr>
                                        <td style="font-family:'JetBrains Mono',monospace;font-weight:700;"><?php echo $t['id']; ?></td>
                                        <td><?php echo htmlspecialchars($t['user']); ?></td>
                                        <td style="color:var(--text-secondary);"><?php echo $t['type']; ?></td>
                                        <td style="font-weight:700;font-family:'JetBrains Mono',monospace;">₹<?php echo number_format($t['amount'], 2); ?></td>
                                        <td>
                                            <span class="status-pill <?php echo $t['status']; ?>">
                                                <?php echo ucfirst($t['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="transaction-details.php?id=<?php echo $t['id']; ?>" class="btn-action">Audit Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column: Shortcuts & Quick Actions -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                Quick Control Tools
                            </div>
                        </div>

                        <button class="ctrl-btn" onclick="alert('Triggering manual NPCI clearing batch...')">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Run Manual NPCI Reconciliation
                        </button>

                        <button class="ctrl-btn" onclick="window.location.href='notifications.php'">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            Broadcast Maintenance Notice
                        </button>

                        <button class="ctrl-btn" onclick="window.location.href='users.php'">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Manage Frozen Accounts
                        </button>

                        <button class="ctrl-btn" onclick="alert('Flushed system Redis session cache.')">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Flush Gateway Cache
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
