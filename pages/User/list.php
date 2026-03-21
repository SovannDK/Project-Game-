<?php
require_once __DIR__ . '/../../init/init.php';
require_once __DIR__ . '/../../init/db.init.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Admin only
if (!isAdmin()) {
    setFlash('error', 'Admin access only.');
    redirect('index.php');
}

$pageTitle = 'Admin: Users - XO Arena';
$users = getAllUsers($pdo);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="page-title">Admin: User Management</h1>
            <a href="<?php echo BASE_URL; ?>pages/User/create.php" class="btn btn-primary">+ Add User</a>
        </div>

        <div class="ranking-table-wrapper">
            <table class="ranking-table admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>W/L/D</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['user_id']; ?></td>
                            <td>
                                <img src="<?php echo BASE_URL; ?>assets/uploads/profiles/<?php echo htmlspecialchars($u['photo']); ?>"
                                     class="rank-avatar" alt="">
                            </td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $u['role']; ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td><?php echo $u['wins'] . '/' . $u['losses'] . '/' . $u['draws']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td class="action-cell">
                                <a href="<?php echo BASE_URL; ?>pages/User/update.php?id=<?php echo $u['user_id']; ?>"
                                   class="btn btn-small btn-secondary">Edit</a>
                                <?php if ($u['user_id'] != getCurrentUserId()): ?>
                                    <a href="<?php echo BASE_URL; ?>pages/User/delete.php?id=<?php echo $u['user_id']; ?>"
                                       class="btn btn-small btn-danger"
                                       onclick="return confirm('Delete user <?php echo htmlspecialchars($u['name']); ?>?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
