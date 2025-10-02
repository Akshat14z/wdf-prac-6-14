<?php
// Check if user wants to see the landing page
if (isset($_GET['landing']) || !isset($_GET['direct'])) {
    // Show landing page by including the HTML
    include 'index.html';
    exit();
}

// Original index.php functionality for direct access
require_once 'config/database.php';
require_once 'classes/SessionManager.php';
require_once 'classes/CookieManager.php';

SessionManager::startSecureSession();

// Check if user is already logged in
if (SessionManager::isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Check remember me cookie
if (CookieManager::checkRememberMeCookie()) {
    header("Location: dashboard.php");
    exit();
}

// Redirect to login page
header("Location: login.php");
exit();
?>