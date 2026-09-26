<?php
/**
 * PaySim API - Get User Wallet Balance
 * GET /api/user/balance.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/AccountService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);
$accountService = new AccountService();
$balance = $accountService->getBalance($auth['id']);
$wallet = $accountService->getWallet($auth['id']);

Response::success([
    'balance'        => $balance,
    'currency'       => 'INR',
    'account_number' => $wallet['account_number'] ?? null,
    'status'         => $wallet['status'] ?? 'active',
], 'Balance retrieved successfully.');
