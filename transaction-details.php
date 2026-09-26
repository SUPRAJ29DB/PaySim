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

// ─── Get Transaction ID ────────────────────────────────────────────────────
$txnId = isset($_GET['id']) ? trim($_GET['id']) : 'TXN001';

// ─── Master Transaction Database (Simulated) ────────────────────────────────
$allTransactions = [
    'TXN001' => [
        'id'               => 'TXN001',
        'ref'              => 'PSM926814750001',
        'utr'              => 'UTR429184029481',
        'type'             => 'debit',
        'status'           => 'completed',
        'amount'           => 500.00,
        'fee'              => 0.00,
        'cashback'         => 15.00,
        'from_name'        => $userName,
        'from_upi'         => $upiId,
        'from_bank'        => 'State Bank of India',
        'from_account_num' => '•••• 4829',
        'to_name'          => 'Ravi Kumar',
        'to_upi'           => 'ravi@paysim',
        'to_bank'          => 'HDFC Bank',
        'to_account_num'   => '•••• 9102',
        'note'             => 'Lunch split at Charcoal Grill',
        'date'             => '18 Sep 2026, 02:32 PM',
        'raw_date'         => '2026-09-18 14:32:10',
        'method'           => 'UPI Transfer',
        'category'         => 'Food & Dining',
        'device'           => 'PaySim Android v3.4.2 (Samsung S23)',
        'ip_address'       => '103.211.54.12',
        'location'         => 'Kolkata, WB, India',
        'verification'     => 'Biometric + 6-Digit UPI PIN',
        'timeline'         => [
            ['title' => 'Payment Initiated', 'time' => '02:32:01 PM', 'desc' => 'Transaction requested via PaySim Mobile', 'status' => 'done'],
            ['title' => 'Sender Bank Auth',  'time' => '02:32:03 PM', 'desc' => 'State Bank of India approved debit', 'status' => 'done'],
            ['title' => 'NPCI Clearing',     'time' => '02:32:05 PM', 'desc' => 'UPI switch routed and approved (140ms latency)', 'status' => 'done'],
            ['title' => 'Beneficiary Credit','time' => '02:32:06 PM', 'desc' => 'HDFC Bank credited to ravi@paysim', 'status' => 'done'],
        ],
        'history' => [
            ['date' => '05 Sep 2026', 'amount' => 350.00, 'type' => 'debit',  'note' => 'Coffee & snacks'],
            ['date' => '22 Aug 2026', 'amount' => 1200.00, 'type' => 'credit', 'note' => 'Movie tickets refund'],
            ['date' => '10 Aug 2026', 'amount' => 450.00, 'type' => 'debit',  'note' => 'Taxi fare share'],
        ]
    ],
    'TXN002' => [
        'id'               => 'TXN002',
        'ref'              => 'PSM926814750002',
        'utr'              => 'UTR981240185921',
        'type'             => 'credit',
        'status'           => 'completed',
        'amount'           => 12400.00,
        'fee'              => 0.00,
        'cashback'         => 0.00,
        'from_name'        => 'Acme Technologies Pvt Ltd',
        'from_upi'         => 'payroll@acmetech',
        'from_bank'        => 'ICICI Bank',
        'from_account_num' => '•••• 1109',
        'to_name'          => $userName,
        'to_upi'           => $upiId,
        'to_bank'          => 'State Bank of India',
        'to_account_num'   => '•••• 4829',
        'note'             => 'Monthly salary & allowances - September 2026',
        'date'             => '17 Sep 2026, 09:00 AM',
        'raw_date'         => '2026-09-17 09:00:00',
        'method'           => 'NEFT Salary Transfer',
        'category'         => 'Salary & Income',
        'device'           => 'Automated Clearing House (ACH)',
        'ip_address'       => ' Corporate Direct Portal',
        'location'         => 'Mumbai, MH, India',
        'verification'     => 'Corporate Gateway Signed Signature',
        'timeline'         => [
            ['title' => 'Batch Initiated',  'time' => '08:45:00 AM', 'desc' => 'Employer submitted payroll file', 'status' => 'done'],
            ['title' => 'RBI NEFT Batch',   'time' => '08:55:00 AM', 'desc' => 'Settled in Batch #4 clearance', 'status' => 'done'],
            ['title' => 'Account Credited', 'time' => '09:00:00 AM', 'desc' => 'Funds available in your account', 'status' => 'done'],
        ],
        'history' => [
            ['date' => '17 Aug 2026', 'amount' => 12400.00, 'type' => 'credit', 'note' => 'August Salary'],
            ['date' => '17 Jul 2026', 'amount' => 12400.00, 'type' => 'credit', 'note' => 'July Salary'],
        ]
    ],
    'TXN003' => [
        'id'               => 'TXN003',
        'ref'              => 'PSM926814750003',
        'utr'              => 'UTR105928104820',
        'type'             => 'debit',
        'status'           => 'completed',
        'amount'           => 649.00,
        'fee'              => 0.00,
        'cashback'         => 0.00,
        'from_name'        => $userName,
        'from_upi'         => $upiId,
        'from_bank'        => 'State Bank of India',
        'from_account_num' => '•••• 4829',
        'to_name'          => 'Netflix India',
        'to_upi'           => 'netflix@razorpay',
        'to_bank'          => 'Razorpay Merchant Vault',
        'to_account_num'   => '•••• 7701',
        'note'             => 'Monthly Premium Subscription (4K Screen)',
        'date'             => '16 Sep 2026, 06:45 PM',
        'raw_date'         => '2026-09-16 18:45:22',
        'method'           => 'UPI Autopay Mandate',
        'category'         => 'Subscription',
        'device'           => 'PaySim Mandate Engine v1.2',
        'ip_address'       => 'Auto-scheduled Execution',
        'location'         => 'Bengaluru, KA, India',
        'verification'     => 'Pre-authorized UPI Mandate ID #MND-8941',
        'timeline'         => [
            ['title' => 'Mandate Triggered', 'time' => '06:45:00 PM', 'desc' => 'Recurring mandate execution initiated', 'status' => 'done'],
            ['title' => 'Merchant Handshake','time' => '06:45:10 PM', 'desc' => 'Razorpay invoice token validated', 'status' => 'done'],
            ['title' => 'Payment Settled',   'time' => '06:45:22 PM', 'desc' => 'Receipt generated by merchant', 'status' => 'done'],
        ],
        'history' => [
            ['date' => '16 Aug 2026', 'amount' => 649.00, 'type' => 'debit', 'note' => 'August Subscription'],
            ['date' => '16 Jul 2026', 'amount' => 649.00, 'type' => 'debit', 'note' => 'July Subscription'],
        ]
    ],
    'TXN004' => [
        'id'               => 'TXN004',
        'ref'              => 'PSM926814750004',
        'utr'              => 'UTR882910481204',
        'type'             => 'credit',
        'status'           => 'completed',
        'amount'           => 1200.00,
        'fee'              => 0.00,
        'cashback'         => 25.00,
        'from_name'        => 'Priya Sharma',
        'from_upi'         => 'priya@paysim',
        'from_bank'        => 'Axis Bank',
        'from_account_num' => '•••• 3341',
        'to_name'          => $userName,
        'to_upi'           => $upiId,
        'to_bank'          => 'State Bank of India',
        'to_account_num'   => '•••• 4829',
        'note'             => 'Weekend trip fuel expense share',
        'date'             => '15 Sep 2026, 11:20 AM',
        'raw_date'         => '2026-09-15 11:20:00',
        'method'           => 'UPI Transfer',
        'category'         => 'Reimbursement',
        'device'           => 'PaySim iOS v3.4.1 (iPhone 15 Pro)',
        'ip_address'       => '115.240.18.99',
        'location'         => 'Kolkata, WB, India',
        'verification'     => 'FaceID + UPI PIN',
        'timeline'         => [
            ['title' => 'Money Received', 'time' => '11:20:00 AM', 'desc' => 'Credited instantly to primary account', 'status' => 'done'],
        ],
        'history' => [
            ['date' => '02 Sep 2026', 'amount' => 800.00, 'type' => 'debit', 'note' => 'Dinner out'],
        ]
    ],
    'TXN005' => [
        'id'               => 'TXN005',
        'ref'              => 'PSM926814750005',
        'utr'              => 'UTR330194820194',
        'type'             => 'debit',
        'status'           => 'pending',
        'amount'           => 2499.00,
        'fee'              => 0.00,
        'cashback'         => 0.00,
        'from_name'        => $userName,
        'from_upi'         => $upiId,
        'from_bank'        => 'State Bank of India',
        'from_account_num' => '•••• 4829',
        'to_name'          => 'Flipkart Internet Pvt Ltd',
        'to_upi'           => 'flipkart@paytm',
        'to_bank'          => 'Paytm Payments Bank',
        'to_account_num'   => '•••• 5521',
        'note'             => 'Order #FK-904812 Wireless Headphones',
        'date'             => '19 Sep 2026, 09:15 PM',
        'raw_date'         => '2026-09-19 21:15:00',
        'method'           => 'UPI QR Scan',
        'category'         => 'Shopping',
        'device'           => 'PaySim Web App (Chrome 128)',
        'ip_address'       => '182.74.88.10',
        'location'         => 'Kolkata, WB, India',
        'verification'     => 'SMS OTP + UPI PIN',
        'timeline'         => [
            ['title' => 'Payment Initiated',  'time' => '09:15:02 PM', 'desc' => 'Debit confirmation received from bank', 'status' => 'done'],
            ['title' => 'Awaiting Settlement','time' => '09:15:15 PM', 'desc' => 'Bank clearing under process (max 48 hrs)', 'status' => 'current'],
            ['title' => 'Merchant Confirm',   'time' => 'Pending',     'desc' => 'Merchant webhook notification pending', 'status' => 'upcoming'],
        ],
        'history' => []
    ],
    'TXN006' => [
        'id'               => 'TXN006',
        'ref'              => 'PSM926814750006',
        'utr'              => 'UTR991823019284',
        'type'             => 'debit',
        'status'           => 'failed',
        'amount'           => 15000.00,
        'fee'              => 0.00,
        'cashback'         => 0.00,
        'from_name'        => $userName,
        'from_upi'         => $upiId,
        'from_bank'        => 'State Bank of India',
        'from_account_num' => '•••• 4829',
        'to_name'          => 'Amit Verma',
        'to_upi'           => 'amit@icici',
        'to_bank'          => 'ICICI Bank',
        'to_account_num'   => '•••• 0012',
        'note'             => 'Security deposit share',
        'date'             => '14 Sep 2026, 04:10 PM',
        'raw_date'         => '2026-09-14 16:10:00',
        'method'           => 'UPI Transfer',
        'category'         => 'Transfer',
        'device'           => 'PaySim Android v3.4.2',
        'ip_address'       => '103.211.54.12',
        'location'         => 'Kolkata, WB, India',
        'verification'     => 'UPI PIN Verification',
        'failure_reason'   => 'Exceeded Daily Transaction Limit set by Bank (Code: U16)',
        'timeline'         => [
            ['title' => 'Payment Initiated', 'time' => '04:10:00 PM', 'desc' => 'UPI PIN entered successfully', 'status' => 'done'],
            ['title' => 'Bank Validation',   'time' => '04:10:02 PM', 'desc' => 'State Bank limit policy triggered (U16)', 'status' => 'failed'],
            ['title' => 'Auto-Refund',       'time' => '04:10:03 PM', 'desc' => 'No money deducted from account', 'status' => 'done'],
        ],
        'history' => []
    ]
];

// Fallback dynamic transaction if requested ID is not in predefined list
if (isset($allTransactions[$txnId])) {
    $txn = $allTransactions[$txnId];
} else {
    // Generate simulated object for custom TXN IDs
    $isDebit = (rand(0, 1) === 1);
    $txn = [
        'id'               => $txnId,
        'ref'              => 'PSM' . rand(100000000, 999999999),
        'utr'              => 'UTR' . rand(100000000000, 999999999999),
        'type'             => $isDebit ? 'debit' : 'credit',
        'status'           => 'completed',
        'amount'           => rand(100, 5000) + (rand(0, 99) / 100),
        'fee'              => 0.00,
        'cashback'         => rand(0, 1) ? 10.00 : 0.00,
        'from_name'        => $isDebit ? $userName : 'Payment Counterparty',
        'from_upi'         => $isDebit ? $upiId : 'counterparty@paysim',
        'from_bank'        => 'State Bank of India',
        'from_account_num' => '•••• 4829',
        'to_name'          => $isDebit ? 'Payment Recipient' : $userName,
        'to_upi'           => $isDebit ? 'recipient@paysim' : $upiId,
        'to_bank'          => 'HDFC Bank',
        'to_account_num'   => '•••• 8821',
        'note'             => 'PaySim Transaction',
        'date'             => date('d M Y, h:i A'),
        'raw_date'         => date('Y-m-d H:i:s'),
        'method'           => 'UPI Transfer',
        'category'         => 'Transfer',
        'device'           => 'PaySim Mobile App',
        'ip_address'       => '127.0.0.1',
        'location'         => 'India',
        'verification'     => 'UPI PIN Verified',
        'timeline'         => [
            ['title' => 'Initiated', 'time' => date('h:i:s A'), 'desc' => 'Payment processed', 'status' => 'done'],
            ['title' => 'Completed', 'time' => date('h:i:s A'), 'desc' => 'Settled successfully', 'status' => 'done'],
        ],
        'history' => []
    ];
}

$isCredit   = ($txn['type'] === 'credit');
$status     = strtolower($txn['status']);
$counterName= $isCredit ? $txn['from_name'] : $txn['to_name'];
$counterUpi = $isCredit ? $txn['from_upi']  : $txn['to_upi'];
$counterInitials = strtoupper(substr($counterName, 0, 1) . (strpos($counterName, ' ') ? substr(explode(' ', $counterName)[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details — <?php echo htmlspecialchars($txn['id']); ?> — PaySim</title>
    <meta name="description" content="View complete audit details, breakdown, timeline, and actions for PaySim transaction <?php echo htmlspecialchars($txn['id']); ?>.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

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
            --error-border:   rgba(239, 68, 68, 0.25);
            --warning:        #f59e0b;
            --warning-bg:     rgba(245, 158, 11, 0.1);
            --warning-border: rgba(245, 158, 11, 0.25);
            --radius:         12px;
            --radius-lg:      20px;
            --sidebar-width:  280px;
        }

        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; overflow-x: hidden; }

        .bg-orb { position:fixed; border-radius:50%; filter:blur(120px); opacity:0.25; z-index:0; pointer-events:none; }
        .bg-orb--1 { width:600px;height:600px;background:radial-gradient(circle,var(--accent) 0%,transparent 70%);top:-200px;right:-150px;animation:orbFloat 10s ease-in-out infinite alternate; }
        .bg-orb--2 { width:450px;height:450px;background:radial-gradient(circle,#8b5cf6 0%,transparent 70%);bottom:-150px;left:-100px;animation:orbFloat 12s ease-in-out infinite alternate-reverse; }
        .bg-orb--3 { width:300px;height:300px;background:radial-gradient(circle,#06b6d4 0%,transparent 70%);top:50%;left:40%;animation:orbFloat 14s ease-in-out infinite alternate; }
        @keyframes orbFloat { 0%{transform:translate(0,0)scale(1)} 100%{transform:translate(40px,-30px)scale(1.15)} }

        .app-layout { display:flex; min-height:100vh; position:relative; z-index:1; }

        /* ── Sidebar Navigation ── */
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
        .sidebar-footer { padding:16px;border-top:1px solid var(--border); }
        .sidebar-user { display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;transition:background 0.2s;cursor:pointer;text-decoration:none; }
        .sidebar-user:hover { background:var(--bg-hover); }
        .sidebar-avatar { width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;flex-shrink:0; }
        .sidebar-user-info .name { font-size:0.85rem;font-weight:600;color:var(--text-primary); }
        .sidebar-user-info .upi  { font-size:0.72rem;color:var(--text-muted); }

        /* ── Main Layout ── */
        .main-content { flex:1;margin-left:var(--sidebar-width);padding:32px 36px 60px;min-height:100vh;max-width:1440px; }

        /* Top Header & Breadcrumbs */
        .breadcrumbs { display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--text-muted);margin-bottom:16px; }
        .breadcrumbs a { color:var(--text-secondary);text-decoration:none;transition:color 0.2s; }
        .breadcrumbs a:hover { color:var(--accent-hover); }
        .breadcrumbs svg { width:14px;height:14px;opacity:0.5; }

        .top-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px; }
        .header-title-area { display:flex;align-items:center;gap:16px; }
        .back-btn { width:40px;height:40px;border-radius:12px;background:var(--bg-card);backdrop-filter:blur(16px);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-secondary);text-decoration:none;transition:all 0.2s; }
        .back-btn:hover { background:var(--bg-hover);color:var(--text-primary);border-color:var(--border-focus);transform:translateX(-2px); }
        .page-title-area h1 { font-size:1.6rem;font-weight:700;letter-spacing:-0.02em;display:flex;align-items:center;gap:10px; }
        .txn-id-badge { font-family:'JetBrains Mono',monospace;font-size:0.85rem;background:rgba(99,102,241,0.12);color:var(--accent-hover);border:1px solid rgba(99,102,241,0.3);padding:3px 10px;border-radius:8px;font-weight:600; }

        .action-toolbar { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
        .btn-secondary { display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;background:var(--bg-card);backdrop-filter:blur(16px);border:1px solid var(--border);color:var(--text-primary);font-size:0.875rem;font-weight:600;cursor:pointer;text-decoration:none;transition:all 0.2s; }
        .btn-secondary:hover { background:var(--bg-hover);border-color:var(--border-focus);box-shadow:0 4px 12px rgba(0,0,0,0.2); }
        .btn-secondary svg { width:18px;height:18px;color:var(--text-secondary); }
        .btn-primary { display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#4f46e5);color:#fff;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;box-shadow:0 4px 14px var(--accent-glow);transition:all 0.2s; }
        .btn-primary:hover { background:linear-gradient(135deg,var(--accent-hover),#6366f1);transform:translateY(-1px);box-shadow:0 6px 20px var(--accent-glow); }
        .btn-primary svg { width:18px;height:18px; }

        /* Grid Layout */
        .details-grid { display:grid;grid-template-columns:1fr 380px;gap:28px; }

        /* Left Column */
        .left-col { display:flex;flex-direction:column;gap:24px; }

        /* Hero Transaction Header Card */
        .hero-card { background:var(--bg-card);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.3); }
        .hero-card::before { content:'';position:absolute;top:0;left:0;right:0;height:4px; }
        .hero-card.status-completed::before { background:linear-gradient(90deg, #10b981, #059669); }
        .hero-card.status-pending::before   { background:linear-gradient(90deg, #f59e0b, #d97706); }
        .hero-card.status-failed::before    { background:linear-gradient(90deg, #ef4444, #dc2626); }

        .hero-top-bar { display:flex;align-items:center;justify-content:space-between;margin-bottom:24px; }
        .status-pill { display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:30px;font-size:0.82rem;font-weight:600;text-transform:capitalize; }
        .status-pill.status-completed { background:var(--success-bg);color:var(--success);border:1px solid var(--success-border); }
        .status-pill.status-pending   { background:var(--warning-bg);color:var(--warning);border:1px solid var(--warning-border); }
        .status-pill.status-failed    { background:var(--error-bg);color:var(--error);border:1px solid var(--error-border); }
        .status-pulse { width:8px;height:8px;border-radius:50%;background:currentColor;display:inline-block; }
        .status-completed .status-pulse { animation:pulseGreen 2s infinite; }
        .status-pending .status-pulse   { animation:pulseAmber 1.2s infinite; }
        @keyframes pulseGreen { 0%,100%{transform:scale(1);opacity:1;} 50%{transform:scale(1.5);opacity:0.5;} }
        @keyframes pulseAmber { 0%,100%{transform:scale(1);opacity:1;} 50%{transform:scale(1.6);opacity:0.4;} }

        .category-tag { display:inline-flex;align-items:center;gap:6px;font-size:0.78rem;color:var(--text-muted);background:rgba(255,255,255,0.04);padding:4px 12px;border-radius:20px;border:1px solid rgba(255,255,255,0.08); }

        .amount-display { text-align:center;margin:16px 0 32px; }
        .amount-label { font-size:0.82rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px; }
        .amount-val { font-size:3rem;font-weight:800;letter-spacing:-0.03em;line-height:1; }
        .amount-val.credit { color:var(--success);text-shadow:0 0 24px rgba(16,185,129,0.3); }
        .amount-val.debit  { color:var(--text-primary); }
        .amount-val.failed { color:var(--error);text-decoration:line-through;opacity:0.8; }
        .cashback-badge { display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:4px 12px;border-radius:12px;background:rgba(16,185,129,0.15);color:var(--success);font-size:0.8rem;font-weight:600;border:1px solid rgba(16,185,129,0.3); }

        /* Transfer Flow Diagram */
        .transfer-flow { display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:16px;background:rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.06);border-radius:16px;padding:20px; }
        .flow-party { display:flex;align-items:center;gap:12px; }
        .party-avatar { width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.05rem;font-weight:700;color:#fff;flex-shrink:0;box-shadow:0 4px 12px rgba(0,0,0,0.3); }
        .party-avatar.user-avatar { background:linear-gradient(135deg,var(--accent),#8b5cf6); }
        .party-avatar.counter-avatar { background:linear-gradient(135deg,#ec4899,#8b5cf6); }
        .party-info { overflow:hidden; }
        .party-name { font-size:0.95rem;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
        .party-upi  { font-size:0.75rem;color:var(--text-secondary);font-family:'JetBrains Mono',monospace;margin-top:2px; }
        .party-bank { font-size:0.72rem;color:var(--text-muted);margin-top:2px; }

        .flow-arrow-container { display:flex;flex-direction:column;align-items:center;gap:4px;padding:0 8px; }
        .flow-line { width:80px;height:2px;background:linear-gradient(90deg,var(--accent),var(--accent-hover));position:relative;border-radius:2px; }
        .flow-dot { width:8px;height:8px;border-radius:50%;background:var(--accent-hover);position:absolute;top:-3px;left:0;animation:flowMove 2s infinite linear;box-shadow:0 0 8px var(--accent); }
        @keyframes flowMove { 0%{left:0;} 100%{left:100%;} }
        .flow-method-tag { font-size:0.68rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-top:4px; }

        /* Metadata & Audit Section */
        .section-card { background:var(--bg-card);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px 28px; }
        .card-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--border); }
        .card-title { font-size:1.05rem;font-weight:700;display:flex;align-items:center;gap:10px; }
        .card-title svg { width:20px;height:20px;color:var(--accent-hover); }

        .meta-grid { display:grid;grid-template-columns:repeat(2, 1fr);gap:18px 24px; }
        .meta-item { display:flex;flex-direction:column;gap:4px; }
        .meta-label { font-size:0.75rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:0.04em; }
        .meta-val { font-size:0.9rem;color:var(--text-primary);font-weight:600;word-break:break-all;display:flex;align-items:center;gap:8px; }
        .mono-val { font-family:'JetBrains Mono',monospace;font-size:0.85rem;color:var(--text-secondary); }
        .copy-btn { background:none;border:none;color:var(--accent-hover);cursor:pointer;opacity:0.7;transition:opacity 0.2s;display:inline-flex;align-items:center; }
        .copy-btn:hover { opacity:1; }
        .copy-btn svg { width:14px;height:14px; }

        /* Payment Calculation Breakdown */
        .calc-box { background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.05);border-radius:14px;padding:16px 20px;display:flex;flex-direction:column;gap:10px;margin-top:16px; }
        .calc-row { display:flex;justify-content:space-between;font-size:0.85rem;color:var(--text-secondary); }
        .calc-row.total { font-size:0.95rem;font-weight:700;color:var(--text-primary);padding-top:10px;border-top:1px dashed var(--border); }

        /* Timeline History Step Tree */
        .timeline { display:flex;flex-direction:column;gap:0;position:relative;padding-left:24px; }
        .timeline::before { content:'';position:absolute;top:8px;bottom:8px;left:7px;width:2px;background:rgba(99,102,241,0.2); }
        .timeline-step { position:relative;padding-bottom:20px; }
        .timeline-step:last-child { padding-bottom:0; }
        .timeline-dot { position:absolute;left:-24px;top:2px;width:16px;height:16px;border-radius:50%;background:var(--bg-card-solid);border:2px solid var(--text-muted);z-index:2;display:flex;align-items:center;justify-content:center; }
        .timeline-step.done .timeline-dot { border-color:var(--success);background:var(--success);box-shadow:0 0 10px rgba(16,185,129,0.4); }
        .timeline-step.current .timeline-dot { border-color:var(--warning);background:var(--warning);box-shadow:0 0 10px rgba(245,158,11,0.4);animation:pulseDot 1s infinite alternate; }
        .timeline-step.failed .timeline-dot  { border-color:var(--error);background:var(--error);box-shadow:0 0 10px rgba(239,68,68,0.4); }
        @keyframes pulseDot { from{transform:scale(0.9);} to{transform:scale(1.2);} }
        .timeline-content { display:flex;flex-direction:column;gap:2px; }
        .timeline-header { display:flex;align-items:center;justify-content:space-between; }
        .step-title { font-size:0.88rem;font-weight:600;color:var(--text-primary); }
        .step-time  { font-size:0.74rem;color:var(--text-muted);font-family:'JetBrains Mono',monospace; }
        .step-desc  { font-size:0.8rem;color:var(--text-secondary); }

        /* Right Column (Sidebar Cards) */
        .right-col { display:flex;flex-direction:column;gap:24px; }

        /* Quick Action List */
        .action-card { background:var(--bg-card);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px; }
        .action-menu { display:flex;flex-direction:column;gap:10px; }
        .action-btn-item { display:flex;align-items:center;gap:14px;padding:12px 16px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);color:var(--text-primary);text-decoration:none;font-size:0.88rem;font-weight:600;transition:all 0.2s;cursor:pointer;width:100%;text-align:left; }
        .action-btn-item:hover { background:var(--bg-hover);border-color:var(--border-focus);color:var(--accent-hover);transform:translateX(3px); }
        .action-btn-icon { width:36px;height:36px;border-radius:10px;background:rgba(99,102,241,0.12);color:var(--accent-hover);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
        .action-btn-icon svg { width:18px;height:18px; }

        /* Tags & Notes Card */
        .note-card { background:var(--bg-card);backdrop-filter:blur(24px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px; }
        .note-input { width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border);border-radius:10px;padding:12px;color:var(--text-primary);font-size:0.85rem;resize:vertical;min-height:80px;font-family:inherit;outline:none;transition:border-color 0.2s; }
        .note-input:focus { border-color:var(--accent); }
        .tag-cloud { display:flex;flex-wrap:wrap;gap:8px;margin-top:12px; }
        .tag-chip { font-size:0.75rem;padding:4px 10px;border-radius:20px;background:rgba(255,255,255,0.06);color:var(--text-secondary);border:1px solid rgba(255,255,255,0.1);cursor:pointer;transition:all 0.2s; }
        .tag-chip:hover, .tag-chip.active { background:rgba(99,102,241,0.2);color:var(--accent-hover);border-color:var(--accent); }

        /* Counterparty Past History */
        .history-card { background:var(--bg-card);backdrop-filter:blur(24px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px; }
        .history-list { display:flex;flex-direction:column;gap:12px;margin-top:12px; }
        .history-item { display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.04); }
        .history-item-left { display:flex;flex-direction:column;gap:2px; }
        .history-date { font-size:0.75rem;color:var(--text-muted); }
        .history-note { font-size:0.82rem;font-weight:500;color:var(--text-primary); }
        .history-amount { font-size:0.85rem;font-weight:700; }

        /* Modals & Toast */
        .modal-overlay { position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.3s ease;padding:20px; }
        .modal-overlay.active { opacity:1;pointer-events:all; }
        .modal-card { background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);width:100%;max-width:520px;padding:28px;box-shadow:0 20px 50px rgba(0,0,0,0.6);transform:translateY(20px);transition:transform 0.3s ease; }
        .modal-overlay.active .modal-card { transform:translateY(0); }
        .modal-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px; }
        .modal-title { font-size:1.15rem;font-weight:700; }
        .modal-close { background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.2rem; }
        .modal-close:hover { color:var(--text-primary); }
        .form-group { margin-bottom:16px; }
        .form-label { display:block;font-size:0.8rem;color:var(--text-secondary);font-weight:600;margin-bottom:6px; }
        .form-control { width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border);border-radius:10px;padding:10px 14px;color:var(--text-primary);font-size:0.88rem;outline:none; }
        .form-control:focus { border-color:var(--accent); }

        .toast { position:fixed;bottom:30px;right:30px;background:#1e293b;border:1px solid var(--accent);color:#fff;padding:14px 22px;border-radius:12px;font-size:0.88rem;font-weight:600;box-shadow:0 10px 30px rgba(0,0,0,0.5);display:flex;align-items:center;gap:10px;z-index:2000;transform:translateY(100px);opacity:0;transition:all 0.3s cubic-bezier(0.16,1,0.3,1); }
        .toast.show { transform:translateY(0);opacity:1; }

        @media (max-width: 1024px) {
            .details-grid { grid-template-columns:1fr; }
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); }
            .main-content { margin-left:0;padding:20px; }
        }
    </style>
</head>
<body>

    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>
    <div class="bg-orb bg-orb--3"></div>

    <div class="app-layout">
        <!-- ── Sidebar Navigation ── -->
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
                <a href="receipt.php?id=<?php echo urlencode($txn['id']); ?>" class="nav-item active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Transaction Details
                </a>
                <a href="notifications.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifications
                </a>

                <div class="nav-section-label">Account</div>
                <a href="profile.php" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
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

        <!-- ── Main Content Area ── -->
        <main class="main-content">
            <!-- Breadcrumb Header -->
            <div class="breadcrumbs">
                <a href="dashboard.php">Dashboard</a>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Transactions</span>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Transaction Overview</span>
            </div>

            <!-- Page Top Header -->
            <div class="top-header">
                <div class="header-title-area">
                    <a href="dashboard.php" class="back-btn" title="Back to Dashboard">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <div class="page-title-area">
                        <h1>Transaction <span class="txn-id-badge"><?php echo htmlspecialchars($txn['id']); ?></span></h1>
                    </div>
                </div>

                <div class="action-toolbar">
                    <a href="receipt.php?id=<?php echo urlencode($txn['id']); ?>" class="btn-secondary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        View Official Receipt
                    </a>
                    <button class="btn-secondary" onclick="shareTransaction()">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        Share
                    </button>
                    <button class="btn-primary" onclick="openDisputeModal()">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Report Issue
                    </button>
                </div>
            </div>

            <!-- Details Main Grid -->
            <div class="details-grid">
                <!-- Left Main Column -->
                <div class="left-col">
                    <!-- Hero Card -->
                    <div class="hero-card status-<?php echo $status; ?>">
                        <div class="hero-top-bar">
                            <div class="status-pill status-<?php echo $status; ?>">
                                <span class="status-pulse"></span>
                                <?php echo ucfirst($status); ?>
                            </div>
                            <div class="category-tag">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm0 13V2a6 6 0 1 1 0 12z"/></svg>
                                <?php echo htmlspecialchars($txn['category'] ?? 'Transfer'); ?>
                            </div>
                        </div>

                        <!-- Amount Hero -->
                        <div class="amount-display">
                            <div class="amount-label"><?php echo $isCredit ? 'Amount Received' : 'Amount Sent'; ?></div>
                            <div class="amount-val <?php echo $status === 'failed' ? 'failed' : ($isCredit ? 'credit' : 'debit'); ?>">
                                <?php echo ($isCredit ? '+ ₹' : '₹') . number_format($txn['amount'], 2); ?>
                            </div>
                            <?php if (!empty($txn['cashback']) && $txn['cashback'] > 0): ?>
                                <div class="cashback-badge">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    + ₹<?php echo number_format($txn['cashback'], 2); ?> Cashback Rewarded
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Transfer Flow Diagram -->
                        <div class="transfer-flow">
                            <!-- Sender -->
                            <div class="flow-party">
                                <div class="party-avatar <?php echo $isCredit ? 'counter-avatar' : 'user-avatar'; ?>">
                                    <?php echo $isCredit ? $counterInitials : $initials; ?>
                                </div>
                                <div class="party-info">
                                    <div class="party-name"><?php echo htmlspecialchars($txn['from_name']); ?></div>
                                    <div class="party-upi"><?php echo htmlspecialchars($txn['from_upi']); ?></div>
                                    <div class="party-bank"><?php echo htmlspecialchars($txn['from_bank']); ?></div>
                                </div>
                            </div>

                            <!-- Animated Arrow -->
                            <div class="flow-arrow-container">
                                <div class="flow-line">
                                    <div class="flow-dot"></div>
                                </div>
                                <span class="flow-method-tag"><?php echo htmlspecialchars($txn['method']); ?></span>
                            </div>

                            <!-- Receiver -->
                            <div class="flow-party" style="justify-content:flex-end;text-align:right;">
                                <div class="party-info">
                                    <div class="party-name"><?php echo htmlspecialchars($txn['to_name']); ?></div>
                                    <div class="party-upi"><?php echo htmlspecialchars($txn['to_upi']); ?></div>
                                    <div class="party-bank"><?php echo htmlspecialchars($txn['to_bank']); ?></div>
                                </div>
                                <div class="party-avatar <?php echo $isCredit ? 'user-avatar' : 'counter-avatar'; ?>">
                                    <?php echo $isCredit ? $initials : $counterInitials; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Audit Details Card -->
                    <div class="section-card">
                        <div class="card-header">
                            <div class="card-title">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Transaction Audit Metadata
                            </div>
                            <span style="font-size:0.78rem;color:var(--text-muted);">Cryptographically Verified</span>
                        </div>

                        <div class="meta-grid">
                            <div class="meta-item">
                                <span class="meta-label">Transaction Reference</span>
                                <span class="meta-val mono-val">
                                    <?php echo htmlspecialchars($txn['ref']); ?>
                                    <button class="copy-btn" onclick="copyText('<?php echo htmlspecialchars($txn['ref']); ?>', 'Ref ID Copied!')" title="Copy Reference">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </span>
                            </div>

                            <div class="meta-item">
                                <span class="meta-label">Bank UTR / RRN</span>
                                <span class="meta-val mono-val">
                                    <?php echo htmlspecialchars($txn['utr']); ?>
                                    <button class="copy-btn" onclick="copyText('<?php echo htmlspecialchars($txn['utr']); ?>', 'UTR Copied!')" title="Copy UTR">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </span>
                            </div>

                            <div class="meta-item">
                                <span class="meta-label">Date & Time</span>
                                <span class="meta-val"><?php echo htmlspecialchars($txn['date']); ?></span>
                            </div>

                            <div class="meta-item">
                                <span class="meta-label">Payment Channel</span>
                                <span class="meta-val"><?php echo htmlspecialchars($txn['device'] ?? 'PaySim Web App'); ?></span>
                            </div>

                            <div class="meta-item">
                                <span class="meta-label">Security & Auth</span>
                                <span class="meta-val" style="color:var(--success);font-size:0.85rem;">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <?php echo htmlspecialchars($txn['verification'] ?? '2FA Verified'); ?>
                                </span>
                            </div>

                            <div class="meta-item">
                                <span class="meta-label">IP Address & Origin</span>
                                <span class="meta-val mono-val"><?php echo htmlspecialchars($txn['ip_address'] ?? '127.0.0.1'); ?> (<?php echo htmlspecialchars($txn['location'] ?? 'India'); ?>)</span>
                            </div>
                        </div>

                        <!-- Remarks/Notes -->
                        <?php if (!empty($txn['note'])): ?>
                            <div style="margin-top:20px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.06);">
                                <span class="meta-label">Payment Remarks / Note</span>
                                <div style="margin-top:6px;background:rgba(0,0,0,0.25);padding:12px 16px;border-radius:10px;font-size:0.88rem;color:var(--text-primary);border-left:3px solid var(--accent);">
                                    "<?php echo htmlspecialchars($txn['note']); ?>"
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Fee & Calculation Box -->
                        <div class="calc-box">
                            <div class="calc-row">
                                <span>Base Transaction Amount</span>
                                <span>₹<?php echo number_format($txn['amount'], 2); ?></span>
                            </div>
                            <div class="calc-row">
                                <span>Processing & Platform Fee</span>
                                <span style="color:var(--success);">FREE (₹0.00)</span>
                            </div>
                            <div class="calc-row">
                                <span>GST / Taxes</span>
                                <span>₹0.00</span>
                            </div>
                            <div class="calc-row total">
                                <span>Total Net Settle Amount</span>
                                <span>₹<?php echo number_format($txn['amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Lifecycle Timeline -->
                    <div class="section-card">
                        <div class="card-header">
                            <div class="card-title">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Clearance & Audit Timeline
                            </div>
                            <span style="font-size:0.75rem;color:var(--text-muted);">Real-time NPCI Node Execution</span>
                        </div>

                        <div class="timeline">
                            <?php foreach ($txn['timeline'] as $step): ?>
                                <div class="timeline-step <?php echo $step['status']; ?>">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <span class="step-title"><?php echo htmlspecialchars($step['title']); ?></span>
                                            <span class="step-time"><?php echo htmlspecialchars($step['time']); ?></span>
                                        </div>
                                        <span class="step-desc"><?php echo htmlspecialchars($step['desc']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Column -->
                <div class="right-col">
                    <!-- Quick Actions Card -->
                    <div class="action-card">
                        <div class="card-header" style="margin-bottom:14px;padding-bottom:10px;">
                            <div class="card-title" style="font-size:0.95rem;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Quick Actions
                            </div>
                        </div>

                        <div class="action-menu">
                            <a href="send-money.php?upi=<?php echo urlencode($counterUpi); ?>&amount=<?php echo urlencode($txn['amount']); ?>" class="action-btn-item">
                                <div class="action-btn-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </div>
                                Repeat Payment
                            </a>

                            <a href="request-money.php?upi=<?php echo urlencode($counterUpi); ?>&amount=<?php echo urlencode($txn['amount']); ?>" class="action-btn-item">
                                <div class="action-btn-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                Request Refund / Money
                            </a>

                            <button class="action-btn-item" onclick="openSplitModal()">
                                <div class="action-btn-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                Split Expense with Friends
                            </button>

                            <button class="action-btn-item" onclick="window.print()">
                                <div class="action-btn-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </div>
                                Print Details Page
                            </button>
                        </div>
                    </div>

                    <!-- Personal Notes & Tags Card -->
                    <div class="note-card">
                        <div class="card-header" style="margin-bottom:14px;padding-bottom:10px;">
                            <div class="card-title" style="font-size:0.95rem;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h8"/></svg>
                                Notes & Accounting Tags
                            </div>
                        </div>

                        <textarea class="note-input" id="personalNote" placeholder="Add private note for your bookkeeping (e.g. Tax deductible expense, team dinner)..."></textarea>
                        <button class="btn-secondary" style="width:100%;margin-top:10px;justify-content:center;font-size:0.8rem;" onclick="saveNote()">
                            Save Note
                        </button>

                        <div class="tag-cloud">
                            <span class="tag-chip active" onclick="toggleTag(this)">#Personal</span>
                            <span class="tag-chip" onclick="toggleTag(this)">#Business</span>
                            <span class="tag-chip" onclick="toggleTag(this)">#TaxDeductible</span>
                            <span class="tag-chip" onclick="toggleTag(this)">#Reimbursable</span>
                        </div>
                    </div>

                    <!-- Counterparty History Card -->
                    <?php if (!empty($txn['history'])): ?>
                        <div class="history-card">
                            <div class="card-header" style="margin-bottom:12px;padding-bottom:8px;">
                                <div class="card-title" style="font-size:0.95rem;">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Recent History with <?php echo htmlspecialchars($counterName); ?>
                                </div>
                            </div>

                            <div class="history-list">
                                <?php foreach ($txn['history'] as $h): ?>
                                    <div class="history-item">
                                        <div class="history-item-left">
                                            <span class="history-note"><?php echo htmlspecialchars($h['note']); ?></span>
                                            <span class="history-date"><?php echo htmlspecialchars($h['date']); ?></span>
                                        </div>
                                        <span class="history-amount <?php echo $h['type'] === 'credit' ? 'credit' : 'debit'; ?>" style="color:<?php echo $h['type'] === 'credit' ? 'var(--success)' : 'var(--text-primary)'; ?>;">
                                            <?php echo ($h['type'] === 'credit' ? '+ ₹' : '- ₹') . number_format($h['amount'], 2); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- ── Report Dispute Modal ── -->
    <div class="modal-overlay" id="disputeModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Report Issue / Raise Dispute</div>
                <button class="modal-close" onclick="closeDisputeModal()">&times;</button>
            </div>
            <form onsubmit="submitDispute(event)">
                <div class="form-group">
                    <label class="form-label">Transaction Reference</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($txn['ref']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Issue Category</label>
                    <select class="form-control" required>
                        <option value="">Select reason...</option>
                        <option value="1">Money deducted but recipient didn't receive</option>
                        <option value="2">Incorrect amount charged</option>
                        <option value="3">Duplicate transaction</option>
                        <option value="4">Fraudulent / Unrecognized charge</option>
                        <option value="5">Other issue</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Description of Problem</label>
                    <textarea class="form-control" rows="3" placeholder="Provide details for support team..." required></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                    <button type="button" class="btn-secondary" onclick="closeDisputeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Dispute Ticket</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Split Expense Modal ── -->
    <div class="modal-overlay" id="splitModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Split ₹<?php echo number_format($txn['amount'], 2); ?> Expense</div>
                <button class="modal-close" onclick="closeSplitModal()">&times;</button>
            </div>
            <div>
                <div class="form-group">
                    <label class="form-label">Number of People (including you)</label>
                    <input type="number" id="splitCount" class="form-control" value="2" min="2" max="20" oninput="calculateSplit()">
                </div>
                <div class="calc-box" style="margin-bottom:16px;">
                    <div class="calc-row">
                        <span>Total Expense</span>
                        <span>₹<?php echo number_format($txn['amount'], 2); ?></span>
                    </div>
                    <div class="calc-row total">
                        <span>Each Person Pays</span>
                        <span id="splitEachVal" style="color:var(--accent-hover);">₹<?php echo number_format($txn['amount'] / 2, 2); ?></span>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" class="btn-secondary" onclick="closeSplitModal()">Close</button>
                    <button type="button" class="btn-primary" onclick="createSplitRequests()">Send Request Money Links</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Toast Notification Container ── -->
    <div class="toast" id="toast">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span id="toastMsg">Action completed successfully</span>
    </div>

    <script>
        const txnAmount = <?php echo (float)$txn['amount']; ?>;

        function copyText(text, message) {
            navigator.clipboard.writeText(text).then(() => {
                showToast(message || 'Copied to clipboard!');
            }).catch(() => {
                showToast('Failed to copy');
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMsg');
            toastMsg.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function shareTransaction() {
            if (navigator.share) {
                navigator.share({
                    title: 'PaySim Transaction <?php echo htmlspecialchars($txn['id']); ?>',
                    text: 'PaySim payment of ₹<?php echo number_format($txn['amount'], 2); ?> for <?php echo htmlspecialchars($txn['note']); ?>',
                    url: window.location.href
                }).catch(() => {});
            } else {
                copyText(window.location.href, 'Transaction URL copied for sharing!');
            }
        }

        function openDisputeModal() {
            document.getElementById('disputeModal').classList.add('active');
        }

        function closeDisputeModal() {
            document.getElementById('disputeModal').classList.remove('active');
        }

        function submitDispute(e) {
            e.preventDefault();
            closeDisputeModal();
            const ticketId = 'TKT-' + Math.floor(100000 + Math.random() * 900000);
            showToast('Dispute ticket raised! Tracking ID: ' + ticketId);
        }

        function openSplitModal() {
            document.getElementById('splitModal').classList.add('active');
            calculateSplit();
        }

        function closeSplitModal() {
            document.getElementById('splitModal').classList.remove('active');
        }

        function calculateSplit() {
            const count = parseInt(document.getElementById('splitCount').value) || 2;
            const each = (txnAmount / count).toFixed(2);
            document.getElementById('splitEachVal').textContent = '₹' + Number(each).toLocaleString('en-IN', {minimumFractionDigits: 2});
        }

        function createSplitRequests() {
            closeSplitModal();
            const count = parseInt(document.getElementById('splitCount').value) || 2;
            const each = (txnAmount / count).toFixed(2);
            window.location.href = `request-money.php?amount=${each}&note=${encodeURIComponent('Split for TXN <?php echo $txn['id']; ?>')}`;
        }

        function saveNote() {
            const note = document.getElementById('personalNote').value;
            localStorage.setItem('paysim_note_<?php echo $txn['id']; ?>', note);
            showToast('Personal note saved for this transaction!');
        }

        function toggleTag(el) {
            el.classList.toggle('active');
        }

        // Load saved note from localStorage on boot
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('paysim_note_<?php echo $txn['id']; ?>');
            if (saved) {
                document.getElementById('personalNote').value = saved;
            }
        });
    </script>
</body>
</html>
