<?php
/**
 * PaySim API - Mark Single Notification as Read
 * POST /api/notification/mark-read.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/NotificationService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$notifId = (int) ($input['notification_id'] ?? 0);

if ($notifId <= 0) {
    Response::error('Invalid notification_id parameter.', 422);
}

$notifService = new NotificationService();
$notifService->markRead($notifId, $auth['id']);

Response::success(null, 'Notification marked as read.');
