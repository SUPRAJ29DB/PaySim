<?php
session_start();

if (isset($_SESSION['USER_ID'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PaySim - UPI Payment Simulator</title>
</head>
<body>
    <h1>Welcome to PaySim</h1>
    <p>Login to your account</p>
    <form action="login.php" method="post">

        <input
            type="text"
            name="username"
            placeholder="Username"
            required
        >
        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >
        <input
            type="submit"
            value="Login"
        >
    </form>
    <p>
        Don't have an account?
        <a href="register.php">Register</a>
    </p>
    <p>
        Forgot your password?
        <a href="forgot-password.php">Forgot Password</a>
    </p>
</body>
</html>