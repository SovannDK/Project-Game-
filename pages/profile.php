<?php
require_once __DIR__ . '/../init/init.php';
require_once __DIR__ . '/../init/db.init.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Profile - XO Arena';
$user = getUserById($pdo, getCurrentUserId());
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_profile':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $result = updateUserProfile($pdo, $user['user_id'], $name, $email);
            if ($result['success']) {
                $success = $result['message'];
                $user = getUserById($pdo, $user['user_id']);
            } else {
                $error = $result['message'];
            }
            break;

        case 'update_photo':
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $result = updateProfilePhoto($pdo, $user['user_id'], $_FILES['photo']);
                if ($result['success']) {
                    $success = $result['message'];
                    $user = getUserById($pdo, $user['user_id']);
                } else {
                    $error = $result['message'];
                }
            } else {
                $error = 'Please select a photo.';
            }
            break;

        case 'delete_photo':
            $result = deleteProfilePhoto($pdo, $user['user_id']);
            $success = $result['message'];
            $user = getUserById($pdo, $user['user_id']);
            break;

        case 'change_password':
            $result = changePassword(
                $pdo,
                $user['user_id'],
                $_POST['current_password'] ?? '',
                $_POST['new_password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
            break;
    }
}

$gameHistory = getUserGameHistory($pdo, $user['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="profile-page">
    <div class="profile-grid">
        <!-- Left: Photo & Stats -->
        <div class="profile-sidebar">
            <div class="profile-photo-section">
                <img src="<?php echo BASE_URL; ?>assets/uploads/profiles/<?php echo htmlspecialchars($user['photo']); ?>"
                    alt="Profile" class="profile-photo-large">

                <form method="POST" enctype="multipart/form-data" class="photo-form" id="photoForm">
                    <input type="hidden" name="action" value="update_photo">
                    <label for="photoInput" class="btn btn-small btn-secondary">Change Photo</label>
                    <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none"
                        onchange="showPhotoConfirm(this)">
                </form>

                <?php if ($user['photo'] !== DEFAULT_PHOTO): ?>
                    <form method="POST" style="margin-top:5px;" id="removePhotoForm">
                        <input type="hidden" name="action" value="delete_photo">
                        <button type="button" class="btn btn-small btn-danger"
                            onclick="showRemoveConfirm()">Remove Photo</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="profile-stats">
                <h3>Game Stats</h3>
                <div class="stat-grid">
                    <div class="stat-item"><span class="stat-num"><?php echo $user['total_games']; ?></span><span
                            class="stat-label">Games</span></div>
                    <div class="stat-item stat-win"><span class="stat-num"><?php echo $user['wins']; ?></span><span
                            class="stat-label">Wins</span></div>
                    <div class="stat-item stat-loss"><span class="stat-num"><?php echo $user['losses']; ?></span><span
                            class="stat-label">Losses</span></div>
                    <div class="stat-item stat-draw"><span class="stat-num"><?php echo $user['draws']; ?></span><span
                            class="stat-label">Draws</span></div>
                </div>
                <?php
                $winRate = $user['total_games'] > 0 ? round(($user['wins'] / $user['total_games']) * 100, 1) : 0;
                ?>
                <div class="win-rate">Win Rate: <strong><?php echo $winRate; ?>%</strong></div>
            </div>
        </div>

        <!-- Right: Edit Forms -->
        <div class="profile-main">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="profile-card">
                <h2>Edit Profile</h2>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            <div class="profile-card">
                <h2>Change Password</h2>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password">
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>

            <!-- Recent Games -->
            <?php if (!empty($gameHistory)): ?>
                <div class="profile-card">
                    <h2>Recent Games</h2>
                    <div class="game-history-list">
                        <?php foreach ($gameHistory as $game): ?>
                            <div class="history-item history-<?php echo $game['result']; ?>">
                                <span class="history-result">
                                    <?php
                                    if ($game['result'] === 'win')
                                        echo '🏆 Win';
                                    elseif ($game['result'] === 'loss')
                                        echo '❌ Loss';
                                    else
                                        echo '🤝 Draw';
                                    ?>
                                </span>
                                <span class="history-date"><?php echo date('M d, H:i', strtotime($game['played_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Photo Upload Confirm Modal -->
<div class="modal-overlay" id="photoModal" style="display:none;">
    <div class="modal-box">
        <h2 class="modal-title">Change Profile Photo</h2>
        <p class="modal-text">Are you sure you want to update your profile photo?</p>
        <img id="photoPreview" src="" alt="Preview"
            style="width:120px; height:120px; border-radius:50%; object-fit:cover; margin:16px auto; display:block; border:3px solid var(--border);">
        <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
            <button class="btn btn-primary" onclick="confirmPhotoUpload()">Yes, Change It</button>
            <button class="btn btn-secondary" onclick="cancelPhotoUpload()">Cancel</button>
        </div>
    </div>
</div>

<!-- Remove Photo Confirm Modal -->
<div class="modal-overlay" id="removePhotoModal" style="display:none;">
    <div class="modal-box">
        <h2 class="modal-title" style="color: var(--color-loss);">Remove Profile Photo</h2>
        <p class="modal-text">Are you sure you want to remove your profile photo?</p>
        <p style="color: var(--text-secondary); font-size:0.9rem;">Your photo will be replaced with the default avatar.</p>
        <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
            <button class="btn btn-danger" onclick="confirmRemovePhoto()">Yes, Remove It</button>
            <button class="btn btn-secondary" onclick="closeRemoveModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
let photoForm = null;

function showPhotoConfirm(input) {
    if (input.files && input.files[0]) {
        photoForm = input.form;
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('photoModal').style.display = 'flex';
    }
}

function confirmPhotoUpload() {
    document.getElementById('photoModal').style.display = 'none';
    if (photoForm) photoForm.submit();
}

function cancelPhotoUpload() {
    document.getElementById('photoModal').style.display = 'none';
    document.getElementById('photoInput').value = '';
    photoForm = null;
}

function showRemoveConfirm() {
    document.getElementById('removePhotoModal').style.display = 'flex';
}

function confirmRemovePhoto() {
    document.getElementById('removePhotoModal').style.display = 'none';
    document.getElementById('removePhotoForm').submit();
}

function closeRemoveModal() {
    document.getElementById('removePhotoModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cancelPhotoUpload();
        closeRemoveModal();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>