<?php
require_once 'config.php';

$error = '';
$success = '';
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard.php';

// Check for timeout message
if (isset($_GET['timeout'])) {
    $error = 'Your session has expired. Please login again.';
}

// Check for logout message
if (isset($_GET['logout'])) {
    $success = 'You have been successfully logged out.';
}

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . $redirect_url);
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Check if user is locked
        if (isUserLocked($username)) {
            $error = 'Account is temporarily locked due to too many failed login attempts. Please try again later.';
        } else {
            // Check for too many recent failed attempts
            $attempts = getLoginAttempts($username, $ip);
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                lockUser($username);
                $error = 'Too many failed login attempts. Account has been temporarily locked.';
            } else {
                // Verify credentials
                $stmt = $pdo->prepare("SELECT id, username, password, role, is_active FROM users WHERE username = ? AND is_active = 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Successful login
                    session_regenerate_id(true); // Prevent session fixation
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();
                    $_SESSION['ip_address'] = $ip;
                    $_SESSION['user_agent'] = $userAgent;
                    
                    // Update last login time
                    $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Record successful login
                    logLoginAttempt($username, true, $ip, $userAgent);
                    recordSession($user['id'], session_id(), $ip, $userAgent);
                    resetLoginAttempts($username);
                    
                    // Handle remember me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $tokenHash = password_hash($token, PASSWORD_DEFAULT);
                        $expires = date('Y-m-d H:i:s', time() + REMEMBER_ME_DURATION);
                        
                        // Store token in database
                        $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
                        $stmt->execute([$user['id'], $tokenHash, $expires]);
                        
                        // Set cookie
                        setcookie('remember_me', $token, time() + REMEMBER_ME_DURATION, '/', '', false, true);
                    }
                    
                    // Redirect to intended page
                    header('Location: ' . $redirect_url);
                    exit();
                } else {
                    // Failed login
                    logLoginAttempt($username, false, $ip, $userAgent);
                    $error = 'Invalid username or password.';
                    
                    // Increment failed attempts for this user
                    $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE username = ?");
                    $stmt->execute([$username]);
                }
            }
        }
    }
}

// Check remember me cookie
if (!isLoggedIn() && isset($_COOKIE['remember_me']) && !isset($_POST['username'])) {
    $token = $_COOKIE['remember_me'];
    
    // Find valid token
    $stmt = $pdo->prepare("
        SELECT rt.user_id, u.username, u.role, u.is_active, rt.token_hash 
        FROM remember_tokens rt 
        JOIN users u ON rt.user_id = u.id 
        WHERE rt.expires_at > NOW() AND u.is_active = 1
    ");
    $stmt->execute();
    $tokens = $stmt->fetchAll();
    
    foreach ($tokens as $tokenRow) {
        if (password_verify($token, $tokenRow['token_hash'])) {
            // Valid token found, auto-login
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $tokenRow['user_id'];
            $_SESSION['username'] = $tokenRow['username'];
            $_SESSION['role'] = $tokenRow['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['auto_login'] = true;
            
            recordSession($tokenRow['user_id'], session_id());
            
            header('Location: ' . $redirect_url);
            exit();
        }
    }
    
    // Invalid token, remove cookie
    setcookie('remember_me', '', time() - 3600, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure Login System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #001BB7;
            --secondary-blue: #0046FF;
            --orange: #FF8040;
            --light-gray: #E9E9E9;
            --white: #ffffff;
            --text-dark: #333333;
            --text-light: #666666;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            position: relative;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .login-header .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--orange);
        }

        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .login-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            padding-left: 2.8rem;
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: var(--white);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(0, 70, 255, 0.1);
        }

        .form-group .input-icon {
            position: absolute;
            left: 1rem;
            top: 2.2rem;
            color: var(--text-light);
            font-size: 1rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .remember-me label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin: 0;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(45deg, var(--orange), #ff6b2b);
            color: var(--white);
            border: none;
            padding: 0.9rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 128, 64, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--light-gray);
        }

        .form-footer a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: var(--secondary-blue);
        }

        .back-link {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }

        .back-link:hover {
            opacity: 1;
        }

        .alert {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: var(--error-color);
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: var(--success-color);
        }

        .demo-info {
            background: var(--light-gray);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .demo-info h4 {
            color: var(--primary-blue);
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .demo-account {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0.6rem;
            margin: 0.3rem 0;
            background: var(--white);
            border-radius: 4px;
            font-size: 0.8rem;
            font-family: 'Courier New', monospace;
        }

        .demo-account strong {
            color: var(--primary-blue);
        }

        .demo-account span {
            color: var(--text-light);
        }

        /* Loading state */
        .login-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                max-width: none;
            }

            .login-header, .login-form {
                padding: 1.5rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }

        /* Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            animation: slideIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
            <div class="icon">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to your secure account</p>
        </div>

        <form method="POST" class="login-form" id="loginForm">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="demo-info">
                <h4>
                    <i class="fas fa-key"></i>
                    Demo Accounts
                </h4>
                <div class="demo-account">
                    <strong>admin</strong>
                    <span>admin123</span>
                </div>
                <div class="demo-account">
                    <strong>user</strong>
                    <span>user123</span>
                </div>
                <div class="demo-account">
                    <strong>demo</strong>
                    <span>demo123</span>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       required 
                       autocomplete="username"
                       placeholder="Enter your username">
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       placeholder="Enter your password">
                <i class="fas fa-key input-icon"></i>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me">Remember me for 7 days</label>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>

            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Create one here</a></p>
                <p style="margin-top: 0.5rem;">
                    <a href="forgot-password.php">Forgot your password?</a>
                </p>
            </div>
        </form>
    </div>

    <script>
        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                e.preventDefault();
                alert('Please enter both username and password.');
                return;
            }

            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
        });

        // Auto-focus on username field
        window.addEventListener('load', function() {
            document.getElementById('username').focus();
        });

        // Demo account quick fill
        document.querySelectorAll('.demo-account').forEach(account => {
            account.addEventListener('click', function() {
                const username = this.querySelector('strong').textContent;
                const password = this.querySelector('span').textContent;
                
                document.getElementById('username').value = username;
                document.getElementById('password').value = password;
                
                // Add visual feedback
                this.style.background = 'var(--secondary-blue)';
                this.style.color = 'white';
                
                setTimeout(() => {
                    this.style.background = 'var(--white)';
                    this.style.color = '';
                }, 200);
            });
            
            // Add cursor pointer
            account.style.cursor = 'pointer';
            account.title = 'Click to auto-fill credentials';
        });

        // Enhanced input animations
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.input-icon').style.color = 'var(--secondary-blue)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.input-icon').style.color = 'var(--text-light)';
            });
        });
    </script>
</body>
</html>