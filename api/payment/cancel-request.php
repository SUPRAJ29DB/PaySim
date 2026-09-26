<?php
/**
 * PaySim API - Cancel Payment Request
 * POST /api/payment/cancel-request.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/PaymentService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$requestId = (int) ($input['request_id'] ?? 0);

if ($requestId <= 0) {
    Response::error('Invalid request_id parameter.', 422);
}

$paymentService = new PaymentService();
$result = $paymentService->cancelRequest($requestId, $auth['id']);

if (!$result['success']) {
    Response::error($result['message'], 400);
}

Response::success(null, $result['message']);
