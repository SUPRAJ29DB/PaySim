<?php
/**
 * PaySim API - Get User Account Details & Linked Banks
 * GET /api/account/details.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/models/Account.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);
$accountModel = new Account();

$wallet = $accountModel->findByUserId($auth['id'], 'wallet');
$banks  = $accountModel->getBankAccounts($auth['id']);

Response::success([
    'wallet' => $wallet,
    'banks'  => $banks,
], 'Account details retrieved successfully.');
