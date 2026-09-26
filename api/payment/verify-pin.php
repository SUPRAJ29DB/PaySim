<?php
/**
 * PaySim API - Verify User UPI PIN
 * POST /api/payment/verify-pin.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/UserService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$pin   = trim((string)($input['pin'] ?? ''));

if (empty($pin)) {
    Response::error('Please enter your UPI PIN.', 422);
}

$userService = new UserService();
$valid = $userService->verifyUpiPin($auth['id'], $pin);

if (!$valid) {
    Response::error('Incorrect UPI PIN. Please try again.', 401);
}

Response::success(['valid' => true], 'UPI PIN verified successfully.');
