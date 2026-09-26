<?php
/**
 * PaySim API - Get User Profile
 * GET /api/user/profile.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/UserService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);
$userService = new UserService();
$profile = $userService->getProfile($auth['id']);

if (!$profile) {
    Response::notFound('User profile not found.');
}

Response::success($profile, 'Profile retrieved successfully.');
