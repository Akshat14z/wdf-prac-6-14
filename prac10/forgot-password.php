<?php
require_once 'config.php';

$error = '';
$success = '';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email exists in database
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // For demo purposes, we'll just show the success message without actually sending an email
            // In a real application, you would generate a secure token and send it via email
            $success = 'If an account with that email exists, a password reset link has been sent to your email address.';
            
            // Log the password reset request
            logLoginAttempt($user['username'] . ' (password reset)', true);
        } else {
            // Don't reveal whether the email exists or not (security best practice)
            $success = 'If an account with that email exists, a password reset link has been sent to your email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Secure Login System</title>
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

        .forgot-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            position: relative;
        }

        .forgot-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .forgot-header .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--orange);
        }

        .forgot-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .forgot-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .forgot-form {
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

        .reset-btn {
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

        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 128, 64, 0.4);
        }

        .reset-btn:active {
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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

        .info-box {
            background: var(--light-gray);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-box h4 {
            color: var(--primary-blue);
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-box p {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Loading state */
        .reset-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .forgot-container {
                margin: 1rem;
                max-width: none;
            }

            .forgot-header, .forgot-form {
                padding: 1.5rem;
            }

            .forgot-header h1 {
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

        .forgot-container {
            animation: slideIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <a href="login.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Login
            </a>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <h1>Forgot Password</h1>
            <p>Reset your account password</p>
        </div>

        <form method="POST" class="forgot-form" id="forgotForm">
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

            <div class="info-box">
                <h4>
                    <i class="fas fa-info-circle"></i>
                    Password Reset Process
                </h4>
                <p>
                    Enter your email address below and we'll send you a secure link to reset your password. 
                    For this demo, the process is simulated and no actual email will be sent.
                </p>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required 
                       autocomplete="email"
                       placeholder="Enter your registered email">
                <i class="fas fa-envelope input-icon"></i>
            </div>

            <button type="submit" class="reset-btn" id="resetBtn">
                <i class="fas fa-paper-plane"></i>
                Send Reset Link
            </button>

            <div class="form-footer">
                <p style="margin-bottom: 0.5rem;">
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt"></i>
                        Back to Login
                    </a>
                </p>
                <p>
                    <a href="register.php">
                        <i class="fas fa-user-plus"></i>
                        Create New Account
                    </a>
                </p>
            </div>
        </form>
    </div>

    <script>
        // Form submission handling
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('resetBtn');
            const email = document.getElementById('email').value.trim();

            if (!email) {
                e.preventDefault();
                alert('Please enter your email address.');
                return;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }

            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        });

        // Auto-focus on email field
        window.addEventListener('load', function() {
            document.getElementById('email').focus();
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

        // Demo email suggestions
        const emailInput = document.getElementById('email');
        const demoEmails = ['admin@example.com', 'user@example.com', 'demo@example.com'];
        
        emailInput.addEventListener('dblclick', function() {
            const randomEmail = demoEmails[Math.floor(Math.random() * demoEmails.length)];
            this.value = randomEmail;
            
            // Visual feedback
            this.style.background = 'rgba(0, 70, 255, 0.1)';
            setTimeout(() => {
                this.style.background = 'var(--white)';
            }, 300);
        });
        
        // Add tooltip for demo functionality
        emailInput.title = 'Double-click for demo email addresses';
    </script>
</body>
</html>