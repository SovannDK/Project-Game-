<?php
// Include this at the top of pages that require login
if (!isLoggedIn()) {
    setFlash('error', 'Please login first.');
    redirect('pages/login.php');
}
