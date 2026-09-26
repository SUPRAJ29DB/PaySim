<?php
/**
 * PaySim API - Send Money (UPI Transfer)
 * POST /api/payment/send.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/PaymentService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$receiverUpi = trim($input['receiver_upi'] ?? '');
$amount      = (float) ($input['amount'] ?? 0);
$pin         = trim((string)($input['pin'] ?? ''));
$note        = trim($input['note'] ?? '');
$category    = trim($input['category'] ?? 'Transfer');
$method      = trim($input['payment_method'] ?? 'UPI Transfer');

if (empty($receiverUpi) || $amount <= 0 || empty($pin)) {
    Response::error('Receiver UPI ID, valid amount, and PIN are required.', 422);
}

$paymentService = new PaymentService();
$result = $paymentService->sendMoney($auth['id'], $receiverUpi, $amount, $pin, $note, $category, $method);

if (!$result['success']) {
    Response::error($result['message'], 400);
}

Response::success($result['transaction'], $result['message'], 200);
