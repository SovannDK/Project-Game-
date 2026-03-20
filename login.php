<?php
session_start();
require_once 'init/init.php';
include 'includes/header.php';

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<section class="card form-card">
    <h2>Login</h2>

    <?php if ($error === 'emptyinput'): ?>
        <p class="error-text">Please fill in all fields.</p>
    <?php elseif ($error === 'notfound'): ?>
        <p class="error-text">User not found.</p>
    <?php elseif ($error === 'wrongpassword'): ?>
        <p class="error-text">Wrong password.</p>
    <?php elseif ($error === 'stmtfailed'): ?>
        <p class="error-text">Something went wrong.</p>
    <?php endif; ?>

    <?php if ($success === 'registered'): ?>
        <p class="success-text">Register successful. Please login.</p>
    <?php elseif ($success === 'logout'): ?>
        <p class="success-text">Logged out successfully.</p>
    <?php elseif ($success === 'deleted'): ?>
        <p class="success-text">Profile deleted successfully.</p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label>Name or Email</label>
        <input type="text" name="login_input" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="submit" class="btn">Login</button>
    </form>
</section>

<?php
if (isset($_POST['submit'])) {
    $loginInput = cleanInput($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';

    if (emptyInputLogin($loginInput, $password)) {
        header('Location: login.php?error=emptyinput');
        exit();
    }

    loginUser($conn, $loginInput, $password);
}

include 'includes/footer.php';