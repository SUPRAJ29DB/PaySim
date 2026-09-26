<?php
/**
 * PaySim API - User Registration
 * POST /api/auth/register.php
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/services/AuthService.php';
require_once dirname(dirname(__DIR__)) . '/utils/Response.php';
require_once dirname(dirname(__DIR__)) . '/utils/Validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('Only POST requests are allowed.');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$validator = new Validator();
$isValid = $validator->validate($input, [
    'full_name' => 'required|min:2|max:100',
    'username'  => 'required|username',
    'email'     => 'required|email',
    'phone'     => 'required|phone',
    'password'  => 'required|min:6',
]);

if (!$isValid) {
    Response::error($validator->getFirstError(), 422, $validator->getErrors());
}

$authService = new AuthService();
$result = $authService->register($input);

if (!$result['success']) {
    Response::error($result['message'], 400);
}

// Auto-login session upon successful registration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $result['user'];
$_SESSION['USER_ID']  = (int) $user['id'];
$_SESSION['USERNAME'] = $user['username'];
$_SESSION['NAME']     = $user['full_name'];
$_SESSION['UPI_ID']   = $user['upi_id'];
$_SESSION['ROLE']     = $user['role'];

unset($user['password_hash'], $user['upi_pin_hash']);

Response::success([
    'user'     => $user,
    'redirect' => APP_URL . '/dashboard.php',
], 'Registration successful.', 201);
