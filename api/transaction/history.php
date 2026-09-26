<?php
/**
 * PaySim API - User Transaction History
 * GET /api/transaction/history.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/TransactionService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(100, max(5, (int)($_GET['per_page'] ?? 20)));

$filters = [
    'status' => trim($_GET['status'] ?? ''),
    'type'   => trim($_GET['type'] ?? ''),
    'search' => trim($_GET['search'] ?? ''),
];

$txnService = new TransactionService();
$data = $txnService->getHistory($auth['id'], $page, $perPage, $filters);

Response::success($data, 'Transaction history retrieved successfully.');
