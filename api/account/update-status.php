<?php
/**
 * PaySim API - Update Account Status
 * POST /api/account/update-status.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/models/Account.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$accountId = (int) ($input['account_id'] ?? 0);
$status    = trim($input['status'] ?? 'active');

$validStatuses = ['active', 'frozen', 'suspended', 'closed'];
if (!in_array($status, $validStatuses)) {
    Response::error('Invalid account status provided.', 422);
}

$accountModel = new Account();
$acc = $accountModel->findByUserId($auth['id'], 'wallet');

if (!$acc || (int)$acc['id'] !== $accountId) {
    Response::forbidden('You do not have permission to modify this account.');
}

$accountModel->updateStatus($accountId, $status);

Response::success([
    'account_id' => $accountId,
    'status'     => $status,
], 'Account status updated successfully.');
