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

// ─── Get Transaction ID ────────────────────────────────────────────────────
$txnId = $_GET['id'] ?? 'TXN' . rand(100000, 999999);

// ─── Simulated Transaction Data ─────────────────────────────────────────────
$transactions = [
    'TXN001' => [
        'id'          => 'TXN001',
        'ref'         => 'PSM926814750001',
        'type'        => 'debit',
        'status'      => 'completed',
        'amount'      => 500.00,
        'from_name'   => $userName,
        'from_upi'    => $upiId,
        'to_name'     => 'Ravi Kumar',
        'to_upi'      => 'ravi@paysim',
        'note'        => 'Lunch split',
        'date'        => '18 Sep 2026, 02:32 PM',
        'method'      => 'UPI',
        'bank'        => 'State Bank of India',
    ],
    'TXN002' => [
        'id'          => 'TXN002',
        'ref'         => 'PSM926814750002',
        'type'        => 'credit',
        'status'      => 'completed',
        'amount'      => 12400.00,
        'from_name'   => 'Salary Credit',
        'from_upi'    => 'payroll@company',
        'to_name'     => $userName,
        'to_upi'      => $upiId,
        'note'        => 'Monthly salary - September 2026',
        'date'        => '17 Sep 2026, 09:00 AM',
        'method'      => 'NEFT',
        'bank'        => 'State Bank of India',
    ],
    'TXN003' => [
        'id'          => 'TXN003',
        'ref'         => 'PSM926814750003',
        'type'        => 'debit',
        'status'      => 'completed',
        'amount'      => 649.00,
        'from_name'   => $userName,
        'from_upi'    => $upiId,
        'to_name'     => 'Netflix',
        'to_upi'      => 'netflix@razorpay',
        'note'        => 'Subscription renewal',
        'date'        => '16 Sep 2026, 06:45 PM',
        'method'      => 'UPI Autopay',
        'bank'        => 'State Bank of India',
    ],
];

// Default transaction if ID not found
$txn = $transactions[$txnId] ?? [
    'id'          => $txnId,
    'ref'         => 'PSM' . rand(100000000, 999999999),
    'type'        => 'debit',
    'status'      => 'completed',
    'amount'      => 250.00,
    'from_name'   => $userName,
    'from_upi'    => $upiId,
    'to_name'     => 'Demo Merchant',
    'to_upi'      => 'merchant@paysim',
    'note'        => 'Payment',
    'date'        => date('d M Y, h:i A'),
    'method'      => 'UPI',
    'bank'        => 'State Bank of India',
];

$isCredit = $txn['type'] === 'credit';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt — PaySim</title>
    <meta name="description" content="Transaction receipt for PaySim UPI payment.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

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
            --radius:         12px;
            --radius-lg:      20px;
            --radius-xl:      24px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* ── Background ───────────────────────────────────────────────── */
        .bg-orb { position: fixed; border-radius: 50%; filter: blur(120px); opacity: 0.25; z-index: 0; pointer-events: none; }
        .bg-orb--1 { width: 500px; height: 500px; background: radial-gradient(circle, var(--accent) 0%, transparent 70%); top: -150px; right: -100px; animation: orbFloat 10s ease-in-out infinite alternate; }
        .bg-orb--2 { width: 400px; height: 400px; background: radial-gradient(circle, #8b5cf6 0%, transparent 70%); bottom: -100px; left: -80px; animation: orbFloat 12s ease-in-out infinite alternate-reverse; }
        @keyframes orbFloat { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(30px,-20px) scale(1.1); } }
        @keyframes cardSlideIn { to { opacity: 1; transform: translateY(0); } }

        /* ── Receipt Container ────────────────────────────────────────── */
        .receipt-page {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
        }

        /* ── Back Link ────────────────────────────────────────────────── */
        .back-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            animation: cardSlideIn 0.4s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0; transform: translateY(12px);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link:hover { color: var(--text-primary); }
        .back-link svg { width: 18px; height: 18px; }

        /* ── Receipt Card ─────────────────────────────────────────────── */
        .receipt-card {
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: 0 25px 60px -12px rgba(0,0,0,0.5);
            animation: cardSlideIn 0.6s cubic-bezier(0.16,1,0.3,1) 0.05s forwards;
            opacity: 0; transform: translateY(20px);
        }

        /* ── Status Header ────────────────────────────────────────────── */
        .receipt-status {
            text-align: center;
            padding: 36px 32px 28px;
            position: relative;
        }

        .receipt-status::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 24px;
            right: 24px;
            height: 1px;
            background: var(--border);
        }

        .status-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            position: relative;
        }

        .status-icon--success {
            background: var(--success-bg);
            border: 2px solid var(--success-border);
        }

        .status-icon--success svg { color: var(--success); width: 32px; height: 32px; }

        /* Animated checkmark ring */
        .status-icon--success::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            border: 2px solid var(--success);
            opacity: 0;
            animation: ringPulse 1.5s ease-out 0.3s forwards;
        }

        @keyframes ringPulse {
            0%   { transform: scale(0.8); opacity: 0.6; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        .status-icon--pending {
            background: rgba(245,158,11,0.1);
            border: 2px solid rgba(245,158,11,0.25);
        }

        .status-icon--pending svg { color: var(--warning); width: 32px; height: 32px; }

        .status-text {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .status-text--success { color: var(--success); }
        .status-text--pending { color: var(--warning); }

        .status-sub {
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        /* ── Amount Section ───────────────────────────────────────────── */
        .receipt-amount {
            text-align: center;
            padding: 28px 32px;
            position: relative;
        }

        .receipt-amount::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 24px;
            right: 24px;
            height: 1px;
            background: var(--border);
        }

        .amount-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .amount-value {
            font-size: 2.6rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 8px;
        }

        .amount-value--debit { color: #f87171; }
        .amount-value--credit { color: var(--success); }

        .amount-value .currency {
            font-size: 1.4rem;
            font-weight: 600;
            opacity: 0.7;
            vertical-align: super;
            margin-right: 2px;
        }

        .amount-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .amount-type-badge--debit {
            background: var(--error-bg);
            color: #f87171;
        }

        .amount-type-badge--credit {
            background: var(--success-bg);
            color: var(--success);
        }

        .amount-type-badge svg { width: 12px; height: 12px; }

        /* ── Transfer Details ─────────────────────────────────────────── */
        .transfer-section {
            padding: 24px 32px;
            position: relative;
        }

        .transfer-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 24px;
            right: 24px;
            height: 1px;
            background: var(--border);
        }

        .transfer-flow {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .transfer-party {
            flex: 1;
            text-align: center;
        }

        .transfer-avatar {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }

        .transfer-avatar--from {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .transfer-avatar--to {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .transfer-name {
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .transfer-upi {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
        }

        .transfer-arrow {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(99,102,241,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .transfer-arrow svg { width: 20px; height: 20px; color: var(--accent-hover); }

        /* ── Details Grid ─────────────────────────────────────────────── */
        .details-section {
            padding: 24px 32px;
            position: relative;
        }

        .details-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 24px;
            right: 24px;
            height: 1px;
            background: var(--border);
        }

        .detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(99,102,241,0.05);
        }

        .detail-row:last-child { border-bottom: none; }

        .detail-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label svg { width: 16px; height: 16px; opacity: 0.5; }

        .detail-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            text-align: right;
            max-width: 55%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .detail-value.mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
            color: var(--text-secondary);
        }

        .detail-value .copy-inline {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .detail-value .copy-inline:hover { color: var(--accent-hover); }
        .detail-value .copy-inline svg { width: 13px; height: 13px; opacity: 0.5; }

        /* ── Note Section ─────────────────────────────────────────────── */
        .note-section {
            padding: 20px 32px;
            position: relative;
        }

        .note-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 24px;
            right: 24px;
            height: 1px;
            background: var(--border);
        }

        .note-box {
            background: rgba(99,102,241,0.05);
            border: 1px solid rgba(99,102,241,0.1);
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .note-box svg { width: 18px; height: 18px; color: var(--text-muted); flex-shrink: 0; margin-top: 1px; }

        .note-box-content {
            flex: 1;
        }

        .note-box-label {
            font-size: 0.68rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 3px;
        }

        .note-box-text {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        /* ── Actions Footer ───────────────────────────────────────────── */
        .receipt-actions {
            padding: 24px 32px;
            display: flex;
            gap: 12px;
        }

        .receipt-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 20px;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .receipt-btn svg { width: 18px; height: 18px; }

        .receipt-btn--primary {
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
        }

        .receipt-btn--primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px var(--accent-glow);
        }

        .receipt-btn--secondary {
            background: rgba(99,102,241,0.1);
            color: var(--accent-hover);
            border: 1px solid rgba(99,102,241,0.2);
        }

        .receipt-btn--secondary:hover {
            background: rgba(99,102,241,0.18);
        }

        .receipt-btn--outline {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        .receipt-btn--outline:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        /* ── Security Footer ──────────────────────────────────────────── */
        .receipt-security {
            text-align: center;
            padding: 16px 32px 24px;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .security-badge svg { width: 14px; height: 14px; color: var(--success); }

        /* ── Bottom Links ─────────────────────────────────────────────── */
        .bottom-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 20px;
            animation: cardSlideIn 0.4s cubic-bezier(0.16,1,0.3,1) 0.15s forwards;
            opacity: 0; transform: translateY(12px);
        }

        .bottom-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .bottom-link:hover { color: var(--accent-hover); }
        .bottom-link svg { width: 16px; height: 16px; }

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

        /* ── Print Styles ─────────────────────────────────────────────── */
        @media print {
            body { background: #fff; color: #000; padding: 0; }
            .bg-orb, .back-row, .receipt-actions, .bottom-links, .toast { display: none !important; }
            .receipt-card { box-shadow: none; border: 1px solid #ddd; background: #fff; backdrop-filter: none; }
            .receipt-status::after, .receipt-amount::after, .transfer-section::after,
            .details-section::after, .note-section::after { background: #eee; }
            .status-text--success { color: #059669; }
            .amount-value--debit { color: #dc2626; }
            .amount-value--credit { color: #059669; }
            .detail-label, .transfer-upi, .note-box-label, .security-badge,
            .status-sub, .amount-label { color: #666; }
            .detail-value, .transfer-name { color: #111; }
            .note-box { background: #f9f9f9; border-color: #eee; }
        }

        /* ── Responsive ───────────────────────────────────────────────── */
        @media (max-width: 520px) {
            body { padding: 16px; }
            .receipt-status, .receipt-amount, .transfer-section,
            .details-section, .note-section, .receipt-actions,
            .receipt-security { padding-left: 20px; padding-right: 20px; }
            .amount-value { font-size: 2rem; }
            .receipt-actions { flex-direction: column; }
            .transfer-name { font-size: 0.8rem; }
        }
    </style>
</head>
<body>

<div class="bg-orb bg-orb--1"></div>
<div class="bg-orb bg-orb--2"></div>

<div class="receipt-page">

    <!-- Back -->
    <div class="back-row">
        <a href="dashboard.php" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Receipt Card -->
    <div class="receipt-card" id="receiptCard">

        <!-- Status -->
        <div class="receipt-status">
            <?php if ($txn['status'] === 'completed'): ?>
            <div class="status-icon status-icon--success">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            </div>
            <div class="status-text status-text--success">Payment Successful</div>
            <?php else: ?>
            <div class="status-icon status-icon--pending">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <div class="status-text status-text--pending">Payment Pending</div>
            <?php endif; ?>
            <div class="status-sub"><?php echo $txn['date']; ?></div>
        </div>

        <!-- Amount -->
        <div class="receipt-amount">
            <div class="amount-label"><?php echo $isCredit ? 'Amount Received' : 'Amount Paid'; ?></div>
            <div class="amount-value amount-value--<?php echo $txn['type']; ?>">
                <span class="currency">₹</span><?php echo number_format($txn['amount'], 2); ?>
            </div>
            <span class="amount-type-badge amount-type-badge--<?php echo $txn['type']; ?>">
                <?php if ($isCredit): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" /></svg>
                    Credited
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" /></svg>
                    Debited
                <?php endif; ?>
            </span>
        </div>

        <!-- Transfer Flow -->
        <div class="transfer-section">
            <div class="transfer-flow">
                <div class="transfer-party">
                    <div class="transfer-avatar transfer-avatar--from">
                        <?php
                            $fromParts = explode(' ', $txn['from_name']);
                            echo strtoupper(substr($fromParts[0], 0, 1) . (isset($fromParts[1]) ? substr($fromParts[1], 0, 1) : ''));
                        ?>
                    </div>
                    <div class="transfer-name"><?php echo htmlspecialchars($txn['from_name']); ?></div>
                    <div class="transfer-upi"><?php echo htmlspecialchars($txn['from_upi']); ?></div>
                </div>

                <div class="transfer-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </div>

                <div class="transfer-party">
                    <div class="transfer-avatar transfer-avatar--to">
                        <?php
                            $toParts = explode(' ', $txn['to_name']);
                            echo strtoupper(substr($toParts[0], 0, 1) . (isset($toParts[1]) ? substr($toParts[1], 0, 1) : ''));
                        ?>
                    </div>
                    <div class="transfer-name"><?php echo htmlspecialchars($txn['to_name']); ?></div>
                    <div class="transfer-upi"><?php echo htmlspecialchars($txn['to_upi']); ?></div>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="details-section">
            <div class="detail-row">
                <div class="detail-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                    Transaction ID
                </div>
                <div class="detail-value mono">
                    <span class="copy-inline" onclick="copyText('<?php echo addslashes($txn['id']); ?>', 'Transaction ID')">
                        <?php echo htmlspecialchars($txn['id']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                    </span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                    Reference No.
                </div>
                <div class="detail-value mono">
                    <span class="copy-inline" onclick="copyText('<?php echo addslashes($txn['ref']); ?>', 'Reference number')">
                        <?php echo htmlspecialchars($txn['ref']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                    </span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Date & Time
                </div>
                <div class="detail-value"><?php echo $txn['date']; ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                    Payment Method
                </div>
                <div class="detail-value"><?php echo htmlspecialchars($txn['method']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" /></svg>
                    Bank
                </div>
                <div class="detail-value"><?php echo htmlspecialchars($txn['bank']); ?></div>
            </div>
        </div>

        <!-- Note -->
        <?php if (!empty($txn['note'])): ?>
        <div class="note-section">
            <div class="note-box">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                <div class="note-box-content">
                    <div class="note-box-label">Note</div>
                    <div class="note-box-text"><?php echo htmlspecialchars($txn['note']); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="receipt-actions">
            <button class="receipt-btn receipt-btn--primary" onclick="downloadReceipt()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Download
            </button>
            <button class="receipt-btn receipt-btn--secondary" onclick="shareReceipt()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
                Share
            </button>
            <button class="receipt-btn receipt-btn--outline" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m0 0a48.159 48.159 0 0 1 12.5 0m-12.5 0V5.625c0-.621.504-1.125 1.125-1.125h10.5c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125h-10.5a1.125 1.125 0 0 1-1.125-1.125V5.625Z" /></svg>
                Print
            </button>
        </div>

        <!-- Security -->
        <div class="receipt-security">
            <div class="security-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                Secured by PaySim • End-to-end encrypted
            </div>
        </div>

    </div>

    <!-- Bottom Links -->
    <div class="bottom-links">
        <a href="dashboard.php" class="bottom-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
            Dashboard
        </a>
        <a href="transactions.php" class="bottom-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            All Transactions
        </a>
        <a href="send-money.php" class="bottom-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
            Send Again
        </a>
    </div>

</div>

<!-- Toast -->
<div class="toast" id="toast">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
    <span id="toastText">Copied!</span>
</div>

<script>
    // ─── Copy ───────────────────────────────────────────────────────
    function copyText(text, label) {
        navigator.clipboard.writeText(text).then(() => {
            showToast(label + ' copied!');
        }).catch(() => {
            const t = document.createElement('textarea');
            t.value = text;
            document.body.appendChild(t);
            t.select();
            document.execCommand('copy');
            document.body.removeChild(t);
            showToast(label + ' copied!');
        });
    }

    // ─── Download Receipt as Image ──────────────────────────────────
    function downloadReceipt() {
        // Use print as fallback for clean receipt download
        showToast('Preparing receipt for download...');
        setTimeout(() => window.print(), 500);
    }

    // ─── Share ──────────────────────────────────────────────────────
    function shareReceipt() {
        const text = `PaySim Receipt\n` +
            `Amount: ₹<?php echo number_format($txn['amount'], 2); ?>\n` +
            `<?php echo $isCredit ? 'From' : 'To'; ?>: <?php echo addslashes($isCredit ? $txn['from_name'] : $txn['to_name']); ?>\n` +
            `Ref: <?php echo addslashes($txn['ref']); ?>\n` +
            `Date: <?php echo addslashes($txn['date']); ?>\n` +
            `Status: <?php echo ucfirst($txn['status']); ?>`;

        if (navigator.share) {
            navigator.share({ title: 'PaySim Receipt', text: text }).catch(() => {});
        } else {
            copyText(text, 'Receipt details');
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
