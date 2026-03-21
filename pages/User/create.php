<?php
require_once __DIR__ . '/../../init/init.php';
require_once __DIR__ . '/../../init/db.init.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isAdmin()) {
    setFlash('error', 'Admin access only.');
    redirect('index.php');
}

$pageTitle = 'Admin: Create User - XO Arena';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    $result = registerUser($pdo, $name, $email, $password, $confirm_password);
    if ($result['success']) {
        // Update role if admin
        if ($role === 'admin') {
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
            $stmt->execute([$email]);
        }
        setFlash('success', 'User created!');
        redirect('pages/User/list.php');
    } else {
        $error = $result['message'];
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Create New User</h1>
            <p>Admin: Add a new player</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-select">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Create User</button>
        </form>

        <div class="auth-footer">
            <a href="<?php echo BASE_URL; ?>pages/User/list.php">&larr; Back to User List</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
