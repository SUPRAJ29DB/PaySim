<?php
/**
 * PaySim API - Update User Profile
 * POST /api/user/update-profile.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/middleware/auth.php';
require_once dirname(dirname(__DIR__)) . '/services/UserService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

$auth = checkAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$userService = new UserService();
$result = $userService->updateProfile($auth['id'], $input);

if (!$result['success']) {
    Response::error($result['message'], 400);
}

// Update session name if full_name was modified
if (!empty($input['full_name'])) {
    $_SESSION['NAME'] = trim($input['full_name']);
}

Response::success($result['user'], $result['message']);
