<?php
/**
 * PaySim API - Admin System Transactions
 * GET /api/admin/transactions.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/admin-auth.php';
require_once dirname(dirname(__DIR__)) . '/services/AdminService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

checkAdminAuth(true);

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(100, max(5, (int)($_GET['per_page'] ?? 50)));

$filters = [
    'status' => trim($_GET['status'] ?? ''),
    'search' => trim($_GET['search'] ?? ''),
];

$adminService = new AdminService();
$data = $adminService->getAllTransactions($page, $perPage, $filters);

Response::success($data, 'System transactions retrieved successfully.');
