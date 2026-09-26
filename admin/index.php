<?php
session_start();

if (isset($_SESSION['ADMIN_LOGGED_IN']) && $_SESSION['ADMIN_LOGGED_IN'] === true) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
