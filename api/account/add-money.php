<?php
/**
 * PaySim API - Add Money to Wallet
 * POST /api/account/add-money.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/AccountService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$amount = (float) ($input['amount'] ?? 0);
$method = trim($input['payment_method'] ?? 'Bank Account');
$note   = trim($input['note'] ?? 'Wallet Top-up');

if ($amount <= 0) {
    Response::error('Please enter a valid amount greater than zero.', 422);
}

$accountService = new AccountService();
$result = $accountService->addMoney($auth['id'], $amount, $method, $note);

if (!$result['success']) {
    Response::error($result['message'], 400);
}

Response::success($result, 'Funds added to wallet successfully.');
