<?php
require_once 'config/database.php';
require_once 'classes/SessionManager.php';
require_once 'classes/CookieManager.php';
require_once 'classes/User.php';

SessionManager::startSecureSession();

// Clear remember me token from database if user is logged in
if (SessionManager::isLoggedIn()) {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    $user->clearRememberToken($_SESSION['user_id']);
}

// Clear remember me cookie
CookieManager::clearRememberMeCookie();

// Destroy session
SessionManager::destroySession();

// Redirect to login page
header("Location: login.php?message=logged_out");
exit();
?>