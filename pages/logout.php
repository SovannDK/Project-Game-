<?php
require_once __DIR__ . '/../init/init.php';
require_once __DIR__ . '/../includes/functions.php';

logoutUser();
// Start new session for flash message
session_start();
setFlash('success', 'Logged out successfully.');
redirect('pages/login.php');
