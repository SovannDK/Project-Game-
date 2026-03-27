<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'XO Game'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo BASE_URL; ?>index.php" class="nav-logo">
                <span class="logo-x">X</span><span class="logo-o">O</span>
                <span class="logo-text">Arena</span>
            </a>
            <div class="nav-links">
                <a href="<?php echo BASE_URL; ?>index.php" class="nav-link">Play vs Bot</a>
                <a href="<?php echo BASE_URL; ?>pages/play-friend.php" class="nav-link">Play vs Friend</a>
                <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="nav-link">Ranking</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>pages/profile.php" class="nav-link">Profile</a>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>pages/User/list.php" class="nav-link nav-admin">Admin</a>
                    <?php endif; ?>
                    <div class="nav-user">
                        <img src="<?php echo BASE_URL; ?>assets/uploads/profiles/<?php echo getCurrentUserPhoto(); ?>"
                            alt="Profile" class="nav-avatar">
                        <span class="nav-username"><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
                        <a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-link btn-logout">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>pages/login.php" class="nav-link btn-login">Login</a>
                    <a href="<?php echo BASE_URL; ?>pages/register.php" class="nav-link btn-register">Sign Up</a>
                <?php endif; ?>
            </div>
            <button class="nav-toggle"
                onclick="document.querySelector('.nav-links').classList.toggle('active')">☰</button>
        </div>
    </nav>

    <?php
    $flash = getFlash();
    if ($flash): ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
            <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
        </div>
    <?php endif; ?>

    <main class="main-content">