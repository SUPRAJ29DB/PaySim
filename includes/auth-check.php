<?php
/**
 * PaySim - User Auth Check Guard Include
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

$currentUserId   = (int) $_SESSION['USER_ID'];
$currentUserName = $_SESSION['NAME'] ?? 'User';
$currentUserUpi  = $_SESSION['UPI_ID'] ?? 'user@paysim';
