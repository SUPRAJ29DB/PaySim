<?php
/**
 * PaySim API - Get Transaction Digital Receipt
 * GET /api/transaction/receipt.php
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
    Response::notFound('Transaction not found or unauthorized.');
}

$receipt = [
    'receipt_no'     => 'RCP-' . substr($details['utr'], -8),
    'transaction_id' => $details['id'],
    'utr'            => $details['utr'],
    'ref_no'         => $details['ref'],
    'date'           => $details['date'],
    'amount'         => $details['amount'],
    'currency'       => 'INR',
    'status'         => $details['status'],
    'sender'         => [
        'name' => $details['sender_name'],
        'upi'  => $details['sender_upi'],
    ],
    'receiver'       => [
        'name' => $details['receiver_name'],
        'upi'  => $details['receiver_upi'],
    ],
    'payment_method' => $details['payment_method'],
    'note'           => $details['note'],
    'cashback'       => $details['cashback'],
    'verified_by'    => 'NPCI Unified Payments Switch',
];

Response::success($receipt, 'Receipt generated successfully.');
