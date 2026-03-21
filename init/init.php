<?php
session_start();

// Base URL - change this to your project URL
define('BASE_URL', '/PROJECT-BACK/');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/profiles/');
define('DEFAULT_PHOTO', 'default.png');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user name
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

// Get current user photo
function getCurrentUserPhoto() {
    return $_SESSION['profile_photo'] ?? DEFAULT_PHOTO;
}

// Redirect helper
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit();
}

// Flash message system
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
