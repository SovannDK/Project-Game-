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

$result = deleteUser($pdo, $id);
setFlash($result['success'] ? 'success' : 'error', $result['message']);
redirect('pages/User/list.php');
