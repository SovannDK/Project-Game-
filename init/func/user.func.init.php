<?php

/**
 * Get user by ID
 */
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COALESCE(s.wins, 0) AS wins, 
               COALESCE(s.losses, 0) AS losses, 
               COALESCE(s.draws, 0) AS draws, 
               COALESCE(s.total_games, 0) AS total_games,
               COALESCE(s.score, 0) AS score
        FROM users u 
        LEFT JOIN user_stats s ON u.user_id = s.user_id 
        WHERE u.user_id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all users (for admin)
 */
function getAllUsers($pdo) {
    $stmt = $pdo->query("
        SELECT u.user_id, u.name, u.email, u.photo, u.role, u.created_at,
               COALESCE(s.wins, 0) AS wins, 
               COALESCE(s.losses, 0) AS losses, 
               COALESCE(s.draws, 0) AS draws, 
               COALESCE(s.total_games, 0) AS total_games,
               COALESCE(s.score, 0) AS score
        FROM users u 
        LEFT JOIN user_stats s ON u.user_id = s.user_id 
        ORDER BY u.created_at DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Update user profile (name, email)
 */
function updateUserProfile($pdo, $id, $name, $email) {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE (name = ? OR email = ?) AND user_id != ?");
    $stmt->execute([$name, $email, $id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Name or email already taken by another user.'];
    }

    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
    $stmt->execute([$name, $email, $id]);

    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    return ['success' => true, 'message' => 'Profile updated!'];
}

/**
 * Update profile photo
 */
function updateProfilePhoto($pdo, $id, $file) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!in_array($file['type'], $allowed)) {
        return ['success' => false, 'message' => 'Only JPG, PNG, GIF, WEBP allowed.'];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File too large. Max 2MB.'];
    }

    $user = getUserById($pdo, $id);
    $oldPhoto = $user['photo'];

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $id . '_' . time() . '.' . $ext;
    $destination = UPLOAD_PATH . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Upload failed.'];
    }

    if ($oldPhoto !== DEFAULT_PHOTO && !empty($oldPhoto) && file_exists(UPLOAD_PATH . $oldPhoto)) {
        unlink(UPLOAD_PATH . $oldPhoto);
    }

    $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
    $stmt->execute([$filename, $id]);

    $_SESSION['profile_photo'] = $filename;

    return ['success' => true, 'message' => 'Photo updated!'];
}

/**
 * Delete profile photo (reset to default)
 */
function deleteProfilePhoto($pdo, $id) {
    $user = getUserById($pdo, $id);
    $oldPhoto = $user['photo'];

    if ($oldPhoto !== DEFAULT_PHOTO && !empty($oldPhoto) && file_exists(UPLOAD_PATH . $oldPhoto)) {
        unlink(UPLOAD_PATH . $oldPhoto);
    }

    $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
    $stmt->execute([DEFAULT_PHOTO, $id]);

    $_SESSION['profile_photo'] = DEFAULT_PHOTO;

    return ['success' => true, 'message' => 'Photo removed. Default photo set.'];
}

/**
 * Delete user account (admin only)
 */
function deleteUser($pdo, $id) {
    if ($id == getCurrentUserId()) {
        return ['success' => false, 'message' => 'Cannot delete your own account.'];
    }

    $user = getUserById($pdo, $id);
    if ($user && $user['photo'] !== DEFAULT_PHOTO && !empty($user['photo'])) {
        $photoPath = UPLOAD_PATH . $user['photo'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM user_stats WHERE user_id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$id]);

    return ['success' => true, 'message' => 'User deleted.'];
}

/**
 * Update game stats (saves to user_stats table)
 */
function updateGameStats($pdo, $userId, $result) {
    // Check if stats row exists
    $stmt = $pdo->prepare("SELECT stats_id FROM user_stats WHERE user_id = ?");
    $stmt->execute([$userId]);
    $exists = $stmt->fetch();

    if (!$exists) {
        $stmt = $pdo->prepare("INSERT INTO user_stats (user_id, wins, losses, draws, total_games, score) VALUES (?, 0, 0, 0, 0, 0)");
        $stmt->execute([$userId]);
    }

    $column = '';
    $scoreChange = 0;
    switch ($result) {
        case 'win': $column = 'wins'; $scoreChange = 3; break;
        case 'loss': $column = 'losses'; $scoreChange = 0; break;
        case 'draw': $column = 'draws'; $scoreChange = 1; break;
        default: return false;
    }

    $stmt = $pdo->prepare("UPDATE user_stats SET {$column} = {$column} + 1, total_games = total_games + 1, score = score + ? WHERE user_id = ?");
    $stmt->execute([$scoreChange, $userId]);

    return true;
}

/**
 * Get ranking (leaderboard)
 */
function getRanking($pdo, $limit = 50) {
    $limit = (int) $limit;
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.name, u.photo, 
               s.wins, s.losses, s.draws, s.total_games, s.score,
               CASE WHEN s.total_games > 0 THEN ROUND((s.wins / s.total_games) * 100, 1) ELSE 0 END AS win_rate
        FROM users u
        INNER JOIN user_stats s ON u.user_id = s.user_id
        WHERE s.total_games > 0
        ORDER BY s.score DESC, s.wins DESC, s.total_games DESC
       LIMIT {$limit}
       ");
      $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get user game history
 */
function getUserGameHistory($pdo, $userId, $limit = 20) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM game_history WHERE user_id = ? ORDER BY played_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Change user password
 */
function changePassword($pdo, $id, $currentPassword, $newPassword, $confirmPassword) {
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        return ['success' => false, 'message' => 'All fields required.'];
    }
    if ($newPassword !== $confirmPassword) {
        return ['success' => false, 'message' => 'New passwords do not match.'];
    }
    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
    }

    $user = getUserById($pdo, $id);
    if (!password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is wrong.'];
    }

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashed, $id]);

    return ['success' => true, 'message' => 'Password changed!'];
}

/**
 * Admin: Update user role
 */
function updateUserRole($pdo, $id, $role) {
    if (!in_array($role, ['user', 'admin'])) {
        return ['success' => false, 'message' => 'Invalid role.'];
    }
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
    $stmt->execute([$role, $id]);
    return ['success' => true, 'message' => 'Role updated.'];
}

/**
 * Ban a user for a specific duration
 * $duration examples: '1 hour', '2 hours', '5 minutes', '30 minutes', '1 day'
 */
function banUser($pdo, $id, $duration) {
    if ($id == getCurrentUserId()) {
        return ['success' => false, 'message' => 'Cannot ban yourself.'];
    }

    $bannedUntil = date('Y-m-d H:i:s', strtotime('+' . $duration));

    $stmt = $pdo->prepare("UPDATE users SET banned_until = ? WHERE user_id = ?");
    $stmt->execute([$bannedUntil, $id]);

    return ['success' => true, 'message' => 'User banned until ' . $bannedUntil];
}

/**
 * Unban a user (remove ban immediately)
 */
function unbanUser($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE users SET banned_until = NULL WHERE user_id = ?");
    $stmt->execute([$id]);

    return ['success' => true, 'message' => 'User unbanned.'];
}

/**
 * Check if a user is currently banned
 * Returns false if not banned, or the banned_until time if banned
 */
function isUserBanned($pdo, $id) {
    $stmt = $pdo->prepare("SELECT banned_until FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if ($user && $user['banned_until'] !== null) {
        $bannedUntil = strtotime($user['banned_until']);
        if ($bannedUntil > time()) {
            return $user['banned_until'];
        } else {
            // Ban expired, clear it
            $stmt = $pdo->prepare("UPDATE users SET banned_until = NULL WHERE user_id = ?");
            $stmt->execute([$id]);
            return false;
        }
    }

    return false;
}