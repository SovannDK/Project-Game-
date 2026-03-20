<?php
require_once '../../includes/auth.php';
requireAdminFromUserPage();
require_once '../../init/init.php';
require_once '../../includes/functions.php';

$userId = $_GET['user_id'] ?? 0;
$user = getUserById($conn, $userId);

if ($user) {
    if ($user['photo'] !== 'default.png' && file_exists('../../assets/img/' . $user['photo'])) {
        unlink('../../assets/img/' . $user['photo']);
    }

    deleteUserData($conn, $userId);
}

header('Location: list.php');
exit();