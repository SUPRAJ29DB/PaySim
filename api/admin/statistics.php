<?php
/**
 * PaySim API - Admin Dashboard Statistics
 * GET /api/admin/statistics.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/admin-auth.php';
require_once dirname(dirname(__DIR__)) . '/services/AdminService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

checkAdminAuth(true);

$adminService = new AdminService();
$stats = $adminService->getDashboardStatistics();

Response::success($stats, 'Dashboard statistics retrieved successfully.');
