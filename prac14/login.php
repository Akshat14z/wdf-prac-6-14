<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config.php';
    
    // Redirect if already logged in
    if ($userAuth->isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
    
    $error = '';
    $success = '';
    
    // Handle success messages
    if (isset($_GET['success'])) {
        switch ($_GET['success']) {
            case 'logout':
                $success = 'You have been logged out successfully.';
                break;
        }
    }
    
    // Handle error messages
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'access_denied':
                $error = 'Access denied. Admin privileges required.';
                break;
        }
    }
    
    // Generate CSRF token
    $csrfToken = SecurityUtil::generateCSRFToken();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !SecurityUtil::verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid security token. Please try again.';
        } else {
            $username = SecurityUtil::sanitizeInput($_POST['username'] ?? '', 'string');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            if (empty($username) || empty($password)) {
                $error = 'Please enter both username/email and password.';
            } else {
                $result = $userAuth->login($username, $password, $rememberMe);
                
                if ($result['success']) {
                    // Check if user has admin privileges
                    if ($userAuth->hasRole('admin')) {
                        header('Location: index.php?success=login');
                        exit;
                    } else {
                        // User doesn't have admin access
                        $userAuth->logout();
                        $error = 'Access denied. Admin privileges required to access this system.';
                    }
                } else {
                    $error = $result['error'];
                }
            }
        }
    }
    ?>

    <div class="container">
        <!-- Header Section -->
        <header class="header fade-in">
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-shield-alt"></i> Admin Portal</h1>
                    <p>Secure login to admin dashboard</p>
                </div>
            </div>
        </header>

        <!-- Login Form -->
        <div style="display: flex; justify-content: center; align-items: center; min-height: 60vh;">
            <div class="card fade-in" style="width: 100%; max-width: 450px;">
                <div class="card-header">
                    <h2><i class="fas fa-sign-in-alt"></i> Admin Login</h2>
                    <p>Enter your admin credentials to access the dashboard</p>
                </div>
                
                <!-- Display success message -->
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Display error message -->
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" id="loginForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <!-- Username/Email Field -->
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username or Email Address
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your admin username or email"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               required
                               autocomplete="username"
                               autofocus>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div style="position: relative;">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required
                                   autocomplete="current-password">
                            <button type="button" 
                                    id="togglePassword" 
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #CDC9C3; cursor: pointer; padding: 5px;"
                                    title="Show/Hide Password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div style="display: flex; align-items: center; margin-bottom: 2rem;">
                        <input type="checkbox" id="remember_me" name="remember_me" style="width: auto; margin-right: 0.5rem;">
                        <label for="remember_me" style="margin: 0; font-weight: normal; cursor: pointer;">
                            Remember me for 30 days
                        </label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i> Sign In to Dashboard
                    </button>
                </form>
            </div>
        </div>

        <!-- Demo Accounts Information -->
        <div class="card fade-in" style="max-width: 800px; margin: 2rem auto;">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Demo Admin Accounts</h3>
                <p>Use these accounts to test the admin dashboard functionality</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div style="text-align: center; padding: 1.5rem; background: rgba(217, 228, 221, 0.3); border-radius: 8px;">
                    <div style="font-size: 2rem; color: #CDC9C3; margin-bottom: 1rem;">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">Super Admin</h4>
                    <div style="background: rgba(251, 247, 240, 0.8); padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.9rem;">
                        <strong>Username:</strong> superadmin<br>
                        <strong>Password:</strong> SuperAdmin123!<br>
                        <strong>Email:</strong> superadmin@demo.com
                    </div>
                    <p style="margin-top: 1rem; font-size: 0.9rem; color: #CDC9C3;">Full system control including user role management</p>
                </div>
                
                <div style="text-align: center; padding: 1.5rem; background: rgba(217, 228, 221, 0.3); border-radius: 8px;">
                    <div style="font-size: 2rem; color: #CDC9C3; margin-bottom: 1rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">Admin</h4>
                    <div style="background: rgba(251, 247, 240, 0.8); padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.9rem;">
                        <strong>Username:</strong> admin<br>
                        <strong>Password:</strong> AdminPass123!<br>
                        <strong>Email:</strong> admin@demo.com
                    </div>
                    <p style="margin-top: 1rem; font-size: 0.9rem; color: #CDC9C3;">User management and system administration</p>
                </div>
                
                <div style="text-align: center; padding: 1.5rem; background: rgba(217, 228, 221, 0.3); border-radius: 8px;">
                    <div style="font-size: 2rem; color: #CDC9C3; margin-bottom: 1rem;">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">Moderator</h4>
                    <div style="background: rgba(251, 247, 240, 0.8); padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.9rem;">
                        <strong>Username:</strong> moderator<br>
                        <strong>Password:</strong> ModeratorPass123!<br>
                        <strong>Email:</strong> moderator@demo.com
                    </div>
                    <p style="margin-top: 1rem; font-size: 0.9rem; color: #CDC9C3;">Limited user management for content moderation</p>
                </div>
            </div>
        </div>

        <!-- Security Features -->
        <div class="card fade-in" style="max-width: 800px; margin: 2rem auto;">
            <div class="card-header">
                <h3><i class="fas fa-shield-alt"></i> Security Features</h3>
                <p>Advanced security measures protecting the admin dashboard</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 1.8rem; color: #CDC9C3; margin-bottom: 0.5rem;">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <h5 style="color: #555555; margin-bottom: 0.5rem;">Role-Based Access</h5>
                    <p style="font-size: 0.9rem; color: #CDC9C3;">Only admin+ roles can access</p>
                </div>
                
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 1.8rem; color: #CDC9C3; margin-bottom: 0.5rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5 style="color: #555555; margin-bottom: 0.5rem;">Rate Limiting</h5>
                    <p style="font-size: 0.9rem; color: #CDC9C3;"><?php echo MAX_LOGIN_ATTEMPTS; ?> attempts, <?php echo LOCKOUT_DURATION/60; ?>min lockout</p>
                </div>
                
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 1.8rem; color: #CDC9C3; margin-bottom: 0.5rem;">
                        <i class="fas fa-key"></i>
                    </div>
                    <h5 style="color: #555555; margin-bottom: 0.5rem;">Password Security</h5>
                    <p style="font-size: 0.9rem; color: #CDC9C3;">bcrypt hashing with cost <?php echo HASH_COST; ?></p>
                </div>
                
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 1.8rem; color: #CDC9C3; margin-bottom: 0.5rem;">
                        <i class="fas fa-history"></i>
                    </div>
                    <h5 style="color: #555555; margin-bottom: 0.5rem;">Activity Logging</h5>
                    <p style="font-size: 0.9rem; color: #CDC9C3;">Complete audit trail</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const loginBtn = document.getElementById('loginBtn');
            
            // Password visibility toggle
            togglePasswordBtn.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                
                const icon = this.querySelector('i');
                icon.classList.remove('fa-eye', 'fa-eye-slash');
                icon.classList.add(isPassword ? 'fa-eye-slash' : 'fa-eye');
                
                this.title = isPassword ? 'Hide Password' : 'Show Password';
            });
            
            // Form submission with loading state
            loginForm.addEventListener('submit', function(e) {
                const username = usernameInput.value.trim();
                const password = passwordInput.value;
                
                if (!username || !password) {
                    e.preventDefault();
                    showError('Please fill in all required fields.');
                    return;
                }
                
                // Show loading state
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<span class="loading"></span> Signing In...';
            });
            
            // Real-time input validation
            usernameInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#D9E4DD';
                    this.style.backgroundColor = '#FFFFFF';
                }
            });
            
            passwordInput.addEventListener('input', function() {
                if (this.value.length >= 8) {
                    this.style.borderColor = '#D9E4DD';
                    this.style.backgroundColor = '#FFFFFF';
                }
            });
            
            function showError(message) {
                const existingAlert = document.querySelector('.alert-error');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                const alert = document.createElement('div');
                alert.className = 'alert alert-error';
                alert.innerHTML = `
                    <i class="fas fa-exclamation-circle"></i>
                    ${message}
                `;
                
                const form = document.querySelector('form');
                form.parentNode.insertBefore(alert, form);
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            }
            
            // Auto-hide existing alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }, 8000);
            });
            
            // Add some visual feedback to demo account cards
            const demoCards = document.querySelectorAll('[style*="background: rgba(217, 228, 221, 0.3)"]');
            demoCards.forEach(card => {
                card.style.cursor = 'pointer';
                card.style.transition = 'all 0.3s ease';
                
                card.addEventListener('click', function() {
                    const credentialsDiv = this.querySelector('[style*="font-family: monospace"]');
                    const username = credentialsDiv.innerHTML.match(/Username:<\/strong> (\w+)/)[1];
                    const password = credentialsDiv.innerHTML.match(/Password:<\/strong> ([^<]+)/)[1];
                    
                    usernameInput.value = username;
                    passwordInput.value = password;
                    
                    // Visual feedback
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
                
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                    this.style.boxShadow = '0 8px 25px rgba(85, 85, 85, 0.15)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>