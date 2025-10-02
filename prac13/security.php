<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$pdo = getDBConnection();
$message = '';
$messageType = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        // Get current user
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        // Verify current password
        if (!verifyPassword($currentPassword, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect';
        }
        
        // Validate new password
        if (!validatePassword($newPassword)) {
            $errors[] = 'New password must be at least 8 characters with uppercase, lowercase, number, and special character';
        }
        
        // Check if passwords match
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match';
        }
        
        // Check if new password is different from current
        if (verifyPassword($newPassword, $user['password_hash'])) {
            $errors[] = 'New password must be different from current password';
        }
        
        if (empty($errors)) {
            try {
                $newPasswordHash = hashPassword($newPassword);
                $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $stmt->execute([$newPasswordHash, $_SESSION['user_id']]);
                
                $message = 'Password changed successfully!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'An error occurred while changing your password.';
                $messageType = 'error';
            }
        } else {
            $message = implode('<br>', $errors);
            $messageType = 'error';
        }
    }
}

// Get user security info
$stmt = $pdo->prepare('SELECT login_attempts, account_locked, locked_until, last_login FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$securityInfo = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings - Secure Auth System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .security-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(124, 60, 33, 0.1);
        }
        
        .security-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #F9C49A;
        }
        
        .security-header h1 {
            color: #7C3C21;
            margin-bottom: 10px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #EC823A;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .security-section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #E8E4E1;
            border-radius: 10px;
        }
        
        .security-section h3 {
            color: #7C3C21;
            margin-bottom: 15px;
        }
        
        .security-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .info-item h4 {
            color: #7C3C21;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .info-item p {
            font-weight: 600;
            font-size: 16px;
        }
        
        .password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .password-requirements h4 {
            color: #7C3C21;
            margin-bottom: 10px;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="security-container">
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            
            <div class="security-header">
                <h1>Security Settings</h1>
                <p>Manage your account security</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Security Overview -->
            <div class="security-section">
                <h3>Security Overview</h3>
                <div class="security-info-grid">
                    <div class="info-item">
                        <h4>Account Status</h4>
                        <p style="color: <?php echo $securityInfo['account_locked'] ? '#dc3545' : '#28a745'; ?>">
                            <?php echo $securityInfo['account_locked'] ? 'Locked' : 'Active'; ?>
                        </p>
                    </div>
                    <div class="info-item">
                        <h4>Failed Attempts</h4>
                        <p style="color: <?php echo $securityInfo['login_attempts'] > 2 ? '#dc3545' : '#28a745'; ?>">
                            <?php echo $securityInfo['login_attempts']; ?>/5
                        </p>
                    </div>
                    <div class="info-item">
                        <h4>Last Login</h4>
                        <p>
                            <?php echo $securityInfo['last_login'] ? date('M j, g:i A', strtotime($securityInfo['last_login'])) : 'Never'; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="security-section">
                <h3>Change Password</h3>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="password-requirements">
                        <h4>Password Requirements:</h4>
                        <ul>
                            <li>At least 8 characters long</li>
                            <li>At least one uppercase letter (A-Z)</li>
                            <li>At least one lowercase letter (a-z)</li>
                            <li>At least one number (0-9)</li>
                            <li>At least one special character (@$!%*?&)</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="change_password" class="submit-btn">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>