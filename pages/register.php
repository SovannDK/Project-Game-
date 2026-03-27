<?php
require_once __DIR__ . '/../init/init.php';
require_once __DIR__ . '/../init/db.init.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Register - XO Arena';

$name = $email = $password = $confirm_password = '';
$nameErr = $emailErr = $passwordErr = $confirmErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $nameErr = 'Please enter a username!';
    } elseif (strlen($name) < 3) {
        $nameErr = 'Username must be at least 3 characters!';
    }

    if (empty($email)) {
        $emailErr = 'Please enter your email!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = 'Invalid email format!';
    }

    if (empty($password)) {
        $passwordErr = 'Please enter a password!';
    } elseif (strlen($password) < 6) {
        $passwordErr = 'Password must be at least 6 characters!';
    }

    if (empty($confirm_password)) {
        $confirmErr = 'Please confirm your password!';
    } elseif ($password !== $confirm_password) {
        $confirmErr = 'Passwords do not match!';
    }

    // If no errors, try to register
    if (empty($nameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmErr)) {
        $result = registerUser($pdo, $name, $email, $password, $confirm_password);
        if ($result['success']) {
            setFlash('success', $result['message']);
            redirect('pages/login.php');
        } else {
            // Check which field the error is about
            if (strpos($result['message'], 'Name') !== false || strpos($result['message'], 'taken') !== false) {
                $nameErr = $result['message'];
            } else {
                $emailErr = $result['message'];
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join the <span class="x-mark">X</span><span class="o-mark">O</span> Arena</p>
        </div>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="name">Username</label>
                <input type="text" id="name" name="name"
                       value="<?php echo htmlspecialchars($name); ?>"
                       placeholder="Choose a username"
                       class="<?php echo empty($nameErr) ? '' : 'is-invalid'; ?>">
                <div class="invalid-feedback"><?php echo $nameErr; ?></div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($email); ?>"
                       placeholder="your@email.com"
                       class="<?php echo empty($emailErr) ? '' : 'is-invalid'; ?>">
                <div class="invalid-feedback"><?php echo $emailErr; ?></div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Min 6 characters"
                       class="<?php echo empty($passwordErr) ? '' : 'is-invalid'; ?>">
                <div class="invalid-feedback"><?php echo $passwordErr; ?></div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Re-enter password"
                       class="<?php echo empty($confirmErr) ? '' : 'is-invalid'; ?>">
                <div class="invalid-feedback"><?php echo $confirmErr; ?></div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="<?php echo BASE_URL; ?>pages/login.php">Login</a></p>
            <p><a href="<?php echo BASE_URL; ?>index.php">or Play as Guest</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>