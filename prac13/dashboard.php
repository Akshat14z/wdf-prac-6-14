<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$pdo = getDBConnection();

// Get user information
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure Auth System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(124, 60, 33, 0.1);
        }
        
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #F9C49A;
        }
        
        .dashboard-header h1 {
            color: #7C3C21;
            margin-bottom: 10px;
        }
        
        .dashboard-header p {
            color: #666;
            font-size: 16px;
        }
        
        .user-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: linear-gradient(135deg, #F9C49A, #E8E4E1);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid rgba(124, 60, 33, 0.1);
        }
        
        .info-card h3 {
            color: #7C3C21;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-card p {
            color: #7C3C21;
            font-size: 18px;
            font-weight: 600;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #EC823A, #F9C49A);
            color: white;
        }
        
        .btn-secondary {
            background: #E8E4E1;
            color: #7C3C21;
            border: 2px solid #7C3C21;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        
        .security-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            border-left: 4px solid #EC823A;
        }
        
        .security-info h3 {
            color: #7C3C21;
            margin-bottom: 15px;
        }
        
        .security-info ul {
            list-style-type: none;
            padding: 0;
        }
        
        .security-info li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .security-info li:last-child {
            border-bottom: none;
        }
        
        .status-indicator {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-info {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p>You have successfully logged into the secure authentication system</p>
            </div>
            
            <div class="user-info">
                <div class="info-card">
                    <h3>Full Name</h3>
                    <p><?php echo htmlspecialchars($user['full_name']); ?></p>
                </div>
                
                <div class="info-card">
                    <h3>Username</h3>
                    <p><?php echo htmlspecialchars($user['username']); ?></p>
                </div>
                
                <div class="info-card">
                    <h3>Email</h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                
                <div class="info-card">
                    <h3>Member Since</h3>
                    <p><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                </div>
                
                <div class="info-card">
                    <h3>Last Login</h3>
                    <p><?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'First login'; ?></p>
                </div>
                
                <div class="info-card">
                    <h3>Session Time</h3>
                    <p><?php echo date('g:i A', $_SESSION['login_time']); ?></p>
                </div>
            </div>
            
            <div class="actions">
                <a href="profile.php" class="action-btn btn-primary">Edit Profile</a>
                <a href="security.php" class="action-btn btn-secondary">Security Settings</a>
                <a href="logout.php" class="action-btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
            
            <div class="security-info">
                <h3>Security Status</h3>
                <ul>
                    <li>
                        <span>Password Security</span>
                        <span class="status-indicator status-success">Strong</span>
                    </li>
                    <li>
                        <span>Account Status</span>
                        <span class="status-indicator status-success"><?php echo $user['account_locked'] ? 'Locked' : 'Active'; ?></span>
                    </li>
                    <li>
                        <span>Failed Login Attempts</span>
                        <span class="status-indicator status-info"><?php echo $user['login_attempts']; ?></span>
                    </li>
                    <li>
                        <span>Session Security</span>
                        <span class="status-indicator status-success">CSRF Protected</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>