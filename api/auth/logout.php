<?php
/**
 * PaySim API - User Logout
 * POST or GET /api/auth/logout.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/services/AuthService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$authService = new AuthService();
$authService->logout();

Response::success([
    'redirect' => APP_URL . '/login.php'
], 'Successfully logged out.');
