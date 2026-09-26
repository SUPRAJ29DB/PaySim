<?php
/**
 * PaySim - User Authentication Middleware Guard
 */

require_once dirname(__DIR__) . '/config/app.php';

function checkAuth(bool $isApi = false): array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['USER_ID'])) {
        if ($isApi || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            Response::unauthorized('Authentication required to access this endpoint.');
        } else {
            Helpers::setFlash('error', 'Please log in to continue.');
            header('Location: ' . APP_URL . '/login.php');
            exit();
        }
    }

    return [
        'id'       => (int) $_SESSION['USER_ID'],
        'username' => $_SESSION['USERNAME'] ?? '',
        'name'     => $_SESSION['NAME'] ?? '',
        'upi_id'   => $_SESSION['UPI_ID'] ?? '',
    ];
}
