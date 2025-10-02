<?php
require_once 'config.php';

// Check if user is logged in
if (!$userAuth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Log the logout activity
$userId = $userAuth->getCurrentUserId();
if ($userId) {
    $securityUtil->logActivity($userId, null, 'logout', 'User logged out from admin dashboard');
}

// Perform logout
$userAuth->logout();

// Redirect to login page with success message
header('Location: login.php?success=logout');
exit;
?>