<?php<?php

require_once 'config.php';require_once 'config.php';

initializeDatabase();

header('Content-Type: application/json');

// Check if user is already logged in

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {if (isset($_SESSION['user_id'])) {

    http_response_code(405);    header('Location: dashboard.php');

    echo json_encode(['success' => false, 'message' => 'Method not allowed']);    exit();

    exit;}

}?>

<!DOCTYPE html>

// Validate CSRF token<html lang="en">

if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {<head>

    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);    <meta charset="UTF-8">

    exit;    <meta name="viewport" content="width=device-width, initial-scale=1.0">

}    <title>Secure Authentication | SecureAuth Pro</title>

    <meta name="description" content="Secure user authentication with advanced validation and protection">

$pdo = getDBConnection();    <link rel="stylesheet" href="css/style.css">

$action = $_POST['action'] ?? '';    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

switch ($action) {    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    case 'register':</head>

        handleRegistration($pdo);<body>

        break;    <!-- Main Wrapper -->

    case 'login':    <div class="main-wrapper">

        handleLogin($pdo);        <!-- Header Section -->

        break;        <header class="landing-header">

    default:            <div class="header-content">

        echo json_encode(['success' => false, 'message' => 'Invalid action']);                <div class="logo-section">

        exit;                    <h1 class="logo">🔐 SecureAuth Pro</h1>

}                    <p class="tagline">Advanced Security Authentication System</p>

                </div>

function handleRegistration($pdo) {                <div class="security-badges">

    $errors = [];                    <span class="badge">🛡️ CSRF Protected</span>

    $response = ['success' => false];                    <span class="badge">🔒 Password Hashed</span>

                        <span class="badge">🚫 SQL Injection Safe</span>

    // Sanitize input                </div>

    $fullname = sanitizeInput($_POST['fullname'] ?? '');            </div>

    $username = sanitizeInput($_POST['username'] ?? '');        </header>

    $email = sanitizeInput($_POST['email'] ?? '');

    $password = $_POST['password'] ?? '';        <!-- Main Content -->

    $confirmPassword = $_POST['confirm_password'] ?? '';        <main class="main-content">

    $captcha = $_POST['captcha'] ?? '';            <div class="container">

                    <!-- Welcome Section -->

    // Validate full name                <section class="welcome-section">

    if (empty($fullname) || strlen($fullname) < 2) {                    <h2 class="welcome-title">Welcome to SecureAuth</h2>

        $errors['fullname'] = 'Full name must be at least 2 characters long';                    <p class="welcome-description">

    }                        Experience enterprise-level security with our advanced authentication system featuring 

                            real-time validation, CAPTCHA protection, and comprehensive security measures.

    // Validate username                    </p>

    if (empty($username) || strlen($username) < 3) {                </section>

        $errors['username'] = 'Username must be at least 3 characters long';

    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {                <!-- Authentication Container -->

        $errors['username'] = 'Username can only contain letters, numbers, and underscores';                <section class="auth-section">

    } else {                    <div class="auth-container">

        // Check if username exists                        <!-- Form Toggle -->

        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');                        <div class="form-toggle">

        $stmt->execute([$username]);                            <button id="login-toggle" class="toggle-btn active" aria-pressed="true">

        if ($stmt->fetch()) {                                <span class="toggle-icon">👤</span>

            $errors['username'] = 'Username already exists';                                <span class="toggle-text">Login</span>

        }                            </button>

    }                            <button id="register-toggle" class="toggle-btn" aria-pressed="false">

                                    <span class="toggle-icon">✨</span>

    // Validate email                                <span class="toggle-text">Register</span>

    if (empty($email) || !validateEmail($email)) {                            </button>

        $errors['email'] = 'Please enter a valid email address';                        </div>

    } else {                        

        // Check if email exists                        <!-- Login Form -->

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');                        <div id="login-form" class="form-container active" role="tabpanel" aria-labelledby="login-toggle">

        $stmt->execute([$email]);                            <div class="form-header">

        if ($stmt->fetch()) {                                <h3 class="form-title">Welcome Back</h3>

            $errors['email'] = 'Email already registered';                                <p class="form-subtitle">Sign in to your secure account</p>

        }                            </div>

    }                            

                                <form id="loginForm" class="auth-form" novalidate>

    // Validate password                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

    if (empty($password) || !validatePassword($password)) {                                

        $errors['password'] = 'Password must be at least 8 characters with uppercase, lowercase, number, and special character';                                <div class="form-group">

    }                                    <label for="login_username" class="form-label">

                                            <span class="label-text">Username or Email</span>

    // Validate confirm password                                        <span class="label-icon">📧</span>

    if ($password !== $confirmPassword) {                                    </label>

        $errors['confirm_password'] = 'Passwords do not match';                                    <input 

    }                                        type="text" 

                                            id="login_username" 

    // Validate CAPTCHA (simple validation - in production, use more secure methods)                                        name="username" 

    if (empty($captcha)) {                                        class="form-input"

        $errors['captcha'] = 'Please enter the CAPTCHA';                                        placeholder="Enter your username or email"

    }                                        autocomplete="username"

                                            required

    if (!empty($errors)) {                                        aria-describedby="login_username_error"

        $response['message'] = 'Please fix the errors below';                                    >

        $response['errors'] = $errors;                                    <span class="error-message" id="login_username_error" role="alert"></span>

        echo json_encode($response);                                </div>

        return;                                

    }                                <div class="form-group">

                                        <label for="login_password" class="form-label">

    try {                                        <span class="label-text">Password</span>

        // Hash password                                        <span class="label-icon">🔑</span>

        $passwordHash = hashPassword($password);                                    </label>

                                            <input 

        // Insert user                                        type="password" 

        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)');                                        id="login_password" 

        $stmt->execute([$username, $email, $passwordHash, $fullname]);                                        name="password" 

                                                class="form-input"

        $response['success'] = true;                                        placeholder="Enter your password"

        $response['message'] = 'Account created successfully! You can now log in.';                                        autocomplete="current-password"

        echo json_encode($response);                                        required

                                                aria-describedby="login_password_error"

    } catch (PDOException $e) {                                    >

        error_log('Registration error: ' . $e->getMessage());                                    <span class="error-message" id="login_password_error" role="alert"></span>

        $response['message'] = 'An error occurred during registration. Please try again.';                                </div>

        echo json_encode($response);                                

    }                                <div class="form-group">

}                                    <div class="captcha-container">

                                        <label for="login_captcha" class="form-label">

function handleLogin($pdo) {                                            <span class="label-text">Security Verification</span>

    $errors = [];                                            <span class="label-icon">🤖</span>

    $response = ['success' => false];                                        </label>

                                            <div class="captcha-wrapper">

    // Sanitize input                                            <div class="captcha-display" id="login_captcha_display" role="img" aria-label="CAPTCHA Code"></div>

    $username = sanitizeInput($_POST['username'] ?? '');                                            <button 

    $password = $_POST['password'] ?? '';                                                type="button" 

    $captcha = $_POST['captcha'] ?? '';                                                onclick="refreshCaptcha('login')" 

                                                    class="refresh-captcha"

    // Basic validation                                                title="Refresh CAPTCHA"

    if (empty($username)) {                                                aria-label="Refresh CAPTCHA code"

        $errors['username'] = 'Username or email is required';                                            >

    }                                                🔄

                                                </button>

    if (empty($password)) {                                        </div>

        $errors['password'] = 'Password is required';                                        <input 

    }                                            type="text" 

                                                id="login_captcha" 

    if (empty($captcha)) {                                            name="captcha" 

        $errors['captcha'] = 'Please enter the CAPTCHA';                                            class="form-input"

    }                                            placeholder="Enter CAPTCHA code"

                                                autocomplete="off"

    if (!empty($errors)) {                                            aria-describedby="login_captcha_error"

        $response['message'] = 'Please fill in all required fields';                                        >

        $response['errors'] = $errors;                                    </div>

        echo json_encode($response);                                    <span class="error-message" id="login_captcha_error" role="alert"></span>

        return;                                </div>

    }                                

                                    <button type="submit" class="submit-btn primary-btn" id="login-submit">

    try {                                    <span class="btn-text">Sign In</span>

        // Find user by username or email                                    <span class="btn-icon">🚀</span>

        $stmt = $pdo->prepare('SELECT id, username, email, password_hash, full_name, login_attempts, account_locked, locked_until FROM users WHERE username = ? OR email = ?');                                </button>

        $stmt->execute([$username, $username]);                            </form>

        $user = $stmt->fetch();                            

                                    <div class="form-footer">

        if (!$user) {                                <a href="#" id="forgot-password-link" class="forgot-link">

            $response['message'] = 'Invalid username/email or password';                                    <span class="link-icon">🔓</span>

            echo json_encode($response);                                    Forgot Password?

            return;                                </a>

        }                            </div>

                                </div>

        // Check if account is locked                        

        if ($user['account_locked'] && $user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {                        <!-- Registration Form -->

            $response['message'] = 'Account is temporarily locked due to too many failed login attempts. Please try again later.';                        <div id="register-form" class="form-container" role="tabpanel" aria-labelledby="register-toggle">

            echo json_encode($response);                            <div class="form-header">

            return;                                <h3 class="form-title">Create Account</h3>

        }                                <p class="form-subtitle">Join our secure platform today</p>

                                    </div>

        // Verify password                            

        if (!verifyPassword($password, $user['password_hash'])) {                            <form id="registerForm" class="auth-form" novalidate>

            // Increment login attempts                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            $loginAttempts = $user['login_attempts'] + 1;                                

            $accountLocked = false;                                <div class="form-group">

            $lockedUntil = null;                                    <label for="register_fullname" class="form-label">

                                                    <span class="label-text">Full Name</span>

            if ($loginAttempts >= 5) {                                        <span class="label-icon">👤</span>

                $accountLocked = true;                                    </label>

                $lockedUntil = (new DateTime())->add(new DateInterval('PT30M'))->format('Y-m-d H:i:s'); // 30 minutes                                    <input 

            }                                        type="text" 

                                                    id="register_fullname" 

            $stmt = $pdo->prepare('UPDATE users SET login_attempts = ?, account_locked = ?, locked_until = ? WHERE id = ?');                                        name="fullname" 

            $stmt->execute([$loginAttempts, $accountLocked, $lockedUntil, $user['id']]);                                        class="form-input"

                                                    placeholder="Enter your full name"

            if ($accountLocked) {                                        autocomplete="name"

                $response['message'] = 'Too many failed login attempts. Account locked for 30 minutes.';                                        required

            } else {                                        aria-describedby="register_fullname_error"

                $response['message'] = 'Invalid username/email or password. ' . (5 - $loginAttempts) . ' attempts remaining.';                                    >

            }                                    <span class="error-message" id="register_fullname_error" role="alert"></span>

            echo json_encode($response);                                </div>

            return;                                

        }                                <div class="form-group">

                                            <label for="register_username" class="form-label">

        // Successful login                                        <span class="label-text">Username</span>

        // Reset login attempts and unlock account                                        <span class="label-icon">🆔</span>

        $stmt = $pdo->prepare('UPDATE users SET login_attempts = 0, account_locked = FALSE, locked_until = NULL, last_login = NOW() WHERE id = ?');                                    </label>

        $stmt->execute([$user['id']]);                                    <input 

                                                type="text" 

        // Set session variables                                        id="register_username" 

        $_SESSION['user_id'] = $user['id'];                                        name="username" 

        $_SESSION['username'] = $user['username'];                                        class="form-input"

        $_SESSION['email'] = $user['email'];                                        placeholder="Choose a unique username"

        $_SESSION['full_name'] = $user['full_name'];                                        autocomplete="username"

        $_SESSION['login_time'] = time();                                        required

                                                aria-describedby="register_username_error"

        // Regenerate session ID for security                                    >

        session_regenerate_id(true);                                    <span class="error-message" id="register_username_error" role="alert"></span>

                                        </div>

        $response['success'] = true;                                

        $response['message'] = 'Login successful! Redirecting...';                                <div class="form-group">

        echo json_encode($response);                                    <label for="register_email" class="form-label">

                                                <span class="label-text">Email Address</span>

    } catch (PDOException $e) {                                        <span class="label-icon">📧</span>

        error_log('Login error: ' . $e->getMessage());                                    </label>

        $response['message'] = 'An error occurred during login. Please try again.';                                    <input 

        echo json_encode($response);                                        type="email" 

    }                                        id="register_email" 

}                                        name="email" 

?>                                        class="form-input"
                                        placeholder="Enter your email address"
                                        autocomplete="email"
                                        required
                                        aria-describedby="register_email_error"
                                    >
                                    <span class="error-message" id="register_email_error" role="alert"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="register_password" class="form-label">
                                        <span class="label-text">Password</span>
                                        <span class="label-icon">🔐</span>
                                    </label>
                                    <input 
                                        type="password" 
                                        id="register_password" 
                                        name="password" 
                                        class="form-input"
                                        placeholder="Create a strong password"
                                        autocomplete="new-password"
                                        required
                                        aria-describedby="register_password_error password-requirements"
                                    >
                                    <div class="password-requirements" id="password-requirements">
                                        <div class="requirements-header">
                                            <span class="req-icon">🛡️</span>
                                            <span class="req-title">Password Requirements:</span>
                                        </div>
                                        <ul class="requirements-list">
                                            <li>At least 8 characters long</li>
                                            <li>One uppercase letter (A-Z)</li>
                                            <li>One lowercase letter (a-z)</li>
                                            <li>One number (0-9)</li>
                                            <li>One special character (@$!%*?&)</li>
                                        </ul>
                                    </div>
                                    <span class="error-message" id="register_password_error" role="alert"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="register_confirm_password" class="form-label">
                                        <span class="label-text">Confirm Password</span>
                                        <span class="label-icon">🔒</span>
                                    </label>
                                    <input 
                                        type="password" 
                                        id="register_confirm_password" 
                                        name="confirm_password" 
                                        class="form-input"
                                        placeholder="Confirm your password"
                                        autocomplete="new-password"
                                        required
                                        aria-describedby="register_confirm_password_error"
                                    >
                                    <span class="error-message" id="register_confirm_password_error" role="alert"></span>
                                </div>
                                
                                <div class="form-group">
                                    <div class="captcha-container">
                                        <label for="register_captcha" class="form-label">
                                            <span class="label-text">Security Verification</span>
                                            <span class="label-icon">🤖</span>
                                        </label>
                                        <div class="captcha-wrapper">
                                            <div class="captcha-display" id="register_captcha_display" role="img" aria-label="CAPTCHA Code"></div>
                                            <button 
                                                type="button" 
                                                onclick="refreshCaptcha('register')" 
                                                class="refresh-captcha"
                                                title="Refresh CAPTCHA"
                                                aria-label="Refresh CAPTCHA code"
                                            >
                                                🔄
                                            </button>
                                        </div>
                                        <input 
                                            type="text" 
                                            id="register_captcha" 
                                            name="captcha" 
                                            class="form-input"
                                            placeholder="Enter CAPTCHA code"
                                            autocomplete="off"
                                            required
                                            aria-describedby="register_captcha_error"
                                        >
                                    </div>
                                    <span class="error-message" id="register_captcha_error" role="alert"></span>
                                </div>
                                
                                <button type="submit" class="submit-btn primary-btn" id="register-submit">
                                    <span class="btn-text">Create Account</span>
                                    <span class="btn-icon">✨</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Features Section -->
                <section class="features-section">
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">🔒</div>
                            <h4 class="feature-title">Password Hashing</h4>
                            <p class="feature-description">Secure password storage using PHP's password_hash() function</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">🛡️</div>
                            <h4 class="feature-title">CSRF Protection</h4>
                            <p class="feature-description">Cross-Site Request Forgery protection on all forms</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">🚫</div>
                            <h4 class="feature-title">SQL Injection Safe</h4>
                            <p class="feature-description">Prepared statements prevent SQL injection attacks</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">⚡</div>
                            <h4 class="feature-title">Real-time Validation</h4>
                            <p class="feature-description">Client and server-side validation with AJAX</p>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Success/Error Messages -->
        <div id="message-container" class="message-container" style="display: none;" role="alert" aria-live="polite">
            <div id="message-content" class="message-content"></div>
            <button id="message-close" class="message-close" aria-label="Close message">×</button>
        </div>

        <!-- Footer -->
        <footer class="landing-footer">
            <div class="footer-content">
                <p class="footer-text">
                    © 2025 SecureAuth Practice 13 | Built with advanced security features
                </p>
                <div class="footer-links">
                    <span class="footer-tech">PHP • MySQL • AJAX • Security</span>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>