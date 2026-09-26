-- ============================================================================
-- PaySim - Sample Transactions, Requests, Notifications & Audit Logs
-- ============================================================================

USE `paysim`;

-- Transactions
INSERT INTO `transactions` (`id`, `txn_id`, `ref_no`, `utr`, `sender_id`, `receiver_id`, `sender_upi`, `receiver_upi`, `sender_name`, `receiver_name`, `amount`, `fee`, `cashback`, `txn_type`, `status`, `note`, `category`, `payment_method`, `created_at`)
VALUES
(1, 'TXN001', 'PSM926814750001', 'UTR429184029481', 1, 2, 'suprakash@paysim', 'ravi@oksbi', 'Suprakash Ghosh', 'Ravi Kumar', 500.00, 0.00, 15.00, 'transfer', 'completed', 'Lunch split at Charcoal Grill', 'Food & Dining', 'UPI Transfer', '2026-09-18 14:32:00'),
(2, 'TXN002', 'PSM926814750002', 'UTR429184029482', NULL, 1, 'salary@techcorp', 'suprakash@paysim', 'TechCorp India Pvt Ltd', 'Suprakash Ghosh', 12400.00, 0.00, 0.00, 'credit', 'completed', 'Monthly Consultant Salary', 'Salary', 'NEFT Credit', '2026-09-17 09:00:00'),
(3, 'TXN003', 'PSM926814750003', 'UTR429184029483', 1, NULL, 'suprakash@paysim', 'netflix@icici', 'Suprakash Ghosh', 'Netflix India', 649.00, 0.00, 5.00, 'debit', 'completed', 'Monthly Premium 4K Streaming', 'Entertainment', 'UPI Mandate', '2026-09-16 18:45:00'),
(4, 'TXN004', 'PSM926814750004', 'UTR429184029484', 2, 1, 'priya@paytm', 'suprakash@paysim', 'Priya Sharma', 'Suprakash Ghosh', 1200.00, 0.00, 0.00, 'transfer', 'completed', 'Cab & Hotel Reimbursement', 'Reimbursement', 'UPI Transfer', '2026-09-15 11:20:00'),
(5, 'TXN005', 'PSM926814750005', 'UTR429184029485', 1, NULL, 'suprakash@paysim', 'swiggy@icici', 'Suprakash Ghosh', 'Swiggy Food Delivery', 342.00, 0.00, 8.00, 'qr_pay', 'completed', 'Dinner Order #8921', 'Food & Dining', 'Dynamic QR Pay', '2026-09-14 20:10:00'),
(6, 'TXN006', 'PSM926814750006', 'UTR429184029486', 1, 2, 'suprakash@paysim', 'amit@ybl', 'Suprakash Ghosh', 'Amit Das', 200.00, 0.00, 0.00, 'transfer', 'pending', 'Weekend Chai & Snacks', 'Personal', 'UPI Transfer', '2026-09-13 16:00:00');

-- Payment Requests
INSERT INTO `payment_requests` (`id`, `request_code`, `requester_id`, `payer_id`, `requester_upi`, `payer_upi`, `amount`, `note`, `status`, `expires_at`, `created_at`)
VALUES
(1, 'REQ10001', 2, 1, 'priya@paytm', 'suprakash@paysim', 750.00, 'Movie Tickets Share', 'pending', '2026-10-01 23:59:59', '2026-09-20 10:00:00'),
(2, 'REQ10002', 1, 2, 'suprakash@paysim', 'demo@paysim', 1200.00, 'Dinner Bill Split', 'accepted', '2026-09-25 18:00:00', '2026-09-19 12:30:00');

-- Notifications
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `action_url`, `created_at`)
VALUES
(1, 1, 'Money Received: ₹12,400.00', 'Salary credited from TechCorp India Pvt Ltd to your wallet.', 'success', 1, 'transactions.php', '2026-09-17 09:01:00'),
(2, 1, 'Cashback Earned: ₹15.00', 'You earned cashback on your UPI transaction to Ravi Kumar.', 'reward', 0, 'dashboard.php', '2026-09-18 14:35:00'),
(3, 1, 'New Payment Request Received', 'Priya Sharma requested ₹750.00 for Movie Tickets Share.', 'alert', 0, 'request-money.php', '2026-09-20 10:05:00'),
(4, 1, 'Security Login Alert', 'New login detected from IP 127.0.0.1 on Windows Desktop.', 'security', 0, 'settings.php', '2026-09-26 14:00:00');

-- Audit Logs
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `payload`, `created_at`)
VALUES
(1, 1, 'USER_LOGIN', 'users', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '{\"status\":\"success\"}', '2026-09-26 14:00:00'),
(2, 1, 'TRANSFER_SUCCESS', 'transactions', 'TXN001', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '{\"amount\":500,\"to\":\"ravi@oksbi\"}', '2026-09-18 14:32:00'),
(3, 3, 'ADMIN_ACCESS', 'admin_portal', 'dashboard', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '{\"action\":\"view_metrics\"}', '2026-09-26 14:15:00');
