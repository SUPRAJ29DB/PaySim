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

// Simulated Wallet Balance
$currentBalance = 24580.75;

// Linked Banks & Saved Cards
$linkedBanks = [
    ['id' => 'bank_sbi',  'bank' => 'State Bank of India', 'account' => '•••• 4829', 'primary' => true,  'logo' => 'SBI'],
    ['id' => 'bank_hdfc', 'bank' => 'HDFC Bank',           'account' => '•••• 9102', 'primary' => false, 'logo' => 'HDFC'],
    ['id' => 'bank_icici','bank' => 'ICICI Bank',          'account' => '•••• 1109', 'primary' => false, 'logo' => 'ICICI'],
];

$savedCards = [
    ['id' => 'card_1', 'brand' => 'Visa',       'number' => '•••• 4242', 'exp' => '08/28', 'bank' => 'HDFC Bank'],
    ['id' => 'card_2', 'brand' => 'Mastercard', 'number' => '•••• 8819', 'exp' => '11/27', 'bank' => 'Axis Bank'],
];

$recentTopups = [
    ['ref' => 'PSM-TOP-92810', 'amount' => 5000.00, 'source' => 'SBI •••• 4829 (UPI)',   'date' => '15 Sep 2026, 04:30 PM', 'status' => 'completed'],
    ['ref' => 'PSM-TOP-88129', 'amount' => 2000.00, 'source' => 'HDFC Visa •••• 4242',  'date' => '01 Sep 2026, 11:15 AM', 'status' => 'completed'],
    ['ref' => 'PSM-TOP-71928', 'amount' => 10000.00,'source' => 'SBI Netbanking',        'date' => '20 Aug 2026, 09:40 AM', 'status' => 'completed'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Money to Wallet — PaySim</title>
    <meta name="description" content="Add funds to your PaySim digital wallet instantly using UPI, Linked Bank Accounts, Cards, or Net Banking.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-primary:     #0a0e1a;
            --bg-secondary:   #111827;
            --bg-card:        rgba(17, 24, 39, 0.7);
            --bg-card-solid:  #111827;
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
            --success-border: rgba(16, 185, 129, 0.25);
            --error:          #ef4444;
            --warning:        #f59e0b;
            --radius:         12px;
            --radius-lg:      20px;
            --sidebar-width:  280px;
        }

        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; overflow-x: hidden; }

        .bg-orb { position:fixed; border-radius:50%; filter:blur(120px); opacity:0.25; z-index:0; pointer-events:none; }
        .bg-orb--1 { width:600px;height:600px;background:radial-gradient(circle,var(--accent) 0%,transparent 70%);top:-200px;right:-150px;animation:orbFloat 10s ease-in-out infinite alternate; }
        .bg-orb--2 { width:450px;height:450px;background:radial-gradient(circle,#10b981 0%,transparent 70%);bottom:-150px;left:-100px;animation:orbFloat 12s ease-in-out infinite alternate-reverse; }
        @keyframes orbFloat { 0%{transform:translate(0,0)scale(1)} 100%{transform:translate(40px,-30px)scale(1.15)} }

        .app-layout { display:flex; min-height:100vh; position:relative; z-index:1; }

        /* ── Sidebar ── */
        .sidebar { width:var(--sidebar-width);background:var(--bg-card);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100; }
        .sidebar-brand { padding:24px 24px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px; }
        .sidebar-brand-icon { width:42px;height:42px;background:linear-gradient(135deg,var(--accent),#8b5cf6);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px var(--accent-glow);flex-shrink:0; }
        .sidebar-brand-icon svg { width:22px;height:22px;color:#fff; }
        .sidebar-brand-text h2 { font-size:1.15rem;font-weight:700;background:linear-gradient(135deg,var(--text-primary),var(--accent-hover));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
        .sidebar-brand-text span { font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em; }
        .sidebar-nav { flex:1;padding:16px 12px;overflow-y:auto; }
        .nav-section-label { font-size:0.65rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;padding:8px 12px 6px;margin-top:8px; }
        .nav-item { display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;color:var(--text-secondary);text-decoration:none;font-size:0.875rem;font-weight:500;transition:all 0.2s;position:relative;margin-bottom:2px; }
        .nav-item:hover { background:var(--bg-hover);color:var(--text-primary); }
        .nav-item.active { background:rgba(99,102,241,0.12);color:var(--accent-hover); }
        .nav-item.active::before { content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:20px;background:var(--accent);border-radius:0 3px 3px 0; }
        .nav-item svg { width:20px;height:20px;flex-shrink:0;opacity:0.7; }
        .nav-item.active svg { opacity:1; }
        .sidebar-footer { padding:16px;border-top:1px solid var(--border); }
        .sidebar-user { display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;transition:background 0.2s;cursor:pointer;text-decoration:none; }
        .sidebar-user:hover { background:var(--bg-hover); }
        .sidebar-avatar { width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;flex-shrink:0; }
        .sidebar-user-info .name { font-size:0.85rem;font-weight:600;color:var(--text-primary); }
        .sidebar-user-info .upi  { font-size:0.72rem;color:var(--text-muted); }

        /* ── Main Layout ── */
        .main-content { flex:1;margin-left:var(--sidebar-width);padding:32px 36px 60px;min-height:100vh;max-width:1440px; }

        .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px; }
        .page-title h1 { font-size:1.6rem;font-weight:700;letter-spacing:-0.02em; }
        .page-title p { color:var(--text-secondary);font-size:0.88rem;margin-top:2px; }

        .grid-container { display:grid;grid-template-columns:1fr 380px;gap:28px; }

        /* Left Section: Form Card */
        .card { background:var(--bg-card);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;box-shadow:0 8px 32px rgba(0,0,0,0.3); }

        /* Balance Banner */
        .balance-banner { background:linear-gradient(135deg,rgba(99,102,241,0.15),rgba(16,185,129,0.1));border:1px solid rgba(99,102,241,0.25);border-radius:16px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:28px; }
        .balance-info label { font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;font-weight:600; }
        .balance-amount { font-size:1.8rem;font-weight:800;color:var(--text-primary);font-family:'JetBrains Mono',monospace;margin-top:2px; }
        .balance-badge { display:inline-flex;align-items:center;gap:6px;background:var(--success-bg);color:var(--success);padding:6px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;border:1px solid var(--success-border); }

        /* Amount Input Section */
        .section-label { font-size:0.85rem;font-weight:700;color:var(--text-primary);margin-bottom:12px;display:flex;align-items:center;justify-content:space-between; }
        .amount-wrapper { position:relative;margin-bottom:14px; }
        .currency-symbol { position:absolute;left:20px;top:50%;transform:translateY(-50%);font-size:1.8rem;font-weight:700;color:var(--accent-hover); }
        .amount-input { width:100%;background:rgba(0,0,0,0.3);border:2px solid var(--border);border-radius:14px;padding:16px 20px 16px 50px;font-size:1.8rem;font-weight:700;color:var(--text-primary);font-family:'JetBrains Mono',monospace;outline:none;transition:all 0.2s; }
        .amount-input:focus { border-color:var(--accent);box-shadow:0 0 20px var(--accent-glow); }

        /* Quick Preset Chips */
        .preset-chips { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px; }
        .chip-btn { padding:8px 16px;border-radius:10px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:var(--text-secondary);font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s; }
        .chip-btn:hover, .chip-btn.active { background:rgba(99,102,241,0.2);color:var(--accent-hover);border-color:var(--accent);transform:translateY(-1px); }

        /* Projected Balance Note */
        .projected-note { background:rgba(0,0,0,0.25);border-radius:10px;padding:12px 16px;font-size:0.84rem;color:var(--text-secondary);display:flex;align-items:center;justify-content:space-between;margin-bottom:28px; }
        .projected-val { font-weight:700;color:var(--success);font-family:'JetBrains Mono',monospace; }

        /* Payment Source Tabs */
        .tabs-header { display:flex;gap:8px;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:20px; }
        .tab-item { padding:8px 16px;border-radius:8px;font-size:0.85rem;font-weight:600;color:var(--text-secondary);cursor:pointer;transition:all 0.2s;background:transparent;border:none; }
        .tab-item:hover { color:var(--text-primary); }
        .tab-item.active { background:rgba(99,102,241,0.15);color:var(--accent-hover); }

        /* Source Selection Cards */
        .source-list { display:flex;flex-direction:column;gap:12px; }
        .source-card { display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-radius:12px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.06);cursor:pointer;transition:all 0.2s; }
        .source-card:hover, .source-card.selected { border-color:var(--accent);background:rgba(99,102,241,0.08); }
        .source-left { display:flex;align-items:center;gap:14px; }
        .source-icon { width:40px;height:40px;border-radius:10px;background:rgba(99,102,241,0.12);color:var(--accent-hover);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem; }
        .source-title { font-size:0.9rem;font-weight:600;color:var(--text-primary); }
        .source-sub { font-size:0.75rem;color:var(--text-muted); }
        .radio-dot { width:18px;height:18px;border-radius:50%;border:2px solid var(--text-muted);display:flex;align-items:center;justify-content:center;transition:all 0.2s; }
        .source-card.selected .radio-dot { border-color:var(--accent);background:var(--accent); }
        .source-card.selected .radio-dot::after { content:'';width:6px;height:6px;border-radius:50%;background:#fff; }

        /* Action Submit Button */
        .btn-add-money { width:100%;padding:16px;border-radius:14px;background:linear-gradient(135deg,var(--accent),#4f46e5);color:#fff;font-size:1.05rem;font-weight:700;border:none;cursor:pointer;box-shadow:0 6px 20px var(--accent-glow);transition:all 0.2s;margin-top:28px;display:flex;align-items:center;justify-content:center;gap:10px; }
        .btn-add-money:hover { background:linear-gradient(135deg,var(--accent-hover),#6366f1);transform:translateY(-2px);box-shadow:0 8px 25px var(--accent-glow); }

        /* Right Sidebar Cards */
        .sidebar-col { display:flex;flex-direction:column;gap:24px; }

        .side-card { background:var(--bg-card);backdrop-filter:blur(24px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px; }
        .side-card-title { font-size:0.95rem;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px; }
        .side-card-title svg { width:18px;height:18px;color:var(--accent-hover); }

        /* Auto Top-up Switch */
        .toggle-row { display:flex;align-items:center;justify-content:space-between;padding:12px 0; }
        .toggle-info label { font-size:0.88rem;font-weight:600;color:var(--text-primary);display:block; }
        .toggle-info span { font-size:0.75rem;color:var(--text-muted); }
        .switch { position:relative;display:inline-block;width:44px;height:24px; }
        .switch input { opacity:0;width:0;height:0; }
        .slider { position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:rgba(255,255,255,0.1);transition:.3s;border-radius:24px; }
        .slider:before { position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background-color:white;transition:.3s;border-radius:50%; }
        input:checked + .slider { background-color:var(--accent); }
        input:checked + .slider:before { transform:translateX(20px); }

        /* Recent Topups List */
        .recent-list { display:flex;flex-direction:column;gap:12px; }
        .recent-item { display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:10px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.04); }
        .recent-left { display:flex;flex-direction:column;gap:2px; }
        .recent-source { font-size:0.85rem;font-weight:600;color:var(--text-primary); }
        .recent-date   { font-size:0.72rem;color:var(--text-muted); }
        .recent-amount { font-size:0.9rem;font-weight:700;color:var(--success);font-family:'JetBrains Mono',monospace; }

        /* PIN Modal */
        .modal-overlay { position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.3s;padding:20px; }
        .modal-overlay.active { opacity:1;pointer-events:all; }
        .modal-card { background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);width:100%;max-width:440px;padding:32px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,0.6); }
        .pin-inputs { display:flex;justify-content:center;gap:10px;margin:24px 0; }
        .pin-digit { width:48px;height:54px;border-radius:12px;background:rgba(0,0,0,0.4);border:2px solid var(--border);color:var(--text-primary);font-size:1.4rem;font-weight:700;text-align:center;outline:none; }
        .pin-digit:focus { border-color:var(--accent); }

        /* Success Animation Container */
        .success-box { text-align:center;padding:20px 0; }
        .checkmark-circle { width:72px;height:72px;border-radius:50%;background:var(--success-bg);border:2px solid var(--success);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:var(--success);animation:popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .checkmark-circle svg { width:38px;height:38px; }
        @keyframes popIn { from{transform:scale(0);} to{transform:scale(1);} }

        @media (max-width: 1024px) {
            .grid-container { grid-template-columns:1fr; }
            .sidebar { transform:translateX(-100%); }
            .main-content { margin-left:0;padding:20px; }
        }
    </style>
</head>
<body>

    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>

    <div class="app-layout">
        <!-- ── Sidebar ── -->
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
                <a href="add-money.php" class="nav-item active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Money
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
                <a href="transactions.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Transactions
                </a>
                <a href="settings.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="profile.php" class="sidebar-user">
                    <div class="sidebar-avatar"><?php echo $initials; ?></div>
                    <div class="sidebar-user-info">
                        <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                        <div class="upi"><?php echo htmlspecialchars($upiId); ?></div>
                    </div>
                </a>
            </div>
        </aside>

        <!-- ── Main Content ── -->
        <main class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Add Money to Wallet</h1>
                    <p>Instant top-up via UPI, Linked Bank, Debit/Credit Cards & Net Banking</p>
                </div>
            </div>

            <div class="grid-container">
                <!-- Left Form Section -->
                <div class="card">
                    <!-- Wallet Balance Header -->
                    <div class="balance-banner">
                        <div class="balance-info">
                            <label>Current Wallet Balance</label>
                            <div class="balance-amount" id="currentBalText">₹<?php echo number_format($currentBalance, 2); ?></div>
                        </div>
                        <div class="balance-badge">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Active & Ready
                        </div>
                    </div>

                    <!-- Topup Amount Input -->
                    <div class="section-label">
                        <span>Enter Top-Up Amount</span>
                        <span style="font-size:0.75rem;color:var(--text-muted);">Max ₹1,00,000 per transaction</span>
                    </div>

                    <div class="amount-wrapper">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="topupAmount" class="amount-input" value="1000" min="1" max="100000" oninput="updateProjectedBalance()">
                    </div>

                    <!-- Preset Chips -->
                    <div class="preset-chips">
                        <button class="chip-btn" onclick="setPreset(500)">+ ₹500</button>
                        <button class="chip-btn active" onclick="setPreset(1000)">+ ₹1,000</button>
                        <button class="chip-btn" onclick="setPreset(2000)">+ ₹2,000</button>
                        <button class="chip-btn" onclick="setPreset(5000)">+ ₹5,000</button>
                        <button class="chip-btn" onclick="setPreset(10000)">+ ₹10,000</button>
                    </div>

                    <!-- Projected Balance Callout -->
                    <div class="projected-note">
                        <span>Projected Balance After Top-Up</span>
                        <span class="projected-val" id="projectedVal">₹<?php echo number_format($currentBalance + 1000, 2); ?></span>
                    </div>

                    <!-- Payment Source Selection -->
                    <div class="section-label">Select Payment Source</div>
                    
                    <div class="tabs-header">
                        <button class="tab-item active" onclick="switchTab('banks')">Linked Banks</button>
                        <button class="tab-item" onclick="switchTab('cards')">Saved Cards</button>
                        <button class="tab-item" onclick="switchTab('netbanking')">Net Banking</button>
                    </div>

                    <!-- Tab 1: Linked Banks -->
                    <div id="tab-banks" class="source-list">
                        <?php foreach ($linkedBanks as $index => $b): ?>
                            <div class="source-card <?php echo $b['primary'] ? 'selected' : ''; ?>" onclick="selectSource(this)">
                                <div class="source-left">
                                    <div class="source-icon"><?php echo $b['logo']; ?></div>
                                    <div>
                                        <div class="source-title"><?php echo htmlspecialchars($b['bank']); ?></div>
                                        <div class="source-sub">Account <?php echo htmlspecialchars($b['account']); ?> <?php echo $b['primary'] ? '• Primary UPI' : ''; ?></div>
                                    </div>
                                </div>
                                <div class="radio-dot"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tab 2: Saved Cards -->
                    <div id="tab-cards" class="source-list" style="display:none;">
                        <?php foreach ($savedCards as $c): ?>
                            <div class="source-card" onclick="selectSource(this)">
                                <div class="source-left">
                                    <div class="source-icon"><?php echo $c['brand']; ?></div>
                                    <div>
                                        <div class="source-title"><?php echo htmlspecialchars($c['bank']); ?> <?php echo htmlspecialchars($c['brand']); ?></div>
                                        <div class="source-sub">Card Number <?php echo htmlspecialchars($c['number']); ?> (Exp <?php echo htmlspecialchars($c['exp']); ?>)</div>
                                    </div>
                                </div>
                                <div class="radio-dot"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tab 3: Netbanking -->
                    <div id="tab-netbanking" class="source-list" style="display:none;">
                        <div class="source-card" onclick="selectSource(this)">
                            <div class="source-left">
                                <div class="source-icon">SBI</div>
                                <div>
                                    <div class="source-title">State Bank of India</div>
                                    <div class="source-sub">Retail & Corporate Netbanking</div>
                                </div>
                            </div>
                            <div class="radio-dot"></div>
                        </div>
                        <div class="source-card" onclick="selectSource(this)">
                            <div class="source-left">
                                <div class="source-icon">HDFC</div>
                                <div>
                                    <div class="source-title">HDFC Bank Direct</div>
                                    <div class="source-sub">Netbanking Portal</div>
                                </div>
                            </div>
                            <div class="radio-dot"></div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button class="btn-add-money" onclick="initiateTopup()">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Proceed to Add Money
                    </button>
                </div>

                <!-- Right Sidebar Column -->
                <div class="sidebar-col">
                    <!-- Auto Top-up Card -->
                    <div class="side-card">
                        <div class="side-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Auto Top-Up Rule
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <label>Smart Auto-Refill</label>
                                <span>Add ₹2,000 when balance &lt; ₹1,000</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="autoTopupCheck" onchange="toggleAutoTopup(this)">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Recent Topups Card -->
                    <div class="side-card">
                        <div class="side-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Recent Wallet Top-Ups
                        </div>
                        <div class="recent-list">
                            <?php foreach ($recentTopups as $r): ?>
                                <div class="recent-item">
                                    <div class="recent-left">
                                        <span class="recent-source"><?php echo htmlspecialchars($r['source']); ?></span>
                                        <span class="recent-date"><?php echo htmlspecialchars($r['date']); ?></span>
                                    </div>
                                    <span class="recent-amount">+ ₹<?php echo number_format($r['amount'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- PIN Confirmation Modal -->
    <div class="modal-overlay" id="pinModal">
        <div class="modal-card" id="modalCardBody">
            <h3 style="font-size:1.15rem;font-weight:700;margin-bottom:6px;">Enter 6-Digit UPI PIN</h3>
            <p style="font-size:0.82rem;color:var(--text-muted);">Authorizing Top-Up of <span id="modalAmountText" style="color:var(--accent-hover);font-weight:700;">₹1,000</span></p>

            <div class="pin-inputs">
                <input type="password" class="pin-digit" maxlength="1" onkeyup="movePin(this, 1)">
                <input type="password" class="pin-digit" maxlength="1" onkeyup="movePin(this, 2)">
                <input type="password" class="pin-digit" maxlength="1" onkeyup="movePin(this, 3)">
                <input type="password" class="pin-digit" maxlength="1" onkeyup="movePin(this, 4)">
                <input type="password" class="pin-digit" maxlength="1" onkeyup="movePin(this, 5)">
                <input type="password" class="pin-digit" maxlength="1" onkeyup="movePin(this, 6)">
            </div>

            <div style="display:flex;gap:10px;justify-content:center;margin-top:20px;">
                <button class="chip-btn" onclick="closePinModal()">Cancel</button>
                <button class="btn-add-money" style="width:auto;padding:10px 24px;margin-top:0;" onclick="submitPin()">Authorize Top-Up</button>
            </div>
        </div>
    </div>

    <script>
        let currentWalletBalance = <?php echo (float)$currentBalance; ?>;

        function setPreset(amt) {
            document.getElementById('topupAmount').value = amt;
            document.querySelectorAll('.chip-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            updateProjectedBalance();
        }

        function updateProjectedBalance() {
            const val = parseFloat(document.getElementById('topupAmount').value) || 0;
            const projected = currentWalletBalance + val;
            document.getElementById('projectedVal').textContent = '₹' + projected.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function switchTab(tabName) {
            document.querySelectorAll('.tab-item').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            document.getElementById('tab-banks').style.display = 'none';
            document.getElementById('tab-cards').style.display = 'none';
            document.getElementById('tab-netbanking').style.display = 'none';

            document.getElementById('tab-' + tabName).style.display = 'flex';
        }

        function selectSource(el) {
            document.querySelectorAll('.source-card').forEach(card => card.classList.remove('selected'));
            el.classList.add('selected');
        }

        function initiateTopup() {
            const val = parseFloat(document.getElementById('topupAmount').value) || 0;
            if (val <= 0) {
                alert('Please enter a valid top-up amount.');
                return;
            }
            document.getElementById('modalAmountText').textContent = '₹' + val.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('pinModal').classList.add('active');
        }

        function closePinModal() {
            document.getElementById('pinModal').classList.remove('active');
        }

        function movePin(el, nextIndex) {
            if (el.value.length === 1 && nextIndex <= 6) {
                const digits = document.querySelectorAll('.pin-digit');
                if (digits[nextIndex - 1]) digits[nextIndex - 1].focus();
            }
        }

        function submitPin() {
            const val = parseFloat(document.getElementById('topupAmount').value) || 0;
            currentWalletBalance += val;

            const modalBody = document.getElementById('modalCardBody');
            modalBody.innerHTML = `
                <div class="success-box">
                    <div class="checkmark-circle">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 style="font-size:1.4rem;font-weight:700;color:var(--text-primary);margin-bottom:6px;">₹${val.toLocaleString('en-IN', {minimumFractionDigits:2})} Added!</h3>
                    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:20px;">Your wallet has been topped up successfully.</p>
                    <button class="btn-add-money" style="width:100%;margin-top:0;" onclick="finishSuccess()">Done & Return to Dashboard</button>
                </div>
            `;

            document.getElementById('currentBalText').textContent = '₹' + currentWalletBalance.toLocaleString('en-IN', {minimumFractionDigits: 2});
            updateProjectedBalance();
        }

        function finishSuccess() {
            window.location.href = 'dashboard.php';
        }

        function toggleAutoTopup(chk) {
            alert(chk.checked ? 'Smart Auto-Refill activated!' : 'Smart Auto-Refill disabled.');
        }
    </script>
</body>
</html>
