<?php
/**
 * PaySim API - Validate & Resolve Beneficiary UPI ID
 * GET or POST /api/payment/validate-upi.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/services/UserService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$upiId = trim($_REQUEST['upi_id'] ?? '');

if (empty($upiId)) {
    // Check raw input
    $input = json_decode(file_get_contents('php://input'), true);
    $upiId = trim($input['upi_id'] ?? '');
}

if (empty($upiId)) {
    Response::error('Please provide a UPI ID (upi_id parameter).', 422);
}

$userService = new UserService();
$result = $userService->resolveUpi($upiId);

if (!$result['valid']) {
    Response::error($result['message'] ?? 'Invalid UPI ID', 404);
}

Response::success($result, 'UPI ID resolved successfully.');
