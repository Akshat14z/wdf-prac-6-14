<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config.php';
    
    // Check if user is logged in
    $userAuth->requireAdmin();
    
    $user = $userAuth->getCurrentUser();
    $error = '';
    $success = '';
    
    // Generate CSRF token
    $csrfToken = SecurityUtil::generateCSRFToken();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !SecurityUtil::verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid security token. Please try again.';
        } else {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_profile') {
                $firstName = SecurityUtil::sanitizeInput($_POST['first_name'] ?? '', 'string');
                $lastName = SecurityUtil::sanitizeInput($_POST['last_name'] ?? '', 'string');
                $email = SecurityUtil::sanitizeInput($_POST['email'] ?? '', 'email');
                
                // Validate inputs
                if (empty($firstName) || empty($lastName) || empty($email)) {
                    $error = 'All fields are required.';
                } elseif (!SecurityUtil::validateEmail($email)) {
                    $error = 'Please enter a valid email address.';
                } else {
                    // Check if email is already taken by another user
                    $checkEmailQuery = "SELECT id FROM users WHERE email = ? AND id != ?";
                    $checkEmailStmt = $db->prepare($checkEmailQuery);
                    $checkEmailStmt->execute([$email, $user['id']]);
                    
                    if ($checkEmailStmt->fetch()) {
                        $error = 'Email address is already registered to another account.';
                    } else {
                        // Update user profile
                        $updateQuery = "UPDATE users SET first_name = ?, last_name = ?, email = ?, updated_at = NOW() WHERE id = ?";
                        $updateStmt = $db->prepare($updateQuery);
                        
                        if ($updateStmt->execute([$firstName, $lastName, $email, $user['id']])) {
                            // Log the activity
                            $securityUtil->logActivity($user['id'], $user['id'], 'profile_update', 'Admin profile updated');
                            
                            $success = 'Profile updated successfully!';
                            
                            // Refresh user data
                            $user = $userAuth->getCurrentUser();
                        } else {
                            $error = 'Failed to update profile. Please try again.';
                        }
                    }
                }
            } elseif ($action === 'change_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    $error = 'All password fields are required.';
                } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New passwords do not match.';
                } elseif (!SecurityUtil::validatePassword($newPassword)) {
                    $error = 'New password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.';
                } else {
                    // Hash the new password
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
                    
                    // Update password
                    $updatePasswordQuery = "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?";
                    $updatePasswordStmt = $db->prepare($updatePasswordQuery);
                    
                    if ($updatePasswordStmt->execute([$hashedPassword, $user['id']])) {
                        // Log the activity
                        $securityUtil->logActivity($user['id'], $user['id'], 'password_change', 'Admin password changed');
                        
                        // Clear all user sessions except current one for security
                        $clearSessionsQuery = "DELETE FROM user_sessions WHERE user_id = ? AND session_id != ?";
                        $clearSessionsStmt = $db->prepare($clearSessionsQuery);
                        $clearSessionsStmt->execute([$user['id'], session_id()]);
                        
                        $success = 'Password changed successfully! Other sessions have been logged out for security.';
                    } else {
                        $error = 'Failed to change password. Please try again.';
                    }
                }
            }
        }
    }
    
    // Get user's recent activity
    $activityQuery = "SELECT action_type, description, ip_address, created_at 
                     FROM activity_log 
                     WHERE user_id = ? OR admin_id = ? 
                     ORDER BY created_at DESC 
                     LIMIT 10";
    $activityStmt = $db->prepare($activityQuery);
    $activityStmt->execute([$user['id'], $user['id']]);
    $activities = $activityStmt->fetchAll();
    ?>

    <div class="container">
        <!-- Header Section -->
        <header class="header fade-in">
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-user-cog"></i> Profile Settings</h1>
                    <p>Manage your admin account settings</p>
                </div>
                <nav class="nav-menu">
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="users.php" class="nav-item">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="profile.php" class="nav-item active">
                        <i class="fas fa-user-cog"></i> Profile
                    </a>
                    <a href="logout.php" class="nav-item" onclick="return confirm('Are you sure you want to log out?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
        </header>

        <!-- Status Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success fade-in">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error fade-in">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Profile Header Card -->
        <div class="card fade-in">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <div style="font-size: 4rem; color: #CDC9C3;">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div style="flex: 1;">
                    <h2 style="color: #555555; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </h2>
                    <p style="color: #CDC9C3; margin-bottom: 1rem;">
                        @<?php echo htmlspecialchars($user['username']); ?> • 
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                        </span>
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 2rem; font-size: 0.9rem; color: #CDC9C3;">
                        <span>
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                        </span>
                        <span>
                            <i class="fas fa-calendar-alt"></i> Member since <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                        </span>
                        <span>
                            <i class="fas fa-clock"></i> Last login: <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Profile Settings -->
            <div>
                <!-- Personal Information Form -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3><i class="fas fa-user-edit"></i> Personal Information</h3>
                        <p>Update your basic profile information</p>
                    </div>
                    
                    <form method="POST" action="profile.php" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label for="first_name">
                                    <i class="fas fa-user"></i> First Name *
                                </label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">
                                    <i class="fas fa-user"></i> Last Name *
                                </label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <i class="fas fa-user-tag"></i> Username
                            </label>
                            <input type="text" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>"
                                   disabled
                                   style="background: rgba(205, 201, 195, 0.2); color: #CDC9C3;">
                            <small>Username cannot be changed for security reasons</small>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <i class="fas fa-shield-alt"></i> Role
                            </label>
                            <input type="text" 
                                   value="<?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>"
                                   disabled
                                   style="background: rgba(205, 201, 195, 0.2); color: #CDC9C3;">
                            <small>Role is managed by super administrators</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3><i class="fas fa-key"></i> Change Password</h3>
                        <p>Update your account password for enhanced security</p>
                    </div>
                    
                    <form method="POST" action="profile.php" id="passwordForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password">
                                <i class="fas fa-lock"></i> Current Password *
                            </label>
                            <div style="position: relative;">
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       required>
                                <button type="button" 
                                        class="password-toggle"
                                        onclick="togglePasswordVisibility('current_password', this)"
                                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #CDC9C3; cursor: pointer; padding: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-key"></i> New Password *
                            </label>
                            <div style="position: relative;">
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       required>
                                <button type="button" 
                                        class="password-toggle"
                                        onclick="togglePasswordVisibility('new_password', this)"
                                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #CDC9C3; cursor: pointer; padding: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength" style="display: none; margin-top: 1rem;">
                                <div style="width: 100%; height: 6px; background: rgba(205, 201, 195, 0.3); border-radius: 3px; overflow: hidden; margin-bottom: 0.5rem;">
                                    <div id="strengthFill" style="height: 100%; transition: width 0.3s ease, background-color 0.3s ease; border-radius: 3px;"></div>
                                </div>
                                <div id="strengthText" style="font-size: 0.9rem; color: #CDC9C3; font-weight: 600;"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-check"></i> Confirm New Password *
                            </label>
                            <div style="position: relative;">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required>
                                <button type="button" 
                                        class="password-toggle"
                                        onclick="togglePasswordVisibility('confirm_password', this)"
                                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #CDC9C3; cursor: pointer; padding: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatchStatus" style="margin-top: 0.5rem; font-size: 0.9rem; display: none;"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Account Security -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3><i class="fas fa-shield-alt"></i> Account Security</h3>
                        <p>Security status and settings</p>
                    </div>
                    
                    <div style="space-y: 1rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid rgba(205, 201, 195, 0.3);">
                            <div>
                                <strong style="color: #555555;">Two-Factor Auth</strong>
                                <div style="font-size: 0.85rem; color: #CDC9C3;">Not configured</div>
                            </div>
                            <span style="color: #8b0000; font-size: 0.9rem;">
                                <i class="fas fa-times-circle"></i> Disabled
                            </span>
                        </div>
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid rgba(205, 201, 195, 0.3);">
                            <div>
                                <strong style="color: #555555;">Password Strength</strong>
                                <div style="font-size: 0.85rem; color: #CDC9C3;">Strong encryption</div>
                            </div>
                            <span style="color: #2d5a2d; font-size: 0.9rem;">
                                <i class="fas fa-check-circle"></i> Strong
                            </span>
                        </div>
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 0;">
                            <div>
                                <strong style="color: #555555;">Session Security</strong>
                                <div style="font-size: 0.85rem; color: #CDC9C3;">Auto-logout enabled</div>
                            </div>
                            <span style="color: #2d5a2d; font-size: 0.9rem;">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Recent Activity</h3>
                        <p>Your latest account actions</p>
                    </div>
                    
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php if ($activities): ?>
                            <?php foreach ($activities as $activity): ?>
                                <div class="activity-item" style="margin-bottom: 1rem; padding: 1rem; background: rgba(217, 228, 221, 0.2); border-radius: 8px;">
                                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                                        <div style="width: 30px; height: 30px; background: #CDC9C3; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #555555; font-size: 0.9rem; flex-shrink: 0;">
                                            <?php
                                            $iconClass = 'fas fa-user';
                                            switch ($activity['action_type']) {
                                                case 'login': $iconClass = 'fas fa-sign-in-alt'; break;
                                                case 'logout': $iconClass = 'fas fa-sign-out-alt'; break;
                                                case 'password_change': $iconClass = 'fas fa-key'; break;
                                                case 'profile_update': $iconClass = 'fas fa-edit'; break;
                                                case 'user_status_change': $iconClass = 'fas fa-toggle-on'; break;
                                                case 'user_role_change': $iconClass = 'fas fa-user-cog'; break;
                                                case 'user_deletion': $iconClass = 'fas fa-user-times'; break;
                                            }
                                            ?>
                                            <i class="<?php echo $iconClass; ?>"></i>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; color: #555555; font-size: 0.9rem; margin-bottom: 0.3rem;">
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #CDC9C3;">
                                                <i class="fas fa-clock"></i> <?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: #CDC9C3;">
                                <i class="fas fa-info-circle"></i>
                                <p>No recent activity found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Quick Stats</h3>
                        <p>Your account overview</p>
                    </div>
                    
                    <div style="display: grid; gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: rgba(217, 228, 221, 0.3); border-radius: 8px;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #555555;">
                                <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #CDC9C3;">ROLE LEVEL</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem; background: rgba(217, 228, 221, 0.3); border-radius: 8px;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #555555;">
                                <?php echo count($activities); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #CDC9C3;">RECENT ACTIONS</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem; background: rgba(217, 228, 221, 0.3); border-radius: 8px;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #555555;">
                                <?php echo date('j', strtotime($user['created_at'])); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #CDC9C3;">DAYS AS ADMIN</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            const passwordMatchStatus = document.getElementById('passwordMatchStatus');
            
            // Password strength checker
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                if (password) {
                    passwordStrength.style.display = 'block';
                    const strength = checkPasswordStrength(password);
                    updatePasswordStrengthDisplay(strength);
                } else {
                    passwordStrength.style.display = 'none';
                }
                checkPasswordMatch();
            });
            
            // Confirm password checker
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
            
            function checkPasswordStrength(password) {
                let score = 0;
                let feedback = [];
                
                if (password.length >= 8) score += 1;
                else feedback.push('at least 8 characters');
                
                if (/[a-z]/.test(password)) score += 1;
                else feedback.push('lowercase letter');
                
                if (/[A-Z]/.test(password)) score += 1;
                else feedback.push('uppercase letter');
                
                if (/\d/.test(password)) score += 1;
                else feedback.push('number');
                
                if (/[^A-Za-z0-9]/.test(password)) score += 1;
                else feedback.push('special character');
                
                return { score, feedback };
            }
            
            function updatePasswordStrengthDisplay(strength) {
                const colors = ['#E8B4B8', '#FBF7F0', '#D9E4DD', '#CDC9C3', '#555555'];
                const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                const widths = ['20%', '40%', '60%', '80%', '100%'];
                
                strengthFill.style.backgroundColor = colors[strength.score - 1] || colors[0];
                strengthFill.style.width = widths[strength.score - 1] || '0%';
                strengthText.textContent = labels[strength.score - 1] || 'Very Weak';
                
                if (strength.feedback.length > 0) {
                    strengthText.textContent += ' - Add: ' + strength.feedback.join(', ');
                }
            }
            
            function checkPasswordMatch() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword) {
                    passwordMatchStatus.style.display = 'block';
                    if (newPassword === confirmPassword) {
                        passwordMatchStatus.innerHTML = '<i class="fas fa-check" style="color: #2d5a2d;"></i> Passwords match';
                        passwordMatchStatus.style.color = '#2d5a2d';
                    } else {
                        passwordMatchStatus.innerHTML = '<i class="fas fa-times" style="color: #8b0000;"></i> Passwords do not match';
                        passwordMatchStatus.style.color = '#8b0000';
                    }
                } else {
                    passwordMatchStatus.style.display = 'none';
                }
            }
            
            // Auto-hide alerts
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
        });
        
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                button.title = 'Hide Password';
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                button.title = 'Show Password';
            }
        }
    </script>
</body>
</html>