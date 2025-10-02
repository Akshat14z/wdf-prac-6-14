<?php
require_once 'config.php';

// If not logged in, redirect to login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Remove user session from database
    $session_id = session_id();
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE id = ?");
    $stmt->execute([$session_id]);
    
    // Clear remember me token if exists
    if (isset($_COOKIE['remember_token'])) {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Delete remember me cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Log logout activity
    logActivity('logout');
    
} catch (PDOException $e) {
    error_log('Logout error: ' . $e->getMessage());
}

// Destroy session
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login with success message
header('Location: login.php?logged_out=1');
exit();
?>