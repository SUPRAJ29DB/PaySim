<?php
session_start();

// ─── Auth Guard ─────────────────────────────────────────────────────────────
if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

// ─── Session Data ───────────────────────────────────────────────────────────
$userName  = $_SESSION['NAME']     ?? 'User';
$upiId     = $_SESSION['UPI_ID']   ?? 'user@paysim';
$userId    = $_SESSION['USER_ID']  ?? 0;

$firstName = explode(' ', $userName)[0];
$initials  = strtoupper(substr($firstName, 0, 1) . (strpos($userName, ' ') ? substr(explode(' ', $userName)[1], 0, 1) : ''));

$transactionsList = [
    [
        'id'        => 'TXN001',
        'ref'       => 'PSM926814750001',
        'name'      => 'Ravi Kumar',
        'upi'       => 'ravi@paysim',
        'type'      => 'debit',
        'status'    => 'completed',
        'amount'    => 500.00,
        'date'      => '18 Sep 2026, 02:32 PM',
        'category'  => 'Food & Dining',
        'note'      => 'Lunch split at Charcoal Grill',
    ],
    [
        'id'        => 'TXN002',
        'ref'       => 'PSM926814750002',
        'name'      => 'Acme Technologies',
        'upi'       => 'payroll@acmetech',
        'type'      => 'credit',
        'status'    => 'completed',
        'amount'    => 12400.00,
        'date'      => '17 Sep 2026, 09:00 AM',
        'category'  => 'Salary & Income',
        'note'      => 'Monthly salary & allowances - Sept 2026',
    ],
    [
        'id'        => 'TXN003',
        'ref'       => 'PSM926814750003',
        'name'      => 'Netflix India',
        'upi'       => 'netflix@razorpay',
        'type'      => 'debit',
        'status'    => 'completed',
        'amount'    => 649.00,
        'date'      => '16 Sep 2026, 06:45 PM',
        'category'  => 'Subscription',
        'note'      => 'Monthly Premium Subscription',
    ],
    [
        'id'        => 'TXN004',
        'ref'       => 'PSM926814750004',
        'name'      => 'Priya Sharma',
        'upi'       => 'priya@paysim',
        'type'      => 'credit',
        'status'    => 'completed',
        'amount'    => 1200.00,
        'date'      => '15 Sep 2026, 11:20 AM',
        'category'  => 'Reimbursement',
        'note'      => 'Weekend trip fuel expense share',
    ],
    [
        'id'        => 'TXN005',
        'ref'       => 'PSM926814750005',
        'name'      => 'Flipkart Internet',
        'upi'       => 'flipkart@paytm',
        'type'      => 'debit',
        'status'    => 'pending',
        'amount'    => 2499.00,
        'date'      => '19 Sep 2026, 09:15 PM',
        'category'  => 'Shopping',
        'note'      => 'Order #FK-904812 Wireless Headphones',
    ],
    [
        'id'        => 'TXN006',
        'ref'       => 'PSM926814750006',
        'name'      => 'Amit Verma',
        'upi'       => 'amit@icici',
        'type'      => 'debit',
        'status'    => 'failed',
        'amount'    => 15000.00,
        'date'      => '14 Sep 2026, 04:10 PM',
        'category'  => 'Transfer',
        'note'      => 'Security deposit share',
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History — PaySim</title>
    <meta name="description" content="View and filter all your past transactions, receipts, and payments on PaySim.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-primary:     #0a0e1a;
            --bg-secondary:   #111827;
            --bg-card:        rgba(17, 24, 39, 0.7);
            --bg-hover:       rgba(99, 102, 241, 0.06);
            --border:         rgba(99, 102, 241, 0.15);
            --border-focus:   rgba(99, 102, 241, 0.6);
            --text-primary:   #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted:     #64748b;
            --accent:         #6366f1;
            --accent-hover:   #818cf8;
            --accent-glow:    rgba(99, 102, 241, 0.35);
            --success:        #10b981;
            --success-bg:     rgba(16, 185, 129, 0.1);
            --error:          #ef4444;
            --error-bg:       rgba(239, 68, 68, 0.1);
            --warning:        #f59e0b;
            --warning-bg:     rgba(245, 158, 11, 0.1);
            --radius:         12px;
            --radius-lg:      20px;
            --sidebar-width:  280px;
        }

        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; }

        .bg-orb { position:fixed; border-radius:50%; filter:blur(120px); opacity:0.25; z-index:0; pointer-events:none; }
        .bg-orb--1 { width:600px;height:600px;background:radial-gradient(circle,var(--accent) 0%,transparent 70%);top:-200px;right:-150px; }

        .app-layout { display:flex; min-height:100vh; position:relative; z-index:1; }

        .sidebar { width:var(--sidebar-width);background:var(--bg-card);backdrop-filter:blur(24px);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100; }
        .sidebar-brand { padding:24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px; }
        .sidebar-brand-icon { width:42px;height:42px;background:linear-gradient(135deg,var(--accent),#8b5cf6);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px var(--accent-glow); }
        .sidebar-brand-icon svg { width:22px;height:22px;color:#fff; }
        .sidebar-brand-text h2 { font-size:1.15rem;font-weight:700;color:var(--text-primary); }
        .sidebar-brand-text span { font-size:0.7rem;color:var(--text-muted);text-transform:uppercase; }
        .sidebar-nav { flex:1;padding:16px 12px;overflow-y:auto; }
        .nav-section-label { font-size:0.65rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;padding:8px 12px 6px; }
        .nav-item { display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;color:var(--text-secondary);text-decoration:none;font-size:0.875rem;font-weight:500;transition:all 0.2s;margin-bottom:2px; }
        .nav-item:hover { background:var(--bg-hover);color:var(--text-primary); }
        .nav-item.active { background:rgba(99,102,241,0.12);color:var(--accent-hover); }
        .nav-item svg { width:20px;height:20px;opacity:0.7; }
        .nav-item.active svg { opacity:1; }

        .main-content { flex:1;margin-left:var(--sidebar-width);padding:32px 36px 60px;min-height:100vh; }

        .page-title { font-size:1.6rem;font-weight:700;margin-bottom:24px; }

        /* Filter Controls */
        .filter-bar { display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap; }
        .search-box { flex:1;min-width:240px;position:relative; }
        .search-box input { width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:10px 14px 10px 38px;color:var(--text-primary);outline:none;font-size:0.88rem; }
        .search-box svg { position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--text-muted); }
        .filter-select { background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:10px 16px;color:var(--text-primary);font-size:0.88rem;outline:none; }

        /* Table Card */
        .table-card { background:var(--bg-card);backdrop-filter:blur(24px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden; }
        .txn-table { width:100%;border-collapse:collapse;text-align:left; }
        .txn-table th { padding:14px 20px;font-size:0.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;border-bottom:1px solid var(--border);background:rgba(0,0,0,0.2); }
        .txn-table td { padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.04);font-size:0.88rem;vertical-align:middle; }
        .txn-table tr:last-child td { border-bottom:none; }
        .txn-table tr:hover td { background:var(--bg-hover); }

        .txn-user { display:flex;align-items:center;gap:12px; }
        .avatar-circle { width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#8b5cf6);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem;color:#fff; }
        .user-name { font-weight:600;color:var(--text-primary); }
        .user-upi  { font-size:0.75rem;color:var(--text-muted);font-family:'JetBrains Mono',monospace; }

        .status-badge { display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;text-transform:capitalize; }
        .status-badge.completed { background:var(--success-bg);color:var(--success); }
        .status-badge.pending   { background:var(--warning-bg);color:var(--warning); }
        .status-badge.failed    { background:var(--error-bg);color:var(--error); }

        .action-link { color:var(--accent-hover);text-decoration:none;font-weight:600;font-size:0.82rem;display:inline-flex;align-items:center;gap:4px; }
        .action-link:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div class="bg-orb bg-orb--1"></div>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="sidebar-brand-text">
                    <h2>PaySim</h2>
                    <span>UPI Banking Suite</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-label">Main Menu</div>
                <a href="dashboard.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="send-money.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Money
                </a>
                <a href="request-money.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Request Money
                </a>
                <a href="scan-pay.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Scan & Pay
                </a>

                <div class="nav-section-label">Records</div>
                <a href="transactions.php" class="nav-item active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    All Transactions
                </a>
                <a href="notifications.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifications
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Transactions History</h1>

            <div class="filter-bar">
                <div class="search-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="searchInput" placeholder="Search by name, UPI ID, TXN ref..." onkeyup="filterTable()">
                </div>
                <select class="filter-select" id="typeFilter" onchange="filterTable()">
                    <option value="all">All Types</option>
                    <option value="debit">Sent (Debit)</option>
                    <option value="credit">Received (Credit)</option>
                </select>
                <select class="filter-select" id="statusFilter" onchange="filterTable()">
                    <option value="all">All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
            </div>

            <div class="table-card">
                <table class="txn-table" id="txnTable">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Reference ID</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactionsList as $t): 
                            $isCr = ($t['type'] === 'credit');
                            $in = strtoupper(substr($t['name'], 0, 1));
                        ?>
                            <tr data-type="<?php echo $t['type']; ?>" data-status="<?php echo $t['status']; ?>" data-search="<?php echo strtolower($t['name'] . ' ' . $t['upi'] . ' ' . $t['ref'] . ' ' . $t['id']); ?>">
                                <td>
                                    <div class="txn-user">
                                        <div class="avatar-circle"><?php echo $in; ?></div>
                                        <div>
                                            <div class="user-name"><?php echo htmlspecialchars($t['name']); ?></div>
                                            <div class="user-upi"><?php echo htmlspecialchars($t['upi']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span style="font-family:'JetBrains Mono',monospace;font-size:0.8rem;color:var(--text-secondary);"><?php echo htmlspecialchars($t['ref']); ?></span></td>
                                <td><?php echo htmlspecialchars($t['category']); ?></td>
                                <td style="color:var(--text-secondary);font-size:0.82rem;"><?php echo htmlspecialchars($t['date']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $t['status']; ?>">
                                        <?php echo ucfirst($t['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight:700;color:<?php echo $t['status'] === 'failed' ? 'var(--error)' : ($isCr ? 'var(--success)' : 'var(--text-primary)'); ?>;">
                                        <?php echo ($isCr ? '+ ₹' : '- ₹') . number_format($t['amount'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="transaction-details.php?id=<?php echo urlencode($t['id']); ?>" class="action-link">
                                        Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function filterTable() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const type = document.getElementById('typeFilter').value;
            const status = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#txnTable tbody tr');

            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                const rowType = row.getAttribute('data-type');
                const rowStatus = row.getAttribute('data-status');

                const matchesSearch = !query || searchData.includes(query);
                const matchesType = (type === 'all') || (rowType === type);
                const matchesStatus = (status === 'all') || (rowStatus === status);

                if (matchesSearch && matchesType && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
