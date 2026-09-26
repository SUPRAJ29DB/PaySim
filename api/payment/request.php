<?php
/**
 * PaySim API - Request Money (UPI Collect)
 * POST /api/payment/request.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/PaymentService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$payerUpi = trim($input['payer_upi'] ?? '');
$amount   = (float) ($input['amount'] ?? 0);
$note     = trim($input['note'] ?? '');

if (empty($payerUpi) || $amount <= 0) {
    Response::error('Payer UPI ID and valid amount are required.', 422);
}

$paymentService = new PaymentService();
$result = $paymentService->requestMoney($auth['id'], $payerUpi, $amount, $note);

if (!$result['success']) {
    Response::error($result['message'], 400);
}

Response::success($result, 'Payment request created successfully.', 201);
