<?php
/**
 * PaySim API - QR Payment Processing & Verification
 * POST /api/payment/qr-pay.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/PaymentService.php';
require_once dirname(dirname(__DIR__)) . '/services/QRService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action  = $input['action'] ?? 'pay'; // 'parse' or 'pay'
$qrData  = trim($input['qr_data'] ?? '');

if (empty($qrData)) {
    Response::error('QR code payload data is required.', 422);
}

// Parse QR
$parsed = QRService::parseUpiUri($qrData);
if (!$parsed) {
    Response::error('Invalid UPI QR code payload.', 400);
}

if ($action === 'parse') {
    // Only parse and resolve info
    $userService = new UserService();
    $resolved = $userService->resolveUpi($parsed['upi_id']);
    Response::success([
        'upi_id' => $parsed['upi_id'],
        'name'   => $parsed['name'] ?: $resolved['name'],
        'amount' => $parsed['amount'],
        'note'   => $parsed['note'],
    ], 'QR payload parsed successfully.');
}

// Otherwise execute QR payment
$amount = !empty($parsed['amount']) ? (float)$parsed['amount'] : (float)($input['amount'] ?? 0);
$pin    = trim((string)($input['pin'] ?? ''));
$note   = trim($input['note'] ?? $parsed['note']);

if ($amount <= 0 || empty($pin)) {
    Response::error('Valid amount and UPI PIN are required to complete payment.', 422);
}

$paymentService = new PaymentService();
$result = $paymentService->sendMoney(
    $auth['id'],
    $parsed['upi_id'],
    $amount,
    $pin,
    $note ?: 'QR Code Scan Payment',
    'QR Payment',
    'Dynamic QR Pay'
);

if (!$result['success']) {
    Response::error($result['message'], 400);
}

Response::success($result['transaction'], 'QR payment completed successfully.');
