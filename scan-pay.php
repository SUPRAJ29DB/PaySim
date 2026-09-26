<?php
session_start();

if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

$userName  = $_SESSION['NAME']     ?? 'User';
$upiId     = $_SESSION['UPI_ID']   ?? 'user@paysim';
$userId    = $_SESSION['USER_ID']  ?? 0;
$balance   = 24580.75;

$firstName = explode(' ', $userName)[0];
$initials  = strtoupper(substr($firstName, 0, 1) . (strpos($userName, ' ') ? substr(explode(' ', $userName)[1], 0, 1) : ''));

$recentScans = [
    ['name' => 'Ravi Kumar',     'upi' => 'ravi@oksbi',    'amount' => 500.00,  'date' => '2026-09-18 14:32', 'avatar' => 'RK', 'status' => 'success'],
    ['name' => 'Swiggy',         'upi' => 'swiggy@icici',  'amount' => 342.00,  'date' => '2026-09-17 20:10', 'avatar' => 'SW', 'status' => 'success'],
    ['name' => 'Priya Sharma',   'upi' => 'priya@paytm',   'amount' => 1200.00, 'date' => '2026-09-16 11:20', 'avatar' => 'PS', 'status' => 'success'],
    ['name' => 'BookMyShow',     'upi' => 'bms@hdfcbank',  'amount' => 720.00,  'date' => '2026-09-15 18:45', 'avatar' => 'BM', 'status' => 'failed'],
];

$quickPay = [
    ['name' => 'Ravi Kumar',   'upi' => 'ravi@oksbi',   'avatar' => 'RK'],
    ['name' => 'Priya Sharma', 'upi' => 'priya@paytm',  'avatar' => 'PS'],
    ['name' => 'Amit Das',     'upi' => 'amit@ybl',     'avatar' => 'AD'],
    ['name' => 'Neha Singh',   'upi' => 'neha@okicici', 'avatar' => 'NS'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan &amp; Pay — PaySim</title>
    <meta name="description" content="Scan any UPI QR code to pay instantly using PaySim.">

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

        /* ── Orbs ── */
        .bg-orb { position: fixed; border-radius: 50%; filter: blur(120px); opacity: 0.3; z-index: 0; pointer-events: none; }
        .bg-orb--1 { width:600px; height:600px; background:radial-gradient(circle,var(--accent) 0%,transparent 70%); top:-200px; right:-150px; animation:orbFloat 10s ease-in-out infinite alternate; }
        .bg-orb--2 { width:450px; height:450px; background:radial-gradient(circle,#8b5cf6 0%,transparent 70%); bottom:-150px; left:-100px; animation:orbFloat 12s ease-in-out infinite alternate-reverse; }
        .bg-orb--3 { width:300px; height:300px; background:radial-gradient(circle,#06b6d4 0%,transparent 70%); top:50%; left:40%; animation:orbFloat 14s ease-in-out infinite alternate; }
        @keyframes orbFloat { 0%{transform:translate(0,0) scale(1)} 100%{transform:translate(40px,-30px) scale(1.15)} }

        /* ── Layout ── */
        .app-layout { display:flex; min-height:100vh; position:relative; z-index:1; }

        /* ── Sidebar ── */
        .sidebar {
            width:var(--sidebar-width); background:var(--bg-card);
            backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px);
            border-right:1px solid var(--border);
            display:flex; flex-direction:column;
            position:fixed; top:0; left:0; bottom:0; z-index:100;
            transition:transform 0.35s cubic-bezier(0.16,1,0.3,1);
        }
        .sidebar-brand { padding:24px 24px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:14px; }
        .sidebar-brand-icon { width:42px; height:42px; background:linear-gradient(135deg,var(--accent),#8b5cf6); border-radius:12px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 16px var(--accent-glow); flex-shrink:0; }
        .sidebar-brand-icon svg { width:22px; height:22px; color:#fff; }
        .sidebar-brand-text h2 { font-size:1.15rem; font-weight:700; background:linear-gradient(135deg,var(--text-primary),var(--accent-hover)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .sidebar-brand-text span { font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; }
        .sidebar-nav { flex:1; padding:16px 12px; overflow-y:auto; }
        .nav-section-label { font-size:0.65rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em; padding:8px 12px 6px; margin-top:8px; }
        .nav-item { display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:10px; color:var(--text-secondary); text-decoration:none; font-size:0.875rem; font-weight:500; transition:all 0.2s; position:relative; margin-bottom:2px; }
        .nav-item:hover { background:var(--bg-hover); color:var(--text-primary); }
        .nav-item.active { background:rgba(99,102,241,0.12); color:var(--accent-hover); }
        .nav-item.active::before { content:''; position:absolute; left:0; top:50%; transform:translateY(-50%); width:3px; height:20px; background:var(--accent); border-radius:0 3px 3px 0; }
        .nav-item svg { width:20px; height:20px; flex-shrink:0; opacity:0.7; }
        .nav-item.active svg { opacity:1; }
        .nav-badge { margin-left:auto; background:var(--accent); color:#fff; font-size:0.65rem; font-weight:700; padding:2px 7px; border-radius:10px; min-width:20px; text-align:center; }
        .sidebar-footer { padding:16px; border-top:1px solid var(--border); }
        .sidebar-user { display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:10px; transition:background 0.2s; cursor:pointer; text-decoration:none; }
        .sidebar-user:hover { background:var(--bg-hover); }
        .sidebar-avatar { width:38px; height:38px; border-radius:10px; background:linear-gradient(135deg,#6366f1,#8b5cf6); display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; color:#fff; flex-shrink:0; }
        .sidebar-user-info { overflow:hidden; }
        .sidebar-user-info .name { font-size:0.85rem; font-weight:600; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .sidebar-user-info .upi { font-size:0.72rem; color:var(--text-muted); }

        /* ── Main ── */
        .main-content { flex:1; margin-left:var(--sidebar-width); padding:32px 36px 48px; min-height:100vh; }

        /* ── Header ── */
        .top-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:32px; }
        .page-title-area h1 { font-size:1.65rem; font-weight:700; letter-spacing:-0.02em; margin-bottom:4px; }
        .page-title-area p { color:var(--text-secondary); font-size:0.9rem; }
        .header-actions { display:flex; align-items:center; gap:12px; }
        .btn-icon { width:42px; height:42px; border-radius:12px; background:var(--bg-card); backdrop-filter:blur(16px); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--text-secondary); cursor:pointer; transition:all 0.2s; text-decoration:none; position:relative; }
        .btn-icon:hover { background:var(--bg-hover); color:var(--text-primary); border-color:var(--border-focus); }
        .btn-icon svg { width:20px; height:20px; }
        .btn-icon .notif-dot { position:absolute; top:8px; right:8px; width:8px; height:8px; background:var(--error); border-radius:50%; border:2px solid var(--bg-primary); }
        .hamburger { display:none; }

        /* ── Page Grid ── */
        .page-grid { display:grid; grid-template-columns:1fr 360px; gap:24px; align-items:start; }

        /* ── Cards ── */
        .card {
            background:var(--bg-card); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
            border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden;
            animation:fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity:0; transform:translateY(20px);
        }
        @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
        .card-header { padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between; }
        .card-header h2 { font-size:1rem; font-weight:700; letter-spacing:-0.01em; display:flex; align-items:center; gap:10px; }
        .card-icon { width:32px; height:32px; border-radius:10px; display:flex; align-items:center; justify-content:center; }
        .card-icon--amber  { background:var(--warning-bg); color:var(--warning); }
        .card-icon--indigo { background:rgba(99,102,241,0.12); color:var(--accent-hover); }
        .card-icon--green  { background:var(--success-bg); color:var(--success); }
        .card-icon--red    { background:var(--error-bg); color:var(--error); }
        .card-icon svg { width:16px; height:16px; }
        .card-body { padding:20px 24px 24px; }

        /* ── Scanner Container ── */
        .scanner-card {
            background: linear-gradient(145deg, #0f172a, #1a1040);
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: var(--radius-xl);
            overflow: hidden;
            animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) 0.05s forwards;
            opacity: 0; transform: translateY(20px);
        }
        .scanner-header {
            padding: 20px 24px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid rgba(99,102,241,0.15);
        }
        .scanner-header h2 { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .scanner-header h2 svg { width: 20px; height: 20px; color: var(--accent-hover); }

        /* Mode Switcher */
        .mode-switcher {
            display: flex; gap: 4px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px; padding: 4px;
        }
        .mode-btn {
            padding: 7px 14px; border: none; border-radius: 7px;
            background: none; color: var(--text-muted);
            font-family: inherit; font-size: 0.78rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; gap: 6px;
        }
        .mode-btn svg { width: 14px; height: 14px; }
        .mode-btn.active {
            background: rgba(99,102,241,0.2);
            color: var(--accent-hover);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        /* ── Camera View ── */
        .scanner-viewport {
            position: relative;
            width: 100%;
            padding-bottom: 64%;
            background: #000;
            overflow: hidden;
        }
        #cameraFeed, #cameraCanvas {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
        }
        #cameraCanvas { display: none; }

        /* Scanner overlay frame */
        .scan-overlay {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none;
        }
        .scan-frame {
            width: 200px; height: 200px;
            position: relative;
        }
        .scan-frame::before, .scan-frame::after {
            content: '';
            position: absolute; inset: 0;
            border: 2px solid transparent;
        }
        /* Four corner brackets */
        .corner {
            position: absolute;
            width: 28px; height: 28px;
            border-color: #fff;
            border-style: solid;
            opacity: 0.9;
        }
        .corner--tl { top: 0; left: 0; border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
        .corner--tr { top: 0; right: 0; border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
        .corner--bl { bottom: 0; left: 0; border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
        .corner--br { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }

        /* Scan laser line */
        .scan-laser {
            position: absolute;
            left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), #8b5cf6, transparent);
            box-shadow: 0 0 12px var(--accent-glow);
            animation: scanLine 2s ease-in-out infinite;
            top: 0;
        }
        @keyframes scanLine {
            0%   { top: 0; opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        /* Camera placeholder (when camera off) */
        .camera-placeholder {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 16px; background: #080c18;
        }
        .camera-placeholder-icon {
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.2);
            display: flex; align-items: center; justify-content: center;
        }
        .camera-placeholder-icon svg { width: 36px; height: 36px; color: var(--accent-hover); }
        .camera-placeholder p { font-size: 0.85rem; color: var(--text-secondary); text-align: center; max-width: 240px; }
        .camera-placeholder small { font-size: 0.72rem; color: var(--text-muted); }

        /* ── Start Camera Button ── */
        .btn-start-camera {
            margin: 20px 24px;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border: none; border-radius: var(--radius);
            color: #fff; font-family: inherit; font-size: 0.9rem; font-weight: 600;
            cursor: pointer; letter-spacing: 0.01em;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: calc(100% - 48px);
        }
        .btn-start-camera:hover { transform: translateY(-2px); box-shadow: 0 8px 30px var(--accent-glow); }
        .btn-start-camera:active { transform: translateY(0); }
        .btn-start-camera svg { width: 18px; height: 18px; }
        .btn-start-camera.active {
            background: var(--error-bg); border: 1px solid rgba(239,68,68,0.3);
            color: var(--error);
        }
        .btn-start-camera.active:hover { box-shadow: 0 8px 30px rgba(239,68,68,0.2); }

        /* Upload QR alternative */
        .scanner-alt {
            padding: 0 24px 24px;
            display: flex; gap: 10px;
        }
        .btn-upload {
            flex: 1; padding: 11px 16px;
            background: rgba(255,255,255,0.04); border: 1px solid var(--border);
            border-radius: var(--radius); color: var(--text-secondary);
            font-family: inherit; font-size: 0.82rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 7px;
        }
        .btn-upload:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }
        .btn-upload svg { width: 15px; height: 15px; }
        #qrFileInput { display: none; }

        /* Demo scan button */
        .btn-demo-scan {
            flex: 1; padding: 11px 16px;
            background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.2);
            border-radius: var(--radius); color: var(--accent-hover);
            font-family: inherit; font-size: 0.82rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 7px;
        }
        .btn-demo-scan:hover { background: rgba(99,102,241,0.15); border-color: var(--accent); }
        .btn-demo-scan svg { width: 15px; height: 15px; }

        /* ── Manual Entry Mode ── */
        .manual-panel { padding: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display:block; font-size:0.78rem; font-weight:600; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px; }
        .input-wrap { position:relative; }
        .input-prefix { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); display:flex; align-items:center; pointer-events:none; }
        .input-prefix svg { width:16px; height:16px; }
        .form-input { width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--border); border-radius:var(--radius); padding:13px 16px 13px 42px; font-family:inherit; font-size:0.9rem; color:var(--text-primary); transition:border-color 0.2s, box-shadow 0.2s; outline:none; }
        .form-input::placeholder { color:var(--text-muted); }
        .form-input:focus { border-color:var(--border-focus); box-shadow:0 0 0 3px rgba(99,102,241,0.12); }
        .amount-currency { position:absolute; left:16px; top:50%; transform:translateY(-50%); font-size:1.2rem; font-weight:700; color:var(--text-muted); pointer-events:none; }
        #manualAmount { padding-left:34px; font-size:1.5rem; font-weight:800; letter-spacing:-0.02em; height:64px; background:rgba(99,102,241,0.04); border-color:rgba(99,102,241,0.2); }
        #manualAmount:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,0.15); }
        .amount-chips { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
        .chip { padding:6px 14px; border-radius:20px; border:1px solid var(--border); background:transparent; color:var(--text-secondary); font-family:inherit; font-size:0.8rem; font-weight:500; cursor:pointer; transition:all 0.2s; }
        .chip:hover { border-color:var(--accent); color:var(--accent-hover); background:rgba(99,102,241,0.08); }
        .chip.active { border-color:var(--accent); color:var(--accent-hover); background:rgba(99,102,241,0.12); }
        .btn-pay-now {
            width:100%; padding:15px 24px; margin-top:20px;
            background:linear-gradient(135deg,var(--accent),#8b5cf6);
            border:none; border-radius:var(--radius); color:#fff;
            font-family:inherit; font-size:0.95rem; font-weight:600;
            cursor:pointer; transition:transform 0.2s, box-shadow 0.2s;
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-pay-now:hover { transform:translateY(-2px); box-shadow:0 8px 30px var(--accent-glow); }
        .btn-pay-now:active { transform:translateY(0); }
        .btn-pay-now svg { width:18px; height:18px; }

        /* ── Detected / Confirm Panel ── */
        .detect-panel {
            padding: 24px;
            border-top: 1px solid var(--border);
            display: none;
        }
        .detect-panel.active { display: block; }
        .detected-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--success-bg); border: 1px solid var(--success-border);
            border-radius: 20px; padding: 5px 12px;
            font-size: 0.72rem; font-weight: 600; color: var(--success);
            margin-bottom: 16px;
            animation: pulse 1s ease infinite alternate;
        }
        @keyframes pulse { from { opacity: 0.8; } to { opacity: 1; } }
        .detected-badge svg { width: 13px; height: 13px; }
        .merchant-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: var(--radius); padding: 16px;
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 16px;
        }
        .merchant-avatar {
            width: 48px; height: 48px; border-radius: 14px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .merchant-info h3 { font-size: 0.95rem; font-weight: 600; margin-bottom: 3px; }
        .merchant-info p  { font-size: 0.78rem; color: var(--text-muted); }
        .pay-amount-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px;
        }
        .pay-amount-label { font-size: 0.8rem; color: var(--text-secondary); }
        .pay-amount-val { font-size: 1.6rem; font-weight: 800; letter-spacing: -0.02em; color: var(--accent-hover); }
        .pay-amount-val span { font-size: 1rem; font-weight: 600; opacity: 0.7; }
        .balance-check { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 20px; }
        .balance-check strong { color: var(--success); }
        .confirm-actions { display: flex; gap: 10px; }
        .btn-confirm {
            flex: 2; padding: 14px; background: linear-gradient(135deg, var(--success), #059669);
            border: none; border-radius: var(--radius); color: #fff;
            font-family: inherit; font-size: 0.9rem; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-confirm:hover { box-shadow: 0 8px 24px rgba(16,185,129,0.3); transform: translateY(-1px); }
        .btn-confirm svg { width: 18px; height: 18px; }
        .btn-cancel-pay {
            flex: 1; padding: 14px;
            background: rgba(255,255,255,0.04); border: 1px solid var(--border);
            border-radius: var(--radius); color: var(--text-secondary);
            font-family: inherit; font-size: 0.88rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-cancel-pay:hover { background: var(--error-bg); color: var(--error); border-color: rgba(239,68,68,0.3); }

        /* ── Success Overlay ── */
        .pay-success {
            padding: 32px 24px; text-align: center; display: none;
        }
        .pay-success.active { display: block; }
        .success-ring {
            width: 80px; height: 80px; border-radius: 50%;
            background: var(--success-bg); border: 2px solid var(--success-border);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            animation: ringPop 0.4s cubic-bezier(0.16,1,0.3,1);
        }
        @keyframes ringPop { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .success-ring svg { width: 40px; height: 40px; color: var(--success); }
        .pay-success h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 6px; }
        .pay-success .success-amount { font-size: 2rem; font-weight: 800; color: var(--success); letter-spacing: -0.03em; margin-bottom: 6px; }
        .pay-success .success-to { font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 6px; }
        .pay-success .txn-id { font-size: 0.72rem; color: var(--text-muted); font-family: 'Courier New', monospace; margin-bottom: 24px; }
        .btn-row-success { display: flex; gap: 10px; justify-content: center; }
        .btn-done {
            padding: 12px 28px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border: none; border-radius: var(--radius); color: #fff;
            font-family: inherit; font-size: 0.88rem; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-done:hover { transform: translateY(-2px); box-shadow: 0 8px 24px var(--accent-glow); }
        .btn-scan-again {
            padding: 12px 28px;
            background: rgba(255,255,255,0.04); border: 1px solid var(--border);
            border-radius: var(--radius); color: var(--text-secondary);
            font-family: inherit; font-size: 0.88rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-scan-again:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-focus); }

        /* ── Right Panel ── */
        .right-panel { display: flex; flex-direction: column; gap: 20px; }

        /* Quick pay people */
        .quick-pay-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 16px; }
        .quick-pay-item {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 14px; border-radius: var(--radius);
            background: rgba(255,255,255,0.03); border: 1px solid var(--border);
            cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .quick-pay-item:hover { background: var(--bg-hover); border-color: var(--border-focus); transform: translateY(-1px); }
        .qp-avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #4338ca, #6366f1);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .qp-info { min-width: 0; }
        .qp-name { font-size: 0.8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .qp-upi  { font-size: 0.68rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Recent scans */
        .scan-list { list-style: none; }
        .scan-item {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 24px; border-bottom: 1px solid rgba(99,102,241,0.06);
            transition: background 0.2s; cursor: pointer;
        }
        .scan-item:last-child { border-bottom: none; }
        .scan-item:hover { background: var(--bg-hover); }
        .scan-item-avatar {
            width: 40px; height: 40px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
            background: rgba(99,102,241,0.1); color: var(--accent-hover);
        }
        .scan-item-avatar--failed { background: var(--error-bg); color: var(--error); }
        .scan-item-info { flex: 1; min-width: 0; }
        .scan-item-name { font-size: 0.85rem; font-weight: 600; margin-bottom: 1px; display: flex; align-items: center; gap: 6px; }
        .scan-item-upi  { font-size: 0.72rem; color: var(--text-muted); }
        .scan-status-dot {
            width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
        }
        .scan-status-dot--success { background: var(--success); }
        .scan-status-dot--failed  { background: var(--error); }
        .scan-item-right { text-align: right; flex-shrink: 0; }
        .scan-amount { font-size: 0.88rem; font-weight: 700; margin-bottom: 2px; }
        .scan-amount--success { color: var(--error); }
        .scan-amount--failed  { color: var(--text-muted); text-decoration: line-through; }
        .scan-date   { font-size: 0.67rem; color: var(--text-muted); }

        /* Tips card */
        .tips-card {
            background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.07));
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: var(--radius-lg); padding: 20px 22px;
            animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) 0.25s forwards;
            opacity: 0; transform: translateY(20px);
        }
        .tips-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(99,102,241,0.15); border-radius: 6px;
            padding: 3px 10px; font-size: 0.65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--accent-hover); margin-bottom: 12px;
        }
        .tips-card h3 { font-size: 0.9rem; font-weight: 700; margin-bottom: 10px; }
        .tip-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
        .tip-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); flex-shrink: 0; margin-top: 6px; }
        .tip-row p { font-size: 0.78rem; color: var(--text-secondary); line-height: 1.4; }

        /* ── Overlays / Misc ── */
        .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99; }
        .toast {
            position:fixed; bottom:32px; left:50%;
            transform:translateX(-50%) translateY(80px);
            background:var(--bg-card-solid); border:1px solid var(--border); border-radius:var(--radius);
            padding:12px 20px; font-size:0.82rem; color:var(--text-primary);
            display:flex; align-items:center; gap:8px; z-index:1000;
            transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);
            box-shadow:0 12px 40px rgba(0,0,0,0.4); white-space:nowrap;
        }
        .toast.show { transform:translateX(-50%) translateY(0); }
        .toast svg { width:18px; height:18px; color:var(--success); }

        /* ── Responsive ── */
        @media (max-width:1100px) {
            .page-grid { grid-template-columns: 1fr; }
            .right-panel { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        }
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); }
            .sidebar-overlay.show { display:block; }
            .main-content { margin-left:0; padding:24px 16px 40px; }
            .hamburger { display:flex; }
            .right-panel { grid-template-columns: 1fr; }
        }
        @keyframes spin  { to { transform:rotate(360deg); } }
        @keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-6px)} 40%{transform:translateX(6px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)} }
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

            <a href="request-money.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                </svg>
                Request Money
            </a>

            <a href="scan-pay.php" class="nav-item active">
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

            <a href="logout.php" class="nav-item" style="color:#f87171;">
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
                <h1>&#x1F4F7; Scan &amp; Pay</h1>
                <p>Scan a QR code or enter UPI ID to pay instantly</p>
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

            <!-- ── Left: Scanner + Manual ─────────────────────── -->
            <div style="display:flex;flex-direction:column;gap:20px;">

                <!-- Scanner Card -->
                <div class="scanner-card" id="scannerCard">
                    <div class="scanner-header">
                        <h2>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                            </svg>
                            QR Scanner
                        </h2>
                        <div class="mode-switcher">
                            <button class="mode-btn active" id="btnCamera" onclick="setMode('camera')">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                                Camera
                            </button>
                            <button class="mode-btn" id="btnManual" onclick="setMode('manual')">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                </svg>
                                Manual
                            </button>
                        </div>
                    </div>

                    <!-- Camera Mode -->
                    <div id="cameraMode">
                        <div class="scanner-viewport" id="scanViewport">
                            <!-- Placeholder when camera off -->
                            <div class="camera-placeholder" id="cameraPlaceholder">
                                <div class="camera-placeholder-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                    </svg>
                                </div>
                                <p>Point your camera at a UPI QR code to pay instantly</p>
                                <small>Camera access required &bull; Tap below to start</small>
                            </div>

                            <!-- Live camera feed -->
                            <video id="cameraFeed" autoplay playsinline muted style="display:none;"></video>
                            <canvas id="cameraCanvas"></canvas>

                            <!-- Overlay frame (visible when camera active) -->
                            <div class="scan-overlay" id="scanOverlay" style="display:none;">
                                <div class="scan-frame">
                                    <div class="scan-laser"></div>
                                    <div class="corner corner--tl"></div>
                                    <div class="corner corner--tr"></div>
                                    <div class="corner corner--bl"></div>
                                    <div class="corner corner--br"></div>
                                </div>
                            </div>
                        </div>

                        <button class="btn-start-camera" id="cameraBtn" onclick="toggleCamera()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            <span id="cameraBtnLabel">Start Camera</span>
                        </button>

                        <div class="scanner-alt">
                            <label for="qrFileInput" class="btn-upload">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                Upload QR Image
                            </label>
                            <input type="file" id="qrFileInput" accept="image/*" onchange="handleQRUpload(event)">
                            <button class="btn-demo-scan" onclick="demoScan()">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                </svg>
                                Demo Scan
                            </button>
                        </div>
                    </div>

                    <!-- Manual Mode -->
                    <div id="manualMode" style="display:none;">
                        <div class="manual-panel">
                            <div class="form-group">
                                <label class="form-label" for="manualUPI">UPI ID or Mobile Number</label>
                                <div class="input-wrap">
                                    <span class="input-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                    </span>
                                    <input type="text" id="manualUPI" class="form-input"
                                        placeholder="e.g. merchant@oksbi or 9876543210"
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="manualAmount">Amount</label>
                                <div class="input-wrap" style="position:relative;">
                                    <span class="amount-currency">&#8377;</span>
                                    <input type="number" id="manualAmount" class="form-input"
                                        placeholder="0.00" min="1" max="100000" step="0.01">
                                </div>
                                <div class="amount-chips" id="manualChips">
                                    <button type="button" class="chip" onclick="setManualAmount(50)">&#8377;50</button>
                                    <button type="button" class="chip" onclick="setManualAmount(100)">&#8377;100</button>
                                    <button type="button" class="chip" onclick="setManualAmount(200)">&#8377;200</button>
                                    <button type="button" class="chip" onclick="setManualAmount(500)">&#8377;500</button>
                                    <button type="button" class="chip" onclick="setManualAmount(1000)">&#8377;1,000</button>
                                    <button type="button" class="chip" onclick="setManualAmount(2000)">&#8377;2,000</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="manualNote">Note (Optional)</label>
                                <div class="input-wrap">
                                    <span class="input-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                    </span>
                                    <input type="text" id="manualNote" class="form-input" placeholder="Reason for payment..." maxlength="100">
                                </div>
                            </div>
                            <button class="btn-pay-now" onclick="manualPay()">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                                Pay Now
                            </button>
                        </div>
                    </div>

                    <!-- Detected QR / Confirm Panel -->
                    <div class="detect-panel" id="detectPanel">
                        <div class="detected-badge" id="detectBadge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            QR Detected
                        </div>
                        <div class="merchant-card">
                            <div class="merchant-avatar" id="merchantAvatar">MR</div>
                            <div class="merchant-info">
                                <h3 id="merchantName">Merchant Name</h3>
                                <p id="merchantUPI">merchant@upi</p>
                            </div>
                        </div>
                        <div class="pay-amount-row">
                            <div class="pay-amount-label">You will pay</div>
                            <div class="pay-amount-val"><span>&#8377;</span><span id="payAmountVal">0.00</span></div>
                        </div>
                        <div class="balance-check">
                            Available balance: <strong>&#8377;<?php echo number_format($balance, 2); ?></strong>
                        </div>
                        <div class="confirm-actions">
                            <button class="btn-confirm" onclick="confirmPay()">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Pay &#8377;<span id="confirmAmtLabel">0</span>
                            </button>
                            <button class="btn-cancel-pay" onclick="cancelPay()">Cancel</button>
                        </div>
                    </div>

                    <!-- Success Panel -->
                    <div class="pay-success" id="paySuccess">
                        <div class="success-ring">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <h3>Payment Successful! &#127881;</h3>
                        <div class="success-amount" id="successAmt">&#8377;0</div>
                        <div class="success-to" id="successTo">Paid to &mdash;</div>
                        <div class="txn-id" id="successTxn">TXN ID: PAYSIM&mdash;</div>
                        <div class="btn-row-success">
                            <button class="btn-done" onclick="goHome()">Go to Dashboard</button>
                            <button class="btn-scan-again" onclick="scanAgain()">Scan Again</button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Right Panel ─────────────────────────────────── -->
            <div class="right-panel">

                <!-- Quick Pay -->
                <div class="card" style="animation-delay:0.1s;">
                    <div class="card-header">
                        <h2>
                            <div class="card-icon card-icon--indigo">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                            </div>
                            Quick Pay
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-pay-grid">
                            <?php foreach ($quickPay as $qp): ?>
                            <button class="quick-pay-item" onclick="quickPayTo('<?php echo htmlspecialchars($qp['upi']); ?>', '<?php echo htmlspecialchars($qp['name']); ?>', '<?php echo htmlspecialchars($qp['avatar']); ?>')">
                                <div class="qp-avatar"><?php echo htmlspecialchars($qp['avatar']); ?></div>
                                <div class="qp-info">
                                    <div class="qp-name"><?php echo htmlspecialchars($qp['name']); ?></div>
                                    <div class="qp-upi"><?php echo htmlspecialchars($qp['upi']); ?></div>
                                </div>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Scans -->
                <div class="card" style="animation-delay:0.15s;">
                    <div class="card-header">
                        <h2>
                            <div class="card-icon card-icon--amber">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            Recent Scans
                        </h2>
                    </div>
                    <ul class="scan-list" style="margin-top:8px;">
                        <?php foreach ($recentScans as $scan): ?>
                        <li class="scan-item" onclick="quickPayTo('<?php echo htmlspecialchars($scan['upi']); ?>', '<?php echo htmlspecialchars($scan['name']); ?>', '<?php echo htmlspecialchars($scan['avatar']); ?>')">
                            <div class="scan-item-avatar <?php echo $scan['status']==='failed' ? 'scan-item-avatar--failed' : ''; ?>">
                                <?php echo htmlspecialchars($scan['avatar']); ?>
                            </div>
                            <div class="scan-item-info">
                                <div class="scan-item-name">
                                    <?php echo htmlspecialchars($scan['name']); ?>
                                    <span class="scan-status-dot scan-status-dot--<?php echo $scan['status']; ?>"></span>
                                </div>
                                <div class="scan-item-upi"><?php echo htmlspecialchars($scan['upi']); ?></div>
                            </div>
                            <div class="scan-item-right">
                                <div class="scan-amount scan-amount--<?php echo $scan['status']; ?>">
                                    <?php echo $scan['status']==='failed' ? '' : '-'; ?>&#8377;<?php echo number_format($scan['amount'], 2); ?>
                                </div>
                                <div class="scan-date"><?php echo htmlspecialchars($scan['date']); ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Tips Card -->
                <div class="tips-card">
                    <div class="tips-badge">&#128274; Safety Tips</div>
                    <h3>Stay Safe While Paying</h3>
                    <div class="tip-row">
                        <div class="tip-dot"></div>
                        <p>Always verify the merchant name before confirming payment.</p>
                    </div>
                    <div class="tip-row">
                        <div class="tip-dot"></div>
                        <p>Never scan QR codes sent via WhatsApp or SMS to receive money — you can only lose, not gain.</p>
                    </div>
                    <div class="tip-row">
                        <div class="tip-dot"></div>
                        <p>Check the UPI ID domain — trusted handles end in @oksbi, @okicici, @ybl, @paytm, etc.</p>
                    </div>
                    <div class="tip-row" style="margin-bottom:0;">
                        <div class="tip-dot"></div>
                        <p>PaySim will never ask for your PIN. Never share it with anyone.</p>
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
    <span id="toastMsg">Done!</span>
</div>

<script>
// ── State ──────────────────────────────────────────────────────────────────────
let cameraStream  = null;
let cameraActive  = false;
let scanInterval  = null;
let currentMode   = 'camera';
let pendingMerchant = null;
let toastTimer;

const BALANCE = <?php echo $balance; ?>;

// ── Mode Switcher ─────────────────────────────────────────────────────────────
function setMode(mode) {
    currentMode = mode;
    document.getElementById('btnCamera').classList.toggle('active', mode === 'camera');
    document.getElementById('btnManual').classList.toggle('active', mode === 'manual');
    document.getElementById('cameraMode').style.display  = mode === 'camera' ? '' : 'none';
    document.getElementById('manualMode').style.display  = mode === 'manual' ? '' : 'none';
    if (mode === 'manual' && cameraActive) stopCamera();
}

// ── Camera ────────────────────────────────────────────────────────────────────
async function toggleCamera() {
    if (cameraActive) { stopCamera(); return; }
    const btn = document.getElementById('cameraBtn');
    btn.innerHTML = '<svg style="width:18px;height:18px;animation:spin 0.8s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Starting…';

    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        const video = document.getElementById('cameraFeed');
        video.srcObject = cameraStream;
        video.style.display = '';
        document.getElementById('cameraPlaceholder').style.display = 'none';
        document.getElementById('scanOverlay').style.display = 'flex';
        cameraActive = true;
        btn.classList.add('active');
        document.getElementById('cameraBtnLabel').textContent = 'Stop Camera';
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 0 1 7.5 5.25h9a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25h-9a2.25 2.25 0 0 1-2.25-2.25v-9Z"/></svg> Stop Camera`;
        showToast('Camera active — point at a QR code');

        // Simulate auto-detection after 4s for demo
        scanInterval = setTimeout(() => {
            if (cameraActive) simulateDetect();
        }, 4000);

    } catch (err) {
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/></svg> Start Camera`;
        showToast('Camera permission denied — use Demo Scan', false);
    }
}

function stopCamera() {
    if (cameraStream) { cameraStream.getTracks().forEach(t => t.stop()); cameraStream = null; }
    clearTimeout(scanInterval);
    cameraActive = false;
    const video = document.getElementById('cameraFeed');
    video.srcObject = null; video.style.display = 'none';
    document.getElementById('cameraPlaceholder').style.display = '';
    document.getElementById('scanOverlay').style.display = 'none';
    const btn = document.getElementById('cameraBtn');
    btn.classList.remove('active');
    btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/></svg> Start Camera`;
}

// ── Simulate detect (for demo + camera auto-detect) ───────────────────────────
const DEMO_MERCHANTS = [
    { name: 'Swiggy',       upi: 'swiggy@icici',   amount: 342, avatar: 'SW' },
    { name: 'Amazon Pay',   upi: 'amazon@apl',     amount: 899, avatar: 'AM' },
    { name: 'Ravi Kumar',   upi: 'ravi@oksbi',     amount: 500, avatar: 'RK' },
    { name: 'BookMyShow',   upi: 'bms@hdfcbank',   amount: 720, avatar: 'BM' },
    { name: 'Zomato',       upi: 'zomato@icici',   amount: 285, avatar: 'ZO' },
];

function demoScan() {
    if (cameraActive) stopCamera();
    const m = DEMO_MERCHANTS[Math.floor(Math.random() * DEMO_MERCHANTS.length)];
    showDetect(m.name, m.upi, m.amount, m.avatar);
}

function simulateDetect() {
    const m = DEMO_MERCHANTS[Math.floor(Math.random() * DEMO_MERCHANTS.length)];
    stopCamera();
    showDetect(m.name, m.upi, m.amount, m.avatar);
}

function showDetect(name, upi, amount, avatar) {
    pendingMerchant = { name, upi, amount, avatar };
    document.getElementById('merchantName').textContent  = name;
    document.getElementById('merchantUPI').textContent   = upi;
    document.getElementById('merchantAvatar').textContent = avatar;
    document.getElementById('payAmountVal').textContent   = amount.toFixed(2);
    document.getElementById('confirmAmtLabel').textContent = amount.toLocaleString('en-IN');
    document.getElementById('detectPanel').classList.add('active');
    document.getElementById('detectPanel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ── Handle QR image upload ────────────────────────────────────────────────────
function handleQRUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    showToast('Reading QR image…');
    setTimeout(() => demoScan(), 1200);
}

// ── Quick Pay (from list / right panel) ──────────────────────────────────────
function quickPayTo(upi, name, avatar) {
    const amount = [100, 200, 300, 500, 750, 1000][Math.floor(Math.random() * 6)];
    showDetect(name, upi, amount, avatar);
    if (currentMode !== 'camera') setMode('camera');
    document.getElementById('scannerCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Confirm Pay ───────────────────────────────────────────────────────────────
function confirmPay() {
    if (!pendingMerchant) return;
    const btn = document.querySelector('.btn-confirm');
    btn.disabled = true;
    btn.innerHTML = '<svg style="width:18px;height:18px;animation:spin 0.8s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Processing…';

    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg> Confirm Pay';
        const { name, amount } = pendingMerchant;
        const txn = 'PAYSIM' + Date.now().toString(36).toUpperCase();
        document.getElementById('successAmt').textContent   = '\u20B9' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('successTo').textContent    = 'Paid to ' + name;
        document.getElementById('successTxn').textContent   = 'TXN ID: ' + txn;
        document.getElementById('detectPanel').classList.remove('active');
        document.getElementById('paySuccess').classList.add('active');
        pendingMerchant = null;
    }, 2000);
}

function cancelPay() {
    document.getElementById('detectPanel').classList.remove('active');
    pendingMerchant = null;
    showToast('Payment cancelled');
}

function scanAgain() {
    document.getElementById('paySuccess').classList.remove('active');
    document.getElementById('detectPanel').classList.remove('active');
}

function goHome() { window.location.href = 'dashboard.php'; }

// ── Manual Pay ────────────────────────────────────────────────────────────────
function manualPay() {
    const upi    = document.getElementById('manualUPI').value.trim();
    const amount = parseFloat(document.getElementById('manualAmount').value);
    if (!upi)                  { shakeEl('manualUPI');    showToast('Enter a UPI ID or mobile number', false); return; }
    if (!amount || amount <= 0){ shakeEl('manualAmount'); showToast('Enter a valid amount', false); return; }
    const name   = upi.includes('@') ? upi.split('@')[0].replace(/^\w/, c => c.toUpperCase()) : 'Contact';
    const avatar = name.substring(0, 2).toUpperCase();
    setMode('camera');
    showDetect(name, upi, amount, avatar);
}

function setManualAmount(val) {
    document.getElementById('manualAmount').value = val;
    document.querySelectorAll('#manualChips .chip').forEach((c, i) => {
        c.classList.toggle('active', [50,100,200,500,1000,2000][i] === val);
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function shakeEl(id) {
    const el = document.getElementById(id);
    el.style.animation = 'none'; el.offsetHeight;
    el.style.animation = 'shake 0.35s';
    el.addEventListener('animationend', () => el.style.animation = '', { once: true });
}

function showToast(msg, ok = true) {
    const el = document.getElementById('toast');
    el.querySelector('svg').style.color = ok ? 'var(--success)' : 'var(--warning)';
    document.getElementById('toastMsg').textContent = msg;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 2600);
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

// Stop camera on page unload
window.addEventListener('beforeunload', () => { if (cameraActive) stopCamera(); });
</script>
</body>
</html>
