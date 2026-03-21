<?php

/**
 * Register a new user
 * Returns: ['success' => bool, 'message' => string]
 */
function registerUser($pdo, $name, $email, $password, $confirm_password) {
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'All fields are required.'];
    }

    if (strlen($name) < 3) {
        return ['success' => false, 'message' => 'Name must be at least 3 characters.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
    }

    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    // Check if name or email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE name = ? OR email = ?");
    $stmt->execute([$name, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Name or email already taken.'];
    }

    // Hash password and insert
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, photo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword, DEFAULT_PHOTO]);

    return ['success' => true, 'message' => 'Account created! Please login.'];
}

/**
 * Login user - accepts name OR email
 * Returns: ['success' => bool, 'message' => string]
 */
function loginUser($pdo, $name_or_email, $password) {
    if (empty($name_or_email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required.'];
    }

    // Check BOTH name and email columns
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ? OR email = ?");
    $stmt->execute([$name_or_email, $name_or_email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid credentials.'];
    }

    // Set session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['profile_photo'] = $user['photo'] ?? DEFAULT_PHOTO;

    return ['success' => true, 'message' => 'Login successful!'];
}

/**
 * Logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
}
