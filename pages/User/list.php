<?php
require_once '../../includes/auth.php';
requireAdminFromUserPage();
require_once '../../init/init.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$users = getAllUsers($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <main class="container">
        <section class="card">
            <div class="page-head">
                <h2>User List</h2>
                <a class="btn link-btn" href="create.php">Create User</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Wins</th>
                        <th>Losses</th>
                        <th>Draws</th>
                        <th>Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><img class="table-avatar" src="<?php echo getAdminPhotoPath($user['photo']); ?>" alt="Photo"></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['wins'] ?? 0; ?></td>
                            <td><?php echo $user['losses'] ?? 0; ?></td>
                            <td><?php echo $user['draws'] ?? 0; ?></td>
                            <td><?php echo $user['score'] ?? 0; ?></td>
                            <td>
                                <a class="table-link" href="update.php?user_id=<?php echo $user['user_id']; ?>">Update</a>
                                <a class="table-link danger-link" href="delete.php?user_id=<?php echo $user['user_id']; ?>" onclick="return confirm('Delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="button-row">
                <a class="btn link-btn" href="../../dashboard.php">Back Dashboard</a>
            </div>
        </section>
    </main>
</body>
</html>