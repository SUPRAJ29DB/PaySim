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

// ─── QR Data ────────────────────────────────────────────────────────────────
// UPI deep link format for QR code
$upiDeepLink = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($userName) . "&cu=INR";

// Recent QR Payments (simulated)
$recentPayments = [
    ['name' => 'Coffee Shop', 'amount' => 180.00, 'time' => '10 min ago', 'type' => 'paid'],
    ['name' => 'Ravi Kumar',  'amount' => 500.00, 'time' => '2 hours ago', 'type' => 'received'],
    ['name' => 'Grocery Mart','amount' => 1245.00,'time' => 'Yesterday',   'type' => 'paid'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Payment — PaySim</title>
    <meta name="description" content="Scan QR codes to pay or share your QR to receive money on PaySim.">

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
            --radius-xl:      24px;
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

        .bg-orb { position: fixed; border-radius: 50%; filter: blur(120px); opacity: 0.3; z-index: 0; pointer-events: none; }
        .bg-orb--1 { width: 600px; height: 600px; background: radial-gradient(circle, var(--accent) 0%, transparent 70%); top: -200px; right: -150px; animation: orbFloat 10s ease-in-out infinite alternate; }
        .bg-orb--2 { width: 450px; height: 450px; background: radial-gradient(circle, #8b5cf6 0%, transparent 70%); bottom: -150px; left: -100px; animation: orbFloat 12s ease-in-out infinite alternate-reverse; }
        @keyframes orbFloat { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(40px,-30px) scale(1.15); } }
        @keyframes cardSlideIn { to { opacity: 1; transform: translateY(0); } }

        .app-layout { display: flex; min-height: 100vh; position: relative; z-index: 1; }

        /* ── Sidebar ──────────────────────────────────────────────────── */
        .sidebar { width: var(--sidebar-width); background: var(--bg-card); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border-right: 1px solid var(--border); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
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
        .nav-badge { margin-left: auto; background: var(--accent); color: #fff; font-size: 0.65rem; font-weight: 700; padding: 2px 7px; border-radius: 10px; }
        .sidebar-footer { padding: 16px; border-top: 1px solid var(--border); }
        .sidebar-user { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; transition: background 0.2s; cursor: pointer; text-decoration: none; }
        .sidebar-user:hover { background: var(--bg-hover); }
        .sidebar-avatar { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: #fff; flex-shrink: 0; }
        .sidebar-user-info .name { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
        .sidebar-user-info .upi { font-size: 0.72rem; color: var(--text-muted); }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; }

        /* ── Main ─────────────────────────────────────────────────────── */
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 32px 36px 48px; min-height: 100vh; }

        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; }
        .page-header-left { display: flex; align-items: center; gap: 16px; }
        .back-btn { width: 40px; height: 40px; border-radius: 10px; background: var(--bg-card); backdrop-filter: blur(16px); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .back-btn:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }
        .back-btn svg { width: 20px; height: 20px; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; }
        .page-header p { color: var(--text-secondary); font-size: 0.85rem; margin-top: 2px; }
        .hamburger { display: none; }
        .btn-icon { width: 42px; height: 42px; border-radius: 12px; background: var(--bg-card); backdrop-filter: blur(16px); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-icon:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }
        .btn-icon svg { width: 20px; height: 20px; }

        /* ── Tab Switcher ─────────────────────────────────────────────── */
        .tab-switcher {
            display: flex;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 5px;
            margin-bottom: 32px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            backdrop-filter: blur(16px);
            animation: cardSlideIn 0.4s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0; transform: translateY(20px);
        }

        .tab-btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border: none;
            background: transparent;
            font-family: inherit;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .tab-btn:hover { color: var(--text-secondary); }

        .tab-btn.active {
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 16px var(--accent-glow);
        }

        .tab-btn svg { width: 18px; height: 18px; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* ── QR Display Card ──────────────────────────────────────────── */
        .qr-section {
            max-width: 520px;
            margin: 0 auto;
        }

        .qr-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 40px;
            text-align: center;
            animation: cardSlideIn 0.5s cubic-bezier(0.16,1,0.3,1) 0.1s forwards;
            opacity: 0; transform: translateY(20px);
        }

        .qr-card-title {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 24px;
        }

        .qr-wrapper {
            display: inline-block;
            background: #fff;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
            position: relative;
            box-shadow: 0 8px 40px rgba(0,0,0,0.3);
        }

        .qr-wrapper canvas {
            display: block;
            border-radius: 8px;
        }

        .qr-logo-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(99,102,241,0.4);
        }

        .qr-logo-overlay svg { width: 24px; height: 24px; color: #fff; }

        .qr-user-info {
            margin-bottom: 24px;
        }

        .qr-user-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .qr-upi-id {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--accent-hover);
            font-weight: 600;
            background: rgba(99,102,241,0.1);
            padding: 6px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .qr-upi-id:hover { background: rgba(99,102,241,0.2); }
        .qr-upi-id svg { width: 14px; height: 14px; }

        .qr-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .qr-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .qr-action-btn svg { width: 18px; height: 18px; }

        .qr-action-btn--primary {
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
        }

        .qr-action-btn--primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px var(--accent-glow);
        }

        .qr-action-btn--secondary {
            background: rgba(99,102,241,0.1);
            color: var(--accent-hover);
            border: 1px solid rgba(99,102,241,0.2);
        }

        .qr-action-btn--secondary:hover {
            background: rgba(99,102,241,0.2);
        }

        /* ── Amount Input ─────────────────────────────────────────────── */
        .amount-section {
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .amount-section label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 10px;
        }

        .amount-input-group {
            display: flex;
            align-items: center;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: border-color 0.25s, box-shadow 0.25s;
            max-width: 300px;
            margin: 0 auto 16px;
        }

        .amount-input-group:focus-within {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .amount-prefix {
            padding: 12px 14px;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-muted);
            border-right: 1px solid var(--border);
            background: rgba(99,102,241,0.05);
        }

        .amount-input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 12px 14px;
            font-family: inherit;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            outline: none;
            text-align: center;
        }

        .amount-input::placeholder { color: var(--text-muted); }

        .amount-note {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* ── Scan Tab ─────────────────────────────────────────────────── */
        .scan-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 48px 40px;
            text-align: center;
            max-width: 520px;
            margin: 0 auto;
            animation: cardSlideIn 0.5s cubic-bezier(0.16,1,0.3,1) 0.1s forwards;
            opacity: 0; transform: translateY(20px);
        }

        .scan-area {
            width: 260px;
            height: 260px;
            margin: 0 auto 28px;
            border-radius: 20px;
            background: rgba(99,102,241,0.05);
            border: 2px dashed rgba(99,102,241,0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }

        /* Animated scanner corners */
        .scan-corner {
            position: absolute;
            width: 32px;
            height: 32px;
        }

        .scan-corner::before,
        .scan-corner::after {
            content: '';
            position: absolute;
            background: var(--accent);
            border-radius: 2px;
        }

        .scan-corner--tl { top: 12px; left: 12px; }
        .scan-corner--tl::before { width: 24px; height: 3px; top: 0; left: 0; }
        .scan-corner--tl::after  { width: 3px; height: 24px; top: 0; left: 0; }

        .scan-corner--tr { top: 12px; right: 12px; }
        .scan-corner--tr::before { width: 24px; height: 3px; top: 0; right: 0; }
        .scan-corner--tr::after  { width: 3px; height: 24px; top: 0; right: 0; }

        .scan-corner--bl { bottom: 12px; left: 12px; }
        .scan-corner--bl::before { width: 24px; height: 3px; bottom: 0; left: 0; }
        .scan-corner--bl::after  { width: 3px; height: 24px; bottom: 0; left: 0; }

        .scan-corner--br { bottom: 12px; right: 12px; }
        .scan-corner--br::before { width: 24px; height: 3px; bottom: 0; right: 0; }
        .scan-corner--br::after  { width: 3px; height: 24px; bottom: 0; right: 0; }

        /* Scan line animation */
        .scan-line {
            position: absolute;
            left: 16px;
            right: 16px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            animation: scanMove 2.5s ease-in-out infinite;
            box-shadow: 0 0 12px var(--accent-glow);
        }

        @keyframes scanMove {
            0%, 100% { top: 20px; }
            50%      { top: calc(100% - 20px); }
        }

        .scan-area-icon svg {
            width: 48px;
            height: 48px;
            color: var(--accent-hover);
            opacity: 0.5;
        }

        .scan-area-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .scan-instructions {
            margin-bottom: 24px;
        }

        .scan-instructions h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .scan-instructions p {
            font-size: 0.82rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .scan-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 20px;
        }

        .scan-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px var(--accent-glow);
        }

        .scan-btn svg { width: 20px; height: 20px; }

        .scan-or {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .scan-or::before,
        .scan-or::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .upload-btn:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
            border-color: var(--border-focus);
        }

        .upload-btn svg { width: 18px; height: 18px; }

        /* ── Recent QR Payments ────────────────────────────────────────── */
        .recent-section {
            max-width: 520px;
            margin: 32px auto 0;
            animation: cardSlideIn 0.5s cubic-bezier(0.16,1,0.3,1) 0.2s forwards;
            opacity: 0; transform: translateY(20px);
        }

        .recent-header {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .recent-header::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .recent-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .recent-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            transition: all 0.2s;
        }

        .recent-item:hover {
            border-color: var(--border-focus);
            background: rgba(99,102,241,0.04);
        }

        .recent-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .recent-icon--paid { background: var(--error-bg); }
        .recent-icon--paid svg { color: var(--error); }
        .recent-icon--received { background: var(--success-bg); }
        .recent-icon--received svg { color: var(--success); }
        .recent-icon svg { width: 20px; height: 20px; }

        .recent-details { flex: 1; }
        .recent-name { font-size: 0.88rem; font-weight: 600; margin-bottom: 2px; }
        .recent-time { font-size: 0.72rem; color: var(--text-muted); }
        .recent-amount { font-size: 0.92rem; font-weight: 700; }
        .recent-amount--paid { color: #f87171; }
        .recent-amount--received { color: var(--success); }

        /* ── Toast ────────────────────────────────────────────────────── */
        .toast {
            position: fixed; bottom: 32px; left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: var(--bg-card-solid); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 12px 20px;
            font-size: 0.82rem; color: var(--text-primary);
            display: flex; align-items: center; gap: 8px;
            z-index: 1000; transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }
        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast svg { width: 18px; height: 18px; color: var(--success); }

        /* ── Responsive ───────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; padding: 24px 16px 40px; }
            .hamburger { display: flex; }
            .qr-card, .scan-card { padding: 28px 20px; }
            .scan-area { width: 220px; height: 220px; }
            .qr-actions { flex-direction: column; }
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
            <a href="notifications.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>Notifications<span class="nav-badge">3</span></a>
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

    <!-- ─── Main ──────────────────────────────────────────────────── -->
    <main class="main-content">

        <div class="page-header">
            <div class="page-header-left">
                <a href="dashboard.php" class="back-btn" title="Back to Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                </a>
                <div>
                    <h1>QR Payment</h1>
                    <p>Scan or share QR to pay & receive</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-icon hamburger" onclick="toggleSidebar()" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
            </div>
        </div>

        <!-- Tab Switcher -->
        <div class="tab-switcher">
            <button class="tab-btn active" onclick="switchTab('myqr', this)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z" /></svg>
                My QR Code
            </button>
            <button class="tab-btn" onclick="switchTab('scan', this)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H4.5m0 0v3m0-3h.75M7.5 20.25H4.5m0 0v-3m0 3h.75M16.5 3.75h3m0 0v3m0-3h-.75M16.5 20.25h3m0 0v-3m0 3h-.75M12 8.25v7.5m3.75-3.75h-7.5" /></svg>
                Scan QR
            </button>
        </div>

        <!-- Tab 1: My QR Code -->
        <div class="tab-content active" id="tab-myqr">
            <div class="qr-section">
                <div class="qr-card">
                    <div class="qr-card-title">Share this QR to receive payments</div>

                    <div class="qr-wrapper">
                        <canvas id="qrCanvas" width="200" height="200"></canvas>
                        <div class="qr-logo-overlay">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        </div>
                    </div>

                    <div class="qr-user-info">
                        <div class="qr-user-name"><?php echo htmlspecialchars($userName); ?></div>
                        <div class="qr-upi-id" onclick="copyUPI()" title="Click to copy">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                            <?php echo htmlspecialchars($upiId); ?>
                        </div>
                    </div>

                    <div class="qr-actions">
                        <button class="qr-action-btn qr-action-btn--primary" onclick="downloadQR()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Download QR
                        </button>
                        <button class="qr-action-btn qr-action-btn--secondary" onclick="shareQR()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
                            Share
                        </button>
                    </div>

                    <!-- Optional Amount -->
                    <div class="amount-section">
                        <label for="qrAmount">Set Amount (Optional)</label>
                        <div class="amount-input-group">
                            <span class="amount-prefix">₹</span>
                            <input type="number" id="qrAmount" class="amount-input" placeholder="0.00" min="1" step="0.01" oninput="updateQR()">
                        </div>
                        <div class="amount-note">Set an amount to generate a fixed-amount QR code</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Scan QR -->
        <div class="tab-content" id="tab-scan">
            <div class="scan-card">
                <div class="scan-area">
                    <div class="scan-corner scan-corner--tl"></div>
                    <div class="scan-corner scan-corner--tr"></div>
                    <div class="scan-corner scan-corner--bl"></div>
                    <div class="scan-corner scan-corner--br"></div>
                    <div class="scan-line"></div>
                    <div class="scan-area-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                    </div>
                    <div class="scan-area-text">Position QR code here</div>
                </div>

                <div class="scan-instructions">
                    <h3>Scan any UPI QR Code</h3>
                    <p>Point your camera at a merchant or person's QR code to make an instant payment.</p>
                </div>

                <button class="scan-btn" onclick="openCamera()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                    Open Camera
                </button>

                <div class="scan-or">or</div>

                <button class="upload-btn" onclick="document.getElementById('qrUpload').click()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    Upload QR Image
                </button>
                <input type="file" id="qrUpload" accept="image/*" style="display:none;" onchange="handleUpload(event)">
            </div>
        </div>

        <!-- Recent QR Payments -->
        <div class="recent-section">
            <div class="recent-header">Recent QR Payments</div>
            <div class="recent-list">
                <?php foreach ($recentPayments as $payment): ?>
                <div class="recent-item">
                    <div class="recent-icon recent-icon--<?php echo $payment['type']; ?>">
                        <?php if ($payment['type'] === 'paid'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" /></svg>
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" /></svg>
                        <?php endif; ?>
                    </div>
                    <div class="recent-details">
                        <div class="recent-name"><?php echo htmlspecialchars($payment['name']); ?></div>
                        <div class="recent-time"><?php echo $payment['time']; ?></div>
                    </div>
                    <div class="recent-amount recent-amount--<?php echo $payment['type']; ?>">
                        <?php echo $payment['type'] === 'paid' ? '−' : '+'; ?>
                        ₹<?php echo number_format($payment['amount'], 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>
</div>

<!-- Toast -->
<div class="toast" id="toast">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
    <span id="toastText">Copied!</span>
</div>

<!-- QR Code Generator (lightweight vanilla JS) -->
<script>
    // ─── Minimal QR Code Generator ──────────────────────────────────
    // Using a simple QR encoding approach with Canvas
    const QR_DATA = '<?php echo addslashes($upiDeepLink); ?>';

    function generateQR(data, canvas, size) {
        const ctx = canvas.getContext('2d');
        canvas.width = size;
        canvas.height = size;

        // Create a simple visual QR pattern (simulated for demo)
        // In production, use a library like qrcode.js
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);

        const moduleCount = 25;
        const moduleSize = size / moduleCount;

        // Seed from data for consistent pattern
        let seed = 0;
        for (let i = 0; i < data.length; i++) seed += data.charCodeAt(i);

        function seededRandom() {
            seed = (seed * 16807) % 2147483647;
            return (seed - 1) / 2147483646;
        }

        ctx.fillStyle = '#1e1b4b';

        // Draw finder patterns (the 3 big squares)
        function drawFinder(x, y) {
            const s = moduleSize;
            // Outer
            ctx.fillRect(x * s, y * s, 7 * s, 7 * s);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect((x + 1) * s, (y + 1) * s, 5 * s, 5 * s);
            ctx.fillStyle = '#1e1b4b';
            ctx.fillRect((x + 2) * s, (y + 2) * s, 3 * s, 3 * s);
        }

        drawFinder(0, 0);
        drawFinder(moduleCount - 7, 0);
        drawFinder(0, moduleCount - 7);

        // Draw data modules
        for (let row = 0; row < moduleCount; row++) {
            for (let col = 0; col < moduleCount; col++) {
                // Skip finder patterns area
                if ((row < 8 && col < 8) || (row < 8 && col > moduleCount - 9) || (row > moduleCount - 9 && col < 8)) continue;
                // Skip center for logo
                if (Math.abs(row - moduleCount/2) < 3 && Math.abs(col - moduleCount/2) < 3) continue;

                if (seededRandom() > 0.5) {
                    ctx.fillStyle = '#1e1b4b';
                    ctx.beginPath();
                    ctx.roundRect(col * moduleSize + 0.5, row * moduleSize + 0.5, moduleSize - 1, moduleSize - 1, 1);
                    ctx.fill();
                }
            }
        }

        // Timing patterns
        ctx.fillStyle = '#1e1b4b';
        for (let i = 8; i < moduleCount - 8; i++) {
            if (i % 2 === 0) {
                ctx.fillRect(i * moduleSize, 6 * moduleSize, moduleSize, moduleSize);
                ctx.fillRect(6 * moduleSize, i * moduleSize, moduleSize, moduleSize);
            }
        }
    }

    // Generate on load
    window.addEventListener('DOMContentLoaded', () => {
        generateQR(QR_DATA, document.getElementById('qrCanvas'), 200);
    });

    function updateQR() {
        const amount = document.getElementById('qrAmount').value;
        let data = QR_DATA;
        if (amount && parseFloat(amount) > 0) {
            data += '&am=' + parseFloat(amount).toFixed(2);
        }
        generateQR(data, document.getElementById('qrCanvas'), 200);
    }

    // ─── Tab Switching ──────────────────────────────────────────────
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
    }

    // ─── Sidebar ────────────────────────────────────────────────────
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }

    // ─── Copy UPI ───────────────────────────────────────────────────
    function copyUPI() {
        const upi = '<?php echo addslashes($upiId); ?>';
        navigator.clipboard.writeText(upi).then(() => {
            showToast('UPI ID copied to clipboard!');
        }).catch(() => {
            const t = document.createElement('textarea');
            t.value = upi;
            document.body.appendChild(t);
            t.select();
            document.execCommand('copy');
            document.body.removeChild(t);
            showToast('UPI ID copied to clipboard!');
        });
    }

    // ─── Download QR ────────────────────────────────────────────────
    function downloadQR() {
        const canvas = document.getElementById('qrCanvas');
        const link = document.createElement('a');
        link.download = 'paysim-qr-<?php echo strtolower($username ?? "user"); ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        showToast('QR code downloaded!');
    }

    // ─── Share ──────────────────────────────────────────────────────
    function shareQR() {
        if (navigator.share) {
            navigator.share({
                title: 'Pay <?php echo addslashes($userName); ?> via PaySim',
                text: 'UPI ID: <?php echo addslashes($upiId); ?>',
                url: window.location.href
            }).catch(() => {});
        } else {
            copyUPI();
        }
    }

    // ─── Camera (placeholder) ───────────────────────────────────────
    function openCamera() {
        showToast('Camera access is a demo feature');
    }

    function handleUpload(event) {
        const file = event.target.files[0];
        if (file) {
            showToast('QR image uploaded — processing...');
            // In production, decode the QR from the image
            setTimeout(() => showToast('Demo: Redirecting to payment...'), 1500);
        }
    }

    // ─── Toast ──────────────────────────────────────────────────────
    function showToast(message) {
        const toast = document.getElementById('toast');
        document.getElementById('toastText').textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }
</script>

</body>
</html>
