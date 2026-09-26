<?php
/**
 * PaySim API - System Audit Logs
 * GET /api/admin/audit-logs.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/admin-auth.php';
require_once dirname(dirname(__DIR__)) . '/services/AdminService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

checkAdminAuth(true);

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(100, max(5, (int)($_GET['per_page'] ?? 50)));

$adminService = new AdminService();
$data = $adminService->getAuditLogs($page, $perPage);

Response::success($data, 'Audit logs retrieved successfully.');
