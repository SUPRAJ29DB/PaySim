<?php
/**
 * PaySim - Guest Only Middleware Guard
 */

require_once dirname(__DIR__) . '/config/app.php';

function checkGuest(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['USER_ID'])) {
        header('Location: ' . APP_URL . '/dashboard.php');
        exit();
    }
}
