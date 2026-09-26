<?php
session_start();

if (!isset($_SESSION['ADMIN_LOGGED_IN']) || $_SESSION['ADMIN_LOGGED_IN'] !== true) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['ADMIN_NAME'] ?? 'Administrator';
$adminRole = $_SESSION['ADMIN_ROLE'] ?? 'Super Admin';

$requests = [
    ['id' => 'REQ-801', 'user' => 'Flipkart Internet', 'type' => 'Merchant Settlement', 'amount' => 145000.00, 'date' => 'Today, 04:15 PM', 'status' => 'Pending'],
    ['id' => 'REQ-802', 'user' => 'Ravi Kumar',        'type' => 'Failed TXN Refund Claim','amount' => 500.00,    'date' => 'Today, 02:30 PM', 'status' => 'Pending'],
    ['id' => 'REQ-803', 'user' => 'Priya Sharma',      'type' => 'Wallet Withdrawal',      'amount' => 2400.00,   'date' => 'Yesterday',      'status' => 'Approved'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disputes & Payment Requests — PaySim Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">

    <style>
        .btn-sm { padding:8px 16px;border-radius:10px;font-size:0.8rem;font-weight:700;border:none;cursor:pointer;margin-right:6px;transition:all 0.2s; }
        .btn-approve { background:var(--success-bg);color:var(--success);border:1px solid var(--success-border); }
        .btn-approve:hover { background:rgba(16,185,129,0.25); }
        .btn-reject  { background:rgba(239,68,68,0.18);color:var(--admin-hover);border:1px solid var(--border); }
        .btn-reject:hover  { background:rgba(239,68,68,0.3); }
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
                <div class="nav-section-label">Risk & Compliance</div>
                <a href="dashboard.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="payment-requests.php" class="nav-item active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Disputes & Payouts
                </a>
                <a href="audit-logs.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Audit Logs
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
                <h1>Disputes & Payout Approval Queue</h1>
                <p>Review merchant settlement requests and user refund claims</p>
            </div>

            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Requestor / Merchant</th>
                            <th>Request Type</th>
                            <th>Amount</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td style="font-family:'JetBrains Mono',monospace;font-weight:700;"><?php echo $r['id']; ?></td>
                                <td><?php echo htmlspecialchars($r['user']); ?></td>
                                <td><?php echo htmlspecialchars($r['type']); ?></td>
                                <td style="font-family:'JetBrains Mono',monospace;font-weight:700;">₹<?php echo number_format($r['amount'], 2); ?></td>
                                <td style="color:var(--text-secondary);font-size:0.82rem;"><?php echo $r['date']; ?></td>
                                <td><span style="color:<?php echo $r['status'] === 'Approved' ? 'var(--success)' : 'var(--admin-hover)'; ?>;font-weight:700;"><?php echo $r['status']; ?></span></td>
                                <td>
                                    <?php if ($r['status'] === 'Pending'): ?>
                                        <button class="btn-sm btn-approve" onclick="alert('Payout approved!')">Approve</button>
                                        <button class="btn-sm btn-reject" onclick="alert('Payout request rejected.')">Reject</button>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);font-size:0.8rem;">Processed</span>
                                    <?php endif; ?>
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
