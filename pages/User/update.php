<?php
require_once __DIR__ . '/../../init/init.php';
require_once __DIR__ . '/../../init/db.init.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isAdmin()) {
    setFlash('error', 'Admin access only.');
    redirect('index.php');
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'Invalid user.');
    redirect('pages/User/list.php');
}

$user = getUserById($pdo, $id);
if (!$user) {
    setFlash('error', 'User not found.');
    redirect('pages/User/list.php');
}

$pageTitle = 'Admin: Edit User - XO Arena';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_info':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            // Check duplicates excluding this user
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE (name = ? OR email = ?) AND user_id != ?");
            $stmt->execute([$name, $email, $id]);
            if ($stmt->fetch()) {
                $error = 'Name or email already taken.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
                $stmt->execute([$name, $email, $id]);
                $success = 'User info updated.';
                $user = getUserById($pdo, $id);
            }
            break;

        case 'update_role':
            $role = $_POST['role'] ?? 'user';
            $result = updateUserRole($pdo, $id, $role);
            if ($result['success']) {
                $success = $result['message'];
                $user = getUserById($pdo, $id);
            } else {
                $error = $result['message'];
            }
            break;

        case 'update_photo':
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Temporarily override session for this user
                $result = updateProfilePhoto($pdo, $id, $_FILES['photo']);
                if ($result['success']) {
                    $success = 'Photo updated.';
                    $user = getUserById($pdo, $id);
                    // Restore session photo if editing self
                    if ($id == getCurrentUserId()) {
                        $_SESSION['profile_photo'] = $user['photo'];
                    }
                } else {
                    $error = $result['message'];
                }
            } else {
                $error = 'Please select a photo.';
            }
            break;

        case 'delete_photo':
            $oldPhoto = $user['photo'];
            if ($oldPhoto !== DEFAULT_PHOTO && file_exists(UPLOAD_PATH . $oldPhoto)) {
                unlink(UPLOAD_PATH . $oldPhoto);
            }
            $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
            $stmt->execute([DEFAULT_PHOTO, $id]);
            $success = 'Photo removed.';
            $user = getUserById($pdo, $id);
            if ($id == getCurrentUserId()) {
                $_SESSION['profile_photo'] = DEFAULT_PHOTO;
            }
            break;

        case 'reset_password':
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';
            if (strlen($newPass) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($newPass !== $confirmPass) {
                $error = 'Passwords do not match.';
            } else {
                $hashed = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->execute([$hashed, $id]);
                $success = 'Password reset.';
            }
            break;

        case 'reset_stats':
            $stmt = $pdo->prepare("UPDATE user_stats SET wins = 0, losses = 0, draws = 0, total_games = 0, score = 0 WHERE user_id = ?");
            $stmt->execute([$id]);
            $success = 'Stats reset to zero.';
            $user = getUserById($pdo, $id);
            break;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="admin-edit-page">
    <div class="admin-edit-container">
        <div class="admin-edit-header">
            <a href="<?php echo BASE_URL; ?>pages/User/list.php" class="btn btn-ghost">&larr; Back</a>
            <h1>Edit User: <?php echo htmlspecialchars($user['name']); ?></h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-photo-section">
                    <img src="<?php echo BASE_URL; ?>assets/uploads/profiles/<?php echo htmlspecialchars($user['photo']); ?>"
                         alt="Profile" class="profile-photo-large">
                    <form method="POST" enctype="multipart/form-data" class="photo-form">
                        <input type="hidden" name="action" value="update_photo">
                        <label for="photoInput" class="btn btn-small btn-secondary">Change Photo</label>
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none"
                               onchange="this.form.submit()">
                    </form>
                    <?php if ($user['photo'] !== DEFAULT_PHOTO): ?>
                        <form method="POST" style="margin-top:5px;">
                            <input type="hidden" name="action" value="delete_photo">
                            <button type="submit" class="btn btn-small btn-danger">Remove Photo</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="profile-stats">
                    <h3>Stats</h3>
                    <div class="stat-grid">
                        <div class="stat-item"><span class="stat-num"><?php echo $user['total_games']; ?></span><span class="stat-label">Games</span></div>
                        <div class="stat-item stat-win"><span class="stat-num"><?php echo $user['wins']; ?></span><span class="stat-label">Wins</span></div>
                        <div class="stat-item stat-loss"><span class="stat-num"><?php echo $user['losses']; ?></span><span class="stat-label">Losses</span></div>
                        <div class="stat-item stat-draw"><span class="stat-num"><?php echo $user['draws']; ?></span><span class="stat-label">Draws</span></div>
                    </div>
                    <form method="POST" style="margin-top:10px;">
                        <input type="hidden" name="action" value="reset_stats">
                        <button type="submit" class="btn btn-small btn-danger btn-full"
                                onclick="return confirm('Reset all stats for this user?')">Reset Stats</button>
                    </form>
                </div>
            </div>

            <!-- Main -->
            <div class="profile-main">
                <div class="profile-card">
                    <h2>User Info</h2>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_info">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>

                <div class="profile-card">
                    <h2>Role</h2>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_role">
                        <div class="form-group">
                            <select name="role" class="form-select">
                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </form>
                </div>

                <div class="profile-card">
                    <h2>Reset Password</h2>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="reset_password">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
