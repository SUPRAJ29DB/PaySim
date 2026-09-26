<?php
/**
 * PaySim Admin API - Login
 * POST username + password (JSON or form data).
 * Only database users with admin/superadmin role are accepted.
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/utils/Security.php';
require_once dirname(__DIR__, 2) . '/utils/Response.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    Response::methodNotAllowed('Use POST to sign in.');
}

$input = json_decode(file_get_contents('php://input'), true);
$input = is_array($input) ? $input : $_POST;
$username = trim((string)($input['username'] ?? ''));
$password = (string)($input['password'] ?? '');
if ($username === '' || $password === '') {
    Response::error('Username and password are required.', 422);
}

try {
    $userModel = new User();
    $user = $userModel->findByUsername($username);
    if (!$user || !in_array(($user['role'] ?? ''), ['admin', 'superadmin'], true)
        || ($user['status'] ?? '') !== 'active'
        || !Security::verifyPassword($password, (string)($user['password_hash'] ?? ''))) {
        Response::unauthorized('Invalid administrator credentials.');
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['ADMIN_LOGGED_IN'] = true;
    $_SESSION['ADMIN_ID'] = (int)$user['id'];
    $_SESSION['ADMIN_NAME'] = (string)$user['full_name'];
    $_SESSION['ADMIN_EMAIL'] = (string)$user['email'];
    $_SESSION['ADMIN_ROLE'] = ($user['role'] === 'superadmin') ? 'Super Admin' : 'Admin';

    Response::success([
        'id' => (int)$user['id'],
        'name' => (string)$user['full_name'],
        'email' => (string)$user['email'],
        'role' => $_SESSION['ADMIN_ROLE'],
    ], 'Administrator login successful.');
} catch (Throwable $e) {
    error_log('[PaySim admin login] ' . $e->getMessage());
    Response::error('Unable to complete login right now.', 500);
}
