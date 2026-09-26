<?php
session_start();
unset($_SESSION['ADMIN_LOGGED_IN']);
unset($_SESSION['ADMIN_ID']);
unset($_SESSION['ADMIN_NAME']);
unset($_SESSION['ADMIN_EMAIL']);
unset($_SESSION['ADMIN_ROLE']);
session_destroy();
header("Location: login.php");
exit();
