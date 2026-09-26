<?php
/**
 * PaySim - Administrator Authentication Middleware Guard
 */

require_once dirname(__DIR__) . '/config/app.php';

function checkAdminAuth(bool $isApi = false): array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['ADMIN_LOGGED_IN'], $_SESSION['ADMIN_ID'], $_SESSION['ADMIN_ROLE']) || $_SESSION['ADMIN_LOGGED_IN'] !== true || !in_array($_SESSION['ADMIN_ROLE'], ['Admin', 'Super Admin'], true) || (int)$_SESSION['ADMIN_ID'] < 1) {
        if ($isApi || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            Response::unauthorized('Administrator privileges required.');
        } else {
            Helpers::setFlash('error', 'Administrator login required.');
            header('Location: ' . APP_URL . '/admin/login.php');
            exit();
        }
    }

    return [
        'id'    => (int) $_SESSION['ADMIN_ID'],
        'name'  => $_SESSION['ADMIN_NAME'] ?? '',
        'email' => $_SESSION['ADMIN_EMAIL'] ?? '',
        'role'  => $_SESSION['ADMIN_ROLE'],
    ];
}
