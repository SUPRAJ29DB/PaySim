<?php
/**
 * PaySim API - Mark All Notifications as Read
 * POST /api/notification/mark-all-read.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/NotificationService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$notifService = new NotificationService();
$notifService->markAllRead($auth['id']);

Response::success(null, 'All notifications marked as read.');
