-- ============================================================================
-- PaySim - Seed Data (System Admins, Base Accounts, Configuration)
-- Demo user passwords: pay@123 (suprakash), demo@123 (demo).
-- Demo UPI PIN: 123456. No administrator account is seeded; provision one securely.
-- ============================================================================

USE `paysim`;

-- Users (bcrypt hashes)
INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `phone`, `password_hash`, `upi_id`, `upi_pin_hash`, `role`, `status`)
VALUES
(1, 'Suprakash Ghosh', 'suprakash', 'suprakash@paysim.com', '+91 9876543210', '$2y$10$eE0o9hI21gQx1Y59v7vCkeu9Fm99Jffz89iXbcvzYvD.Y6U6a8e6u', 'suprakash@paysim', '$2y$10$k1a4h0lGfH8m7FvH2kLkeuegQjJ22Qffz89iXbcvzYvD.Y6U6a8e6u', 'user', 'active'),
(2, 'Demo User', 'demo', 'demo@paysim.com', '+91 9876500000', '$2y$10$w09u7uR41gQx1Y59v7vCkeu9Fm99Jffz89iXbcvzYvD.Y6U6a8e6u', 'demo@paysim', '$2y$10$k1a4h0lGfH8m7FvH2kLkeuegQjJ22Qffz89iXbcvzYvD.Y6U6a8e6u', 'user', 'active'));

-- User Accounts (Wallet balances)
INSERT INTO `accounts` (`id`, `user_id`, `account_number`, `account_type`, `balance`, `currency`, `status`)
VALUES
(1, 1, 'ACC100019283', 'wallet', 24580.75, 'INR', 'active'),
(2, 2, 'ACC100019284', 'wallet', 15000.00, 'INR', 'active'),
(3, 3, 'ACC100019285', 'wallet', 500000.00, 'INR', 'active');

-- Bank Accounts
INSERT INTO `bank_accounts` (`id`, `user_id`, `bank_name`, `account_number`, `ifsc_code`, `branch`, `account_type`, `balance`, `is_primary`, `color_gradient`)
VALUES
(1, 1, 'State Bank of India', '•••• 4829', 'SBIN0001234', 'Main Branch, Kolkata', 'Savings Account', 142500.50, 1, 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)'),
(2, 1, 'HDFC Bank', '•••• 9102', 'HDFC0004567', 'Park Street, Kolkata', 'Salary Account', 89200.00, 0, 'linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)'),
(3, 1, 'ICICI Bank', '•••• 1109', 'ICIC0008910', 'Salt Lake, Kolkata', 'Savings Account', 23150.25, 0, 'linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%)');

-- System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`)
VALUES
('site_name', 'PaySim', 'Platform brand name'),
('max_daily_limit', '100000', 'Maximum 24h transfer limit in INR'),
('max_single_limit', '50000', 'Maximum single transaction limit in INR'),
('maintenance_mode', '0', 'Toggle platform maintenance mode (1=active, 0=inactive)'),
('cashback_percent', '1.5', 'Cashback percentage on qualifying transactions'),
('enable_qr_pay', '1', 'Enable Dynamic and Static QR Payments');
