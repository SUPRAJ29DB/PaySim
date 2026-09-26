-- ============================================================================
-- PaySim - Next-Gen UPI Payment Simulator Database Schema
-- Compatible with MySQL 8.0+, MariaDB 10.4+, and PDO
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `paysim` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `paysim`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `payment_requests`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `bank_accounts`;
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `system_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Table: users
-- ----------------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `upi_id` VARCHAR(50) NOT NULL UNIQUE,
    `upi_pin_hash` VARCHAR(255) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('user', 'admin', 'superadmin') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'frozen', 'suspended', 'closed') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_users_username` (`username`),
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_upi_id` (`upi_id`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: accounts (User Wallet/Primary Virtual Accounts)
-- ----------------------------------------------------------------------------
CREATE TABLE `accounts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `account_number` VARCHAR(30) NOT NULL UNIQUE,
    `account_type` VARCHAR(20) NOT NULL DEFAULT 'wallet',
    `balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
    `status` ENUM('active', 'frozen', 'suspended', 'closed') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_accounts_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_accounts_user_id` (`user_id`),
    INDEX `idx_accounts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: bank_accounts (Linked Real-World / Simulator Bank Accounts)
-- ----------------------------------------------------------------------------
CREATE TABLE `bank_accounts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `bank_name` VARCHAR(100) NOT NULL,
    `account_number` VARCHAR(30) NOT NULL,
    `ifsc_code` VARCHAR(20) NOT NULL,
    `branch` VARCHAR(100) DEFAULT '',
    `account_type` VARCHAR(30) NOT NULL DEFAULT 'Savings Account',
    `balance` DECIMAL(15,2) NOT NULL DEFAULT 50000.00,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `color_gradient` VARCHAR(100) DEFAULT '',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_bank_accounts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_bank_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: transactions (UPI & Wallet Ledger)
-- ----------------------------------------------------------------------------
CREATE TABLE `transactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `txn_id` VARCHAR(50) NOT NULL UNIQUE,
    `ref_no` VARCHAR(50) NOT NULL UNIQUE,
    `utr` VARCHAR(50) NOT NULL UNIQUE,
    `sender_id` INT UNSIGNED NULL,
    `receiver_id` INT UNSIGNED NULL,
    `sender_upi` VARCHAR(50) NOT NULL,
    `receiver_upi` VARCHAR(50) NOT NULL,
    `sender_name` VARCHAR(100) NOT NULL,
    `receiver_name` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `cashback` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `txn_type` ENUM('debit', 'credit', 'transfer', 'add_money', 'qr_pay', 'request') NOT NULL DEFAULT 'transfer',
    `status` ENUM('pending', 'completed', 'failed', 'reversed') NOT NULL DEFAULT 'completed',
    `note` TEXT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'Transfer',
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'UPI',
    `device_info` VARCHAR(255) DEFAULT 'PaySim Web Client',
    `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_txns_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_txns_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_txns_txn_id` (`txn_id`),
    INDEX `idx_txns_sender_id` (`sender_id`),
    INDEX `idx_txns_receiver_id` (`receiver_id`),
    INDEX `idx_txns_status` (`status`),
    INDEX `idx_txns_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: payment_requests (Collect / Request Money Requests)
-- ----------------------------------------------------------------------------
CREATE TABLE `payment_requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_code` VARCHAR(50) NOT NULL UNIQUE,
    `requester_id` INT UNSIGNED NOT NULL,
    `payer_id` INT UNSIGNED NULL,
    `requester_upi` VARCHAR(50) NOT NULL,
    `payer_upi` VARCHAR(50) NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `note` TEXT NULL,
    `status` ENUM('pending', 'accepted', 'declined', 'expired') NOT NULL DEFAULT 'pending',
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_pay_req_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pay_req_payer` FOREIGN KEY (`payer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_pay_req_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: notifications (User and System Alerts)
-- ----------------------------------------------------------------------------
CREATE TABLE `notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `type` VARCHAR(30) NOT NULL DEFAULT 'info',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `action_url` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_notif_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: audit_logs (Security & Admin Activity Trail)
-- ----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` VARCHAR(50) NULL,
    `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
    `user_agent` TEXT NULL,
    `payload` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: system_settings (Platform Configuration)
-- ----------------------------------------------------------------------------
CREATE TABLE `system_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NOT NULL,
    `description` VARCHAR(255) NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
