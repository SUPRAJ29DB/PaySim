<?php
session_start();

// ─── Auth Guard ─────────────────────────────────────────────────────────────
if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

// ─── Session Data ───────────────────────────────────────────────────────────
$userName  = $_SESSION['NAME']     ?? 'User';
$username  = $_SESSION['USERNAME'] ?? 'user';
$upiId     = $_SESSION['UPI_ID']   ?? 'user@paysim';
$userId    = $_SESSION['USER_ID']  ?? 0;
$firstName = explode(' ', $userName)[0];

// ─── Simulated Profile Data ─────────────────────────────────────────────────
$profile = [
    'name'         => $userName,
    'username'     => $username,
    'email'        => strtolower($username) . '@gmail.com',
    'phone'        => '+91 98765 43210',
    'upi_id'       => $upiId,
    'joined'       => '15 Jan 2025',
    'kyc_status'   => 'verified',   // verified | pending | rejected
    'account_type' => 'Premium',
    'bank_name'    => 'State Bank of India',
    'bank_acc'     => '●●●● ●●●● 4829',
    'ifsc'         => 'SBIN0001234',
    'total_sent'   => 45200.00,
    'total_recv'   => 62800.00,
    'total_txns'   => 128,
    'avatar_initials' => strtoupper(substr($firstName, 0, 1) . (strpos($userName, ' ') ? substr(explode(' ', $userName)[1], 0, 1) : '')),
];

// ─── Handle Profile Update ──────────────────────────────────────────────────
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $newName  = trim($_POST['full_name'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');

        if (empty($newName) || empty($newEmail)) {
            $error = 'Name and email are required fields.';
        } else {
            // Simulate update
            $_SESSION['NAME'] = $newName;
            $userName = $newName;
            $firstName = explode(' ', $userName)[0];
            $profile['name']  = $newName;
            $profile['email'] = $newEmail;
            $profile['phone'] = $newPhone;
            $profile['avatar_initials'] = strtoupper(substr($firstName, 0, 1) . (strpos($userName, ' ') ? substr(explode(' ', $userName)[1], 0, 1) : ''));
            $success = 'Profile updated successfully!';
        }
    }

    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $newPass  = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($newPass) || empty($confirm)) {
            $error = 'All password fields are required.';
        } elseif ($newPass !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPass) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $success = 'Password changed successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — PaySim</title>
    <meta name="description" content="Manage your PaySim profile, account settings, and linked bank details.">

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
            --error-bg:       rgba(239, 68, 68, 0.12);
            --error-border:   rgba(239, 68, 68, 0.4);
            --error-text:     #fca5a5;
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

        @keyframes orbFloat {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, -30px) scale(1.15); }
        }

        @keyframes cardSlideIn {
            to { opacity: 1; transform: translateY(0); }
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
            top: 0; left: 0; bottom: 0;
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
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px var(--accent-glow);
            flex-shrink: 0;
        }

        .sidebar-brand-icon svg { width: 22px; height: 22px; color: #fff; }

        .sidebar-brand-text h2 {
            font-size: 1.15rem; font-weight: 700;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-hover));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        .sidebar-brand-text span {
            font-size: 0.7rem; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.08em;
        }

        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }

        .nav-section-label {
            font-size: 0.65rem; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.1em;
            padding: 8px 12px 6px; margin-top: 8px;
        }

        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; border-radius: 10px;
            color: var(--text-secondary); text-decoration: none;
            font-size: 0.875rem; font-weight: 500;
            transition: all 0.2s; position: relative; margin-bottom: 2px;
        }

        .nav-item:hover { background: var(--bg-hover); color: var(--text-primary); }

        .nav-item.active {
            background: rgba(99, 102, 241, 0.12);
            color: var(--accent-hover);
        }

        .nav-item.active::before {
            content: ''; position: absolute; left: 0; top: 50%;
            transform: translateY(-50%); width: 3px; height: 20px;
            background: var(--accent); border-radius: 0 3px 3px 0;
        }

        .nav-item svg { width: 20px; height: 20px; flex-shrink: 0; opacity: 0.7; }
        .nav-item.active svg { opacity: 1; }

        .nav-badge {
            margin-left: auto; background: var(--accent); color: #fff;
            font-size: 0.65rem; font-weight: 700; padding: 2px 7px;
            border-radius: 10px; min-width: 20px; text-align: center;
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

        .sidebar-user-info .name {
            font-size: 0.85rem; font-weight: 600; color: var(--text-primary);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .sidebar-user-info .upi { font-size: 0.72rem; color: var(--text-muted); }

        /* ── Main Content ─────────────────────────────────────────────── */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 32px 36px 48px;
            min-height: 100vh;
        }

        /* ── Page Header ──────────────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .page-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .back-btn {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary);
            cursor: pointer; transition: all 0.2s; text-decoration: none;
        }

        .back-btn:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
            border-color: var(--border-focus);
        }

        .back-btn svg { width: 20px; height: 20px; }

        .page-header h1 {
            font-size: 1.5rem; font-weight: 700;
            letter-spacing: -0.02em;
        }

        .page-header p {
            color: var(--text-secondary); font-size: 0.85rem; margin-top: 2px;
        }

        .header-actions { display: flex; align-items: center; gap: 12px; }

        .hamburger { display: none; }

        .btn-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: var(--bg-card); backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); cursor: pointer;
            transition: all 0.2s; text-decoration: none;
        }

        .btn-icon:hover {
            background: var(--bg-hover); color: var(--text-primary);
            border-color: var(--border-focus);
        }

        .btn-icon svg { width: 20px; height: 20px; }

        /* ── Profile Hero Card ────────────────────────────────────────── */
        .profile-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);
            border-radius: var(--radius-xl);
            padding: 40px;
            position: relative;
            overflow: hidden;
            margin-bottom: 28px;
            border: 1px solid rgba(99, 102, 241, 0.25);
            animation: cardSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0; transform: translateY(20px);
        }

        .profile-hero::before {
            content: ''; position: absolute;
            top: -60%; right: -20%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
            border-radius: 50%;
        }

        .profile-hero::after {
            content: ''; position: absolute;
            bottom: -40%; left: -10%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(139,92,246,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .profile-hero-content {
            display: flex;
            align-items: center;
            gap: 28px;
            position: relative;
            z-index: 1;
        }

        .profile-avatar-large {
            width: 88px; height: 88px;
            border-radius: 22px;
            background: linear-gradient(135deg, #6366f1, #a78bfa);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; font-weight: 800; color: #fff;
            flex-shrink: 0;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.4);
            position: relative;
        }

        .profile-avatar-large .kyc-badge {
            position: absolute;
            bottom: -4px; right: -4px;
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 3px solid #312e81;
        }

        .kyc-badge.verified { background: var(--success); }
        .kyc-badge.pending  { background: var(--warning); }
        .kyc-badge.rejected { background: var(--error); }

        .kyc-badge svg { width: 14px; height: 14px; color: #fff; }

        .profile-hero-info {
            flex: 1;
        }

        .profile-hero-info h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
            letter-spacing: -0.02em;
        }

        .profile-hero-info .username {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.5);
            margin-bottom: 12px;
        }

        .profile-hero-tags {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .profile-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .profile-tag svg { width: 14px; height: 14px; }

        .profile-tag--premium {
            background: rgba(245, 158, 11, 0.15);
            border-color: rgba(245, 158, 11, 0.3);
            color: #fbbf24;
        }

        .profile-tag--verified {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .profile-hero-stats {
            display: flex;
            gap: 32px;
            position: relative;
            z-index: 1;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 2px;
        }

        .hero-stat-label {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* ── Content Grid ─────────────────────────────────────────────── */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        /* ── Card Base ────────────────────────────────────────────────── */
        .card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 28px;
            animation: cardSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0; transform: translateY(20px);
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.15s; }
        .card:nth-child(3) { animation-delay: 0.2s; }
        .card:nth-child(4) { animation-delay: 0.25s; }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .card-header h3 {
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h3 svg {
            width: 20px; height: 20px;
            color: var(--accent-hover);
        }

        .card-header .edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: var(--accent-hover);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
        }

        .card-header .edit-btn:hover {
            background: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.4);
        }

        .card-header .edit-btn svg { width: 14px; height: 14px; }

        /* ── Info Rows ────────────────────────────────────────────────── */
        .info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid rgba(99, 102, 241, 0.06);
        }

        .info-row:last-child { border-bottom: none; }

        .info-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .info-label svg {
            width: 18px; height: 18px;
            color: var(--text-muted);
            opacity: 0.6;
        }

        .info-value {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .copy-btn {
            width: 28px; height: 28px;
            border-radius: 6px;
            background: transparent;
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: var(--bg-hover);
            color: var(--accent-hover);
            border-color: var(--border-focus);
        }

        .copy-btn svg { width: 14px; height: 14px; }

        /* ── Form Styles ──────────────────────────────────────────────── */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 14px;
            font-family: inherit;
            font-size: 0.9rem;
            color: var(--text-primary);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
        }

        .form-input::placeholder { color: var(--text-muted); }

        .form-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px var(--accent-glow);
        }

        .btn-primary:active { transform: translateY(0); }

        .btn-primary svg { width: 18px; height: 18px; }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
            border-color: var(--border-focus);
        }

        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        .btn-danger svg { width: 18px; height: 18px; }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        /* ── Bank Card ────────────────────────────────────────────────── */
        .bank-card-mini {
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid rgba(99, 102, 241, 0.2);
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .bank-card-mini::after {
            content: '';
            position: absolute;
            top: -30%; right: -20%;
            width: 150px; height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
            border-radius: 50%;
        }

        .bank-card-mini .bank-name {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .bank-card-mini .bank-acc-num {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .bank-card-mini .bank-ifsc {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.6);
        }

        .bank-card-mini .bank-ifsc strong {
            color: rgba(255,255,255,0.85);
        }

        /* ── Alert Messages ───────────────────────────────────────────── */
        .alert {
            border-radius: var(--radius);
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            animation: cardSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .alert svg {
            width: 20px; height: 20px;
            flex-shrink: 0;
        }

        .alert--success {
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            color: #34d399;
        }

        .alert--success svg { color: var(--success); }

        .alert--error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
        }

        .alert--error svg { color: var(--error); }

        /* ── Quick Action Links ───────────────────────────────────────── */
        .quick-links {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .quick-link:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .quick-link svg {
            width: 20px; height: 20px;
            opacity: 0.6;
        }

        .quick-link .arrow {
            margin-left: auto;
            opacity: 0;
            transition: opacity 0.2s, transform 0.2s;
        }

        .quick-link:hover .arrow {
            opacity: 1;
            transform: translateX(2px);
        }

        .quick-link--danger { color: #f87171; }
        .quick-link--danger:hover { background: rgba(239, 68, 68, 0.06); color: #f87171; }

        /* ── Edit Modal ───────────────────────────────────────────────── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active { display: flex; }

        .modal {
            background: var(--bg-card-solid);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 32px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
            animation: cardSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .modal-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .modal-close {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: transparent;
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .modal-close svg { width: 18px; height: 18px; }

        /* ── Sidebar Overlay ──────────────────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
        }

        /* ── Toast ────────────────────────────────────────────────────── */
        .toast {
            position: fixed;
            bottom: 32px; left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: var(--bg-card-solid);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 20px;
            font-size: 0.82rem;
            color: var(--text-primary);
            display: flex; align-items: center; gap: 8px;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }

        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast svg { width: 18px; height: 18px; color: var(--success); }

        /* ── Responsive ───────────────────────────────────────────────── */
        @media (max-width: 1024px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; padding: 24px 16px 40px; }
            .hamburger { display: flex; }

            .profile-hero { padding: 28px; }
            .profile-hero-content { flex-direction: column; text-align: center; gap: 20px; }
            .profile-hero-tags { justify-content: center; }
            .profile-hero-stats { justify-content: center; }

            .page-header { flex-wrap: wrap; gap: 12px; }
        }

        @media (max-width: 480px) {
            .profile-hero-stats { flex-wrap: wrap; gap: 16px; }
        }
    </style>
</head>
<body>

<!-- Background Orbs -->
<div class="bg-orb bg-orb--1"></div>
<div class="bg-orb bg-orb--2"></div>

<!-- Sidebar Overlay -->
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
                Request Money
            </a>

            <a href="scan-pay.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
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

            <a href="profile.php" class="nav-item active">
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
                <div class="sidebar-avatar"><?php echo $profile['avatar_initials']; ?></div>
                <div class="sidebar-user-info">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="upi"><?php echo htmlspecialchars($upiId); ?></div>
                </div>
            </a>
        </div>
    </aside>

    <!-- ─── Main Content ──────────────────────────────────────────── -->
    <main class="main-content">

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <a href="dashboard.php" class="back-btn" title="Back to Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h1>My Profile</h1>
                    <p>Manage your account & personal information</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-icon hamburger" onclick="toggleSidebar()" aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <a href="settings.php" class="btn-icon" title="Settings">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($success)): ?>
        <div class="alert alert--success">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="alert alert--error">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Profile Hero Card -->
        <div class="profile-hero">
            <div class="profile-hero-content">
                <div class="profile-avatar-large">
                    <?php echo $profile['avatar_initials']; ?>
                    <div class="kyc-badge <?php echo $profile['kyc_status']; ?>" title="KYC: <?php echo ucfirst($profile['kyc_status']); ?>">
                        <?php if ($profile['kyc_status'] === 'verified'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <?php elseif ($profile['kyc_status'] === 'pending'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="profile-hero-info">
                    <h2><?php echo htmlspecialchars($profile['name']); ?></h2>
                    <div class="username">@<?php echo htmlspecialchars($profile['username']); ?></div>
                    <div class="profile-hero-tags">
                        <span class="profile-tag profile-tag--premium">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                            <?php echo $profile['account_type']; ?>
                        </span>
                        <span class="profile-tag profile-tag--verified">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            KYC Verified
                        </span>
                        <span class="profile-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            Joined <?php echo $profile['joined']; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="profile-hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-value">₹<?php echo number_format($profile['total_sent'], 0); ?></div>
                    <div class="hero-stat-label">Total Sent</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value">₹<?php echo number_format($profile['total_recv'], 0); ?></div>
                    <div class="hero-stat-label">Total Received</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value"><?php echo $profile['total_txns']; ?></div>
                    <div class="hero-stat-label">Transactions</div>
                </div>
            </div>
        </div>

        <!-- Profile Grid -->
        <div class="profile-grid">

            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Personal Information
                    </h3>
                    <button class="edit-btn" onclick="openModal('editProfileModal')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Edit
                    </button>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Full Name
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($profile['name']); ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" />
                        </svg>
                        Username
                    </div>
                    <div class="info-value">@<?php echo htmlspecialchars($profile['username']); ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        Email
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($profile['email']); ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                        Phone
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($profile['phone']); ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                        </svg>
                        UPI ID
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($profile['upi_id']); ?>
                        <button class="copy-btn" onclick="copyText('<?php echo addslashes($profile['upi_id']); ?>', 'UPI ID')" title="Copy UPI ID">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Linked Bank Account -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" />
                        </svg>
                        Linked Bank Account
                    </h3>
                    <a href="bank-account.php" class="edit-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        Manage
                    </a>
                </div>

                <div class="bank-card-mini">
                    <div class="bank-name"><?php echo htmlspecialchars($profile['bank_name']); ?></div>
                    <div class="bank-acc-num"><?php echo $profile['bank_acc']; ?></div>
                    <div class="bank-ifsc">IFSC: <strong><?php echo htmlspecialchars($profile['ifsc']); ?></strong></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                        Account Status
                    </div>
                    <div class="info-value" style="color: var(--success);">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;color:var(--success);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Active & Verified
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Linked Since
                    </div>
                    <div class="info-value"><?php echo $profile['joined']; ?></div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        Security
                    </h3>
                </div>

                <form method="POST" action="profile.php">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-input" placeholder="Enter current password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Enter new password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Confirm new password" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Links -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        Quick Links
                    </h3>
                </div>

                <div class="quick-links">
                    <a href="transactions.php" class="quick-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        Transaction History
                        <svg class="arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                    <a href="qr-payment.php" class="quick-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z" />
                        </svg>
                        My QR Code
                        <svg class="arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                    <a href="settings.php" class="quick-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        App Settings
                        <svg class="arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                    <a href="notifications.php" class="quick-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        Notifications
                        <svg class="arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                    <a href="logout.php" class="quick-link quick-link--danger">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        Logout
                        <svg class="arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>

    </main>
</div>

<!-- Edit Profile Modal -->
<div class="modal-overlay" id="editProfileModal" onclick="closeModalOutside(event, 'editProfileModal')">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Profile</h3>
            <button class="modal-close" onclick="closeModal('editProfileModal')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="profile.php">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-group">
                <label for="edit_name">Full Name</label>
                <input type="text" id="edit_name" name="full_name" class="form-input" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="edit_email">Email Address</label>
                <input type="email" id="edit_email" name="email" class="form-input" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="edit_phone">Phone Number</label>
                <input type="tel" id="edit_phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($profile['phone']); ?>">
            </div>

            <div class="form-group">
                <label for="edit_upi">UPI ID</label>
                <input type="text" id="edit_upi" class="form-input" value="<?php echo htmlspecialchars($profile['upi_id']); ?>" disabled>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Save Changes
                </button>
                <button type="button" class="btn-outline" onclick="closeModal('editProfileModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    <span id="toastText">Copied!</span>
</div>

<script>
    // ─── Sidebar Toggle ─────────────────────────────────────────────
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }

    // ─── Modal ──────────────────────────────────────────────────────
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
    }

    function closeModalOutside(event, id) {
        if (event.target === document.getElementById(id)) {
            closeModal(id);
        }
    }

    // ─── Copy to Clipboard ──────────────────────────────────────────
    function copyText(text, label) {
        navigator.clipboard.writeText(text).then(() => {
            showToast(label + ' copied to clipboard!');
        }).catch(() => {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast(label + ' copied to clipboard!');
        });
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        document.getElementById('toastText').textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }
</script>

</body>
</html>
