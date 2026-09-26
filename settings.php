<?php
session_start();

if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

$userName  = $_SESSION['NAME']     ?? 'User';
$upiId     = $_SESSION['UPI_ID']   ?? 'user@paysim';
$userId    = $_SESSION['USER_ID']  ?? 0;

$firstName = explode(' ', $userName)[0];
$initials  = strtoupper(substr($firstName, 0, 1) . (strpos($userName, ' ') ? substr(explode(' ', $userName)[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — PaySim</title>
    <meta name="description" content="Manage your PaySim account settings, security preferences, notifications, and privacy controls.">

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
            --sidebar-width:  280px;
        }

        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; overflow-x: hidden; }

        .bg-orb { position:fixed; border-radius:50%; filter:blur(120px); opacity:0.3; z-index:0; pointer-events:none; }
        .bg-orb--1 { width:600px;height:600px;background:radial-gradient(circle,var(--accent) 0%,transparent 70%);top:-200px;right:-150px;animation:orbFloat 10s ease-in-out infinite alternate; }
        .bg-orb--2 { width:450px;height:450px;background:radial-gradient(circle,#8b5cf6 0%,transparent 70%);bottom:-150px;left:-100px;animation:orbFloat 12s ease-in-out infinite alternate-reverse; }
        .bg-orb--3 { width:300px;height:300px;background:radial-gradient(circle,#06b6d4 0%,transparent 70%);top:50%;left:40%;animation:orbFloat 14s ease-in-out infinite alternate; }
        @keyframes orbFloat { 0%{transform:translate(0,0)scale(1)} 100%{transform:translate(40px,-30px)scale(1.15)} }

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
        .sidebar-user-info .name { font-size:0.85rem;font-weight:600;color:var(--text-primary); }
        .sidebar-user-info .upi  { font-size:0.72rem;color:var(--text-muted); }

        /* ── Main ── */
        .main-content { flex:1;margin-left:var(--sidebar-width);padding:32px 36px 60px;min-height:100vh; }

        .top-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:32px; }
        .page-title-area h1 { font-size:1.65rem;font-weight:700;letter-spacing:-0.02em;margin-bottom:4px; }
        .page-title-area p { color:var(--text-secondary);font-size:0.9rem; }
        .header-actions { display:flex;align-items:center;gap:12px; }
        .btn-icon { width:42px;height:42px;border-radius:12px;background:var(--bg-card);backdrop-filter:blur(16px);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-secondary);cursor:pointer;transition:all 0.2s;text-decoration:none;position:relative; }
        .btn-icon:hover { background:var(--bg-hover);color:var(--text-primary);border-color:var(--border-focus); }
        .btn-icon svg { width:20px;height:20px; }
        .btn-icon .notif-dot { position:absolute;top:8px;right:8px;width:8px;height:8px;background:var(--error);border-radius:50%;border:2px solid var(--bg-primary); }
        .hamburger { display:none; }

        /* ── Settings Layout ── */
        .settings-grid { display:grid;grid-template-columns:220px 1fr;gap:24px;align-items:start; }

        /* ── Settings Nav ── */
        .settings-nav-card {
            background:var(--bg-card); backdrop-filter:blur(16px);
            border:1px solid var(--border); border-radius:var(--radius-lg);
            overflow:hidden; position:sticky; top:24px;
            animation:fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity:0; transform:translateY(20px);
        }
        @keyframes fadeUp { to{opacity:1;transform:translateY(0)} }
        .settings-nav-item {
            display:flex; align-items:center; gap:12px;
            padding:12px 18px; color:var(--text-secondary);
            cursor:pointer; font-size:0.85rem; font-weight:500;
            transition:all 0.18s; border-bottom:1px solid rgba(99,102,241,0.06);
            border:none; width:100%; text-align:left; background:none; font-family:inherit;
        }
        .settings-nav-item:last-child { border-bottom:none; }
        .settings-nav-item:hover { background:var(--bg-hover); color:var(--text-primary); }
        .settings-nav-item.active { background:rgba(99,102,241,0.1); color:var(--accent-hover); }
        .settings-nav-item .s-icon { width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
        .settings-nav-item .s-icon svg { width:16px;height:16px; }
        .settings-nav-item.active .s-icon { background:rgba(99,102,241,0.15); }
        .settings-nav-item:not(.active) .s-icon { background:rgba(255,255,255,0.04); }

        /* ── Panels ── */
        .settings-panels { display:flex;flex-direction:column;gap:20px; }
        .panel {
            background:var(--bg-card); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
            border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden;
            animation:fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity:0; transform:translateY(20px); display:none;
        }
        .panel.active { display:block; }
        .panel-header {
            padding:20px 24px;
            border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between;
        }
        .panel-header-left { display:flex; align-items:center; gap:14px; }
        .panel-header-icon { width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center; }
        .panel-header-icon svg { width:20px;height:20px; }
        .ph-indigo { background:rgba(99,102,241,0.12);color:var(--accent-hover); }
        .ph-green  { background:var(--success-bg);color:var(--success); }
        .ph-amber  { background:var(--warning-bg);color:var(--warning); }
        .ph-red    { background:var(--error-bg);color:var(--error); }
        .ph-cyan   { background:rgba(6,182,212,0.12);color:#06b6d4; }
        .panel-header h2 { font-size:1rem;font-weight:700;letter-spacing:-0.01em; }
        .panel-header p  { font-size:0.75rem;color:var(--text-muted);margin-top:2px; }

        /* ── Setting Rows ── */
        .setting-section { padding:20px 24px; border-bottom:1px solid rgba(99,102,241,0.07); }
        .setting-section:last-child { border-bottom:none; }
        .section-label { font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:14px; }

        .setting-row {
            display:flex; align-items:center; justify-content:space-between;
            padding:12px 0; border-bottom:1px solid rgba(255,255,255,0.04);
        }
        .setting-row:last-child { border-bottom:none; padding-bottom:0; }
        .setting-info { flex:1;min-width:0;padding-right:16px; }
        .setting-info .setting-name { font-size:0.88rem;font-weight:600;margin-bottom:3px; }
        .setting-info .setting-desc { font-size:0.75rem;color:var(--text-muted);line-height:1.4; }

        /* ── Toggle switch ── */
        .toggle { position:relative;width:44px;height:24px;flex-shrink:0; }
        .toggle input { opacity:0;width:0;height:0;position:absolute; }
        .toggle-track {
            position:absolute;inset:0;border-radius:12px;
            background:rgba(255,255,255,0.1);border:1px solid var(--border);
            cursor:pointer;transition:all 0.3s;
        }
        .toggle input:checked + .toggle-track { background:var(--accent);border-color:var(--accent);box-shadow:0 0 12px var(--accent-glow); }
        .toggle-thumb {
            position:absolute;top:3px;left:3px;
            width:16px;height:16px;border-radius:50%;
            background:#fff;transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);
            box-shadow:0 2px 4px rgba(0,0,0,0.3);
        }
        .toggle input:checked ~ .toggle-thumb { transform:translateX(20px); }

        /* ── Form controls ── */
        .form-row { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px; }
        .form-row.full { grid-template-columns:1fr; }
        .form-group { display:flex;flex-direction:column;gap:6px; }
        .form-label { font-size:0.75rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.06em; }
        .form-input { background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:var(--radius);padding:11px 14px;font-family:inherit;font-size:0.88rem;color:var(--text-primary);transition:border-color 0.2s,box-shadow 0.2s;outline:none; }
        .form-input::placeholder { color:var(--text-muted); }
        .form-input:focus { border-color:var(--border-focus);box-shadow:0 0 0 3px rgba(99,102,241,0.12); }
        .form-input:disabled { opacity:0.5;cursor:not-allowed; }
        select.form-input { cursor:pointer; }

        /* ── Buttons ── */
        .btn-save { padding:10px 22px;background:linear-gradient(135deg,var(--accent),#8b5cf6);border:none;border-radius:var(--radius);color:#fff;font-family:inherit;font-size:0.85rem;font-weight:600;cursor:pointer;transition:transform 0.2s,box-shadow 0.2s;display:inline-flex;align-items:center;gap:7px; }
        .btn-save:hover { transform:translateY(-1px);box-shadow:0 6px 20px var(--accent-glow); }
        .btn-save svg { width:15px;height:15px; }
        .btn-outline-sm { padding:9px 18px;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:var(--radius);color:var(--text-secondary);font-family:inherit;font-size:0.82rem;font-weight:500;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:7px; }
        .btn-outline-sm:hover { background:var(--bg-hover);color:var(--text-primary);border-color:var(--border-focus); }
        .btn-outline-sm svg { width:14px;height:14px; }
        .btn-danger { padding:10px 22px;background:var(--error-bg);border:1px solid rgba(239,68,68,0.25);border-radius:var(--radius);color:var(--error);font-family:inherit;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:7px; }
        .btn-danger:hover { background:rgba(239,68,68,0.2);box-shadow:0 4px 16px rgba(239,68,68,0.2); }
        .btn-danger svg { width:15px;height:15px; }
        .panel-actions { padding:16px 24px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px; }

        /* ── Avatar editor ── */
        .avatar-editor { display:flex;align-items:center;gap:20px;margin-bottom:20px; }
        .avatar-big { width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#fff;flex-shrink:0;position:relative;border:2px solid rgba(99,102,241,0.3); }
        .avatar-change-btn { padding:8px 16px;background:rgba(255,255,255,0.05);border:1px solid var(--border);border-radius:var(--radius);color:var(--text-secondary);font-family:inherit;font-size:0.8rem;font-weight:500;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:6px; }
        .avatar-change-btn:hover { background:var(--bg-hover);color:var(--text-primary);border-color:var(--border-focus); }
        .avatar-change-btn svg { width:14px;height:14px; }
        .avatar-hint { font-size:0.72rem;color:var(--text-muted);margin-top:4px; }

        /* ── Linked UPI chips ── */
        .upi-list { display:flex;flex-direction:column;gap:10px;margin-bottom:16px; }
        .upi-chip {
            display:flex;align-items:center;justify-content:space-between;
            padding:12px 16px;background:rgba(255,255,255,0.03);
            border:1px solid var(--border);border-radius:var(--radius);
        }
        .upi-chip-left { display:flex;align-items:center;gap:12px; }
        .upi-chip-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
        .upi-chip-dot--active { background:var(--success);box-shadow:0 0 6px rgba(16,185,129,0.5); }
        .upi-chip-dot--idle   { background:var(--text-muted); }
        .upi-chip-id   { font-size:0.85rem;font-weight:600; }
        .upi-chip-bank { font-size:0.72rem;color:var(--text-muted); }
        .upi-chip-badge { font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;padding:3px 8px;border-radius:6px; }
        .badge-primary { background:rgba(99,102,241,0.15);color:var(--accent-hover); }
        .badge-idle    { background:rgba(255,255,255,0.06);color:var(--text-muted); }

        /* ── Security items ── */
        .sec-item { display:flex;align-items:center;gap:14px;padding:14px 0;border-bottom:1px solid rgba(255,255,255,0.04); }
        .sec-item:last-child { border-bottom:none; }
        .sec-icon { width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
        .sec-icon svg { width:18px;height:18px; }
        .sec-info { flex:1; }
        .sec-info .sec-name { font-size:0.88rem;font-weight:600;margin-bottom:2px; }
        .sec-info .sec-desc { font-size:0.74rem;color:var(--text-muted); }
        .sec-status { font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px; }
        .status-on  { background:var(--success-bg);color:var(--success);border:1px solid var(--success-border); }
        .status-off { background:rgba(255,255,255,0.05);color:var(--text-muted);border:1px solid var(--border); }
        .status-warn { background:var(--warning-bg);color:var(--warning);border:1px solid rgba(245,158,11,0.25); }

        /* ── Limit slider ── */
        .limit-row { margin-bottom:16px; }
        .limit-header { display:flex;justify-content:space-between;align-items:baseline;margin-bottom:8px; }
        .limit-label  { font-size:0.82rem;font-weight:600; }
        .limit-value  { font-size:0.82rem;font-weight:700;color:var(--accent-hover); }
        input[type="range"] {
            width:100%;height:5px;-webkit-appearance:none;appearance:none;
            background:rgba(255,255,255,0.08);border-radius:10px;outline:none;cursor:pointer;
        }
        input[type="range"]::-webkit-slider-thumb { -webkit-appearance:none;width:18px;height:18px;border-radius:50%;background:var(--accent);cursor:pointer;box-shadow:0 0 8px var(--accent-glow); }
        input[type="range"]::-moz-range-thumb { width:18px;height:18px;border-radius:50%;background:var(--accent);cursor:pointer;border:none;box-shadow:0 0 8px var(--accent-glow); }

        /* ── Theme options ── */
        .theme-options { display:flex;gap:12px;flex-wrap:wrap; }
        .theme-opt { padding:8px 16px;border-radius:10px;border:1px solid var(--border);background:rgba(255,255,255,0.04);color:var(--text-secondary);font-family:inherit;font-size:0.82rem;font-weight:500;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:8px; }
        .theme-opt:hover { border-color:var(--border-focus);color:var(--text-primary); }
        .theme-opt.active { border-color:var(--accent);color:var(--accent-hover);background:rgba(99,102,241,0.1); }
        .theme-swatch { width:14px;height:14px;border-radius:50%;flex-shrink:0; }

        /* ── Danger zone ── */
        .danger-zone { padding:20px 24px; }
        .danger-title { font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--error);margin-bottom:14px;display:flex;align-items:center;gap:8px; }
        .danger-title svg { width:13px;height:13px; }
        .danger-row { display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid rgba(255,255,255,0.04); }
        .danger-row:last-child { border-bottom:none; }
        .danger-info .d-name { font-size:0.88rem;font-weight:600;margin-bottom:2px; }
        .danger-info .d-desc { font-size:0.74rem;color:var(--text-muted); }

        /* ── Session list ── */
        .session-item { display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.04); }
        .session-item:last-child { border-bottom:none; }
        .session-icon { width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
        .session-icon svg { width:18px;height:18px; }
        .si-desktop { background:rgba(99,102,241,0.1);color:var(--accent-hover); }
        .si-mobile  { background:rgba(16,185,129,0.1);color:var(--success); }
        .session-info { flex:1;min-width:0; }
        .session-name  { font-size:0.85rem;font-weight:600;margin-bottom:2px; }
        .session-meta  { font-size:0.72rem;color:var(--text-muted); }
        .session-this  { font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;padding:2px 8px;border-radius:6px;background:var(--success-bg);color:var(--success); }

        /* ── Modal ── */
        .modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:500;display:flex;align-items:center;justify-content:center;padding:24px;opacity:0;pointer-events:none;transition:opacity 0.25s; }
        .modal-overlay.active { opacity:1;pointer-events:all; }
        .modal { background:#111827;border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;max-width:440px;width:100%;transform:scale(0.9);transition:transform 0.3s cubic-bezier(0.16,1,0.3,1); }
        .modal-overlay.active .modal { transform:scale(1); }
        .modal h3 { font-size:1rem;font-weight:700;margin-bottom:8px; }
        .modal p  { font-size:0.82rem;color:var(--text-secondary);margin-bottom:20px;line-height:1.5; }
        .modal-actions { display:flex;gap:10px;justify-content:flex-end; }
        .modal .form-input { margin-bottom:16px;width:100%; }

        /* ── Misc ── */
        .sidebar-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99; }
        .toast { position:fixed;bottom:32px;left:50%;transform:translateX(-50%) translateY(80px);background:var(--bg-card-solid);border:1px solid var(--border);border-radius:var(--radius);padding:12px 20px;font-size:0.82rem;color:var(--text-primary);display:flex;align-items:center;gap:8px;z-index:1000;transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);box-shadow:0 12px 40px rgba(0,0,0,0.4); }
        .toast.show { transform:translateX(-50%) translateY(0); }
        .toast-icon { width:18px;height:18px; }

        @media (max-width:1100px) { .settings-grid{grid-template-columns:1fr} .settings-nav-card{position:static;display:flex;flex-wrap:wrap;gap:4px;background:var(--bg-card);padding:12px} .settings-nav-item{width:auto;padding:8px 14px;border-radius:10px;border-bottom:none} }
        @media (max-width:768px) { .sidebar{transform:translateX(-100%)} .sidebar.open{transform:translateX(0)} .sidebar-overlay.show{display:block} .main-content{margin-left:0;padding:24px 16px 48px} .hamburger{display:flex} .form-row{grid-template-columns:1fr} }
        @keyframes spin { to{transform:rotate(360deg)} }
        @keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-6px)} 40%{transform:translateX(6px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)} }
    </style>
</head>
<body>

<div class="bg-orb bg-orb--1"></div>
<div class="bg-orb bg-orb--2"></div>
<div class="bg-orb bg-orb--3"></div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modalBox">
        <h3 id="modalTitle">Are you sure?</h3>
        <p id="modalDesc">This action cannot be undone.</p>
        <div id="modalInputWrap" style="display:none;">
            <input type="text" class="form-input" id="modalInput" placeholder="Type to confirm…">
        </div>
        <div class="modal-actions">
            <button class="btn-outline-sm" onclick="closeModal()">Cancel</button>
            <button class="btn-danger" id="modalConfirmBtn" onclick="modalConfirm()">Confirm</button>
        </div>
    </div>
</div>

<div class="app-layout">

    <!-- ─── Sidebar ───────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
            </div>
            <div class="sidebar-brand-text"><h2>PaySim</h2><span>UPI Simulator</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu</div>
            <a href="dashboard.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>Dashboard</a>
            <a href="send-money.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>Send Money</a>
            <a href="request-money.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3"/></svg>Request Money</a>
            <a href="scan-pay.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/></svg>Scan &amp; Pay</a>
            <div class="nav-section-label">Manage</div>
            <a href="transactions.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>Transactions</a>
            <a href="add-money.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Add Money</a>
            <a href="bank-account.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21"/></svg>Bank Account</a>
            <a href="notifications.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>Notifications<span class="nav-badge">3</span></a>
            <div class="nav-section-label">Account</div>
            <a href="profile.php" class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>Profile</a>
            <a href="settings.php" class="nav-item active"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>Settings</a>
            <a href="logout.php" class="nav-item" style="color:#f87171;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>Logout</a>
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
                <h1>&#x2699;&#xFE0F; Settings</h1>
                <p>Manage your account preferences and security</p>
            </div>
            <div class="header-actions">
                <button class="btn-icon hamburger" onclick="toggleSidebar()" aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <a href="notifications.php" class="btn-icon" aria-label="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                    <span class="notif-dot"></span>
                </a>
            </div>
        </div>

        <div class="settings-grid">

            <!-- ── Settings Nav ─────────────────────────────────── -->
            <div class="settings-nav-card">
                <button class="settings-nav-item active" onclick="switchPanel('profile',this)" id="nav-profile">
                    <div class="s-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></div>
                    Profile
                </button>
                <button class="settings-nav-item" onclick="switchPanel('upi',this)" id="nav-upi">
                    <div class="s-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg></div>
                    UPI &amp; Payments
                </button>
                <button class="settings-nav-item" onclick="switchPanel('security',this)" id="nav-security">
                    <div class="s-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg></div>
                    Security
                </button>
                <button class="settings-nav-item" onclick="switchPanel('notifications',this)" id="nav-notifications">
                    <div class="s-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg></div>
                    Notifications
                </button>
                <button class="settings-nav-item" onclick="switchPanel('appearance',this)" id="nav-appearance">
                    <div class="s-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z"/></svg></div>
                    Appearance
                </button>
                <button class="settings-nav-item" onclick="switchPanel('privacy',this)" id="nav-privacy">
                    <div class="s-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></div>
                    Privacy
                </button>
                <button class="settings-nav-item" onclick="switchPanel('sessions',this)" id="nav-sessions">
                    <div class="s-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg></div>
                    Sessions
                </button>
                <button class="settings-nav-item" onclick="switchPanel('danger',this)" id="nav-danger" style="color:var(--error);">
                    <div class="s-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg></div>
                    Danger Zone
                </button>
            </div>

            <!-- ── Panels ─────────────────────────────────────── -->
            <div class="settings-panels">

                <!-- ═══ Profile ════════════════════════════════════ -->
                <div class="panel active" id="panel-profile" style="animation-delay:0.05s;">
                    <div class="panel-header">
                        <div class="panel-header-left">
                            <div class="panel-header-icon ph-indigo"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></div>
                            <div><h2>Profile Information</h2><p>Update your name, email and personal details</p></div>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="avatar-editor">
                            <div class="avatar-big"><?php echo $initials; ?></div>
                            <div>
                                <button class="avatar-change-btn" onclick="showToast('Photo upload coming soon!')">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                    Change Photo
                                </button>
                                <div class="avatar-hint">JPG, PNG or GIF &bull; Max 5 MB</div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="firstName">First Name</label>
                                <input type="text" id="firstName" class="form-input" value="<?php echo htmlspecialchars(explode(' ',$userName)[0]); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="lastName">Last Name</label>
                                <input type="text" id="lastName" class="form-input" value="<?php echo htmlspecialchars(strpos($userName,' ') ? implode(' ',array_slice(explode(' ',$userName),1)) : ''); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="emailInput">Email Address</label>
                                <input type="email" id="emailInput" class="form-input" value="<?php echo strtolower(str_replace(' ', '.', $userName)); ?>@email.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="mobileInput">Mobile Number</label>
                                <input type="tel" id="mobileInput" class="form-input" value="+91 98765 43210">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="dobInput">Date of Birth</label>
                                <input type="date" id="dobInput" class="form-input" value="1995-06-15">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="genderInput">Gender</label>
                                <select id="genderInput" class="form-input">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other / Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row full">
                            <div class="form-group">
                                <label class="form-label" for="addressInput">Address</label>
                                <input type="text" id="addressInput" class="form-input" placeholder="123 Street, City, State, PIN">
                            </div>
                        </div>
                    </div>
                    <div class="panel-actions">
                        <div style="font-size:0.75rem;color:var(--text-muted);">Last updated: 12 Sep 2026</div>
                        <button class="btn-save" onclick="saveSection('profile')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Save Changes
                        </button>
                    </div>
                </div>

                <!-- ═══ UPI & Payments ══════════════════════════════ -->
                <div class="panel" id="panel-upi" style="animation-delay:0.05s;">
                    <div class="panel-header">
                        <div class="panel-header-left">
                            <div class="panel-header-icon ph-indigo"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg></div>
                            <div><h2>UPI &amp; Payment Settings</h2><p>Manage UPI IDs, limits and default accounts</p></div>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Linked UPI IDs</div>
                        <div class="upi-list">
                            <div class="upi-chip">
                                <div class="upi-chip-left">
                                    <div class="upi-chip-dot upi-chip-dot--active"></div>
                                    <div>
                                        <div class="upi-chip-id"><?php echo htmlspecialchars($upiId); ?></div>
                                        <div class="upi-chip-bank">SBI &bull; ****4321</div>
                                    </div>
                                </div>
                                <span class="upi-chip-badge badge-primary">Primary</span>
                            </div>
                            <div class="upi-chip">
                                <div class="upi-chip-left">
                                    <div class="upi-chip-dot upi-chip-dot--idle"></div>
                                    <div>
                                        <div class="upi-chip-id"><?php echo htmlspecialchars(strtolower(explode(' ',$userName)[0])); ?>@paytm</div>
                                        <div class="upi-chip-bank">Paytm Bank &bull; ****8790</div>
                                    </div>
                                </div>
                                <span class="upi-chip-badge badge-idle">Secondary</span>
                            </div>
                        </div>
                        <button class="btn-outline-sm" onclick="showToast('Link new UPI coming soon!')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Add UPI ID
                        </button>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Transaction Limits</div>
                        <div class="limit-row">
                            <div class="limit-header">
                                <span class="limit-label">Per-transaction limit</span>
                                <span class="limit-value" id="limitPerTxnVal">₹50,000</span>
                            </div>
                            <input type="range" min="1000" max="100000" step="1000" value="50000" id="limitPerTxn" oninput="updateLimitLabel('limitPerTxn','limitPerTxnVal')">
                        </div>
                        <div class="limit-row">
                            <div class="limit-header">
                                <span class="limit-label">Daily limit</span>
                                <span class="limit-value" id="limitDailyVal">₹1,00,000</span>
                            </div>
                            <input type="range" min="5000" max="200000" step="5000" value="100000" id="limitDaily" oninput="updateLimitLabel('limitDaily','limitDailyVal')">
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Preferences</div>
                        <div class="setting-row">
                            <div class="setting-info"><div class="setting-name">Confirm before sending</div><div class="setting-desc">Show a review screen before every payment</div></div>
                            <label class="toggle"><input type="checkbox" checked id="togConfirm"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info"><div class="setting-name">Auto-fill UPI from contacts</div><div class="setting-desc">Suggest UPI IDs from your phonebook</div></div>
                            <label class="toggle"><input type="checkbox" checked id="togAutoFill"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info"><div class="setting-name">Remember recent payees</div><div class="setting-desc">Show recently paid contacts on the send screen</div></div>
                            <label class="toggle"><input type="checkbox" checked id="togRecent"><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                        </div>
                    </div>
                    <div class="panel-actions">
                        <div></div>
                        <button class="btn-save" onclick="saveSection('upi')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Save
                        </button>
                    </div>
                </div>

                <!-- ═══ Security ════════════════════════════════════ -->
                <div class="panel" id="panel-security" style="animation-delay:0.05s;">
                    <div class="panel-header">
                        <div class="panel-header-left">
                            <div class="panel-header-icon ph-green"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg></div>
                            <div><h2>Security</h2><p>Protect your account with strong authentication</p></div>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Authentication</div>
                        <div class="sec-item">
                            <div class="sec-icon" style="background:var(--success-bg);color:var(--success);"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></div>
                            <div class="sec-info"><div class="sec-name">UPI PIN</div><div class="sec-desc">6-digit PIN for authorising transactions</div></div>
                            <button class="btn-outline-sm" onclick="openModal('Change UPI PIN','Enter your current PIN then set a new one.','Current PIN','pin')">Change PIN</button>
                        </div>
                        <div class="sec-item">
                            <div class="sec-icon" style="background:rgba(99,102,241,0.1);color:var(--accent-hover);"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0 1 19.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.459 7.459 0 0 0 4.5 10.5a7.464 7.464 0 0 1-1.15 3.993m1.989 3.559A11.209 11.209 0 0 0 8.25 10.5a3.75 3.75 0 1 1 7.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 0 1-3.6 9.75m6.633-4.596a18.666 18.666 0 0 1-2.485 5.33"/></svg></div>
                            <div class="sec-info"><div class="sec-name">Biometric Auth</div><div class="sec-desc">Use fingerprint or face ID to authorise</div></div>
                            <span class="sec-status status-on">Active</span>
                        </div>
                        <div class="sec-item">
                            <div class="sec-icon" style="background:var(--warning-bg);color:var(--warning);"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 4.5h1.5"/></svg></div>
                            <div class="sec-info"><div class="sec-name">Two-Factor via SMS</div><div class="sec-desc">OTP sent to +91 987** **210 for logins</div></div>
                            <span class="sec-status status-on">Active</span>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Login Password</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="curPwd">Current Password</label>
                                <input type="password" id="curPwd" class="form-input" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="newPwd">New Password</label>
                                <input type="password" id="newPwd" class="form-input" placeholder="At least 8 characters">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="confPwd">Confirm New Password</label>
                                <input type="password" id="confPwd" class="form-input" placeholder="Repeat new password">
                            </div>
                        </div>
                    </div>
                    <div class="panel-actions">
                        <div style="font-size:0.75rem;color:var(--text-muted);">Last password change: 45 days ago</div>
                        <button class="btn-save" onclick="changePassword()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Update Password
                        </button>
                    </div>
                </div>

                <!-- ═══ Notifications ═══════════════════════════════ -->
                <div class="panel" id="panel-notifications" style="animation-delay:0.05s;">
                    <div class="panel-header">
                        <div class="panel-header-left">
                            <div class="panel-header-icon ph-amber"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg></div>
                            <div><h2>Notification Preferences</h2><p>Choose what alerts you want to receive</p></div>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Transactions</div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Payment sent</div><div class="setting-desc">Alert when money leaves your account</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Payment received</div><div class="setting-desc">Alert when money is credited</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Failed transactions</div><div class="setting-desc">Alert on declined or failed payments</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Money requests</div><div class="setting-desc">Alert when someone requests money from you</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Security</div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Login alerts</div><div class="setting-desc">Notify on new device logins</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">PIN change alerts</div><div class="setting-desc">Notify when UPI PIN is updated</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Suspicious activity</div><div class="setting-desc">Alert on unusual account behaviour</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Promotions &amp; Updates</div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Cashback &amp; offers</div><div class="setting-desc">Deals and reward notifications</div></div><label class="toggle"><input type="checkbox"><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Product updates</div><div class="setting-desc">New features and improvements</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Weekly summary</div><div class="setting-desc">Spending digest every Sunday</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                    </div>
                    <div class="panel-actions">
                        <div></div>
                        <button class="btn-save" onclick="saveSection('notifications')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Save
                        </button>
                    </div>
                </div>

                <!-- ═══ Appearance ══════════════════════════════════ -->
                <div class="panel" id="panel-appearance" style="animation-delay:0.05s;">
                    <div class="panel-header">
                        <div class="panel-header-left">
                            <div class="panel-header-icon ph-cyan"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z"/></svg></div>
                            <div><h2>Appearance</h2><p>Customise the look and feel of PaySim</p></div>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Theme</div>
                        <div class="theme-options">
                            <button class="theme-opt active" onclick="setTheme(this,'dark')"><div class="theme-swatch" style="background:#0a0e1a;border:1px solid #444;"></div>Dark (Default)</button>
                            <button class="theme-opt" onclick="setTheme(this,'light')"><div class="theme-swatch" style="background:#f8fafc;border:1px solid #ddd;"></div>Light</button>
                            <button class="theme-opt" onclick="setTheme(this,'amoled')"><div class="theme-swatch" style="background:#000;border:1px solid #333;"></div>AMOLED Black</button>
                            <button class="theme-opt" onclick="setTheme(this,'ocean')"><div class="theme-swatch" style="background:#0c1a2e;border:1px solid #1e3a5f;"></div>Ocean</button>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Accent Colour</div>
                        <div class="theme-options">
                            <button class="theme-opt active" onclick="setAccent(this)"><div class="theme-swatch" style="background:#6366f1;"></div>Indigo</button>
                            <button class="theme-opt" onclick="setAccent(this)"><div class="theme-swatch" style="background:#8b5cf6;"></div>Violet</button>
                            <button class="theme-opt" onclick="setAccent(this)"><div class="theme-swatch" style="background:#06b6d4;"></div>Cyan</button>
                            <button class="theme-opt" onclick="setAccent(this)"><div class="theme-swatch" style="background:#10b981;"></div>Emerald</button>
                            <button class="theme-opt" onclick="setAccent(this)"><div class="theme-swatch" style="background:#f59e0b;"></div>Amber</button>
                            <button class="theme-opt" onclick="setAccent(this)"><div class="theme-swatch" style="background:#f472b6;"></div>Pink</button>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Display</div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Animated background orbs</div><div class="setting-desc">Decorative colour orb animations behind content</div></div><label class="toggle"><input type="checkbox" checked id="togOrbs"><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Compact mode</div><div class="setting-desc">Reduce spacing for more content on screen</div></div><label class="toggle"><input type="checkbox" id="togCompact"><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Hide balance by default</div><div class="setting-desc">Mask wallet balance on the dashboard</div></div><label class="toggle"><input type="checkbox" id="togHideBal"><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Language &amp; Region</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="langSel">Language</label>
                                <select id="langSel" class="form-input">
                                    <option value="en">English</option>
                                    <option value="hi">हिन्दी</option>
                                    <option value="bn">বাংলা</option>
                                    <option value="ta">தமிழ்</option>
                                    <option value="te">తెలుగు</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="currSel">Currency Display</label>
                                <select id="currSel" class="form-input">
                                    <option value="inr">₹ Indian Rupee</option>
                                    <option value="usd">$ US Dollar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="panel-actions">
                        <div></div>
                        <button class="btn-save" onclick="saveSection('appearance')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Save
                        </button>
                    </div>
                </div>

                <!-- ═══ Privacy ══════════════════════════════════════ -->
                <div class="panel" id="panel-privacy" style="animation-delay:0.05s;">
                    <div class="panel-header">
                        <div class="panel-header-left">
                            <div class="panel-header-icon ph-indigo"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></div>
                            <div><h2>Privacy</h2><p>Control who can see your data and activity</p></div>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Visibility</div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Show in UPI search</div><div class="setting-desc">Allow others to find you by UPI ID or name</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Show profile photo to payers</div><div class="setting-desc">People sending you money can see your avatar</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Share transaction history</div><div class="setting-desc">Allow linked banks to analyse spending</div></div><label class="toggle"><input type="checkbox"><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                    </div>
                    <div class="setting-section">
                        <div class="section-label">Data &amp; Analytics</div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Analytics &amp; improvements</div><div class="setting-desc">Send anonymous usage data to help us improve</div></div><label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Personalised offers</div><div class="setting-desc">Use spending habits to show relevant deals</div></div><label class="toggle"><input type="checkbox"><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                        <div class="setting-row"><div class="setting-info"><div class="setting-name">Location for nearby stores</div><div class="setting-desc">Use GPS to suggest nearby merchants</div></div><label class="toggle"><input type="checkbox"><div class="toggle-track"></div><div class="toggle-thumb"></div></label></div>
                    </div>
                    <div class="panel-actions">
                        <button class="btn-outline-sm" onclick="showToast('Downloading your data…')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download My Data
                        </button>
                        <button class="btn-save" onclick="saveSection('privacy')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Save
                        </button>
                    </div>
                </div>

                <!-- ═══ Sessions ════════════════════════════════════ -->
                <div class="panel" id="panel-sessions" style="animation-delay:0.05s;">
                    <div class="panel-header">
                        <div class="panel-header-left">
                            <div class="panel-header-icon ph-cyan"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg></div>
                            <div><h2>Active Sessions</h2><p>Devices currently logged into your account</p></div>
                        </div>
                    </div>
                    <div class="setting-section">
                        <div class="session-item">
                            <div class="session-icon si-desktop"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg></div>
                            <div class="session-info">
                                <div class="session-name">Windows PC — Chrome 128</div>
                                <div class="session-meta">Kolkata, West Bengal &bull; Just now</div>
                            </div>
                            <span class="session-this">This device</span>
                        </div>
                        <div class="session-item">
                            <div class="session-icon si-mobile"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 4.5h1.5"/></svg></div>
                            <div class="session-info">
                                <div class="session-name">Android — Samsung Galaxy S23</div>
                                <div class="session-meta">Kolkata, West Bengal &bull; 2 hours ago</div>
                            </div>
                            <button class="btn-outline-sm" onclick="revokeSession(this)">Revoke</button>
                        </div>
                        <div class="session-item">
                            <div class="session-icon si-desktop"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg></div>
                            <div class="session-info">
                                <div class="session-name">MacBook — Safari 17</div>
                                <div class="session-meta">Mumbai, Maharashtra &bull; 3 days ago</div>
                            </div>
                            <button class="btn-outline-sm" onclick="revokeSession(this)">Revoke</button>
                        </div>
                    </div>
                    <div class="panel-actions">
                        <div style="font-size:0.75rem;color:var(--text-muted);">3 active sessions</div>
                        <button class="btn-danger" onclick="openModal('Revoke All Sessions','This will log you out of all devices except the current one. You will need to log back in on each device.','','action')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                            Revoke All Other Sessions
                        </button>
                    </div>
                </div>

                <!-- ═══ Danger Zone ════════════════════════════════ -->
                <div class="panel" id="panel-danger" style="animation-delay:0.05s;border-color:rgba(239,68,68,0.2);">
                    <div class="panel-header" style="border-bottom-color:rgba(239,68,68,0.15);">
                        <div class="panel-header-left">
                            <div class="panel-header-icon ph-red"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg></div>
                            <div><h2>Danger Zone</h2><p>Irreversible and destructive actions</p></div>
                        </div>
                    </div>
                    <div class="danger-zone">
                        <div class="danger-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>These actions are permanent and cannot be undone</div>
                        <div class="danger-row">
                            <div class="danger-info">
                                <div class="d-name">Reset All Settings</div>
                                <div class="d-desc">Restore all preferences to factory defaults</div>
                            </div>
                            <button class="btn-outline-sm" onclick="openModal('Reset All Settings','All your preferences, notification settings and appearance customisations will be reset to defaults. Your account data and transactions will not be affected.','Type RESET to confirm','confirm-text')">Reset</button>
                        </div>
                        <div class="danger-row">
                            <div class="danger-info">
                                <div class="d-name">Clear Transaction History</div>
                                <div class="d-desc">Remove all local transaction data (does not affect bank records)</div>
                            </div>
                            <button class="btn-outline-sm" onclick="openModal('Clear History','This will delete all locally stored transaction history. Bank statements will remain unaffected.','Type CLEAR to confirm','confirm-text')">Clear</button>
                        </div>
                        <div class="danger-row">
                            <div class="danger-info">
                                <div class="d-name">Deactivate Account</div>
                                <div class="d-desc">Temporarily suspend your PaySim account</div>
                            </div>
                            <button class="btn-danger" onclick="openModal('Deactivate Account','Your account will be suspended. You can reactivate it by logging back in within 90 days.','Type your UPI ID to confirm','confirm-text')">Deactivate</button>
                        </div>
                        <div class="danger-row" style="border-bottom:none;">
                            <div class="danger-info">
                                <div class="d-name" style="color:var(--error);">Delete Account</div>
                                <div class="d-desc">Permanently delete your PaySim account and all data. This cannot be undone.</div>
                            </div>
                            <button class="btn-danger" onclick="openModal('Delete Account','⚠️ This is PERMANENT. Your account, all data, and linked bank associations will be removed forever. There is no recovery.','Type DELETE to confirm','confirm-text')">Delete Account</button>
                        </div>
                    </div>
                </div>

            </div><!-- /.settings-panels -->
        </div><!-- /.settings-grid -->
    </main>
</div>

<!-- Toast -->
<div class="toast" id="toast">
    <svg class="toast-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" id="toastIcon"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
    <span id="toastMsg">Done!</span>
</div>

<script>
let toastTimer;
let modalAction = null;

// ── Panel switching ───────────────────────────────────────────────────────────
function switchPanel(id, btn) {
    document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const panel = document.getElementById('panel-' + id);
    panel.classList.add('active');
    // Re-trigger animation
    panel.style.animation = 'none';
    panel.offsetHeight;
    panel.style.animation = '';
}

// ── Limit labels ──────────────────────────────────────────────────────────────
function updateLimitLabel(inputId, labelId) {
    const val = parseInt(document.getElementById(inputId).value);
    document.getElementById(labelId).textContent = '₹' + val.toLocaleString('en-IN');
}

// ── Theme / accent ────────────────────────────────────────────────────────────
function setTheme(btn, theme) {
    document.querySelectorAll('#panel-appearance .theme-options:first-of-type .theme-opt').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    showToast('Theme set to ' + btn.textContent.trim());
}
function setAccent(btn) {
    document.querySelectorAll('#panel-appearance .theme-options:nth-of-type(2) .theme-opt').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    showToast('Accent colour updated');
}

// ── Save section ──────────────────────────────────────────────────────────────
function saveSection(name) {
    showToast(name.charAt(0).toUpperCase() + name.slice(1) + ' settings saved');
}

// ── Password change ───────────────────────────────────────────────────────────
function changePassword() {
    const cur  = document.getElementById('curPwd').value;
    const nw   = document.getElementById('newPwd').value;
    const conf = document.getElementById('confPwd').value;
    if (!cur)  { shakeEl('curPwd');  showToast('Enter current password', false); return; }
    if (!nw || nw.length < 8) { shakeEl('newPwd'); showToast('Password must be at least 8 characters', false); return; }
    if (nw !== conf) { shakeEl('confPwd'); showToast('Passwords do not match', false); return; }
    document.getElementById('curPwd').value = '';
    document.getElementById('newPwd').value = '';
    document.getElementById('confPwd').value = '';
    showToast('Password updated successfully');
}

// ── Revoke session ────────────────────────────────────────────────────────────
function revokeSession(btn) {
    const item = btn.closest('.session-item');
    item.style.transition = 'opacity 0.4s, transform 0.4s';
    item.style.opacity = '0';
    item.style.transform = 'translateX(20px)';
    setTimeout(() => item.remove(), 400);
    showToast('Session revoked');
}

// ── Modal ─────────────────────────────────────────────────────────────────────
function openModal(title, desc, inputPlaceholder, type) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalDesc').textContent  = desc;
    const wrap = document.getElementById('modalInputWrap');
    const inp  = document.getElementById('modalInput');
    if (inputPlaceholder) {
        wrap.style.display = '';
        inp.placeholder    = inputPlaceholder;
        inp.value          = '';
        inp.type           = type === 'pin' ? 'password' : 'text';
    } else {
        wrap.style.display = 'none';
    }
    modalAction = type;
    document.getElementById('modalOverlay').classList.add('active');
}
function closeModal() {
    document.getElementById('modalOverlay').classList.remove('active');
    modalAction = null;
}
function modalConfirm() {
    closeModal();
    showToast('Action completed');
}
document.getElementById('modalOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('modalOverlay')) closeModal();
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function shakeEl(id) {
    const el = document.getElementById(id);
    el.style.animation = 'none'; el.offsetHeight;
    el.style.animation = 'shake 0.35s';
    el.addEventListener('animationend', () => el.style.animation = '', { once: true });
}
function showToast(msg, ok = true) {
    const el   = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    icon.style.color = ok ? 'var(--success)' : 'var(--warning)';
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
