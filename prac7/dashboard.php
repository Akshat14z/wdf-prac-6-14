<?php
require_once 'config/database.php';
require_once 'classes/SessionManager.php';
require_once 'classes/CookieManager.php';

SessionManager::startSecureSession();

// Check remember me cookie if not logged in
if (!SessionManager::isLoggedIn()) {
    CookieManager::checkRememberMeCookie();
}

// Require login
SessionManager::requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure Authentication System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <div class="user-actions">
                    <a href="session_info.php" class="btn-secondary">Session Info</a>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </header>
            
            <div class="dashboard-content">
                <div class="card">
                    <h3>Session Information</h3>
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p><strong>Session ID:</strong> <?php echo htmlspecialchars(session_id()); ?></p>
                    <p><strong>Login Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
                
                <div class="card">
                    <h3>Cookie Status</h3>
                    <p><strong>Remember Me Cookie:</strong> 
                        <?php echo isset($_COOKIE['remember_me']) ? 'Active' : 'Not Set'; ?>
                    </p>
                    <p><strong>Session Cookie:</strong> 
                        <?php echo isset($_COOKIE[session_name()]) ? 'Active' : 'Not Set'; ?>
                    </p>
                </div>
                
                <div class="card">
                    <h3>Security Features</h3>
                    <ul>
                        <li>✓ Secure session handling</li>
                        <li>✓ HTTP-only cookies</li>
                        <li>✓ Secure cookie transmission</li>
                        <li>✓ Session regeneration</li>
                        <li>✓ Password hashing</li>
                        <li>✓ Remember me functionality</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>