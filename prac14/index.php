<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Practice 14</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config.php';
    
    // Require admin access
    if (!$userAuth->isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    // Check if user has admin privileges
    if (!$userAuth->hasRole('admin')) {
        header('Location: login.php?error=access_denied');
        exit;
    }
    
    $currentUser = $userAuth->getCurrentUser();
    $dashboardStats = $adminManager->getDashboardStats();
    $recentActivity = $adminManager->getActivityLog(10);
    
    // Handle success messages
    $success = '';
    if (isset($_GET['success'])) {
        switch ($_GET['success']) {
            case 'login':
                $success = 'Welcome to the Admin Dashboard!';
                break;
            case 'user_updated':
                $success = 'User has been updated successfully.';
                break;
            case 'user_deleted':
                $success = 'User has been deleted successfully.';
                break;
        }
    }
    ?>

    <div class="container">
        <!-- Header Section -->
        <header class="header fade-in">
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                    <p>User Management & System Administration</p>
                </div>
                <nav class="nav-menu">
                    <a href="index.php" class="nav-item active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="users.php" class="nav-item">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="profile.php" class="nav-item">
                        <i class="fas fa-user-cog"></i> Profile
                    </a>
                    <a href="logout.php" class="nav-item" onclick="return confirm('Are you sure you want to log out?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
        </header>

        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="alert alert-success fade-in">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="card fade-in">
            <div class="card-header">
                <h2>
                    <i class="fas fa-user-shield"></i> 
                    Welcome, <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                </h2>
                <p>You are logged in as <strong><?php echo ucfirst(str_replace('_', ' ', $currentUser['role'])); ?></strong></p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; align-items: center;">
                <div>
                    <h4 style="color: #555555; margin-bottom: 0.5rem;">Account Information</h4>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($currentUser['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
                    <p><strong>Last Login:</strong> <?php echo $currentUser['last_login'] ? date('M j, Y g:i A', strtotime($currentUser['last_login'])) : 'Never'; ?></p>
                </div>
                
                <div style="text-align: center;">
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Dashboard Statistics -->
        <div class="stats-grid fade-in">
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-number"><?php echo $dashboardStats['total_users']; ?></div>
                <div class="stat-card-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-card-number"><?php echo $dashboardStats['active_users']; ?></div>
                <div class="stat-card-label">Active Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-card-number"><?php echo $dashboardStats['inactive_users']; ?></div>
                <div class="stat-card-label">Inactive Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-card-number"><?php echo $dashboardStats['suspended_users']; ?></div>
                <div class="stat-card-label">Suspended Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-card-number"><?php echo $dashboardStats['recent_activity']; ?></div>
                <div class="stat-card-label">Recent Activity</div>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="card fade-in">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> User Role Distribution</h3>
                <p>Current distribution of user roles in the system</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                <?php foreach ($dashboardStats['roles'] as $role => $count): ?>
                    <div style="text-align: center; padding: 1.5rem; background: rgba(217, 228, 221, 0.3); border-radius: 8px;">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">
                            <?php
                            $roleIcons = [
                                'super_admin' => 'fas fa-crown',
                                'admin' => 'fas fa-user-shield',
                                'moderator' => 'fas fa-user-cog',
                                'user' => 'fas fa-user'
                            ];
                            ?>
                            <i class="<?php echo $roleIcons[$role] ?? 'fas fa-user'; ?>" style="color: #CDC9C3;"></i>
                        </div>
                        <div style="font-size: 1.8rem; font-weight: 700; color: #555555; margin-bottom: 0.5rem;">
                            <?php echo $count; ?>
                        </div>
                        <div style="color: #CDC9C3; font-weight: 600; text-transform: uppercase; font-size: 0.9rem;">
                            <?php echo ucfirst(str_replace('_', ' ', $role)); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card fade-in">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <p>Common administrative tasks</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; color: #CDC9C3; margin-bottom: 1rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">Manage Users</h4>
                    <p style="color: #CDC9C3; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        View, edit, activate, deactivate, and delete user accounts
                    </p>
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-users"></i> Go to Users
                    </a>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; color: #CDC9C3; margin-bottom: 1rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">View Activity</h4>
                    <p style="color: #CDC9C3; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        Monitor user activities and system events
                    </p>
                    <a href="#activity-log" class="btn btn-secondary" onclick="document.getElementById('activity-log').scrollIntoView();">
                        <i class="fas fa-history"></i> View Activity
                    </a>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; color: #CDC9C3; margin-bottom: 1rem;">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">Account Settings</h4>
                    <p style="color: #CDC9C3; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        Update your profile and security settings
                    </p>
                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-user-cog"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity Log -->
        <div class="card fade-in" id="activity-log">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
                <p>Latest system activities and user actions</p>
            </div>
            
            <div style="max-height: 500px; overflow-y: auto;">
                <?php if ($recentActivity): ?>
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php
                                $iconClass = 'fas fa-user';
                                switch ($activity['action_type']) {
                                    case 'login': $iconClass = 'fas fa-sign-in-alt'; break;
                                    case 'logout': $iconClass = 'fas fa-sign-out-alt'; break;
                                    case 'user_status_change': $iconClass = 'fas fa-toggle-on'; break;
                                    case 'user_role_change': $iconClass = 'fas fa-user-cog'; break;
                                    case 'user_deletion': $iconClass = 'fas fa-user-times'; break;
                                    case 'failed_login': $iconClass = 'fas fa-exclamation-triangle'; break;
                                    case 'profile_update': $iconClass = 'fas fa-edit'; break;
                                }
                                ?>
                                <i class="<?php echo $iconClass; ?>"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-description">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </div>
                                <div class="activity-meta">
                                    <i class="fas fa-clock"></i> <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                    <?php if ($activity['admin_username']): ?>
                                        <i class="fas fa-user-shield"></i> Admin: <?php echo htmlspecialchars($activity['admin_username']); ?>
                                    <?php endif; ?>
                                    <?php if ($activity['user_username']): ?>
                                        <i class="fas fa-user"></i> User: <?php echo htmlspecialchars($activity['user_username']); ?>
                                    <?php endif; ?>
                                    <i class="fas fa-globe"></i> <?php echo htmlspecialchars($activity['ip_address']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-description">No recent activity found</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($recentActivity) >= 10): ?>
                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(205, 201, 195, 0.3);">
                    <a href="activity.php" class="btn btn-secondary">
                        <i class="fas fa-history"></i> View Full Activity Log
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- System Information -->
        <div class="card fade-in">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> System Information</h3>
                <p>Current system status and configuration</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">Security Configuration</h4>
                    <div style="background: rgba(217, 228, 221, 0.3); padding: 1rem; border-radius: 8px;">
                        <p><strong>Session Timeout:</strong> <?php echo SESSION_TIMEOUT / 60; ?> minutes</p>
                        <p><strong>Max Login Attempts:</strong> <?php echo MAX_LOGIN_ATTEMPTS; ?></p>
                        <p><strong>Lockout Duration:</strong> <?php echo LOCKOUT_DURATION / 60; ?> minutes</p>
                        <p><strong>Password Hash Cost:</strong> <?php echo HASH_COST; ?></p>
                    </div>
                </div>
                
                <div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">Role Permissions</h4>
                    <div style="background: rgba(217, 228, 221, 0.3); padding: 1rem; border-radius: 8px;">
                        <p><strong>Super Admin:</strong> Full system access</p>
                        <p><strong>Admin:</strong> User management</p>
                        <p><strong>Moderator:</strong> Content moderation</p>
                        <p><strong>User:</strong> Basic access</p>
                    </div>
                </div>
                
                <div>
                    <h4 style="color: #555555; margin-bottom: 1rem;">Database Status</h4>
                    <div style="background: rgba(217, 228, 221, 0.3); padding: 1rem; border-radius: 8px;">
                        <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                        <p><strong>Host:</strong> <?php echo DB_HOST; ?>:<?php echo DB_PORT; ?></p>
                        <p><strong>Connection:</strong> <span style="color: #2d5a2d; font-weight: 600;">Active</span></p>
                        <p><strong>Tables:</strong> 5 (Users, Sessions, Activity, Tokens, Audit)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success alerts
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
                }, 5000);
            });
            
            // Animate stats on scroll
            const observerOptions = {
                threshold: 0.5,
                rootMargin: '0px 0px -100px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const statNumber = entry.target.querySelector('.stat-card-number');
                        const finalNumber = parseInt(statNumber.textContent);
                        animateCounter(statNumber, 0, finalNumber, 1000);
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.stat-card').forEach(card => {
                observer.observe(card);
            });
            
            function animateCounter(element, start, end, duration) {
                const startTime = performance.now();
                
                function update(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    const current = Math.floor(start + (end - start) * easeOutCubic(progress));
                    element.textContent = current;
                    
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    }
                }
                
                requestAnimationFrame(update);
            }
            
            function easeOutCubic(t) {
                return 1 - Math.pow(1 - t, 3);
            }
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Add hover effects to stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>