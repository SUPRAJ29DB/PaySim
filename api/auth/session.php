<?php
/**
 * PaySim API - Current Session State
 * GET /api/auth/session.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/services/UserService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';
require_once dirname(dirname(__DIR__)) . '/utils/Security.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrfToken = Security::generateCsrfToken();

if (!isset($_SESSION['USER_ID'])) {
    Response::success([
        'authenticated' => false,
        'user'          => null,
        'csrf_token'    => $csrfToken,
    ]);
}

$userService = new UserService();
$profile = $userService->getProfile((int)$_SESSION['USER_ID']);

Response::success([
    'authenticated' => true,
    'user'          => $profile,
    'csrf_token'    => $csrfToken,
]);
