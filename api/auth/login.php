<?php
/**
 * PaySim API - User Login
 * POST /api/auth/login.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/services/AuthService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    Response::error('Please enter both username and password.', 422);
}

$authService = new AuthService();
$result = $authService->login($username, $password);

if (!$result['success']) {
    Response::error($result['message'], 401);
}

$user = $result['user'];
unset($user['password_hash'], $user['upi_pin_hash']);

Response::success([
    'user'     => $user,
    'redirect' => APP_URL . '/dashboard.php',
], 'Login successful.');
