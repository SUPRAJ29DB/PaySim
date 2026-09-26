<?php
/**
 * PaySim API - Freeze / Unfreeze User Account
 * POST /api/admin/freeze-user.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/admin-auth.php';
require_once dirname(dirname(__DIR__)) . '/services/AdminService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

checkAdminAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$userId = (int) ($input['user_id'] ?? 0);
$action = strtolower(trim($input['action'] ?? 'freeze')); // 'freeze' or 'unfreeze'
$reason = trim($input['reason'] ?? 'Flagged by compliance administrator');

if ($userId <= 0) {
    Response::error('Invalid user_id parameter.', 422);
}

$adminService = new AdminService();

if ($action === 'unfreeze') {
    $adminService->unfreezeUser($userId);
    Response::success(null, "User #{$userId} has been unfreezed and reactivated.");
} else {
    $adminService->freezeUser($userId, $reason);
    Response::success(null, "User #{$userId} has been frozen. Transactions restricted.");
}
