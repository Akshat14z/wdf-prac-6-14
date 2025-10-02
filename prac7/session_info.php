<?php
require_once 'config/database.php';
require_once 'classes/SessionManager.php';
require_once 'classes/CookieManager.php';

SessionManager::startSecureSession();
SessionManager::requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Information - Secure Authentication System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>Session Information</h1>
                <div class="user-actions">
                    <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </header>
            
            <div class="session-info">
                <div class="card">
                    <h3>PHP Session Details</h3>
                    <table class="info-table">
                        <tr><td><strong>Session ID:</strong></td><td><?php echo session_id(); ?></td></tr>
                        <tr><td><strong>Session Name:</strong></td><td><?php echo session_name(); ?></td></tr>
                        <tr><td><strong>Session Status:</strong></td><td><?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?></td></tr>
                        <tr><td><strong>Session Save Path:</strong></td><td><?php echo session_save_path(); ?></td></tr>
                    </table>
                </div>
                
                <div class="card">
                    <h3>Session Configuration</h3>
                    <table class="info-table">
                        <tr><td><strong>Cookie Lifetime:</strong></td><td><?php echo ini_get('session.cookie_lifetime'); ?> seconds</td></tr>
                        <tr><td><strong>Cookie HTTP Only:</strong></td><td><?php echo ini_get('session.cookie_httponly') ? 'Yes' : 'No'; ?></td></tr>
                        <tr><td><strong>Cookie Secure:</strong></td><td><?php echo ini_get('session.cookie_secure') ? 'Yes' : 'No'; ?></td></tr>
                        <tr><td><strong>Cookie SameSite:</strong></td><td><?php echo ini_get('session.cookie_samesite'); ?></td></tr>
                        <tr><td><strong>Use Only Cookies:</strong></td><td><?php echo ini_get('session.use_only_cookies') ? 'Yes' : 'No'; ?></td></tr>
                    </table>
                </div>
                
                <div class="card">
                    <h3>Current Session Data</h3>
                    <pre class="session-data"><?php print_r($_SESSION); ?></pre>
                </div>
                
                <div class="card">
                    <h3>Cookie Information</h3>
                    <table class="info-table">
                        <?php if (!empty($_COOKIE)): ?>
                            <?php foreach ($_COOKIE as $name => $value): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($name); ?>:</strong></td>
                                    <td><?php echo $name === 'remember_me' ? 'Hidden for security' : htmlspecialchars($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="2">No cookies found</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>