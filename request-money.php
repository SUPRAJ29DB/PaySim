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

// ─── Simulated Pending Requests ─────────────────────────────────────────────
$pendingRequests = [
    [
        'id'     => 'REQ001',
        'from'   => 'Ravi Kumar',
        'upi'    => 'ravi@oksbi',
        'amount' => 500.00,
        'note'   => 'Lunch split',
        'date'   => '2026-09-18 14:32',
        'avatar' => 'RK',
    ],
    [
        'id'     => 'REQ002',
        'from'   => 'Priya Sharma',
        'upi'    => 'priya@paytm',
        'amount' => 1200.00,
        'note'   => 'Movie tickets',
        'date'   => '2026-09-17 11:20',
        'avatar' => 'PS',
    ],
    [
        'id'     => 'REQ003',
        'from'   => 'Amit Das',
        'upi'    => 'amit@ybl',
        'amount' => 350.00,
        'note'   => 'Petrol',
        'date'   => '2026-09-16 09:45',
        'avatar' => 'AD',
    ],
];

$sentRequests = [
    [
        'id'     => 'REQ004',
        'to'     => 'Neha Singh',
        'upi'    => 'neha@okicici',
        'amount' => 800.00,
        'note'   => 'Rent share',
        'date'   => '2026-09-15 16:00',
        'avatar' => 'NS',
        'status' => 'completed',
    ],
    [
        'id'     => 'REQ005',
        'to'     => 'Karan Mehta',
        'upi'    => 'karan@okaxis',
        'amount' => 240.00,
        'note'   => 'Coffee',
        'date'   => '2026-09-14 10:30',
        'avatar' => 'KM',
        'status' => 'expired',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Money — PaySim</title>
    <meta name="description" content="Request money from anyone using PaySim UPI. Generate payment links and QR codes instantly.">

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
            --success-border: rgba(16, 185, 129, 0.25);
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

        /* ── Background Orbs ── */
        .bg-orb {
            position: fixed; border-radius: 50%;
            filter: blur(120px); opacity: 0.3;
            z-index: 0; pointer-events: none;
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

        /* ── Layout ── */
        .app-layout { display: flex; min-height: 100vh; position: relative; z-index: 1; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .sidebar-brand {
            padding: 24px 24px 20px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 14px;
        }
        .sidebar-brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px var(--accent-glow); flex-shrink: 0;
        }
        .sidebar-brand-icon svg { width: 22px; height: 22px; color: #fff; }
        .sidebar-brand-text h2 {
            font-size: 1.15rem; font-weight: 700;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-hover));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .sidebar-brand-text span { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-section-label {
            font-size: 0.65rem; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.1em; padding: 8px 12px 6px; margin-top: 8px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; border-radius: 10px;
            color: var(--text-secondary); text-decoration: none;
            font-size: 0.875rem; font-weight: 500;
            transition: all 0.2s; position: relative; margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--bg-hover); color: var(--text-primary); }
        .nav-item.active { background: rgba(99, 102, 241, 0.12); color: var(--accent-hover); }
        .nav-item.active::before {
            content: ''; position: absolute; left: 0; top: 50%;
            transform: translateY(-50%); width: 3px; height: 20px;
            background: var(--accent); border-radius: 0 3px 3px 0;
        }
        .nav-item svg { width: 20px; height: 20px; flex-shrink: 0; opacity: 0.7; }
        .nav-item.active svg { opacity: 1; }
        .nav-badge {
            margin-left: auto; background: var(--accent); color: #fff;
            font-size: 0.65rem; font-weight: 700;
            padding: 2px 7px; border-radius: 10px; min-width: 20px; text-align: center;
        }
        .sidebar-footer { padding: 16px; border-top: 1px solid var(--border); }
        .sidebar-user {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; border-radius: 10px;
            transition: background 0.2s; cursor: pointer; text-decoration: none;
        }
        .sidebar-user:hover { background: var(--bg-hover); }
        .sidebar-avatar {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .sidebar-user-info { overflow: hidden; }
        .sidebar-user-info .name {
            font-size: 0.85rem; font-weight: 600; color: var(--text-primary);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-user-info .upi { font-size: 0.72rem; color: var(--text-muted); }

        /* ── Main Content ── */
        .main-content {
            flex: 1; margin-left: var(--sidebar-width);
            padding: 32px 36px 48px; min-height: 100vh;
        }

        /* ── Top Header ── */
        .top-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; }
        .page-title-area h1 { font-size: 1.65rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 4px; }
        .page-title-area p { color: var(--text-secondary); font-size: 0.9rem; }
        .header-actions { display: flex; align-items: center; gap: 12px; }
        .btn-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: var(--bg-card); backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); cursor: pointer;
            transition: all 0.2s; text-decoration: none; position: relative;
        }
        .btn-icon:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }
        .btn-icon svg { width: 20px; height: 20px; }
        .btn-icon .notif-dot {
            position: absolute; top: 8px; right: 8px;
            width: 8px; height: 8px; background: var(--error);
            border-radius: 50%; border: 2px solid var(--bg-primary);
        }
        .hamburger { display: none; }

        /* ── Page Grid ── */
        .page-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; }

        /* ── Card Base ── */
        .card {
            background: var(--bg-card); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden;
            animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0; transform: translateY(20px);
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .card-header {
            padding: 20px 24px 0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-header h2 {
            font-size: 1rem; font-weight: 700; letter-spacing: -0.01em;
            display: flex; align-items: center; gap: 10px;
        }
        .card-icon {
            width: 32px; height: 32px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .card-icon--green  { background: var(--success-bg); color: var(--success); }
        .card-icon--indigo { background: rgba(99,102,241,0.12); color: var(--accent-hover); }
        .card-icon--amber  { background: var(--warning-bg); color: var(--warning); }
        .card-icon svg { width: 16px; height: 16px; }
        .card-body { padding: 20px 24px 24px; }

        /* ── Form Elements ── */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block; font-size: 0.78rem; font-weight: 600;
            color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px;
        }
        .input-wrap { position: relative; }
        .input-prefix {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 0.9rem; pointer-events: none;
            display: flex; align-items: center;
        }
        .input-prefix svg { width: 16px; height: 16px; }
        .form-input {
            width: 100%; background: rgba(255,255,255,0.04);
            border: 1px solid var(--border); border-radius: var(--radius);
            padding: 13px 16px 13px 42px;
            font-family: inherit; font-size: 0.9rem; color: var(--text-primary);
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
            appearance: none; -webkit-appearance: none;
        }
        .form-input.no-prefix { padding-left: 16px; }
        .form-input::placeholder { color: var(--text-muted); }
        .form-input:focus { border-color: var(--border-focus); box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }

        /* Amount */
        .amount-currency {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            font-size: 1.25rem; font-weight: 700; color: var(--text-muted); pointer-events: none;
        }
        #amountInput {
            padding-left: 36px; font-size: 1.6rem; font-weight: 800;
            letter-spacing: -0.02em; height: 68px;
            background: rgba(99,102,241,0.04); border-color: rgba(99,102,241,0.2);
        }
        #amountInput:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }

        /* Chips */
        .amount-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
        .chip {
            padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border);
            background: transparent; color: var(--text-secondary);
            font-family: inherit; font-size: 0.8rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
        }
        .chip:hover { border-color: var(--accent); color: var(--accent-hover); background: rgba(99,102,241,0.08); }
        .chip.active { border-color: var(--accent); color: var(--accent-hover); background: rgba(99,102,241,0.12); }

        /* Buttons */
        .btn-primary {
            width: 100%; padding: 15px 24px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border: none; border-radius: var(--radius); color: #fff;
            font-family: inherit; font-size: 0.95rem; font-weight: 600;
            cursor: pointer; letter-spacing: 0.01em;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 24px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px var(--accent-glow); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary svg { width: 18px; height: 18px; }

        .btn-secondary {
            flex: 1; padding: 12px 20px;
            background: rgba(255,255,255,0.04); border: 1px solid var(--border);
            border-radius: var(--radius); color: var(--text-secondary);
            font-family: inherit; font-size: 0.85rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-secondary:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }
        .btn-secondary svg { width: 16px; height: 16px; }
        .btn-row { display: flex; gap: 12px; margin-top: 16px; }

        /* Link preview */
        .link-preview {
            background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.2);
            border-radius: var(--radius); padding: 14px 16px;
            display: flex; align-items: center; gap: 12px; margin-top: 20px;
        }
        .link-preview-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(99,102,241,0.15);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .link-preview-icon svg { width: 18px; height: 18px; color: var(--accent-hover); }
        .link-preview-text { flex: 1; min-width: 0; }
        .link-preview-text span { display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 2px; }
        .link-preview-text code { font-family: 'Courier New', monospace; font-size: 0.78rem; color: var(--accent-hover); word-break: break-all; }
        .copy-link-btn {
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); padding: 4px; transition: color 0.2s; flex-shrink: 0;
        }
        .copy-link-btn:hover { color: var(--accent-hover); }
        .copy-link-btn svg { width: 16px; height: 16px; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px;
            color: var(--text-muted); font-size: 0.75rem; margin: 20px 0;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        /* ── QR Code Card ── */
        .qr-card {
            background: linear-gradient(145deg, #0f172a, #1e1b4b);
            border: 1px solid rgba(99,102,241,0.25); border-radius: var(--radius-lg);
            padding: 28px 24px; text-align: center;
            animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards;
            opacity: 0; transform: translateY(20px);
        }
        .qr-card h2 { font-size: 0.9rem; font-weight: 700; margin-bottom: 4px; }
        .qr-card .qr-sub { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 20px; }
        .qr-wrapper {
            width: 200px; height: 200px; margin: 0 auto 20px;
            background: #fff; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            padding: 12px; box-shadow: 0 8px 32px rgba(99,102,241,0.2);
        }
        .qr-wrapper canvas { width: 100%; height: 100%; border-radius: 8px; }
        .qr-upi-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.2);
            border-radius: 20px; padding: 6px 14px;
            font-size: 0.78rem; color: var(--accent-hover); font-weight: 500; margin-bottom: 16px;
        }
        .qr-upi-badge svg { width: 13px; height: 13px; }
        .qr-amount-display {
            font-size: 1.5rem; font-weight: 800; color: #fff;
            margin-bottom: 4px; letter-spacing: -0.02em;
        }
        .qr-amount-display.hidden { display: none; }
        .qr-amount-placeholder { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 16px; }
        .qr-actions { display: flex; gap: 10px; margin-top: 16px; }
        .qr-btn {
            flex: 1; padding: 10px 12px; border-radius: 10px;
            border: 1px solid var(--border); background: rgba(255,255,255,0.05);
            color: var(--text-secondary); font-family: inherit;
            font-size: 0.78rem; font-weight: 500; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .qr-btn:hover { background: rgba(99,102,241,0.1); border-color: var(--accent); color: var(--accent-hover); }
        .qr-btn svg { width: 14px; height: 14px; }

        /* ── Requests List ── */
        .req-list { list-style: none; }
        .req-item {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 24px; border-bottom: 1px solid rgba(99,102,241,0.06);
            transition: background 0.2s; transition: opacity 0.4s ease;
        }
        .req-item:last-child { border-bottom: none; }
        .req-item:hover { background: var(--bg-hover); }
        .req-avatar {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
            background: rgba(16,185,129,0.12); color: var(--success);
        }
        .req-avatar--sent { background: rgba(99,102,241,0.12); color: var(--accent-hover); }
        .req-details { flex: 1; min-width: 0; }
        .req-name {
            font-size: 0.875rem; font-weight: 600; margin-bottom: 2px;
            display: flex; align-items: center; gap: 7px;
        }
        .req-badge {
            font-size: 0.58rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.06em; padding: 2px 6px; border-radius: 4px;
        }
        .req-badge--pending   { background: var(--warning-bg); color: var(--warning); }
        .req-badge--completed { background: var(--success-bg); color: var(--success); }
        .req-badge--expired   { background: var(--error-bg); color: var(--error); }
        .req-note { font-size: 0.73rem; color: var(--text-muted); }
        .req-actions { display: flex; gap: 6px; margin-top: 6px; }
        .req-action-btn {
            padding: 4px 10px; border-radius: 6px;
            font-size: 0.7rem; font-weight: 600;
            border: none; cursor: pointer; font-family: inherit; transition: all 0.2s;
        }
        .req-action-btn--pay { background: var(--success-bg); color: var(--success); border: 1px solid var(--success-border); }
        .req-action-btn--pay:hover { background: var(--success); color: #fff; }
        .req-action-btn--decline { background: var(--error-bg); color: var(--error); border: 1px solid rgba(239,68,68,0.2); }
        .req-action-btn--decline:hover { background: var(--error); color: #fff; }
        .req-right { text-align: right; flex-shrink: 0; }
        .req-amount { font-size: 0.9rem; font-weight: 700; color: var(--success); margin-bottom: 2px; }
        .req-date { font-size: 0.68rem; color: var(--text-muted); }

        /* Tabs */
        .tabs {
            display: flex; gap: 4px;
            background: rgba(255,255,255,0.04);
            border-radius: 10px; padding: 4px; margin: 16px 24px 0;
        }
        .tab-btn {
            flex: 1; padding: 8px 14px; border: none; border-radius: 8px;
            background: none; color: var(--text-muted);
            font-family: inherit; font-size: 0.8rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
        }
        .tab-btn.active { background: var(--bg-card-solid); color: var(--text-primary); box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        .tab-count {
            display: inline-flex; align-items: center; justify-content: center;
            width: 16px; height: 16px; border-radius: 50%;
            background: var(--warning); color: #fff;
            font-size: 0.6rem; font-weight: 700; margin-left: 5px; vertical-align: middle;
        }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── Success State ── */
        .success-panel { display: none; text-align: center; padding: 32px 24px; }
        .success-panel.active { display: block; }
        .success-icon-wrap {
            width: 72px; height: 72px; border-radius: 50%;
            background: var(--success-bg); border: 2px solid var(--success-border);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            animation: successPop 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes successPop { 0% { transform: scale(0.6); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .success-icon-wrap svg { width: 36px; height: 36px; color: var(--success); }
        .success-panel h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; }
        .success-panel p { font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 24px; line-height: 1.5; }
        .success-link-box {
            background: rgba(16,185,129,0.06); border: 1px solid var(--success-border);
            border-radius: var(--radius); padding: 14px 16px;
            display: flex; align-items: center; gap: 10px; margin-bottom: 20px; text-align: left;
        }
        .success-link-box code {
            flex: 1; font-size: 0.78rem; color: var(--success);
            font-family: 'Courier New', monospace; word-break: break-all;
        }
        .success-link-box button {
            background: none; border: none; cursor: pointer; color: var(--success); padding: 4px;
        }
        .success-link-box button svg { width: 16px; height: 16px; }
        .btn-new-request {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; border: 1px solid var(--border); border-radius: var(--radius);
            background: none; color: var(--text-secondary);
            font-family: inherit; font-size: 0.85rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-new-request:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }
        .btn-new-request svg { width: 16px; height: 16px; }

        /* ── Stats ── */
        .stat-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 14px; border-radius: 10px; margin-bottom: 10px;
        }
        .stat-row--green  { background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.15); }
        .stat-row--indigo { background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.15); }
        .stat-row--amber  { background: var(--warning-bg); border: 1px solid rgba(245,158,11,0.2); }
        .stat-label { font-size: 0.7rem; color: var(--text-muted); margin-bottom: 2px; }
        .stat-val   { font-size: 1.05rem; font-weight: 700; }
        .stat-val--green  { color: var(--success); }
        .stat-val--indigo { color: var(--accent-hover); }
        .stat-val--amber  { color: var(--warning); }
        .stat-emoji { font-size: 1.4rem; }

        /* Progress bar */
        .progress-wrap { margin-top: 6px; }
        .progress-meta { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .progress-meta span { font-size: 0.72rem; color: var(--text-muted); }
        .progress-meta strong { font-size: 0.72rem; font-weight: 600; color: var(--success); }
        .progress-bar-bg { height: 6px; background: rgba(255,255,255,0.06); border-radius: 10px; overflow: hidden; }
        .progress-bar-fill {
            height: 100%; width: 66%;
            background: linear-gradient(90deg, var(--success), #34d399);
            border-radius: 10px; transition: width 1s ease;
        }

        /* ── Toast ── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 99;
        }
        .toast {
            position: fixed; bottom: 32px; left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: var(--bg-card-solid); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 12px 20px;
            font-size: 0.82rem; color: var(--text-primary);
            display: flex; align-items: center; gap: 8px; z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }
        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast svg { width: 18px; height: 18px; color: var(--success); }

        /* ── Responsive ── */
        @media (max-width: 1100px) {
            .page-grid { grid-template-columns: 1fr; }
            .right-panel { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; padding: 24px 16px 40px; }
            .hamburger { display: flex; }
            .right-panel { grid-template-columns: 1fr; }
        }
        @keyframes spin  { to { transform: rotate(360deg); } }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-6px); }
            40%       { transform: translateX(6px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }
    </style>
</head>
<body>

<div class="bg-orb bg-orb--1"></div>
<div class="bg-orb bg-orb--2"></div>
<div class="bg-orb bg-orb--3"></div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="app-layout">

    <!-- ─── Sidebar ───────────────────────────────────────────── -->
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

            <a href="dashboard.php" class="nav-item">
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

            <a href="request-money.php" class="nav-item active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                </svg>
                Request Money
            </a>

            <a href="scan-pay.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                </svg>
                Scan &amp; Pay
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
                <div class="sidebar-avatar"><?php echo $initials; ?></div>
                <div class="sidebar-user-info">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="upi"><?php echo htmlspecialchars($upiId); ?></div>
                </div>
            </a>
        </div>
    </aside>

    <!-- ─── Main Content ──────────────────────────────────────── -->
    <main class="main-content">

        <div class="top-header">
            <div class="page-title-area">
                <h1>💸 Request Money</h1>
                <p>Send payment requests &amp; collect money from anyone</p>
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

        <div class="page-grid">

            <!-- Left column -->
            <div style="display:flex;flex-direction:column;gap:20px;">

                <!-- New Request Card -->
                <div class="card" id="formCard" style="animation-delay:0.05s;">
                    <div class="card-header">
                        <h2>
                            <div class="card-icon card-icon--green">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                                </svg>
                            </div>
                            New Request
                        </h2>
                    </div>

                    <!-- Form -->
                    <div class="card-body" id="formPanel">
                        <form id="requestForm" onsubmit="handleSubmit(event)" novalidate>

                            <!-- Amount -->
                            <div class="form-group">
                                <label class="form-label" for="amountInput">Amount</label>
                                <div class="input-wrap" style="position:relative;">
                                    <span class="amount-currency">&#8377;</span>
                                    <input type="number" id="amountInput" class="form-input"
                                        placeholder="0.00" min="1" max="100000" step="0.01"
                                        oninput="onAmountChange(this.value)">
                                </div>
                                <div class="amount-chips" id="amountChips">
                                    <button type="button" class="chip" onclick="setAmount(100)">&#8377;100</button>
                                    <button type="button" class="chip" onclick="setAmount(200)">&#8377;200</button>
                                    <button type="button" class="chip" onclick="setAmount(500)">&#8377;500</button>
                                    <button type="button" class="chip" onclick="setAmount(1000)">&#8377;1,000</button>
                                    <button type="button" class="chip" onclick="setAmount(2000)">&#8377;2,000</button>
                                    <button type="button" class="chip" onclick="setAmount(5000)">&#8377;5,000</button>
                                </div>
                            </div>

                            <!-- Request From -->
                            <div class="form-group">
                                <label class="form-label" for="fromInput">Request From (UPI ID or Mobile)</label>
                                <div class="input-wrap">
                                    <span class="input-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                    </span>
                                    <input type="text" id="fromInput" class="form-input"
                                        placeholder="e.g. ravi@oksbi or 9876543210"
                                        autocomplete="off" oninput="updateLinkPreview()">
                                </div>
                                <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                                    <button type="button" class="chip" onclick="setContact('ravi@oksbi','Ravi Kumar')">Ravi Kumar</button>
                                    <button type="button" class="chip" onclick="setContact('priya@paytm','Priya Sharma')">Priya Sharma</button>
                                    <button type="button" class="chip" onclick="setContact('amit@ybl','Amit Das')">Amit Das</button>
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="form-group">
                                <label class="form-label" for="noteInput">Note (Optional)</label>
                                <div class="input-wrap">
                                    <span class="input-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                    </span>
                                    <input type="text" id="noteInput" class="form-input"
                                        placeholder="e.g. Lunch split, Movie tickets..."
                                        maxlength="100" oninput="updateLinkPreview()">
                                </div>
                            </div>

                            <!-- Expiry -->
                            <div class="form-group">
                                <label class="form-label" for="expiryInput">Expires In</label>
                                <div class="input-wrap">
                                    <span class="input-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </span>
                                    <select id="expiryInput" class="form-input" style="cursor:pointer;">
                                        <option value="24">24 Hours</option>
                                        <option value="48">48 Hours</option>
                                        <option value="72" selected>72 Hours</option>
                                        <option value="168">1 Week</option>
                                        <option value="0">No Expiry</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Live preview -->
                            <div class="link-preview" id="linkPreview" style="display:none;">
                                <div class="link-preview-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                    </svg>
                                </div>
                                <div class="link-preview-text">
                                    <span>Payment Link Preview</span>
                                    <code id="previewUrl"></code>
                                </div>
                                <button type="button" class="copy-link-btn" onclick="copyPreviewLink()" title="Copy link">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                    </svg>
                                </button>
                            </div>

                            <button type="submit" class="btn-primary" id="submitBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                                Send Request
                            </button>

                            <div class="btn-row">
                                <button type="button" class="btn-secondary" onclick="generateQR()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z" />
                                    </svg>
                                    Generate QR
                                </button>
                                <button type="button" class="btn-secondary" onclick="shareLink()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                                    </svg>
                                    Share Link
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Success -->
                    <div class="success-panel" id="successPanel">
                        <div class="success-icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <h3>Request Sent! &#127881;</h3>
                        <p>Your payment request has been sent to <strong id="successRecipient"></strong>.<br>They'll be notified and can pay you directly.</p>
                        <div class="success-link-box">
                            <code id="successLink"></code>
                            <button onclick="copySuccessLink()" title="Copy">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                </svg>
                            </button>
                        </div>
                        <button class="btn-new-request" onclick="resetForm()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            New Request
                        </button>
                    </div>
                </div>

                <!-- History Card -->
                <div class="card" style="animation-delay:0.15s;">
                    <div class="card-header">
                        <h2>
                            <div class="card-icon card-icon--indigo">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            Requests History
                        </h2>
                    </div>
                    <div class="tabs">
                        <button class="tab-btn active" onclick="switchTab('Incoming', this)">
                            Incoming
                            <span class="tab-count"><?php echo count($pendingRequests); ?></span>
                        </button>
                        <button class="tab-btn" onclick="switchTab('Sent', this)">Sent</button>
                    </div>

                    <div class="tab-panel active" id="panelIncoming">
                        <ul class="req-list">
                            <?php foreach ($pendingRequests as $req): ?>
                            <li class="req-item" id="req-<?php echo htmlspecialchars($req['id']); ?>">
                                <div class="req-avatar"><?php echo htmlspecialchars($req['avatar']); ?></div>
                                <div class="req-details">
                                    <div class="req-name">
                                        <?php echo htmlspecialchars($req['from']); ?>
                                        <span class="req-badge req-badge--pending">Pending</span>
                                    </div>
                                    <div class="req-note"><?php echo htmlspecialchars($req['upi']); ?> &middot; <?php echo htmlspecialchars($req['note']); ?></div>
                                    <div class="req-actions">
                                        <button class="req-action-btn req-action-btn--pay"
                                            onclick="payRequest('<?php echo htmlspecialchars($req['id']); ?>', <?php echo $req['amount']; ?>, '<?php echo htmlspecialchars($req['from']); ?>')">
                                            Pay &#8377;<?php echo number_format($req['amount'], 0); ?>
                                        </button>
                                        <button class="req-action-btn req-action-btn--decline"
                                            onclick="declineRequest('<?php echo htmlspecialchars($req['id']); ?>')">
                                            Decline
                                        </button>
                                    </div>
                                </div>
                                <div class="req-right">
                                    <div class="req-amount">+&#8377;<?php echo number_format($req['amount'], 2); ?></div>
                                    <div class="req-date"><?php echo htmlspecialchars($req['date']); ?></div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="tab-panel" id="panelSent">
                        <ul class="req-list">
                            <?php foreach ($sentRequests as $req): ?>
                            <li class="req-item">
                                <div class="req-avatar req-avatar--sent"><?php echo htmlspecialchars($req['avatar']); ?></div>
                                <div class="req-details">
                                    <div class="req-name">
                                        <?php echo htmlspecialchars($req['to']); ?>
                                        <span class="req-badge req-badge--<?php echo $req['status']; ?>"><?php echo ucfirst($req['status']); ?></span>
                                    </div>
                                    <div class="req-note"><?php echo htmlspecialchars($req['upi']); ?> &middot; <?php echo htmlspecialchars($req['note']); ?></div>
                                </div>
                                <div class="req-right">
                                    <div class="req-amount" style="color:var(--accent-hover);">&#8377;<?php echo number_format($req['amount'], 2); ?></div>
                                    <div class="req-date"><?php echo htmlspecialchars($req['date']); ?></div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Right column -->
            <div class="right-panel">

                <!-- QR Card -->
                <div class="qr-card" id="qrCard">
                    <h2>Your Payment QR</h2>
                    <div class="qr-sub">Anyone can scan to pay you instantly</div>

                    <div class="qr-upi-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                        </svg>
                        <?php echo htmlspecialchars($upiId); ?>
                    </div>

                    <div class="qr-wrapper">
                        <canvas id="qrCanvas"></canvas>
                    </div>

                    <div class="qr-amount-display hidden" id="qrAmountDisplay"></div>
                    <div class="qr-amount-placeholder" id="qrAmountPlaceholder">Enter amount to customize QR</div>

                    <div class="qr-actions">
                        <button class="qr-btn" onclick="downloadQR()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Save
                        </button>
                        <button class="qr-btn" onclick="shareQR()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                            </svg>
                            Share
                        </button>
                        <button class="qr-btn" onclick="copyUPIId()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                            </svg>
                            Copy ID
                        </button>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="card" style="animation-delay:0.2s;">
                    <div class="card-header">
                        <h2>
                            <div class="card-icon card-icon--amber">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            </div>
                            This Month
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="stat-row stat-row--green">
                            <div>
                                <div class="stat-label">Requested</div>
                                <div class="stat-val stat-val--green">&#8377;4,850</div>
                            </div>
                            <span class="stat-emoji">&#128229;</span>
                        </div>
                        <div class="stat-row stat-row--indigo">
                            <div>
                                <div class="stat-label">Collected</div>
                                <div class="stat-val stat-val--indigo">&#8377;3,200</div>
                            </div>
                            <span class="stat-emoji">&#9989;</span>
                        </div>
                        <div class="stat-row stat-row--amber">
                            <div>
                                <div class="stat-label">Pending</div>
                                <div class="stat-val stat-val--amber">&#8377;2,050</div>
                            </div>
                            <span class="stat-emoji">&#9203;</span>
                        </div>
                        <div class="progress-wrap">
                            <div class="progress-meta">
                                <span>Collection Rate</span>
                                <strong>66%</strong>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<!-- Toast -->
<div class="toast" id="toast">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
    </svg>
    <span id="toastMsg">Copied!</span>
</div>

<script>
const UPI_ID   = <?php echo json_encode($upiId); ?>;
const USER_NAME = <?php echo json_encode($userName); ?>;

let currentAmount = 0;
let toastTimer;

// ── QR Canvas Draw ────────────────────────────────────────────────────────────
function drawQR(amount) {
    const canvas = document.getElementById('qrCanvas');
    const ctx    = canvas.getContext('2d');
    const SIZE   = 176;
    const MOD    = 25;
    const cell   = SIZE / MOD;
    canvas.width = canvas.height = SIZE;

    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, SIZE, SIZE);

    const seed = [...(UPI_ID + String(amount || 0))].reduce((a, c) => a + c.charCodeAt(0), 0);
    const rng  = seededRand(seed);
    ctx.fillStyle = '#1a1a2e';

    for (let r = 0; r < MOD; r++) {
        for (let c = 0; c < MOD; c++) {
            if (isFinder(r, c, MOD) || isTiming(r, c)) continue;
            if (rng() > 0.48) ctx.fillRect(c * cell, r * cell, cell - 0.3, cell - 0.3);
        }
    }

    drawFinder(ctx, 0, 0, cell);
    drawFinder(ctx, MOD - 7, 0, cell);
    drawFinder(ctx, 0, MOD - 7, cell);

    for (let i = 8; i < MOD - 8; i++) {
        if (i % 2 === 0) {
            ctx.fillRect(i * cell, 6 * cell, cell, cell);
            ctx.fillRect(6 * cell, i * cell, cell, cell);
        }
    }

    // Center logo
    const ls = cell * 5, lx = (SIZE - ls) / 2, ly = (SIZE - ls) / 2;
    ctx.fillStyle = '#fff';
    ctx.fillRect(lx - 2, ly - 2, ls + 4, ls + 4);
    const g = ctx.createLinearGradient(lx, ly, lx + ls, ly + ls);
    g.addColorStop(0, '#6366f1'); g.addColorStop(1, '#8b5cf6');
    ctx.fillStyle = g;
    ctx.beginPath();
    if (ctx.roundRect) ctx.roundRect(lx, ly, ls, ls, 4);
    else ctx.rect(lx, ly, ls, ls);
    ctx.fill();
    ctx.fillStyle = '#fff';
    ctx.font = `bold ${Math.floor(cell * 2.2)}px Inter, sans-serif`;
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.fillText('\u20B9', lx + ls / 2, ly + ls / 2 + 1);
}

function seededRand(seed) {
    let s = seed;
    return () => { s = (s * 9301 + 49297) % 233280; return s / 233280; };
}
function isFinder(r, c, m) {
    return (r < 7 && c < 7) || (r < 7 && c >= m - 7) || (r >= m - 7 && c < 7);
}
function isTiming(r, c) { return (r === 6 && c >= 8) || (c === 6 && r >= 8); }
function drawFinder(ctx, sr, sc, cell) {
    ctx.fillStyle = '#1a1a2e';
    ctx.fillRect(sc * cell, sr * cell, 7 * cell, 7 * cell);
    ctx.fillStyle = '#fff';
    ctx.fillRect((sc + 1) * cell, (sr + 1) * cell, 5 * cell, 5 * cell);
    ctx.fillStyle = '#1a1a2e';
    ctx.fillRect((sc + 2) * cell, (sr + 2) * cell, 3 * cell, 3 * cell);
}

// ── Amount ────────────────────────────────────────────────────────────────────
function onAmountChange(val) {
    currentAmount = parseFloat(val) || 0;
    drawQR(currentAmount);
    const display = document.getElementById('qrAmountDisplay');
    const placeholder = document.getElementById('qrAmountPlaceholder');
    if (currentAmount > 0) {
        display.textContent = '\u20B9' + currentAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        display.classList.remove('hidden');
        placeholder.style.display = 'none';
    } else {
        display.classList.add('hidden');
        placeholder.style.display = '';
    }
    updateChips(val);
    updateLinkPreview();
}

function setAmount(val) {
    document.getElementById('amountInput').value = val;
    onAmountChange(val);
}

function updateChips(val) {
    const parsed = parseFloat(val);
    const amounts = [100, 200, 500, 1000, 2000, 5000];
    document.querySelectorAll('.amount-chips .chip').forEach((chip, i) => {
        chip.classList.toggle('active', amounts[i] === parsed);
    });
}

// ── Link Preview ──────────────────────────────────────────────────────────────
function buildURL() {
    const note = encodeURIComponent(document.getElementById('noteInput').value || '');
    return 'upi://pay?pa=' + encodeURIComponent(UPI_ID) +
           '&pn=' + encodeURIComponent(USER_NAME) +
           '&am=' + (currentAmount || '') +
           '&tn=' + note + '&cu=INR';
}

function updateLinkPreview() {
    const from = document.getElementById('fromInput').value;
    const preview = document.getElementById('linkPreview');
    if (currentAmount > 0 || from) {
        document.getElementById('previewUrl').textContent = buildURL();
        preview.style.display = 'flex';
    } else {
        preview.style.display = 'none';
    }
}

// ── Contacts ──────────────────────────────────────────────────────────────────
function setContact(upi, name) {
    document.getElementById('fromInput').value = upi;
    updateLinkPreview();
    showToast('Selected: ' + name);
}

// ── Submit ────────────────────────────────────────────────────────────────────
function handleSubmit(e) {
    e.preventDefault();
    const amount = parseFloat(document.getElementById('amountInput').value);
    const from   = document.getElementById('fromInput').value.trim();
    if (!amount || amount <= 0) { shakeInput('amountInput'); showToast('Enter a valid amount', false); return; }
    if (!from)                  { shakeInput('fromInput');   showToast('Enter UPI ID or mobile', false); return; }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg style="width:18px;height:18px;animation:spin 0.8s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Sending...';

    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg> Send Request';
        document.getElementById('successRecipient').textContent = from;
        document.getElementById('successLink').textContent = buildURL();
        document.getElementById('formPanel').style.display = 'none';
        document.getElementById('successPanel').classList.add('active');
    }, 1600);
}

function resetForm() {
    document.getElementById('requestForm').reset();
    document.getElementById('formPanel').style.display = '';
    document.getElementById('successPanel').classList.remove('active');
    document.getElementById('linkPreview').style.display = 'none';
    document.getElementById('qrAmountDisplay').classList.add('hidden');
    document.getElementById('qrAmountPlaceholder').style.display = '';
    currentAmount = 0;
    document.querySelectorAll('.amount-chips .chip').forEach(c => c.classList.remove('active'));
    drawQR(0);
}

// ── Copy / Share ──────────────────────────────────────────────────────────────
function copyPreviewLink() { navigator.clipboard.writeText(buildURL()).then(() => showToast('Link copied!')); }
function copySuccessLink() {
    navigator.clipboard.writeText(document.getElementById('successLink').textContent).then(() => showToast('Link copied!'));
}
function copyUPIId() { navigator.clipboard.writeText(UPI_ID).then(() => showToast('UPI ID copied!')); }

function downloadQR() {
    const a = document.createElement('a');
    a.download = 'paysim-qr.png';
    a.href = document.getElementById('qrCanvas').toDataURL();
    a.click();
    showToast('QR saved!');
}
function generateQR() {
    document.getElementById('qrCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
    drawQR(currentAmount);
    showToast('QR updated!');
}
function shareQR() {
    document.getElementById('qrCanvas').toBlob(blob => {
        if (navigator.share && blob) {
            navigator.share({ title: 'PaySim QR', files: [new File([blob], 'qr.png', { type: 'image/png' })] }).catch(() => downloadQR());
        } else { downloadQR(); }
    });
}
function shareLink() {
    const url = buildURL();
    if (navigator.share) {
        navigator.share({ title: 'PaySim Payment Request', url }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(() => showToast('Link copied!'));
    }
}

// ── Request Actions ───────────────────────────────────────────────────────────
function payRequest(id, amount, name) {
    showToast('Processing payment…');
    setTimeout(() => {
        const el = document.getElementById('req-' + id);
        if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }
        showToast('\u2705 Paid \u20B9' + amount + ' to ' + name);
    }, 1200);
}
function declineRequest(id) {
    const el = document.getElementById('req-' + id);
    if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }
    showToast('Request declined');
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
function switchTab(name, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('panel' + name).classList.add('active');
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function showToast(msg, ok = true) {
    const el = document.getElementById('toast');
    el.querySelector('svg').style.color = ok ? 'var(--success)' : 'var(--warning)';
    document.getElementById('toastMsg').textContent = msg;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 2400);
}

// ── Input shake ───────────────────────────────────────────────────────────────
function shakeInput(id) {
    const el = document.getElementById(id);
    el.style.animation = 'none'; el.offsetHeight;
    el.style.animation = 'shake 0.35s';
    el.addEventListener('animationend', () => el.style.animation = '', { once: true });
}

// ── Sidebar (mobile) ──────────────────────────────────────────────────────────
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => drawQR(0));
</script>
</body>
</html>
