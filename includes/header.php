<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game XO</title>
    <link rel="stylesheet" href="/Project-Back/assets/style.css">
</head>
<body>
    <header class="topbar">
        <div class="container nav-wrap">
            <a class="logo" href="/Project-Back/index.php">GAME XO</a>
            <nav class="nav-links">
                <a href="/Project-Back/index.php">Home</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/Project-Back/dashboard.php">Dashboard</a>
                    <a href="/Project-Back/profile.php">Profile</a>
                    <a href="/Project-Back/logout.php">Logout</a>
                <?php else: ?>
                    <a href="/Project-Back/login.php">Login</a>
                    <a href="/Project-Back/register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">