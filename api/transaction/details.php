<?php
/**
 * PaySim API - Get Transaction Details
 * GET /api/transaction/details.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/TransactionService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);
$txnId = trim($_GET['id'] ?? '');

if (empty($txnId)) {
    Response::error('Transaction ID parameter "id" is required.', 422);
}

$txnService = new TransactionService();
$details = $txnService->getDetails($txnId, $auth['id']);

if (!$details) {
    Response::notFound('Transaction not found or access denied.');
}

Response::success($details, 'Transaction details retrieved successfully.');
