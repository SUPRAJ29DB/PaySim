<?php
/**
 * PaySim API - Link Bank Account
 * POST /api/account/create.php
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

$accountService = new AccountService();
$result = $accountService->linkBankAccount($auth['id'], $input);

if (!$result['success']) {
    Response::error($result['message'], 400);
}

Response::success($result, 'Bank account linked successfully.', 201);
