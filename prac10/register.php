<?php
require_once 'config.php';

$error = '';
$success = '';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists. Please choose a different one.';
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email address already registered. Please use a different one.';
                } else {
                    // Create new user
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                    $stmt->execute([$username, $email, $hashedPassword]);
                    
                    $success = 'Registration successful! You can now login with your credentials.';
                    
                    // Log the registration
                    logLoginAttempt($username . ' (registration)', true);
                    
                    // Clear form data on success
                    $username = $email = '';
                }
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Secure Login System</title>
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

        .register-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            position: relative;
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .register-header .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--orange);
        }

        .register-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .register-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .register-form {
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

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }

        .strength-weak { color: var(--error-color); }
        .strength-medium { color: var(--orange); }
        .strength-strong { color: var(--success-color); }

        .register-btn {
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

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 128, 64, 0.4);
        }

        .register-btn:active {
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

        .requirements {
            background: var(--light-gray);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .requirements h4 {
            color: var(--primary-blue);
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .requirements ul {
            list-style: none;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .requirements li {
            margin: 0.3rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .req-check {
            color: var(--success-color);
        }

        .req-times {
            color: var(--error-color);
        }

        /* Loading state */
        .register-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .register-container {
                margin: 1rem;
                max-width: none;
            }

            .register-header, .register-form {
                padding: 1.5rem;
            }

            .register-header h1 {
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

        .register-container {
            animation: slideIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
            <div class="icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p>Join our secure platform today</p>
        </div>

        <form method="POST" class="register-form" id="registerForm">
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

            <div class="requirements">
                <h4>
                    <i class="fas fa-shield-alt"></i>
                    Account Requirements
                </h4>
                <ul id="requirements">
                    <li id="req-username">
                        <i class="fas fa-times req-times"></i>
                        Username: at least 3 characters
                    </li>
                    <li id="req-email">
                        <i class="fas fa-times req-times"></i>
                        Valid email address
                    </li>
                    <li id="req-password">
                        <i class="fas fa-times req-times"></i>
                        Password: at least 6 characters
                    </li>
                    <li id="req-match">
                        <i class="fas fa-times req-times"></i>
                        Passwords must match
                    </li>
                </ul>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?= htmlspecialchars($username ?? '') ?>"
                       required 
                       autocomplete="username"
                       placeholder="Choose a username"
                       minlength="3">
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= htmlspecialchars($email ?? '') ?>"
                       required 
                       autocomplete="email"
                       placeholder="Enter your email">
                <i class="fas fa-envelope input-icon"></i>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="new-password"
                       placeholder="Create a password"
                       minlength="6">
                <i class="fas fa-lock input-icon"></i>
                <div class="password-strength" id="passwordStrength"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       required 
                       autocomplete="new-password"
                       placeholder="Confirm your password"
                       minlength="6">
                <i class="fas fa-lock input-icon"></i>
            </div>

            <button type="submit" class="register-btn" id="registerBtn">
                <i class="fas fa-user-plus"></i>
                Create Account
            </button>

            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </form>
    </div>

    <script>
        // Password strength checker
        function checkPasswordStrength(password) {
            let score = 0;
            let feedback = [];
            
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            let strength = '';
            let className = '';
            
            if (score < 2) {
                strength = 'Weak';
                className = 'strength-weak';
            } else if (score < 4) {
                strength = 'Medium';
                className = 'strength-medium';
            } else {
                strength = 'Strong';
                className = 'strength-strong';
            }
            
            return { strength, className, score };
        }

        // Update requirement indicators
        function updateRequirements() {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Username requirement
            const usernameReq = document.getElementById('req-username');
            const usernameIcon = usernameReq.querySelector('i');
            if (username.length >= 3) {
                usernameIcon.className = 'fas fa-check req-check';
            } else {
                usernameIcon.className = 'fas fa-times req-times';
            }
            
            // Email requirement
            const emailReq = document.getElementById('req-email');
            const emailIcon = emailReq.querySelector('i');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(email)) {
                emailIcon.className = 'fas fa-check req-check';
            } else {
                emailIcon.className = 'fas fa-times req-times';
            }
            
            // Password requirement
            const passwordReq = document.getElementById('req-password');
            const passwordIcon = passwordReq.querySelector('i');
            if (password.length >= 6) {
                passwordIcon.className = 'fas fa-check req-check';
            } else {
                passwordIcon.className = 'fas fa-times req-times';
            }
            
            // Password match requirement
            const matchReq = document.getElementById('req-match');
            const matchIcon = matchReq.querySelector('i');
            if (password && confirmPassword && password === confirmPassword) {
                matchIcon.className = 'fas fa-check req-check';
            } else {
                matchIcon.className = 'fas fa-times req-times';
            }
        }

        // Event listeners
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthElement = document.getElementById('passwordStrength');
            
            if (password.length > 0) {
                const { strength, className } = checkPasswordStrength(password);
                strengthElement.textContent = `Password strength: ${strength}`;
                strengthElement.className = `password-strength ${className}`;
            } else {
                strengthElement.textContent = '';
            }
            
            updateRequirements();
        });

        // Update requirements on input
        ['username', 'email', 'password', 'confirm_password'].forEach(id => {
            document.getElementById(id).addEventListener('input', updateRequirements);
        });

        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('registerBtn');
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Basic validation
            if (!username || !email || !password || !confirmPassword) {
                e.preventDefault();
                alert('Please fill in all fields.');
                return;
            }

            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters long.');
                return;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }

            // Show loading state
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        });

        // Auto-focus on username field
        window.addEventListener('load', function() {
            document.getElementById('username').focus();
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

        // Initialize requirements check
        updateRequirements();
    </script>
</body>
</html>