<?php
/**
 * PaySim - Global Constants Configuration
 */

// Application Information
if (!defined('APP_NAME')) {
    define('APP_NAME', 'PaySim');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}
if (!defined('APP_TAGLINE')) {
    define('APP_TAGLINE', 'Next-Gen UPI Payment Simulator');
}
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost/PaySim');
}

// Transaction Statuses
if (!defined('TXN_STATUS_PENDING')) {
    define('TXN_STATUS_PENDING', 'pending');
    define('TXN_STATUS_COMPLETED', 'completed');
    define('TXN_STATUS_FAILED', 'failed');
    define('TXN_STATUS_REVERSED', 'reversed');
}

// Transaction Types
if (!defined('TXN_TYPE_DEBIT')) {
    define('TXN_TYPE_DEBIT', 'debit');
    define('TXN_TYPE_CREDIT', 'credit');
    define('TXN_TYPE_TRANSFER', 'transfer');
    define('TXN_TYPE_ADD_MONEY', 'add_money');
    define('TXN_TYPE_QR_PAY', 'qr_pay');
    define('TXN_TYPE_REQUEST', 'request');
}

// Payment Request Statuses
if (!defined('REQ_STATUS_PENDING')) {
    define('REQ_STATUS_PENDING', 'pending');
    define('REQ_STATUS_ACCEPTED', 'accepted');
    define('REQ_STATUS_DECLINED', 'declined');
    define('REQ_STATUS_EXPIRED', 'expired');
}

// Account Statuses
if (!defined('ACC_STATUS_ACTIVE')) {
    define('ACC_STATUS_ACTIVE', 'active');
    define('ACC_STATUS_FROZEN', 'frozen');
    define('ACC_STATUS_SUSPENDED', 'suspended');
    define('ACC_STATUS_CLOSED', 'closed');
}

// User Roles
if (!defined('ROLE_USER')) {
    define('ROLE_USER', 'user');
    define('ROLE_ADMIN', 'admin');
    define('ROLE_SUPERADMIN', 'superadmin');
}

// Limits & Thresholds
if (!defined('MAX_DAILY_TRANSFER_LIMIT')) {
    define('MAX_DAILY_TRANSFER_LIMIT', 100000.00); // ₹1,00,000 NPCI Standard
}
if (!defined('MAX_SINGLE_TXN_LIMIT')) {
    define('MAX_SINGLE_TXN_LIMIT', 50000.00);
}
if (!defined('MIN_TXN_AMOUNT')) {
    define('MIN_TXN_AMOUNT', 1.00);
}
if (!defined('DEFAULT_UPI_HANDLE')) {
    define('DEFAULT_UPI_HANDLE', '@paysim');
}

// Path Definitions
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ROOT_PATH . '/config');
}
if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', ROOT_PATH . '/uploads');
}
if (!defined('LOGS_PATH')) {
    define('LOGS_PATH', ROOT_PATH . '/logs');
}
