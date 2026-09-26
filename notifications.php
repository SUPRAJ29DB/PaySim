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
$firstName = explode(' ', $userName)[0];
$avatarInitials = strtoupper(substr($firstName, 0, 1) . (strpos($userName, ' ') ? substr(explode(' ', $userName)[1], 0, 1) : ''));

// ─── Simulated Notifications ────────────────────────────────────────────────
$notifications = [
    [
        'id'       => 1,
        'type'     => 'credit',
        'title'    => 'Money Received',
        'message'  => 'You received ₹1,200.00 from Priya Sharma.',
        'time'     => '2 minutes ago',
        'read'     => false,
        'icon'     => 'arrow-down',
        'color'    => 'success',
    ],
    [
        'id'       => 2,
        'type'     => 'promo',
        'title'    => '🎉 Cashback Earned!',
        'message'  => 'You earned ₹25 cashback on your last transaction. Keep transacting to earn more!',
        'time'     => '15 minutes ago',
        'read'     => false,
        'icon'     => 'gift',
        'color'    => 'warning',
    ],
    [
        'id'       => 3,
        'type'     => 'request',
        'title'    => 'Payment Request',
        'message'  => 'Amit Das has requested ₹200.00 for "Chai money".',
        'time'     => '1 hour ago',
        'read'     => false,
        'icon'     => 'request',
        'color'    => 'accent',
        'action'   => true,
    ],
    [
        'id'       => 4,
        'type'     => 'debit',
        'title'    => 'Payment Sent',
        'message'  => '₹500.00 sent to Ravi Kumar successfully.',
        'time'     => '3 hours ago',
        'read'     => true,
        'icon'     => 'arrow-up',
        'color'    => 'error',
    ],
    [
        'id'       => 5,
        'type'     => 'security',
        'title'    => 'Login from New Device',
        'message'  => 'Your account was accessed from a new device. If this wasn\'t you, change your password immediately.',
        'time'     => '5 hours ago',
        'read'     => true,
        'icon'     => 'shield',
        'color'    => 'warning',
    ],
    [
        'id'       => 6,
        'type'     => 'system',
        'title'    => 'KYC Verified',
        'message'  => 'Your KYC verification is complete. You can now enjoy unlimited transactions.',
        'time'     => 'Yesterday',
        'read'     => true,
        'icon'     => 'check',
        'color'    => 'success',
    ],
    [
        'id'       => 7,
        'type'     => 'promo',
        'title'    => 'New Feature: Scan & Pay',
        'message'  => 'Use our new QR scanner to pay merchants instantly. Try it now!',
        'time'     => 'Yesterday',
        'read'     => true,
        'icon'     => 'qr',
        'color'    => 'accent',
    ],
    [
        'id'       => 8,
        'type'     => 'credit',
        'title'    => 'Money Received',
        'message'  => 'You received ₹12,400.00 from Salary Credit.',
        'time'     => '2 days ago',
        'read'     => true,
        'icon'     => 'arrow-down',
        'color'    => 'success',
    ],
    [
        'id'       => 9,
        'type'     => 'debit',
        'title'    => 'Bill Payment',
        'message'  => '₹649.00 paid to Netflix for subscription renewal.',
        'time'     => '3 days ago',
        'read'     => true,
        'icon'     => 'arrow-up',
        'color'    => 'error',
    ],
    [
        'id'       => 10,
        'type'     => 'system',
        'title'    => 'Welcome to PaySim!',
        'message'  => 'Thanks for joining PaySim. Set up your UPI PIN to start making payments.',
        'time'     => '1 week ago',
        'read'     => true,
        'icon'     => 'star',
        'color'    => 'accent',
    ],
];

$unreadCount = count(array_filter($notifications, fn($n) => !$n['read']));
$todayNotifs = array_filter($notifications, fn($n) => !$n['read'] || in_array($n['time'], ['2 minutes ago', '15 minutes ago', '1 hour ago', '3 hours ago', '5 hours ago']));
$olderNotifs = array_filter($notifications, fn($n) => !in_array($n['id'], array_column(iterator_to_array($todayNotifs), 'id') ?: array_map(fn($x) => $x['id'], $todayNotifs)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications — PaySim</title>
    <meta name="description" content="View your PaySim payment notifications, alerts, and updates.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
            --error:          #ef4444;
            --error-bg:       rgba(239, 68, 68, 0.1);
            --warning:        #f59e0b;
            --warning-bg:     rgba(245, 158, 11, 0.1);
            --radius:         12px;
            --radius-lg:      20px;
            --sidebar-width:  280px;
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
        .bg-orb { position: fixed; border-radius: 50%; filter: blur(120px); opacity: 0.3; z-index: 0; pointer-events: none; }
        .bg-orb--1 { width: 600px; height: 600px; background: radial-gradient(circle, var(--accent) 0%, transparent 70%); top: -200px; right: -150px; animation: orbFloat 10s ease-in-out infinite alternate; }
        .bg-orb--2 { width: 450px; height: 450px; background: radial-gradient(circle, #8b5cf6 0%, transparent 70%); bottom: -150px; left: -100px; animation: orbFloat 12s ease-in-out infinite alternate-reverse; }
        @keyframes orbFloat { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(40px,-30px) scale(1.15); } }
        @keyframes cardSlideIn { to { opacity: 1; transform: translateY(0); } }

        /* ── Layout ───────────────────────────────────────────────────── */
        .app-layout { display: flex; min-height: 100vh; position: relative; z-index: 1; }

        /* ── Sidebar ──────────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width); background: var(--bg-card);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid var(--border); display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .sidebar-brand { padding: 24px 24px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 14px; }
        .sidebar-brand-icon { width: 42px; height: 42px; background: linear-gradient(135deg, var(--accent), #8b5cf6); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px var(--accent-glow); flex-shrink: 0; }
        .sidebar-brand-icon svg { width: 22px; height: 22px; color: #fff; }
        .sidebar-brand-text h2 { font-size: 1.15rem; font-weight: 700; background: linear-gradient(135deg, var(--text-primary), var(--accent-hover)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .sidebar-brand-text span { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-section-label { font-size: 0.65rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; padding: 8px 12px 6px; margin-top: 8px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 10px; color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: all 0.2s; position: relative; margin-bottom: 2px; }
        .nav-item:hover { background: var(--bg-hover); color: var(--text-primary); }
        .nav-item.active { background: rgba(99,102,241,0.12); color: var(--accent-hover); }
        .nav-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: var(--accent); border-radius: 0 3px 3px 0; }
        .nav-item svg { width: 20px; height: 20px; flex-shrink: 0; opacity: 0.7; }
        .nav-item.active svg { opacity: 1; }
        .nav-badge { margin-left: auto; background: var(--accent); color: #fff; font-size: 0.65rem; font-weight: 700; padding: 2px 7px; border-radius: 10px; min-width: 20px; text-align: center; }
        .sidebar-footer { padding: 16px; border-top: 1px solid var(--border); }
        .sidebar-user { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; transition: background 0.2s; cursor: pointer; text-decoration: none; }
        .sidebar-user:hover { background: var(--bg-hover); }
        .sidebar-avatar { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: #fff; flex-shrink: 0; }
        .sidebar-user-info .name { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-info .upi { font-size: 0.72rem; color: var(--text-muted); }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; }

        /* ── Main ─────────────────────────────────────────────────────── */
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 32px 36px 48px; min-height: 100vh; max-width: 900px; }

        /* ── Page Header ──────────────────────────────────────────────── */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .page-header-left { display: flex; align-items: center; gap: 16px; }
        .back-btn { width: 40px; height: 40px; border-radius: 10px; background: var(--bg-card); backdrop-filter: blur(16px); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .back-btn:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }
        .back-btn svg { width: 20px; height: 20px; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; }
        .page-header p { color: var(--text-secondary); font-size: 0.85rem; margin-top: 2px; }
        .header-actions { display: flex; align-items: center; gap: 10px; }
        .hamburger { display: none; }
        .btn-icon { width: 42px; height: 42px; border-radius: 12px; background: var(--bg-card); backdrop-filter: blur(16px); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-icon:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }
        .btn-icon svg { width: 20px; height: 20px; }

        .btn-text {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px; border-radius: 10px;
            background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.2);
            color: var(--accent-hover); font-size: 0.8rem; font-weight: 600;
            cursor: pointer; font-family: inherit; transition: all 0.2s;
        }
        .btn-text:hover { background: rgba(99,102,241,0.2); border-color: rgba(99,102,241,0.4); }
        .btn-text svg { width: 16px; height: 16px; }

        /* ── Filter Tabs ──────────────────────────────────────────────── */
        .filter-tabs {
            display: flex; gap: 6px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 5px;
            margin-bottom: 24px;
            backdrop-filter: blur(16px);
            animation: cardSlideIn 0.4s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0; transform: translateY(20px);
            overflow-x: auto;
        }

        .filter-tab {
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border: none;
            background: transparent;
            font-family: inherit;
            transition: all 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-tab:hover { color: var(--text-secondary); background: var(--bg-hover); }

        .filter-tab.active {
            background: rgba(99,102,241,0.12);
            color: var(--accent-hover);
        }

        .filter-count {
            background: var(--accent);
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 8px;
            min-width: 18px;
            text-align: center;
        }

        /* ── Notification Sections ────────────────────────────────────── */
        .notif-section-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 12px 0 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notif-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Notification Cards ───────────────────────────────────────── */
        .notif-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 24px;
        }

        .notif-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: all 0.25s;
            cursor: pointer;
            animation: cardSlideIn 0.4s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0; transform: translateY(12px);
            position: relative;
        }

        .notif-card:nth-child(1) { animation-delay: 0.05s; }
        .notif-card:nth-child(2) { animation-delay: 0.1s; }
        .notif-card:nth-child(3) { animation-delay: 0.15s; }
        .notif-card:nth-child(4) { animation-delay: 0.2s; }
        .notif-card:nth-child(5) { animation-delay: 0.25s; }

        .notif-card:hover {
            border-color: var(--border-focus);
            background: rgba(99,102,241,0.04);
        }

        .notif-card.unread {
            border-left: 3px solid var(--accent);
            background: rgba(99,102,241,0.03);
        }

        .notif-card.unread::after {
            content: '';
            position: absolute;
            top: 20px; right: 20px;
            width: 8px; height: 8px;
            background: var(--accent);
            border-radius: 50%;
        }

        .notif-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .notif-icon svg { width: 22px; height: 22px; }

        .notif-icon--success { background: var(--success-bg); }
        .notif-icon--success svg { color: var(--success); }
        .notif-icon--error { background: var(--error-bg); }
        .notif-icon--error svg { color: var(--error); }
        .notif-icon--warning { background: var(--warning-bg); }
        .notif-icon--warning svg { color: var(--warning); }
        .notif-icon--accent { background: rgba(99,102,241,0.1); }
        .notif-icon--accent svg { color: var(--accent-hover); }

        .notif-body { flex: 1; min-width: 0; }

        .notif-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 4px;
            padding-right: 24px;
        }

        .notif-message {
            font-size: 0.82rem;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .notif-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notif-time {
            font-size: 0.72rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .notif-time svg { width: 12px; height: 12px; }

        .notif-type-badge {
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .notif-type-badge--credit { background: var(--success-bg); color: var(--success); }
        .notif-type-badge--debit { background: var(--error-bg); color: var(--error); }
        .notif-type-badge--promo { background: var(--warning-bg); color: var(--warning); }
        .notif-type-badge--request { background: rgba(99,102,241,0.1); color: var(--accent-hover); }
        .notif-type-badge--security { background: rgba(245,158,11,0.1); color: var(--warning); }
        .notif-type-badge--system { background: rgba(99,102,241,0.1); color: var(--accent-hover); }

        /* ── Action Buttons ───────────────────────────────────────────── */
        .notif-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .notif-action-btn {
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .notif-action-btn--pay {
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
        }
        .notif-action-btn--pay:hover {
            box-shadow: 0 4px 16px var(--accent-glow);
            transform: translateY(-1px);
        }

        .notif-action-btn--decline {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .notif-action-btn--decline:hover {
            background: var(--bg-hover);
            color: var(--text-secondary);
        }

        /* ── Empty State ──────────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            animation: cardSlideIn 0.5s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0; transform: translateY(20px);
        }

        .empty-state-icon {
            width: 72px; height: 72px;
            border-radius: 20px;
            background: rgba(99,102,241,0.1);
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }

        .empty-state-icon svg { width: 32px; height: 32px; color: var(--accent-hover); }
        .empty-state h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }
        .empty-state p { font-size: 0.85rem; color: var(--text-secondary); max-width: 320px; margin: 0 auto; }

        /* ── Responsive ───────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; padding: 24px 16px 40px; }
            .hamburger { display: flex; }
            .filter-tabs { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        }
    </style>
</head>
<body>

<div class="bg-orb bg-orb--1"></div>
<div class="bg-orb bg-orb--2"></div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="app-layout">

    <!-- ─── Sidebar ───────────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
            </div>
            <div class="sidebar-brand-text"><h2>PaySim</h2><span>UPI Simulator</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu</div>
            <a href="dashboard.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>Dashboard</a>
            <a href="send-money.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>Send Money</a>
            <a href="request-money.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>Request Money</a>
            <a href="scan-pay.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" /></svg>Scan & Pay</a>
            <div class="nav-section-label">Manage</div>
            <a href="transactions.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>Transactions</a>
            <a href="add-money.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>Add Money</a>
            <a href="bank-account.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" /></svg>Bank Account</a>
            <a href="notifications.php" class="nav-item active"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>Notifications<span class="nav-badge"><?php echo $unreadCount; ?></span></a>
            <div class="nav-section-label">Account</div>
            <a href="profile.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>Profile</a>
            <a href="settings.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>Settings</a>
            <a href="logout.php" class="nav-item" style="color:#f87171;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>Logout</a>
        </nav>
        <div class="sidebar-footer">
            <a href="profile.php" class="sidebar-user">
                <div class="sidebar-avatar"><?php echo $avatarInitials; ?></div>
                <div class="sidebar-user-info">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="upi"><?php echo htmlspecialchars($upiId); ?></div>
                </div>
            </a>
        </div>
    </aside>

    <!-- ─── Main Content ──────────────────────────────────────────── -->
    <main class="main-content">

        <!-- Header -->
        <div class="page-header">
            <div class="page-header-left">
                <a href="dashboard.php" class="back-btn" title="Back to Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                </a>
                <div>
                    <h1>Notifications</h1>
                    <p><?php echo $unreadCount; ?> unread notification<?php echo $unreadCount !== 1 ? 's' : ''; ?></p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-icon hamburger" onclick="toggleSidebar()" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
                <button class="btn-text" onclick="markAllRead()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Mark all read
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterNotifs('all', this)">
                All
                <span class="filter-count"><?php echo count($notifications); ?></span>
            </button>
            <button class="filter-tab" onclick="filterNotifs('unread', this)">
                Unread
                <span class="filter-count"><?php echo $unreadCount; ?></span>
            </button>
            <button class="filter-tab" onclick="filterNotifs('credit', this)">💰 Received</button>
            <button class="filter-tab" onclick="filterNotifs('debit', this)">📤 Sent</button>
            <button class="filter-tab" onclick="filterNotifs('request', this)">📨 Requests</button>
            <button class="filter-tab" onclick="filterNotifs('promo', this)">🎁 Offers</button>
            <button class="filter-tab" onclick="filterNotifs('security', this)">🔒 Security</button>
        </div>

        <!-- Today / Recent -->
        <div class="notif-section-label">Today</div>
        <div class="notif-list" id="notifList">
            <?php foreach ($notifications as $notif): ?>
            <div class="notif-card <?php echo !$notif['read'] ? 'unread' : ''; ?>" data-type="<?php echo $notif['type']; ?>" data-read="<?php echo $notif['read'] ? '1' : '0'; ?>">
                <div class="notif-icon notif-icon--<?php echo $notif['color']; ?>">
                    <?php if ($notif['icon'] === 'arrow-down'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                    <?php elseif ($notif['icon'] === 'arrow-up'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" /></svg>
                    <?php elseif ($notif['icon'] === 'gift'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                    <?php elseif ($notif['icon'] === 'request'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" /></svg>
                    <?php elseif ($notif['icon'] === 'shield'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    <?php elseif ($notif['icon'] === 'check'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>
                    <?php elseif ($notif['icon'] === 'qr'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" /></svg>
                    <?php elseif ($notif['icon'] === 'star'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                    <?php endif; ?>
                </div>
                <div class="notif-body">
                    <div class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                    <div class="notif-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                    <div class="notif-meta">
                        <span class="notif-time">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <?php echo $notif['time']; ?>
                        </span>
                        <span class="notif-type-badge notif-type-badge--<?php echo $notif['type']; ?>">
                            <?php echo ucfirst($notif['type']); ?>
                        </span>
                    </div>
                    <?php if (isset($notif['action']) && $notif['action']): ?>
                    <div class="notif-actions">
                        <button class="notif-action-btn notif-action-btn--pay" onclick="window.location.href='send-money.php'">Pay ₹200</button>
                        <button class="notif-action-btn notif-action-btn--decline">Decline</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty state (hidden by default) -->
        <div class="empty-state" id="emptyState" style="display:none;">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
            </div>
            <h3>No notifications</h3>
            <p>No notifications match this filter. Try selecting a different category.</p>
        </div>

    </main>
</div>

<script>
    // ─── Sidebar ────────────────────────────────────────────────────
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }

    // ─── Filter ─────────────────────────────────────────────────────
    function filterNotifs(type, btn) {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');

        const cards = document.querySelectorAll('.notif-card');
        let visible = 0;

        cards.forEach(card => {
            const cardType = card.dataset.type;
            const isRead = card.dataset.read === '1';

            let show = false;
            if (type === 'all') show = true;
            else if (type === 'unread') show = !isRead;
            else show = cardType === type;

            card.style.display = show ? 'flex' : 'none';
            if (show) visible++;
        });

        document.getElementById('emptyState').style.display = visible === 0 ? 'block' : 'none';
    }

    // ─── Mark All Read ──────────────────────────────────────────────
    function markAllRead() {
        document.querySelectorAll('.notif-card.unread').forEach(card => {
            card.classList.remove('unread');
            card.dataset.read = '1';
        });
        // Update unread count in header
        const subtitle = document.querySelector('.page-header p');
        if (subtitle) subtitle.textContent = '0 unread notifications';
    }
</script>

</body>
</html>
