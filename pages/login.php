<?php
require_once __DIR__ . '/../init/init.php';
require_once __DIR__ . '/../init/db.init.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Login - XO Arena';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_or_email = trim($_POST['name_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = loginUser($pdo, $name_or_email, $password);
    if ($result['success']) {
        setFlash('success', 'Welcome back, ' . getCurrentUserName() . '!');
        redirect('index.php');
    } else {
        $error = $result['message'];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1><span class="x-mark">X</span><span class="o-mark">O</span> Login</h1>
            <p>Welcome back, player!</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="name_or_email">Username or Email</label>
                <input type="text" id="name_or_email" name="name_or_email"
                       value="<?php echo htmlspecialchars($name_or_email ?? ''); ?>"
                       placeholder="Enter your name or email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Login</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="<?php echo BASE_URL; ?>pages/register.php">Create one</a></p>
            <p><a href="<?php echo BASE_URL; ?>index.php">or Play as Guest</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
