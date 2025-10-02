<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        try {
            // Check if user exists and is not locked
            $stmt = $pdo->prepare("
                SELECT id, username, password_hash, first_name, last_name, role, status, 
                       login_attempts, locked_until 
                FROM users 
                WHERE (username = ? OR email = ?) AND status = 'active'
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Check if account is locked
                if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                    $error_message = 'Account is temporarily locked due to multiple failed login attempts. Please try again later.';
                } elseif (password_verify($password, $user['password_hash'])) {
                    // Successful login
                    
                    // Reset login attempts
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET login_attempts = 0, locked_until = NULL, last_login = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$user['id']]);
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Handle remember me
                    if ($remember_me) {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', time() + REMEMBER_ME_DURATION);
                        
                        // Store token in database
                        $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        $stmt->execute([$token, $user['id']]);
                        
                        // Set cookie
                        setcookie('remember_token', $token, time() + REMEMBER_ME_DURATION, '/', '', false, true);
                    }
                    
                    // Create session record
                    $session_id = session_id();
                    $stmt = $pdo->prepare("
                        INSERT INTO user_sessions (id, user_id, ip_address, user_agent, expires_at) 
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        user_id = VALUES(user_id),
                        ip_address = VALUES(ip_address),
                        user_agent = VALUES(user_agent),
                        expires_at = VALUES(expires_at)
                    ");
                    $stmt->execute([
                        $session_id,
                        $user['id'],
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? '',
                        date('Y-m-d H:i:s', time() + SESSION_TIMEOUT)
                    ]);
                    
                    // Log successful login
                    logActivity('login_success');
                    
                    // Add welcome notification
                    addNotification($user['id'], 'Welcome back!', 'You have successfully logged in to the portal.', 'success');
                    
                    // Redirect to dashboard or intended page
                    $redirect_url = $_GET['redirect'] ?? 'index.php';
                    header('Location: ' . $redirect_url);
                    exit();
                    
                } else {
                    // Invalid password
                    $attempts = $user['login_attempts'] + 1;
                    
                    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                        // Lock account
                        $locked_until = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET login_attempts = ?, locked_until = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$attempts, $locked_until, $user['id']]);
                        
                        $error_message = 'Too many failed login attempts. Account has been temporarily locked.';
                        
                        // Log lockout
                        logActivity('account_locked', 'users', $user['id']);
                        
                    } else {
                        // Increment login attempts
                        $stmt = $pdo->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
                        $stmt->execute([$attempts, $user['id']]);
                        
                        $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
                        $error_message = "Invalid password. $remaining attempt(s) remaining before account lockout.";
                    }
                    
                    // Log failed login attempt
                    logActivity('login_failed');
                }
            } else {
                $error_message = 'Invalid username or password.';
                logActivity('login_failed');
            }
            
        } catch (PDOException $e) {
            $error_message = 'Login system temporarily unavailable. Please try again later.';
            error_log('Login error: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    <title><?= APP_NAME ?> - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 400px;
            margin: var(--spacing-lg);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }
        
        .login-header h1 {
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
        }
        
        .login-header p {
            color: var(--text-muted);
        }
        
        .demo-credentials {
            background: var(--secondary-light);
            border: 1px solid var(--primary-light);
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            font-size: 0.875rem;
        }
        
        .demo-credentials h4 {
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
        }
        
        .demo-credentials ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .demo-credentials li {
            margin-bottom: var(--spacing-xs);
            color: var(--text-secondary);
        }
        
        .demo-credentials .role {
            font-weight: 600;
            color: var(--primary);
        }
        
        .login-footer {
            text-align: center;
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--border-light);
        }
        
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-globe"></i> <?= APP_NAME ?></h1>
            <p>Please sign in to continue</p>
        </div>

        <!-- Demo Credentials -->
        <div class="demo-credentials">
            <h4><i class="fas fa-info-circle"></i> Demo Credentials</h4>
            <ul>
                <li><span class="role">Super Admin:</span> admin / admin123</li>
                <li><span class="role">Manager:</span> manager / admin123</li>
                <li><span class="role">Student:</span> student1 / admin123</li>
                <li><span class="role">User:</span> user1 / admin123</li>
            </ul>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="ajax-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> Username or Email
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required 
                    autofocus
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="form-group">
                <label class="d-flex align-center">
                    <input 
                        type="checkbox" 
                        name="remember_me" 
                        value="1"
                        style="margin-right: var(--spacing-sm);"
                    >
                    Remember me for 7 days
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="login-footer">
            <p>
                <a href="forgot_password.php">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
            </p>
            <p>
                Don't have an account? 
                <a href="register.php">
                    <i class="fas fa-user-plus"></i> Register Here
                </a>
            </p>
            <p class="text-muted mt-2">
                <small>
                    <i class="fas fa-shield-alt"></i> 
                    Secure login with session protection
                </small>
            </p>
        </div>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container"></div>

    <script src="js/script.js"></script>
    <script>
        // Login page specific functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-fill demo credentials
            document.addEventListener('click', function(e) {
                if (e.target.closest('.demo-credentials')) {
                    const text = e.target.textContent;
                    const usernameField = document.getElementById('username');
                    const passwordField = document.getElementById('password');
                    
                    if (text.includes('admin /')) {
                        usernameField.value = 'admin';
                        passwordField.value = 'admin123';
                    } else if (text.includes('manager /')) {
                        usernameField.value = 'manager';
                        passwordField.value = 'admin123';
                    } else if (text.includes('student1 /')) {
                        usernameField.value = 'student1';
                        passwordField.value = 'admin123';
                    } else if (text.includes('user1 /')) {
                        usernameField.value = 'user1';
                        passwordField.value = 'admin123';
                    }
                }
            });
            
            // Show caps lock warning
            const passwordField = document.getElementById('password');
            passwordField.addEventListener('keyup', function(e) {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    portal.showNotification('Caps Lock is on', 'warning', 3000);
                }
            });
        });
    </script>
</body>
</html>