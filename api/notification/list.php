<?php
/**
 * PaySim API - List Notifications
 * GET /api/notification/list.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/NotificationService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);
$notifService = new NotificationService();

$notifications = $notifService->getUserNotifications($auth['id'], 50);
$unreadCount   = $notifService->getUnreadCount($auth['id']);

Response::success([
    'notifications' => $notifications,
    'unread_count'  => $unreadCount,
], 'Notifications retrieved successfully.');
