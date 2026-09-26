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

$banks = [
    [
        'id'          => 'b1',
        'name'        => 'State Bank of India',
        'account_no'  => '•••• 4829',
        'ifsc'        => 'SBIN0001234',
        'branch'      => 'Main Branch, Kolkata',
        'type'        => 'Savings Account',
        'is_primary'  => true,
        'balance'     => 142500.50,
        'color_gradient' => 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
    ],
    [
        'id'          => 'b2',
        'name'        => 'HDFC Bank',
        'account_no'  => '•••• 9102',
        'ifsc'        => 'HDFC0004567',
        'branch'      => 'Park Street, Kolkata',
        'type'        => 'Salary Account',
        'is_primary'  => false,
        'balance'     => 89200.00,
        'color_gradient' => 'linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)',
    ],
    [
        'id'          => 'b3',
        'name'        => 'ICICI Bank',
        'account_no'  => '•••• 1109',
        'ifsc'        => 'ICIC0008910',
        'branch'      => 'Salt Lake, Kolkata',
        'type'        => 'Savings Account',
        'is_primary'  => false,
        'balance'     => 23150.25,
        'color_gradient' => 'linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%)',
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Accounts & Cards — PaySim</title>
    <meta name="description" content="Manage your linked UPI bank accounts, primary payment methods, and check account balances.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

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
            --warning:        #f59e0b;
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

        .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px; }
        .page-title h1 { font-size:1.6rem;font-weight:700; }
        .page-title p { color:var(--text-secondary);font-size:0.88rem; }

        .btn-add { display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#4f46e5);color:#fff;font-size:0.88rem;font-weight:600;border:none;cursor:pointer;box-shadow:0 4px 14px var(--accent-glow);transition:all 0.2s; }
        .btn-add:hover { transform:translateY(-1px);box-shadow:0 6px 20px var(--accent-glow); }

        .banks-grid { display:grid;grid-template-columns:repeat(auto-fill, minmax(340px, 1fr));gap:24px; }

        .bank-card { border-radius:var(--radius-lg);padding:24px;position:relative;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.4);border:1px solid rgba(255,255,255,0.1);display:flex;flex-direction:column;justify-content:space-between;min-height:220px; }
        .card-top { display:flex;align-items:center;justify-content:space-between; }
        .bank-name { font-size:1.1rem;font-weight:700;color:#fff; }
        .bank-type { font-size:0.75rem;color:rgba(255,255,255,0.7);margin-top:2px; }

        .primary-badge { background:rgba(16,185,129,0.25);border:1px solid rgba(16,185,129,0.5);color:#10b981;font-size:0.72rem;font-weight:700;padding:4px 10px;border-radius:20px;text-transform:uppercase; }

        .account-num { font-family:'JetBrains Mono',monospace;font-size:1.3rem;font-weight:700;color:#fff;letter-spacing:0.1em;margin:20px 0; }

        .card-bottom { display:flex;align-items:center;justify-content:space-between;padding-top:14px;border-top:1px solid rgba(255,255,255,0.15); }
        .ifsc-code { font-size:0.78rem;color:rgba(255,255,255,0.8);font-family:'JetBrains Mono',monospace; }

        .btn-check-bal { background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.25);color:#fff;padding:6px 14px;border-radius:8px;font-size:0.8rem;font-weight:600;cursor:pointer;transition:all 0.2s; }
        .btn-check-bal:hover { background:rgba(255,255,255,0.3); }

        /* Balance Display Modal */
        .modal-overlay { position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.3s;padding:20px; }
        .modal-overlay.active { opacity:1;pointer-events:all; }
        .modal-card { background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);width:100%;max-width:400px;padding:28px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,0.6); }
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
                <a href="bank-account.php" class="nav-item active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Bank Accounts
                </a>
                <a href="add-money.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Money
                </a>
                <a href="send-money.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Money
                </a>
                <a href="settings.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Linked Bank Accounts</h1>
                    <p>Manage your bank accounts, check balances, and set primary UPI method</p>
                </div>
                <button class="btn-add" onclick="alert('Linking new bank account feature coming soon!')">
                    + Link New Bank Account
                </button>
            </div>

            <div class="banks-grid">
                <?php foreach ($banks as $b): ?>
                    <div class="bank-card" style="background: <?php echo $b['color_gradient']; ?>;">
                        <div class="card-top">
                            <div>
                                <div class="bank-name"><?php echo htmlspecialchars($b['name']); ?></div>
                                <div class="bank-type"><?php echo htmlspecialchars($b['type']); ?></div>
                            </div>
                            <?php if ($b['is_primary']): ?>
                                <span class="primary-badge">PRIMARY UPI</span>
                            <?php endif; ?>
                        </div>

                        <div class="account-num"><?php echo htmlspecialchars($b['account_no']); ?></div>

                        <div class="card-bottom">
                            <span class="ifsc-code"><?php echo htmlspecialchars($b['ifsc']); ?></span>
                            <button class="btn-check-bal" onclick="checkBalance('<?php echo htmlspecialchars($b['name']); ?>', <?php echo $b['balance']; ?>)">
                                Check Balance
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Balance Check Modal -->
    <div class="modal-overlay" id="balModal">
        <div class="modal-card">
            <h3 id="balBankTitle" style="font-size:1.1rem;font-weight:700;margin-bottom:6px;">Account Balance</h3>
            <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:20px;">Available Balance via NPCI Node</p>
            <div id="balValue" style="font-size:2.2rem;font-weight:800;color:var(--success);font-family:'JetBrains Mono',monospace;margin-bottom:24px;">₹0.00</div>
            <button class="btn-add" style="width:100%;justify-content:center;" onclick="closeBalModal()">Close</button>
        </div>
    </div>

    <script>
        function checkBalance(bankName, amount) {
            document.getElementById('balBankTitle').textContent = bankName + ' Balance';
            document.getElementById('balValue').textContent = '₹' + amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('balModal').classList.add('active');
        }

        function closeBalModal() {
            document.getElementById('balModal').classList.remove('active');
        }
    </script>
</body>
</html>
