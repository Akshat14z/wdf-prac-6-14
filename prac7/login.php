<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'classes/SessionManager.php';
require_once 'classes/CookieManager.php';
require_once 'classes/User.php';

SessionManager::startSecureSession();

// Check if already logged in
SessionManager::redirectIfLoggedIn();

// Check remember me cookie
if (CookieManager::checkRememberMeCookie()) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log the POST data
    error_log("Login attempt - Username: " . $_POST['username']);
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);
    
    if (!empty($username) && !empty($password)) {
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        
        $authenticated_user = $user->authenticate($username, $password);
        
        // Debug: Log authentication result
        error_log("Authentication result for $username: " . ($authenticated_user ? "SUCCESS" : "FAILED"));
        
        if ($authenticated_user) {
            $_SESSION['user_id'] = $authenticated_user['id'];
            $_SESSION['username'] = $authenticated_user['username'];
            
            // Handle remember me
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $user->updateRememberToken($authenticated_user['id'], $token);
                CookieManager::setRememberMeCookie($authenticated_user['id'], $token);
            }
            
            // Debug: Check if headers already sent
            if (headers_sent($file, $line)) {
                echo "<div style='color: red; background: yellow; padding: 10px;'>";
                echo "ERROR: Headers already sent in $file on line $line. Cannot redirect.";
                echo "<br><a href='dashboard.php'>Click here to go to dashboard manually</a>";
                echo "</div>";
            } else {
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error_message = "Invalid username or password!";
        }
    } else {
        $error_message = "Please fill in all fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure Authentication System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Login</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 4px;">
                    <strong>Debug Mode Active:</strong><br>
                    Available test credentials:<br>
                    • Username: <strong>testuser</strong>, Password: <strong>password</strong><br>
                    • Username: <strong>admin</strong>, Password: <strong>password</strong><br>
                    • Username: <strong>dhrumil246</strong>, Password: <strong>password</strong>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="remember_me" value="1">
                        Remember me for 30 days
                    </label>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <p class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>