<?php
session_start(); // Start the session to manage login state

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Finance and Management Tool - Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Your Personal Finance and Management Tool</h1>
        <p>Manage your expenses and income easily.</p>
        <div class="buttons">
            <a href="log_in.php" class="btn">Login</a>
            <a href="sign_up.php" class="btn">Sign Up</a>
        </div>
    </div>
</body>
</html>

