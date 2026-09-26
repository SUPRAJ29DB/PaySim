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

// ─── Simulated Data & Database Integration ────────────────────────────────────
$balance       = 24580.75;
$monthIncome   = 12400.00;
$monthExpense  = 8320.50;
$cashback      = 156.00;

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/services/AccountService.php';
require_once __DIR__ . '/services/TransactionService.php';

try {
    $accountService = new AccountService();
    $txnService = new TransactionService();
    $dbWallet = $accountService->getWallet($userId);
    if ($dbWallet) {
        $balance = (float) $dbWallet['balance'];
        $summary = $txnService->getMonthlySummary($userId);
        if ($summary['month_income'] > 0 || $summary['month_expense'] > 0 || $summary['total_cashback'] > 0) {
            $monthIncome = (float) $summary['month_income'];
            $monthExpense = (float) $summary['month_expense'];
            $cashback = (float) $summary['total_cashback'];
        }
        $hist = $txnService->getHistory($userId, 1, 6);
        if (!empty($hist['transactions'])) {
            $realTxns = [];
            foreach ($hist['transactions'] as $tx) {
                $realTxns[] = [
                    'id'     => $tx['id'],
                    'name'   => $tx['counterparty'],
                    'type'   => $tx['direction'],
                    'amount' => $tx['amount'],
                    'date'   => $tx['date'],
                    'note'   => $tx['note'],
                    'avatar' => $tx['avatar'],
                    'status' => $tx['status'],
                ];
            }
            $transactions = $realTxns;
        }
    }
} catch (Exception $e) {
    // fallback to simulated defaults
}

$transactions = [
    [
        'id'     => 'TXN001',
        'name'   => 'Ravi Kumar',
        'type'   => 'debit',
        'amount' => 500.00,
        'date'   => '2026-09-18 14:32',
        'note'   => 'Lunch split',
        'avatar' => 'RK',
        'status' => 'completed',
    ],
    [
        'id'     => 'TXN002',
        'name'   => 'Salary Credit',
        'type'   => 'credit',
        'amount' => 12400.00,
        'date'   => '2026-09-17 09:00',
        'note'   => 'Monthly salary',
        'avatar' => 'SC',
        'status' => 'completed',
    ],
    [
        'id'     => 'TXN003',
        'name'   => 'Netflix',
        'type'   => 'debit',
        'amount' => 649.00,
        'date'   => '2026-09-16 18:45',
        'note'   => 'Subscription',
        'avatar' => 'NF',
        'status' => 'completed',
    ],
    [
        'id'     => 'TXN004',
        'name'   => 'Priya Sharma',
        'type'   => 'credit',
        'amount' => 1200.00,
        'date'   => '2026-09-15 11:20',
        'note'   => 'Reimbursement',
        'avatar' => 'PS',
        'status' => 'completed',
    ],
    [
        'id'     => 'TXN005',
        'name'   => 'Swiggy',
        'type'   => 'debit',
        'amount' => 342.00,
        'date'   => '2026-09-14 20:10',
        'note'   => 'Food order',
        'avatar' => 'SW',
        'status' => 'completed',
    ],
    [
        'id'     => 'TXN006',
        'name'   => 'Amit Das',
        'type'   => 'debit',
        'amount' => 200.00,
        'date'   => '2026-09-13 16:00',
        'note'   => 'Chai money',
        'avatar' => 'AD',
        'status' => 'pending',
    ],
];

$firstName = explode(' ', $userName)[0];
$hour = (int) date('H');
if ($hour < 12) {
    $greeting = 'Good Morning';
    $greetIcon = '☀️';
} elseif ($hour < 17) {
    $greeting = 'Good Afternoon';
    $greetIcon = '🌤️';
} else {
    $greeting = 'Good Evening';
    $greetIcon = '🌙';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — PaySim</title>
    <meta name="description" content="PaySim Dashboard — Manage your UPI payments, check balance, and track transactions.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & Variables ────────────────────────────────────────── */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            --error-bg:       rgba(239, 68, 68, 0.1);
            --warning:        #f59e0b;
            --warning-bg:     rgba(245, 158, 11, 0.1);
            --radius:         12px;
            --radius-lg:      20px;
            --radius-xl:      24px;
            --sidebar-width:  280px;
            --header-height:  72px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Background Orbs ──────────────────────────────────────────── */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.3;
            z-index: 0;
            pointer-events: none;
        }

        .bg-orb--1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            top: -200px; right: -150px;
            animation: orbFloat 10s ease-in-out infinite alternate;
        }

        .bg-orb--2 {
            width: 450px; height: 450px;
            background: radial-gradient(circle, #8b5cf6 0%, transparent 70%);
            bottom: -150px; left: -100px;
            animation: orbFloat 12s ease-in-out infinite alternate-reverse;
        }

        .bg-orb--3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, #06b6d4 0%, transparent 70%);
            top: 50%; left: 40%;
            animation: orbFloat 14s ease-in-out infinite alternate;
        }

        @keyframes orbFloat {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, -30px) scale(1.15); }
        }

        /* ── Layout ───────────────────────────────────────────────────── */
        .app-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* ── Sidebar ──────────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sidebar-brand {
            padding: 24px 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .sidebar-brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px var(--accent-glow);
            flex-shrink: 0;
        }

        .sidebar-brand-icon svg {
            width: 22px;
            height: 22px;
            color: #fff;
        }

        .sidebar-brand-text h2 {
            font-size: 1.15rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-hover));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-brand-text span {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 8px 12px 6px;
            margin-top: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: rgba(99, 102, 241, 0.12);
            color: var(--accent-hover);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
        }

        .nav-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            opacity: 0.7;
        }

        .nav-item.active svg { opacity: 1; }

        .nav-badge {
            margin-left: auto;
            background: var(--accent);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            transition: background 0.2s;
            cursor: pointer;
            text-decoration: none;
        }

        .sidebar-user:hover {
            background: var(--bg-hover);
        }

        .sidebar-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            overflow: hidden;
        }

        .sidebar-user-info .name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-info .upi {
            font-size: 0.72rem;
            color: var(--text-muted);
        }

        /* ── Main Content ─────────────────────────────────────────────── */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 32px 36px 48px;
            min-height: 100vh;
        }

        /* ── Top Header ───────────────────────────────────────────────── */
        .top-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .greeting h1 {
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .greeting p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            position: relative;
        }

        .btn-icon:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
            border-color: var(--border-focus);
        }

        .btn-icon svg {
            width: 20px;
            height: 20px;
        }

        .btn-icon .notif-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--error);
            border-radius: 50%;
            border: 2px solid var(--bg-primary);
        }

        .hamburger {
            display: none;
        }

        /* ── Balance Card ─────────────────────────────────────────────── */
        .balance-card {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);
            border-radius: var(--radius-xl);
            padding: 36px;
            position: relative;
            overflow: hidden;
            margin-bottom: 28px;
            border: 1px solid rgba(99, 102, 241, 0.25);
            animation: cardSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes cardSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .balance-card::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
            border-radius: 50%;
        }

        .balance-card::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(139,92,246,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .balance-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            position: relative;
            z-index: 1;
            margin-bottom: 24px;
        }

        .balance-label {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .balance-amount {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #fff;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .balance-amount .currency {
            font-size: 1.4rem;
            font-weight: 600;
            opacity: 0.7;
        }

        .balance-upi {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 0.78rem;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .balance-upi:hover {
            background: rgba(255,255,255,0.15);
        }

        .balance-upi svg {
            width: 14px;
            height: 14px;
            opacity: 0.6;
        }

        .balance-stats {
            display: flex;
            gap: 32px;
            position: relative;
            z-index: 1;
        }

        .balance-stat {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .balance-stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .balance-stat-icon.income {
            background: var(--success-bg);
        }

        .balance-stat-icon.expense {
            background: var(--error-bg);
        }

        .balance-stat-icon.cashback {
            background: var(--warning-bg);
        }

        .balance-stat-icon svg {
            width: 18px;
            height: 18px;
        }

        .balance-stat-icon.income svg { color: var(--success); }
        .balance-stat-icon.expense svg { color: var(--error); }
        .balance-stat-icon.cashback svg { color: var(--warning); }

        .balance-stat-info span {
            display: block;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 2px;
        }

        .balance-stat-info strong {
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
        }

        /* ── Quick Actions ────────────────────────────────────────────── */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .section-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .section-header a {
            font-size: 0.8rem;
            color: var(--accent-hover);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .section-header a:hover {
            color: var(--accent);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .action-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 24px 20px;
            text-align: center;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            animation: cardSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .action-card:nth-child(1) { animation-delay: 0.1s; }
        .action-card:nth-child(2) { animation-delay: 0.15s; }
        .action-card:nth-child(3) { animation-delay: 0.2s; }
        .action-card:nth-child(4) { animation-delay: 0.25s; }

        .action-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.03) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .action-card:hover {
            transform: translateY(-4px);
            border-color: var(--border-focus);
            box-shadow: 0 12px 40px -8px var(--accent-glow);
        }

        .action-card:hover::before {
            opacity: 1;
        }

        .action-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }

        .action-icon svg {
            width: 24px;
            height: 24px;
            color: #fff;
        }

        .action-icon--send    { background: linear-gradient(135deg, #6366f1, #818cf8); }
        .action-icon--receive { background: linear-gradient(135deg, #10b981, #34d399); }
        .action-icon--scan    { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .action-icon--add     { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }

        .action-card h3 {
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .action-card p {
            font-size: 0.73rem;
            color: var(--text-muted);
            position: relative;
            z-index: 1;
        }

        /* ── Two Column Layout ────────────────────────────────────────── */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        /* ── Transactions List ────────────────────────────────────────── */
        .transactions-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            animation: cardSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .transactions-card .section-header {
            padding: 20px 24px 0;
        }

        .txn-list {
            list-style: none;
        }

        .txn-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 24px;
            border-bottom: 1px solid rgba(99, 102, 241, 0.06);
            transition: background 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .txn-item:last-child {
            border-bottom: none;
        }

        .txn-item:hover {
            background: var(--bg-hover);
        }

        .txn-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .txn-avatar--debit {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        .txn-avatar--credit {
            background: var(--success-bg);
            color: var(--success);
        }

        .txn-details {
            flex: 1;
            min-width: 0;
        }

        .txn-name {
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .txn-pending-badge {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: var(--warning-bg);
            color: var(--warning);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .txn-note {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .txn-right {
            text-align: right;
            flex-shrink: 0;
        }

        .txn-amount {
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .txn-amount--debit  { color: #f87171; }
        .txn-amount--credit { color: var(--success); }

        .txn-date {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .txn-footer {
            padding: 16px 24px;
            text-align: center;
            border-top: 1px solid var(--border);
        }

        .txn-footer a {
            font-size: 0.82rem;
            color: var(--accent-hover);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .txn-footer a:hover {
            color: var(--accent);
        }

        /* ── Right Panel ──────────────────────────────────────────────── */
        .right-panel {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ── Favorites Card ───────────────────────────────────────────── */
        .favorites-card,
        .promo-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            animation: cardSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.35s forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .fav-item {
            text-align: center;
            padding: 14px 8px;
            border-radius: var(--radius);
            transition: background 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .fav-item:hover {
            background: var(--bg-hover);
        }

        .fav-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4338ca, #6366f1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .fav-item span {
            display: block;
            font-size: 0.72rem;
            color: var(--text-secondary);
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Promo Card ───────────────────────────────────────────────── */
        .promo-card {
            background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.08));
            border-color: rgba(99, 102, 241, 0.2);
            animation-delay: 0.4s;
        }

        .promo-card .promo-badge {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--accent-hover);
            background: rgba(99, 102, 241, 0.15);
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .promo-card h3 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .promo-card p {
            font-size: 0.8rem;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .promo-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .promo-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px var(--accent-glow);
        }

        .promo-btn svg {
            width: 16px;
            height: 16px;
        }

        /* ── Sidebar Overlay ──────────────────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
        }

        /* ── Copy Toast ───────────────────────────────────────────────── */
        .toast {
            position: fixed;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: var(--bg-card-solid);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 20px;
            font-size: 0.82rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
        }

        .toast svg {
            width: 18px;
            height: 18px;
            color: var(--success);
        }

        /* ── Responsive ───────────────────────────────────────────────── */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding: 24px 16px 40px;
            }

            .hamburger {
                display: flex;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .balance-amount {
                font-size: 2rem;
            }

            .balance-stats {
                flex-wrap: wrap;
                gap: 16px;
            }

            .greeting h1 {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .balance-card {
                padding: 24px;
            }

            .balance-stats {
                gap: 12px;
            }

            .favorites-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>

<!-- Background Orbs -->
<div class="bg-orb bg-orb--1"></div>
<div class="bg-orb bg-orb--2"></div>
<div class="bg-orb bg-orb--3"></div>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="app-layout">

    <!-- ─── Sidebar ───────────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            </div>
            <div class="sidebar-brand-text">
                <h2>PaySim</h2>
                <span>UPI Simulator</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu</div>

            <a href="dashboard.php" class="nav-item active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Dashboard
            </a>

            <a href="send-money.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
                Send Money
            </a>

            <a href="request-money.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
                Request Money
            </a>

            <a href="scan-pay.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                </svg>
                Scan & Pay
            </a>

            <div class="nav-section-label">Manage</div>

            <a href="transactions.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Transactions
            </a>

            <a href="add-money.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Add Money
            </a>

            <a href="bank-account.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" />
                </svg>
                Bank Account
            </a>

            <a href="notifications.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                Notifications
                <span class="nav-badge">3</span>
            </a>

            <div class="nav-section-label">Account</div>

            <a href="profile.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Profile
            </a>

            <a href="settings.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Settings
            </a>

            <a href="logout.php" class="nav-item" style="color: #f87171;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                Logout
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="profile.php" class="sidebar-user">
                <div class="sidebar-avatar">
                    <?php echo strtoupper(substr($firstName, 0, 1) . (strpos($userName, ' ') ? substr(explode(' ', $userName)[1], 0, 1) : '')); ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="upi"><?php echo htmlspecialchars($upiId); ?></div>
                </div>
            </a>
        </div>
    </aside>

    <!-- ─── Main Content ──────────────────────────────────────────── -->
    <main class="main-content">

        <!-- Top Header -->
        <div class="top-header">
            <div class="greeting">
                <h1><?php echo $greetIcon; ?> <?php echo $greeting; ?>, <?php echo htmlspecialchars($firstName); ?></h1>
                <p>Here's your financial overview</p>
            </div>
            <div class="header-actions">
                <button class="btn-icon hamburger" onclick="toggleSidebar()" aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <a href="notifications.php" class="btn-icon" aria-label="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <span class="notif-dot"></span>
                </a>
                <a href="profile.php" class="btn-icon" aria-label="Profile">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Balance Card -->
        <div class="balance-card">
            <div class="balance-top">
                <div>
                    <div class="balance-label">Available Balance</div>
                    <div class="balance-amount">
                        <span class="currency">₹</span>
                        <span id="balanceValue"><?php echo number_format($balance, 2); ?></span>
                    </div>
                </div>
                <div class="balance-upi" onclick="copyUPI()" title="Click to copy UPI ID">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                    </svg>
                    <?php echo htmlspecialchars($upiId); ?>
                </div>
            </div>
            <div class="balance-stats">
                <div class="balance-stat">
                    <div class="balance-stat-icon income">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                        </svg>
                    </div>
                    <div class="balance-stat-info">
                        <span>Income</span>
                        <strong>₹<?php echo number_format($monthIncome, 0); ?></strong>
                    </div>
                </div>
                <div class="balance-stat">
                    <div class="balance-stat-icon expense">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" />
                        </svg>
                    </div>
                    <div class="balance-stat-info">
                        <span>Expense</span>
                        <strong>₹<?php echo number_format($monthExpense, 0); ?></strong>
                    </div>
                </div>
                <div class="balance-stat">
                    <div class="balance-stat-icon cashback">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </div>
                    <div class="balance-stat-info">
                        <span>Cashback</span>
                        <strong>₹<?php echo number_format($cashback, 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section-header">
            <h2>Quick Actions</h2>
        </div>
        <div class="quick-actions">
            <a href="send-money.php" class="action-card" id="actionSend">
                <div class="action-icon action-icon--send">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </div>
                <h3>Send Money</h3>
                <p>Transfer to anyone</p>
            </a>
            <a href="request-money.php" class="action-card" id="actionRequest">
                <div class="action-icon action-icon--receive">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                    </svg>
                </div>
                <h3>Request</h3>
                <p>Collect payments</p>
            </a>
            <a href="scan-pay.php" class="action-card" id="actionScan">
                <div class="action-icon action-icon--scan">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    </svg>
                </div>
                <h3>Scan & Pay</h3>
                <p>QR code payment</p>
            </a>
            <a href="add-money.php" class="action-card" id="actionAdd">
                <div class="action-icon action-icon--add">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <h3>Add Money</h3>
                <p>Top up wallet</p>
            </a>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">

            <!-- Transactions -->
            <div class="transactions-card">
                <div class="section-header">
                    <h2>Recent Transactions</h2>
                    <a href="transactions.php">View All →</a>
                </div>
                <ul class="txn-list">
                    <?php foreach ($transactions as $txn): ?>
                    <li>
                        <a href="transaction-details.php?id=<?php echo urlencode($txn['id']); ?>" class="txn-item">
                            <div class="txn-avatar txn-avatar--<?php echo $txn['type']; ?>">
                                <?php echo htmlspecialchars($txn['avatar']); ?>
                            </div>
                            <div class="txn-details">
                                <div class="txn-name">
                                    <?php echo htmlspecialchars($txn['name']); ?>
                                    <?php if ($txn['status'] === 'pending'): ?>
                                        <span class="txn-pending-badge">Pending</span>
                                    <?php endif; ?>
                                </div>
                                <div class="txn-note"><?php echo htmlspecialchars($txn['note']); ?></div>
                            </div>
                            <div class="txn-right">
                                <div class="txn-amount txn-amount--<?php echo $txn['type']; ?>">
                                    <?php echo $txn['type'] === 'debit' ? '−' : '+'; ?>
                                    ₹<?php echo number_format($txn['amount'], 2); ?>
                                </div>
                                <div class="txn-date">
                                    <?php
                                        $dt = new DateTime($txn['date']);
                                        echo $dt->format('d M, h:i A');
                                    ?>
                                </div>
                            </div>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="txn-footer">
                    <a href="transactions.php">See all transactions →</a>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="right-panel">

                <!-- Favorites -->
                <div class="favorites-card">
                    <div class="section-header" style="margin-bottom:0;">
                        <h2>Favourites</h2>
                        <a href="send-money.php">+ Add</a>
                    </div>
                    <div class="favorites-grid">
                        <a href="send-money.php?to=ravi@paysim" class="fav-item">
                            <div class="fav-avatar">RK</div>
                            <span>Ravi</span>
                        </a>
                        <a href="send-money.php?to=priya@paysim" class="fav-item">
                            <div class="fav-avatar" style="background: linear-gradient(135deg, #10b981, #34d399);">PS</div>
                            <span>Priya</span>
                        </a>
                        <a href="send-money.php?to=amit@paysim" class="fav-item">
                            <div class="fav-avatar" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">AD</div>
                            <span>Amit</span>
                        </a>
                        <a href="send-money.php?to=neha@paysim" class="fav-item">
                            <div class="fav-avatar" style="background: linear-gradient(135deg, #ef4444, #f87171);">NM</div>
                            <span>Neha</span>
                        </a>
                        <a href="send-money.php?to=arjun@paysim" class="fav-item">
                            <div class="fav-avatar" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">AJ</div>
                            <span>Arjun</span>
                        </a>
                        <a href="send-money.php?to=demo@paysim" class="fav-item">
                            <div class="fav-avatar" style="background: linear-gradient(135deg, #06b6d4, #22d3ee);">DU</div>
                            <span>Demo</span>
                        </a>
                    </div>
                </div>

                <!-- Promo Card -->
                <div class="promo-card">
                    <span class="promo-badge">🎉 Offer</span>
                    <h3>Earn ₹50 Cashback!</h3>
                    <p>Send money to 3 friends this week and get instant cashback credited to your wallet.</p>
                    <button class="promo-btn" onclick="window.location.href='send-money.php'">
                        Send Now
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

    </main>
</div>

<!-- Toast Notification -->
<div class="toast" id="toast">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    <span>UPI ID copied to clipboard!</span>
</div>

<script>
    // ─── Sidebar Toggle (Mobile) ────────────────────────────────────
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }

    // ─── Copy UPI ID ────────────────────────────────────────────────
    function copyUPI() {
        const upiId = '<?php echo addslashes($upiId); ?>';
        navigator.clipboard.writeText(upiId).then(() => {
            showToast();
        }).catch(() => {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = upiId;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast();
        });
    }

    function showToast() {
        const toast = document.getElementById('toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

    // ─── Animated Balance Counter ───────────────────────────────────
    function animateCounter(element, target, duration = 1200) {
        const start = 0;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            const current = start + (target - start) * eased;
            element.textContent = current.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            if (progress < 1) requestAnimationFrame(update);
        }

        requestAnimationFrame(update);
    }

    // Run counter animation on page load
    window.addEventListener('DOMContentLoaded', () => {
        const balanceEl = document.getElementById('balanceValue');
        const target = <?php echo $balance; ?>;
        animateCounter(balanceEl, target);
    });
</script>

</body>
</html>
