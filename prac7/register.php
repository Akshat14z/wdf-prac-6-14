<?php
require_once 'config/database.php';
require_once 'classes/SessionManager.php';
require_once 'classes/User.php';

SessionManager::startSecureSession();
SessionManager::redirectIfLoggedIn();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    
    if (!empty($username) && !empty($password) && !empty($email)) {
        if ($password === $confirm_password) {
            if (strlen($password) >= 6) {
                $database = new Database();
                $db = $database->getConnection();
                $user = new User($db);
                
                if ($user->register($username, $password, $email)) {
                    $success_message = "Registration successful! You can now login.";
                } else {
                    $error_message = "Username already exists!";
                }
            } else {
                $error_message = "Password must be at least 6 characters long!";
            }
        } else {
            $error_message = "Passwords do not match!";
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
    <title>Register - Secure Authentication System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Register</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-login">Register</button>
            </form>
            
            <p class="register-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>