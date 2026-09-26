<?php
/**
 * PaySim API - Check Real-Time Transaction Status
 * GET /api/payment/transaction-status.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/models/Transaction.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$txnId = trim($_GET['txn_id'] ?? '');

if (empty($txnId)) {
    Response::error('Parameter txn_id is required.', 422);
}

$txnModel = new Transaction();
$txn = $txnModel->findByTxnId($txnId);

if (!$txn) {
    Response::notFound("Transaction {$txnId} not found in system ledger.");
}

Response::success([
    'txn_id'         => $txn['txn_id'],
    'status'         => $txn['status'],
    'utr'            => $txn['utr'],
    'amount'         => (float)$txn['amount'],
    'sender_upi'     => $txn['sender_upi'],
    'receiver_upi'   => $txn['receiver_upi'],
    'timestamp'      => $txn['created_at'],
], 'Transaction status retrieved successfully.');
