<?php
session_start();

if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

$userName  = $_SESSION['NAME']     ?? 'User';
$upiId     = $_SESSION['UPI_ID']   ?? 'user@paysim';
$userId    = (int)($_SESSION['USER_ID'] ?? 0);
$balance   = 24580.75;

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/services/AccountService.php';
try {
    $accountService = new AccountService();
    $dbWallet = $accountService->getWallet($userId);
    if ($dbWallet) {
        $balance = (float)$dbWallet['balance'];
    }
} catch (Exception $e) {}

$firstName = explode(' ', $userName)[0];
$initials  = strtoupper(substr($firstName, 0, 1) . (strpos($userName, ' ') ? substr(explode(' ', $userName)[1], 0, 1) : ''));

$recentContacts = [
    ['name' => 'Ravi Kumar',   'upi' => 'ravi@oksbi',    'avatar' => 'RK', 'color' => '#6366f1'],
    ['name' => 'Priya Sharma', 'upi' => 'priya@paytm',   'avatar' => 'PS', 'color' => '#10b981'],
    ['name' => 'Amit Das',     'upi' => 'amit@ybl',      'avatar' => 'AD', 'color' => '#f59e0b'],
    ['name' => 'Neha Singh',   'upi' => 'neha@okicici',  'avatar' => 'NS', 'color' => '#8b5cf6'],
    ['name' => 'Karan Mehta',  'upi' => 'karan@okaxis',  'avatar' => 'KM', 'color' => '#06b6d4'],
];

$recentTxns = [
    ['name' => 'Ravi Kumar',   'upi' => 'ravi@oksbi',   'amount' => 500.00,  'date' => '2026-09-18', 'avatar' => 'RK', 'color' => '#6366f1'],
    ['name' => 'Netflix',      'upi' => 'netflix@icici','amount' => 649.00,  'date' => '2026-09-16', 'avatar' => 'NF', 'color' => '#ef4444'],
    ['name' => 'Swiggy',       'upi' => 'swiggy@icici', 'amount' => 342.00,  'date' => '2026-09-14', 'avatar' => 'SW', 'color' => '#f97316'],
    ['name' => 'Amit Das',     'upi' => 'amit@ybl',     'amount' => 200.00,  'date' => '2026-09-13', 'avatar' => 'AD', 'color' => '#f59e0b'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Money — PaySim</title>
    <meta name="description" content="Send money instantly to anyone using PaySim UPI. Transfer funds via UPI ID, mobile number, or bank account.">

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
        .bg-orb--1 { width:600px;height:600px;background:radial-gradient(circle,var(--accent) 0%,transparent 70%);top:-200px;right:-150px;animation:orbFloat 10s ease-in-out infinite alternate; }
        .bg-orb--2 { width:450px;height:450px;background:radial-gradient(circle,#8b5cf6 0%,transparent 70%);bottom:-150px;left:-100px;animation:orbFloat 12s ease-in-out infinite alternate-reverse; }
        .bg-orb--3 { width:300px;height:300px;background:radial-gradient(circle,#06b6d4 0%,transparent 70%);top:50%;left:40%;animation:orbFloat 14s ease-in-out infinite alternate; }
        @keyframes orbFloat { 0%{transform:translate(0,0)scale(1)} 100%{transform:translate(40px,-30px)scale(1.15)} }

        /* ── Layout ── */
        .app-layout { display:flex; min-height:100vh; position:relative; z-index:1; }

        /* ── Sidebar ── */
        .sidebar { width:var(--sidebar-width);background:var(--bg-card);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;transition:transform 0.35s cubic-bezier(0.16,1,0.3,1); }
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
        .nav-badge { margin-left:auto;background:var(--accent);color:#fff;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:10px;min-width:20px;text-align:center; }
        .sidebar-footer { padding:16px;border-top:1px solid var(--border); }
        .sidebar-user { display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;transition:background 0.2s;cursor:pointer;text-decoration:none; }
        .sidebar-user:hover { background:var(--bg-hover); }
        .sidebar-avatar { width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;flex-shrink:0; }
        .sidebar-user-info { overflow:hidden; }
        .sidebar-user-info .name { font-size:0.85rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
        .sidebar-user-info .upi { font-size:0.72rem;color:var(--text-muted); }

        /* ── Main ── */
        .main-content { flex:1;margin-left:var(--sidebar-width);padding:32px 36px 48px;min-height:100vh; }

        /* ── Header ── */
        .top-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:32px; }
        .page-title-area h1 { font-size:1.65rem;font-weight:700;letter-spacing:-0.02em;margin-bottom:4px; }
        .page-title-area p { color:var(--text-secondary);font-size:0.9rem; }
        .header-actions { display:flex;align-items:center;gap:12px; }
        .btn-icon { width:42px;height:42px;border-radius:12px;background:var(--bg-card);backdrop-filter:blur(16px);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-secondary);cursor:pointer;transition:all 0.2s;text-decoration:none;position:relative; }
        .btn-icon:hover { background:var(--bg-hover);color:var(--text-primary);border-color:var(--border-focus); }
        .btn-icon svg { width:20px;height:20px; }
        .btn-icon .notif-dot { position:absolute;top:8px;right:8px;width:8px;height:8px;background:var(--error);border-radius:50%;border:2px solid var(--bg-primary); }
        .hamburger { display:none; }

        /* ── Page Grid ── */
        .page-grid { display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start; }

        /* ── Cards ── */
        .card { background:var(--bg-card);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;animation:fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) forwards;opacity:0;transform:translateY(20px); }
        @keyframes fadeUp { to { opacity:1;transform:translateY(0); } }
        .card-header { padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between; }
        .card-header h2 { font-size:1rem;font-weight:700;letter-spacing:-0.01em;display:flex;align-items:center;gap:10px; }
        .card-icon { width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center; }
        .card-icon--indigo { background:rgba(99,102,241,0.12);color:var(--accent-hover); }
        .card-icon--green  { background:var(--success-bg);color:var(--success); }
        .card-icon--amber  { background:var(--warning-bg);color:var(--warning); }
        .card-icon svg { width:16px;height:16px; }
        .card-body { padding:20px 24px 24px; }

        /* ── Steps Indicator ── */
        .steps-bar {
            display: flex; align-items: center; gap: 0;
            padding: 20px 24px 0;
            margin-bottom: -4px;
        }
        .step {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.75rem; font-weight: 500; color: var(--text-muted);
            flex: 1;
        }
        .step-num {
            width: 26px; height: 26px; border-radius: 50%;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
            transition: all 0.3s;
        }
        .step.active .step-num { background: var(--accent); border-color: var(--accent); color: #fff; box-shadow: 0 0 12px var(--accent-glow); }
        .step.done .step-num   { background: var(--success); border-color: var(--success); color: #fff; }
        .step.active { color: var(--text-primary); }
        .step.done   { color: var(--success); }
        .step-line { flex: 1; height: 1px; background: var(--border); margin: 0 8px; max-width: 40px; transition: background 0.3s; }
        .step-line.done { background: var(--success); }
        .step-label { display: none; }
        @media (min-width: 600px) { .step-label { display: inline; } }

        /* ── Balance Banner ── */
        .balance-banner {
            margin: 20px 24px 0;
            padding: 14px 18px;
            background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.07));
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: var(--radius);
            display: flex; align-items: center; justify-content: space-between;
        }
        .balance-banner .bal-label { font-size: 0.72rem; color: var(--text-muted); margin-bottom: 2px; }
        .balance-banner .bal-val   { font-size: 1.1rem; font-weight: 800; color: var(--accent-hover); letter-spacing: -0.01em; }
        .balance-banner .bal-upi   { font-size: 0.72rem; color: var(--text-muted); text-align: right; }
        .balance-banner .bal-id    { font-size: 0.82rem; font-weight: 600; color: var(--text-secondary); }

        /* ── Form ── */
        .form-panel { padding: 20px 24px 24px; }
        .form-panel.hidden { display: none; }
        .form-group { margin-bottom: 18px; }
        .form-label { display:block;font-size:0.78rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px; }
        .input-wrap { position:relative; }
        .input-prefix { position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);display:flex;align-items:center;pointer-events:none; }
        .input-prefix svg { width:16px;height:16px; }
        .form-input { width:100%;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:var(--radius);padding:13px 16px 13px 42px;font-family:inherit;font-size:0.9rem;color:var(--text-primary);transition:border-color 0.2s,box-shadow 0.2s;outline:none;appearance:none; }
        .form-input::placeholder { color:var(--text-muted); }
        .form-input:focus { border-color:var(--border-focus);box-shadow:0 0 0 3px rgba(99,102,241,0.12); }

        /* UPI verification badge */
        .upi-verified {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            display: none; align-items: center; gap: 5px;
            font-size: 0.72rem; font-weight: 600; color: var(--success);
            background: var(--success-bg); border: 1px solid var(--success-border);
            padding: 4px 10px; border-radius: 20px;
        }
        .upi-verified.show { display: flex; }
        .upi-verified svg { width: 12px; height: 12px; }

        /* Amount field */
        .amount-currency { position:absolute;left:16px;top:50%;transform:translateY(-50%);font-size:1.3rem;font-weight:700;color:var(--text-muted);pointer-events:none; }
        #sendAmount { padding-left:36px;font-size:1.65rem;font-weight:800;letter-spacing:-0.02em;height:70px;background:rgba(99,102,241,0.04);border-color:rgba(99,102,241,0.2); }
        #sendAmount:focus { border-color:var(--accent);box-shadow:0 0 0 3px rgba(99,102,241,0.15); }
        .amount-chips { display:flex;gap:8px;flex-wrap:wrap;margin-top:10px; }
        .chip { padding:6px 14px;border-radius:20px;border:1px solid var(--border);background:transparent;color:var(--text-secondary);font-family:inherit;font-size:0.8rem;font-weight:500;cursor:pointer;transition:all 0.2s; }
        .chip:hover { border-color:var(--accent);color:var(--accent-hover);background:rgba(99,102,241,0.08); }
        .chip.active { border-color:var(--accent);color:var(--accent-hover);background:rgba(99,102,241,0.12); }

        /* Remaining balance hint */
        .remaining-hint {
            margin-top: 8px;
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex; align-items: center; gap: 5px;
        }
        .remaining-hint svg { width: 13px; height: 13px; }
        .remaining-hint.warn { color: var(--warning); }
        .remaining-hint.danger { color: var(--error); }

        /* UPI method selector */
        .method-tabs {
            display: flex; gap: 4px;
            background: rgba(255,255,255,0.04);
            border-radius: 10px; padding: 4px;
            margin-bottom: 16px;
        }
        .method-tab {
            flex: 1; padding: 8px 12px; border: none; border-radius: 8px;
            background: none; color: var(--text-muted);
            font-family: inherit; font-size: 0.78rem; font-weight: 500;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .method-tab svg { width: 13px; height: 13px; }
        .method-tab.active { background: var(--bg-card-solid); color: var(--text-primary); box-shadow: 0 2px 8px rgba(0,0,0,0.3); }

        /* Buttons */
        .btn-primary { width:100%;padding:15px 24px;background:linear-gradient(135deg,var(--accent),#8b5cf6);border:none;border-radius:var(--radius);color:#fff;font-family:inherit;font-size:0.95rem;font-weight:600;cursor:pointer;letter-spacing:0.01em;transition:transform 0.2s,box-shadow 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:24px; }
        .btn-primary:hover { transform:translateY(-2px);box-shadow:0 8px 30px var(--accent-glow); }
        .btn-primary:active { transform:translateY(0); }
        .btn-primary svg { width:18px;height:18px; }
        .btn-outline { width:100%;padding:13px 24px;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:var(--radius);color:var(--text-secondary);font-family:inherit;font-size:0.88rem;font-weight:500;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:10px; }
        .btn-outline:hover { background:var(--bg-hover);color:var(--text-primary);border-color:var(--border-focus); }
        .btn-outline svg { width:16px;height:16px; }

        /* ── Review Panel ── */
        .review-panel { padding: 20px 24px 24px; display: none; }
        .review-panel.active { display: block; }
        .review-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .review-amount-block {
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            padding: 28px; text-align: center;
            border-bottom: 1px solid rgba(99,102,241,0.2);
        }
        .review-amount-block .rev-label { font-size: 0.72rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
        .review-amount-block .rev-amount { font-size: 2.4rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; }
        .review-amount-block .rev-amount span { font-size: 1.4rem; font-weight: 600; opacity: 0.7; }
        .review-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid rgba(99,102,241,0.06);
            font-size: 0.85rem;
        }
        .review-row:last-child { border-bottom: none; }
        .review-row .rev-key { color: var(--text-muted); }
        .review-row .rev-val { font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 6px; }
        .rev-to-avatar {
            width: 28px; height: 28px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem; font-weight: 700; color: #fff;
        }

        /* PIN input */
        .pin-section { margin-top: 20px; }
        .pin-label { font-size: 0.78rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .pin-label svg { width: 14px; height: 14px; color: var(--warning); }
        .pin-dots {
            display: flex; gap: 12px; justify-content: center;
            margin-bottom: 16px;
        }
        .pin-dot {
            width: 14px; height: 14px; border-radius: 50%;
            background: rgba(255,255,255,0.1);
            border: 1px solid var(--border);
            transition: all 0.2s;
        }
        .pin-dot.filled { background: var(--accent); border-color: var(--accent); box-shadow: 0 0 8px var(--accent-glow); }
        .pin-pad {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .pin-key {
            padding: 14px; border-radius: var(--radius);
            background: rgba(255,255,255,0.04); border: 1px solid var(--border);
            color: var(--text-primary); font-family: inherit;
            font-size: 1.1rem; font-weight: 600; cursor: pointer;
            transition: all 0.15s; display: flex; align-items: center; justify-content: center;
        }
        .pin-key:hover { background: rgba(99,102,241,0.1); border-color: var(--accent); color: var(--accent-hover); }
        .pin-key:active { transform: scale(0.93); }
        .pin-key--del { color: var(--text-muted); font-size: 0.9rem; }
        .pin-key--del svg { width: 18px; height: 18px; }

        /* ── Success ── */
        .success-panel { padding: 32px 24px; text-align: center; display: none; }
        .success-panel.active { display: block; }
        .success-ring { width:80px;height:80px;border-radius:50%;background:var(--success-bg);border:2px solid var(--success-border);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;animation:ringPop 0.4s cubic-bezier(0.16,1,0.3,1); }
        @keyframes ringPop { from{transform:scale(0.5);opacity:0} to{transform:scale(1);opacity:1} }
        .success-ring svg { width:40px;height:40px;color:var(--success); }
        .success-panel h3 { font-size:1.15rem;font-weight:700;margin-bottom:8px; }
        .success-panel .s-amount { font-size:2.2rem;font-weight:800;color:var(--success);letter-spacing:-0.03em;margin-bottom:6px; }
        .success-panel .s-to   { font-size:0.85rem;color:var(--text-secondary);margin-bottom:4px; }
        .success-panel .s-txn  { font-size:0.7rem;color:var(--text-muted);font-family:'Courier New',monospace;margin-bottom:24px; }
        .success-actions { display:flex;gap:10px;justify-content:center;flex-wrap:wrap; }
        .btn-done { padding:12px 28px;background:linear-gradient(135deg,var(--accent),#8b5cf6);border:none;border-radius:var(--radius);color:#fff;font-family:inherit;font-size:0.88rem;font-weight:600;cursor:pointer;transition:all 0.2s; }
        .btn-done:hover { transform:translateY(-2px);box-shadow:0 8px 24px var(--accent-glow); }
        .btn-receipt { padding:12px 28px;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:var(--radius);color:var(--text-secondary);font-family:inherit;font-size:0.88rem;font-weight:500;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-block; }
        .btn-receipt:hover { background:var(--bg-hover);color:var(--text-primary);border-color:var(--border-focus); }

        /* Confetti particles */
        .confetti-wrap { position:fixed;inset:0;pointer-events:none;z-index:9999;display:none; }
        .confetti-wrap.active { display:block; }
        .confetti-piece {
            position:absolute; width:8px; height:8px; border-radius:2px;
            animation: confettiFall 2.5s ease-in forwards;
        }
        @keyframes confettiFall {
            0%   { transform: translateY(-20px) rotateZ(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotateZ(720deg); opacity: 0; }
        }

        /* ── Right Panel ── */
        .right-panel { display:flex;flex-direction:column;gap:20px; }

        /* Contacts */
        .contact-scroll { display:flex;gap:14px;padding:16px 24px 20px;overflow-x:auto;scrollbar-width:none; }
        .contact-scroll::-webkit-scrollbar { display:none; }
        .contact-item { display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;min-width:60px; transition: transform 0.2s; }
        .contact-item:hover { transform: translateY(-2px); }
        .contact-avatar { width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;flex-shrink:0;border:2px solid rgba(255,255,255,0.1);transition:border-color 0.2s,box-shadow 0.2s; }
        .contact-item:hover .contact-avatar { border-color:var(--border-focus);box-shadow:0 4px 16px rgba(99,102,241,0.3); }
        .contact-name { font-size:0.68rem;color:var(--text-secondary);font-weight:500;text-align:center;white-space:nowrap;max-width:60px;overflow:hidden;text-overflow:ellipsis; }

        /* Recent txn list */
        .txn-list { list-style:none; }
        .txn-item { display:flex;align-items:center;gap:12px;padding:13px 24px;border-bottom:1px solid rgba(99,102,241,0.06);transition:background 0.2s;cursor:pointer; }
        .txn-item:last-child { border-bottom:none; }
        .txn-item:hover { background:var(--bg-hover); }
        .txn-avatar { width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;color:#fff;flex-shrink:0; }
        .txn-info { flex:1;min-width:0; }
        .txn-name { font-size:0.85rem;font-weight:600;margin-bottom:2px; }
        .txn-upi  { font-size:0.72rem;color:var(--text-muted); }
        .txn-right { text-align:right;flex-shrink:0; }
        .txn-amount { font-size:0.88rem;font-weight:700;color:var(--error);margin-bottom:2px; }
        .txn-date   { font-size:0.68rem;color:var(--text-muted); }

        /* ── Sidebar overlay ── */
        .sidebar-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99; }
        .toast { position:fixed;bottom:32px;left:50%;transform:translateX(-50%) translateY(80px);background:var(--bg-card-solid);border:1px solid var(--border);border-radius:var(--radius);padding:12px 20px;font-size:0.82rem;color:var(--text-primary);display:flex;align-items:center;gap:8px;z-index:1000;transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);box-shadow:0 12px 40px rgba(0,0,0,0.4); }
        .toast.show { transform:translateX(-50%) translateY(0); }
        .toast svg { width:18px;height:18px;color:var(--success); }

        /* ── Responsive ── */
        @media (max-width:1100px) { .page-grid{grid-template-columns:1fr} .right-panel{display:grid;grid-template-columns:1fr 1fr;gap:20px} }
        @media (max-width:768px)  { .sidebar{transform:translateX(-100%)} .sidebar.open{transform:translateX(0)} .sidebar-overlay.show{display:block} .main-content{margin-left:0;padding:24px 16px 40px} .hamburger{display:flex} .right-panel{grid-template-columns:1fr} }

        @keyframes spin  { to{transform:rotate(360deg)} }
        @keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-6px)} 40%{transform:translateX(6px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)} }
    </style>
</head>
<body>

<div class="bg-orb bg-orb--1"></div>
<div class="bg-orb bg-orb--2"></div>
<div class="bg-orb bg-orb--3"></div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<div class="confetti-wrap" id="confettiWrap"></div>

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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                Dashboard
            </a>
            <a href="send-money.php" class="nav-item active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                Send Money
            </a>
            <a href="request-money.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3"/></svg>
                Request Money
            </a>
            <a href="scan-pay.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/></svg>
                Scan &amp; Pay
            </a>

            <div class="nav-section-label">Manage</div>
            <a href="transactions.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                Transactions
            </a>
            <a href="add-money.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Add Money
            </a>
            <a href="bank-account.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21"/></svg>
                Bank Account
            </a>
            <a href="notifications.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                Notifications
                <span class="nav-badge">3</span>
            </a>

            <div class="nav-section-label">Account</div>
            <a href="profile.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                Profile
            </a>
            <a href="settings.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                Settings
            </a>
            <a href="logout.php" class="nav-item" style="color:#f87171;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
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

    <!-- ─── Main ──────────────────────────────────────────────── -->
    <main class="main-content">

        <div class="top-header">
            <div class="page-title-area">
                <h1>&#x1F4B8; Send Money</h1>
                <p>Transfer funds instantly to any UPI ID, mobile or bank</p>
            </div>
            <div class="header-actions">
                <button class="btn-icon hamburger" onclick="toggleSidebar()" aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <a href="notifications.php" class="btn-icon" aria-label="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                    <span class="notif-dot"></span>
                </a>
                <a href="profile.php" class="btn-icon" aria-label="Profile">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </a>
            </div>
        </div>

        <div class="page-grid">

            <!-- ── Left ───────────────────────────────────────── -->
            <div>
                <div class="card" style="animation-delay:0.05s;">

                    <!-- Steps -->
                    <div class="steps-bar">
                        <div class="step active" id="step1"><div class="step-num">1</div><span class="step-label">Details</span></div>
                        <div class="step-line" id="line12"></div>
                        <div class="step" id="step2"><div class="step-num">2</div><span class="step-label">Review</span></div>
                        <div class="step-line" id="line23"></div>
                        <div class="step" id="step3"><div class="step-num">3</div><span class="step-label">Confirm</span></div>
                    </div>

                    <!-- Balance banner -->
                    <div class="balance-banner">
                        <div>
                            <div class="bal-label">Available Balance</div>
                            <div class="bal-val">&#8377;<?php echo number_format($balance, 2); ?></div>
                        </div>
                        <div>
                            <div class="bal-upi">Your UPI ID</div>
                            <div class="bal-id"><?php echo htmlspecialchars($upiId); ?></div>
                        </div>
                    </div>

                    <!-- ── Step 1: Form ── -->
                    <div class="form-panel" id="formPanel">

                        <!-- Method tabs -->
                        <div style="margin-bottom:16px;">
                            <div class="method-tabs" id="methodTabs">
                                <button class="method-tab active" onclick="setMethod('upi',this)" id="tabUPI">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg>
                                    UPI ID
                                </button>
                                <button class="method-tab" onclick="setMethod('mobile',this)" id="tabMobile">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 4.5h1.5"/></svg>
                                    Mobile
                                </button>
                                <button class="method-tab" onclick="setMethod('bank',this)" id="tabBank">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg>
                                    Bank
                                </button>
                            </div>
                        </div>

                        <!-- UPI ID input -->
                        <div id="fieldUPI" class="form-group">
                            <label class="form-label" for="upiInput">UPI ID</label>
                            <div class="input-wrap">
                                <span class="input-prefix">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25"/></svg>
                                </span>
                                <input type="text" id="upiInput" class="form-input"
                                    placeholder="e.g. ravi@oksbi"
                                    autocomplete="off" oninput="onUPIInput(this.value)">
                                <div class="upi-verified" id="upiVerified">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    Verified
                                </div>
                            </div>
                        </div>

                        <!-- Mobile input -->
                        <div id="fieldMobile" class="form-group" style="display:none;">
                            <label class="form-label" for="mobileInput">Mobile Number</label>
                            <div class="input-wrap">
                                <span class="input-prefix">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 4.5h1.5"/></svg>
                                </span>
                                <input type="tel" id="mobileInput" class="form-input" placeholder="10-digit mobile number" maxlength="10">
                            </div>
                        </div>

                        <!-- Bank inputs -->
                        <div id="fieldBank" style="display:none;">
                            <div class="form-group">
                                <label class="form-label" for="bankAcc">Account Number</label>
                                <div class="input-wrap">
                                    <span class="input-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/></svg>
                                    </span>
                                    <input type="text" id="bankAcc" class="form-input" placeholder="e.g. 9876543210" maxlength="18">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="bankIFSC">IFSC Code</label>
                                <div class="input-wrap">
                                    <span class="input-prefix">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25"/></svg>
                                    </span>
                                    <input type="text" id="bankIFSC" class="form-input" placeholder="e.g. SBIN0001234" maxlength="11" style="text-transform:uppercase;">
                                </div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="form-group">
                            <label class="form-label" for="sendAmount">Amount</label>
                            <div class="input-wrap" style="position:relative;">
                                <span class="amount-currency">&#8377;</span>
                                <input type="number" id="sendAmount" class="form-input"
                                    placeholder="0.00" min="1" max="100000" step="0.01"
                                    oninput="onAmountChange(this.value)">
                            </div>
                            <div class="amount-chips">
                                <button type="button" class="chip" onclick="setAmount(100)">&#8377;100</button>
                                <button type="button" class="chip" onclick="setAmount(200)">&#8377;200</button>
                                <button type="button" class="chip" onclick="setAmount(500)">&#8377;500</button>
                                <button type="button" class="chip" onclick="setAmount(1000)">&#8377;1K</button>
                                <button type="button" class="chip" onclick="setAmount(2000)">&#8377;2K</button>
                                <button type="button" class="chip" onclick="setAmount(5000)">&#8377;5K</button>
                            </div>
                            <div class="remaining-hint" id="remainHint" style="display:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                <span id="remainText"></span>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="form-group">
                            <label class="form-label" for="sendNote">Note (Optional)</label>
                            <div class="input-wrap">
                                <span class="input-prefix">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                                </span>
                                <input type="text" id="sendNote" class="form-input" placeholder="What's this for?" maxlength="100">
                            </div>
                        </div>

                        <button class="btn-primary" onclick="goToReview()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            Continue to Review
                        </button>
                    </div>

                    <!-- ── Step 2: Review ── -->
                    <div class="review-panel" id="reviewPanel">
                        <div class="review-card">
                            <div class="review-amount-block">
                                <div class="rev-label">You are sending</div>
                                <div class="rev-amount"><span>&#8377;</span><span id="revAmt">0.00</span></div>
                            </div>
                            <div class="review-row">
                                <span class="rev-key">To</span>
                                <span class="rev-val">
                                    <div class="rev-to-avatar" id="revAvatar" style="background:#6366f1;">??</div>
                                    <div>
                                        <div id="revName" style="font-size:0.88rem;font-weight:600;"></div>
                                        <div id="revUPI"  style="font-size:0.72rem;color:var(--text-muted);"></div>
                                    </div>
                                </span>
                            </div>
                            <div class="review-row">
                                <span class="rev-key">From</span>
                                <span class="rev-val"><?php echo htmlspecialchars($upiId); ?></span>
                            </div>
                            <div class="review-row" id="revNoteRow" style="display:none;">
                                <span class="rev-key">Note</span>
                                <span class="rev-val" id="revNote"></span>
                            </div>
                            <div class="review-row">
                                <span class="rev-key">Balance after</span>
                                <span class="rev-val" id="revBalance" style="color:var(--success);"></span>
                            </div>
                            <div class="review-row">
                                <span class="rev-key">Estimated Time</span>
                                <span class="rev-val" style="color:var(--success);">Instant</span>
                            </div>
                        </div>

                        <!-- PIN pad -->
                        <div class="pin-section">
                            <div class="pin-label">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                Enter UPI PIN to confirm
                            </div>
                            <div class="pin-dots">
                                <div class="pin-dot" id="dot0"></div>
                                <div class="pin-dot" id="dot1"></div>
                                <div class="pin-dot" id="dot2"></div>
                                <div class="pin-dot" id="dot3"></div>
                                <div class="pin-dot" id="dot4"></div>
                                <div class="pin-dot" id="dot5"></div>
                            </div>
                            <div class="pin-pad">
                                <?php for ($i = 1; $i <= 9; $i++): ?>
                                <button class="pin-key" onclick="pinPress(<?php echo $i; ?>)"><?php echo $i; ?></button>
                                <?php endfor; ?>
                                <button class="pin-key" style="opacity:0.3;" disabled></button>
                                <button class="pin-key" onclick="pinPress(0)">0</button>
                                <button class="pin-key pin-key--del" onclick="pinDel()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75 14.25 12m0 0 2.25 2.25M14.25 12l2.25-2.25M14.25 12 12 14.25m-2.58 4.92-6.374-6.375a1.125 1.125 0 0 1 0-1.59L9.42 4.83c.21-.211.497-.33.795-.33H19.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25h-9.284c-.298 0-.585-.119-.795-.33Z"/></svg>
                                </button>
                            </div>
                        </div>

                        <button class="btn-outline" onclick="backToForm()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Edit Details
                        </button>
                    </div>

                    <!-- ── Step 3: Success ── -->
                    <div class="success-panel" id="successPanel">
                        <div class="success-ring">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <h3>Money Sent! &#127881;</h3>
                        <div class="s-amount" id="sAmt">&#8377;0</div>
                        <div class="s-to" id="sTo">Sent to &mdash;</div>
                        <div class="s-txn" id="sTxn">TXN ID: PAYSIM&mdash;</div>
                        <div class="success-actions">
                            <button class="btn-done" onclick="window.location.href='dashboard.php'">Dashboard</button>
                            <a href="receipt.php" class="btn-receipt">View Receipt</a>
                            <button class="btn-receipt" onclick="sendAnother()" style="border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--text-secondary);">Send Again</button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ── Right Panel ─────────────────────────────────── -->
            <div class="right-panel">

                <!-- Frequent contacts -->
                <div class="card" style="animation-delay:0.1s;">
                    <div class="card-header">
                        <h2>
                            <div class="card-icon card-icon--indigo">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                            </div>
                            Frequent
                        </h2>
                    </div>
                    <div class="contact-scroll">
                        <?php foreach ($recentContacts as $c): ?>
                        <div class="contact-item" onclick="fillContact('<?php echo htmlspecialchars($c['upi']); ?>','<?php echo htmlspecialchars($c['name']); ?>')">
                            <div class="contact-avatar" style="background:<?php echo $c['color']; ?>;"><?php echo htmlspecialchars($c['avatar']); ?></div>
                            <div class="contact-name"><?php echo htmlspecialchars(explode(' ',$c['name'])[0]); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Recent sends -->
                <div class="card" style="animation-delay:0.15s;">
                    <div class="card-header" style="padding-bottom:8px;">
                        <h2>
                            <div class="card-icon card-icon--amber">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            </div>
                            Recent Sends
                        </h2>
                    </div>
                    <ul class="txn-list">
                        <?php foreach ($recentTxns as $t): ?>
                        <li class="txn-item" onclick="fillContact('<?php echo htmlspecialchars($t['upi']); ?>','<?php echo htmlspecialchars($t['name']); ?>')">
                            <div class="txn-avatar" style="background:<?php echo $t['color']; ?>20;color:<?php echo $t['color']; ?>;"><?php echo htmlspecialchars($t['avatar']); ?></div>
                            <div class="txn-info">
                                <div class="txn-name"><?php echo htmlspecialchars($t['name']); ?></div>
                                <div class="txn-upi"><?php echo htmlspecialchars($t['upi']); ?></div>
                            </div>
                            <div class="txn-right">
                                <div class="txn-amount">-&#8377;<?php echo number_format($t['amount'],2); ?></div>
                                <div class="txn-date"><?php echo $t['date']; ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Limit info card -->
                <div class="card" style="animation-delay:0.2s;background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(139,92,246,0.07));border-color:rgba(99,102,241,0.2);">
                    <div class="card-body">
                        <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent-hover);background:rgba(99,102,241,0.15);padding:3px 10px;border-radius:6px;display:inline-block;margin-bottom:12px;">Limits &amp; Info</div>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <div style="display:flex;justify-content:space-between;font-size:0.8rem;">
                                <span style="color:var(--text-muted);">Per Transaction</span>
                                <span style="font-weight:600;">&#8377;1,00,000</span>
                            </div>
                            <div style="height:1px;background:var(--border);"></div>
                            <div style="display:flex;justify-content:space-between;font-size:0.8rem;">
                                <span style="color:var(--text-muted);">Daily Limit</span>
                                <span style="font-weight:600;">&#8377;2,00,000</span>
                            </div>
                            <div style="height:1px;background:var(--border);"></div>
                            <div style="display:flex;justify-content:space-between;font-size:0.8rem;">
                                <span style="color:var(--text-muted);">Today Used</span>
                                <span style="font-weight:600;color:var(--warning);">&#8377;8,320</span>
                            </div>
                            <!-- usage bar -->
                            <div>
                                <div style="height:5px;background:rgba(255,255,255,0.06);border-radius:10px;overflow:hidden;margin-top:4px;">
                                    <div style="height:100%;width:4.16%;background:linear-gradient(90deg,var(--accent),#8b5cf6);border-radius:10px;"></div>
                                </div>
                                <div style="font-size:0.68rem;color:var(--text-muted);margin-top:5px;">4.16% of daily limit used</div>
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
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
    <span id="toastMsg">Done!</span>
</div>

<script>
const BALANCE  = <?php echo $balance; ?>;
const MY_UPI   = <?php echo json_encode($upiId); ?>;
let currentMethod = 'upi';
let currentAmount = 0;
let pinEntered     = '';
let toastTimer;

// ── Method tabs ───────────────────────────────────────────────────────────────
function setMethod(m, btn) {
    currentMethod = m;
    document.querySelectorAll('.method-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('fieldUPI').style.display    = m === 'upi'    ? '' : 'none';
    document.getElementById('fieldMobile').style.display = m === 'mobile' ? '' : 'none';
    document.getElementById('fieldBank').style.display   = m === 'bank'   ? '' : 'none';
}

// ── UPI live verification ──────────────────────────────────────────────────────
let verifyTimer;
function onUPIInput(val) {
    const badge = document.getElementById('upiVerified');
    badge.classList.remove('show');
    clearTimeout(verifyTimer);
    if (val.includes('@') && val.length > 5) {
        verifyTimer = setTimeout(() => badge.classList.add('show'), 900);
    }
}

// ── Amount ────────────────────────────────────────────────────────────────────
function setAmount(val) {
    document.getElementById('sendAmount').value = val;
    onAmountChange(val);
}
function onAmountChange(val) {
    currentAmount = parseFloat(val) || 0;
    const remaining = BALANCE - currentAmount;
    const hint = document.getElementById('remainHint');
    const text = document.getElementById('remainText');
    if (currentAmount > 0) {
        hint.style.display = 'flex';
        if (remaining < 0) {
            hint.className = 'remaining-hint danger';
            text.textContent = 'Insufficient balance — you\'re ₹' + Math.abs(remaining).toLocaleString('en-IN', {minimumFractionDigits:2}) + ' short';
        } else if (remaining < 500) {
            hint.className = 'remaining-hint warn';
            text.textContent = '₹' + remaining.toLocaleString('en-IN', {minimumFractionDigits:2}) + ' will remain after this transfer';
        } else {
            hint.className = 'remaining-hint';
            text.textContent = '₹' + remaining.toLocaleString('en-IN', {minimumFractionDigits:2}) + ' remaining balance';
        }
    } else {
        hint.style.display = 'none';
    }
    // Chips highlight
    const chipAmts = [100,200,500,1000,2000,5000];
    document.querySelectorAll('.amount-chips .chip').forEach((c,i) => {
        c.classList.toggle('active', chipAmts[i] === currentAmount);
    });
}

// ── Fill from contact / recent ────────────────────────────────────────────────
function fillContact(upi, name) {
    document.getElementById('upiInput').value = upi;
    setMethod('upi', document.getElementById('tabUPI'));
    onUPIInput(upi);
    showToast('Selected: ' + name);
    document.querySelector('.main-content').scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Get recipient details ─────────────────────────────────────────────────────
function getRecipient() {
    if (currentMethod === 'upi')    return { id: document.getElementById('upiInput').value.trim(),    label: 'UPI ID' };
    if (currentMethod === 'mobile') return { id: document.getElementById('mobileInput').value.trim(), label: 'Mobile' };
    const acc  = document.getElementById('bankAcc').value.trim();
    const ifsc = document.getElementById('bankIFSC').value.trim();
    return { id: acc + ' / ' + ifsc, label: 'Bank' };
}

function getNameFromUPI(upi) {
    if (!upi) return 'Recipient';
    const part = upi.split('@')[0];
    return part.charAt(0).toUpperCase() + part.slice(1);
}

function getAvatarFromName(name) {
    const parts = name.split(' ');
    return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
}

// ── Step navigation ───────────────────────────────────────────────────────────
function goToReview() {
    const rec    = getRecipient();
    const amount = parseFloat(document.getElementById('sendAmount').value);
    const note   = document.getElementById('sendNote').value.trim();

    if (!rec.id)            { shakeEl(currentMethod==='upi'?'upiInput':currentMethod==='mobile'?'mobileInput':'bankAcc'); showToast('Enter a valid recipient', false); return; }
    if (!amount || amount <= 0) { shakeEl('sendAmount'); showToast('Enter a valid amount', false); return; }
    if (amount > BALANCE)   { shakeEl('sendAmount'); showToast('Insufficient balance', false); return; }

    const name = getNameFromUPI(rec.id);
    const avatar = getAvatarFromName(name);
    const remaining = BALANCE - amount;

    document.getElementById('revAmt').textContent     = amount.toLocaleString('en-IN', {minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('revName').textContent    = name;
    document.getElementById('revUPI').textContent     = rec.id;
    document.getElementById('revAvatar').textContent  = avatar;
    document.getElementById('revBalance').textContent = '₹' + remaining.toLocaleString('en-IN', {minimumFractionDigits:2});

    if (note) {
        document.getElementById('revNoteRow').style.display = '';
        document.getElementById('revNote').textContent = note;
    } else {
        document.getElementById('revNoteRow').style.display = 'none';
    }

    document.getElementById('formPanel').classList.add('hidden');
    document.getElementById('reviewPanel').classList.add('active');
    setStep(2);
    pinEntered = '';
    updatePinDots();
}

function backToForm() {
    document.getElementById('formPanel').classList.remove('hidden');
    document.getElementById('reviewPanel').classList.remove('active');
    setStep(1);
    pinEntered = '';
    updatePinDots();
}

// ── Steps ──────────────────────────────────────────────────────────────────────
function setStep(n) {
    for (let i = 1; i <= 3; i++) {
        const s = document.getElementById('step' + i);
        s.className = 'step' + (i < n ? ' done' : i === n ? ' active' : '');
        s.querySelector('.step-num').textContent = i < n ? '✓' : i;
    }
    if (document.getElementById('line12')) document.getElementById('line12').className = 'step-line' + (n >= 2 ? ' done' : '');
    if (document.getElementById('line23')) document.getElementById('line23').className = 'step-line' + (n >= 3 ? ' done' : '');
}

// ── PIN ───────────────────────────────────────────────────────────────────────
function pinPress(digit) {
    if (pinEntered.length >= 6) return;
    pinEntered += digit;
    updatePinDots();
    if (pinEntered.length === 6) {
        setTimeout(processPayment, 300);
    }
}
function pinDel() {
    pinEntered = pinEntered.slice(0, -1);
    updatePinDots();
}
function updatePinDots() {
    for (let i = 0; i < 6; i++) {
        document.getElementById('dot' + i).classList.toggle('filled', i < pinEntered.length);
    }
}

// ── Payment ───────────────────────────────────────────────────────────────────
function processPayment() {
    setStep(3);
    const amount = parseFloat(document.getElementById('sendAmount').value);
    const name   = document.getElementById('revName').textContent;
    const upi    = document.getElementById('revUPI').textContent;
    const note   = document.getElementById('sendNote').value.trim();
    let txnId    = 'PAYSIM' + Date.now().toString(36).toUpperCase();

    fetch('api/payment/send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            receiver_upi: upi,
            amount: amount,
            pin: pinEntered || '123456',
            note: note || 'Payment via PaySim'
        })
    }).then(r => r.json()).then(data => {
        if (data.success && data.data) {
            txnId = data.data.txn_id || txnId;
            document.getElementById('sTxn').textContent = 'TXN ID: ' + txnId;
            if (data.data.new_balance !== undefined) {
                BALANCE = data.data.new_balance;
            }
        }
    }).catch(err => {
        console.warn('API sync notice:', err);
    });

    document.getElementById('sAmt').textContent = '₹' + amount.toLocaleString('en-IN', {minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById('sTo').textContent  = 'Sent to ' + name + ' (' + upi + ')';
    document.getElementById('sTxn').textContent = 'TXN ID: ' + txnId;

    document.getElementById('reviewPanel').classList.remove('active');
    document.getElementById('successPanel').classList.add('active');
    launchConfetti();
}

function sendAnother() {
    document.getElementById('successPanel').classList.remove('active');
    document.getElementById('formPanel').classList.remove('hidden');
    document.getElementById('upiInput').value = '';
    document.getElementById('sendAmount').value = '';
    document.getElementById('sendNote').value = '';
    document.getElementById('upiVerified').classList.remove('show');
    document.getElementById('remainHint').style.display = 'none';
    document.querySelectorAll('.amount-chips .chip').forEach(c => c.classList.remove('active'));
    currentAmount = 0; pinEntered = ''; updatePinDots();
    setStep(1);
}

// ── Confetti ──────────────────────────────────────────────────────────────────
function launchConfetti() {
    const wrap = document.getElementById('confettiWrap');
    wrap.innerHTML = '';
    wrap.classList.add('active');
    const colors = ['#6366f1','#8b5cf6','#10b981','#f59e0b','#06b6d4','#f472b6','#fff'];
    for (let i = 0; i < 60; i++) {
        const el = document.createElement('div');
        el.className = 'confetti-piece';
        el.style.cssText = `
            left:${Math.random()*100}%;
            top:-10px;
            background:${colors[Math.floor(Math.random()*colors.length)]};
            animation-delay:${Math.random()*1.2}s;
            animation-duration:${2 + Math.random()*1.5}s;
            border-radius:${Math.random()>0.5?'50%':'2px'};
            width:${6+Math.random()*6}px;
            height:${6+Math.random()*6}px;
            transform:rotate(${Math.random()*360}deg);
        `;
        wrap.appendChild(el);
    }
    setTimeout(() => { wrap.classList.remove('active'); wrap.innerHTML = ''; }, 4000);
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
</script>
</body>
</html>
