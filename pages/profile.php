<?php
mysqli_stmt_close($stmtStats);
?>

<section class="card">
    <div class="profile-top">
        <img class="avatar-large" src="<?php echo getPhotoPath($user['photo']); ?>" alt="Profile">
        <div>
            <h2>My Profile</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        </div>
    </div>
</section>

<section class="card stats-grid">
    <div class="stat-box"><strong>Wins</strong><span><?php echo $stats['wins'] ?? 0; ?></span></div>
    <div class="stat-box"><strong>Losses</strong><span><?php echo $stats['losses'] ?? 0; ?></span></div>
    <div class="stat-box"><strong>Draws</strong><span><?php echo $stats['draws'] ?? 0; ?></span></div>
    <div class="stat-box"><strong>Total Games</strong><span><?php echo $stats['total_games'] ?? 0; ?></span></div>
    <div class="stat-box"><strong>Score</strong><span><?php echo $stats['score'] ?? 0; ?></span></div>
</section>

<section class="card">
    <h3>Update My Profile</h3>
    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>New Photo (optional)</label>
        <input type="file" name="photo" accept="image/*">

        <button type="submit" name="update_profile" class="btn">Update Profile</button>
    </form>

    <form action="profile.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your profile?');">
        <button type="submit" name="delete_profile" class="btn btn-danger">Delete My Profile</button>
    </form>
</section>

<?php
if (isset($_POST['update_profile'])) {
    $newName = cleanInput($_POST['name'] ?? '');
    $newEmail = cleanInput($_POST['email'] ?? '');
    $newPhoto = $user['photo'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $fileName = $_FILES['photo']['name'];
        $fileTmpName = $_FILES['photo']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExt, $allowed)) {
            $newPhoto = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
            move_uploaded_file($fileTmpName, 'assets/img/' . $newPhoto);

            if ($user['photo'] !== 'default.png' && file_exists('assets/img/' . $user['photo'])) {
                unlink('assets/img/' . $user['photo']);
            }
        }
    }

    updateUserData($conn, $_SESSION['user_id'], $newName, $newEmail, $newPhoto);
    $_SESSION['name'] = $newName;
    $_SESSION['email'] = $newEmail;
    $_SESSION['photo'] = $newPhoto;
    header('Location: profile.php');
    exit();
}

if (isset($_POST['delete_profile'])) {
    if ($user['photo'] !== 'default.png' && file_exists('assets/img/' . $user['photo'])) {
        unlink('assets/img/' . $user['photo']);
    }

    deleteUserData($conn, $_SESSION['user_id']);
    session_unset();
    session_destroy();
    header('Location: login.php?success=deleted');
    exit();
}

include 'includes/footer.php';