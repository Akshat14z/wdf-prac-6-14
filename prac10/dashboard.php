<?php
require_once 'config.php';

// Require login to access dashboard
requireLogin();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$login_time = $_SESSION['login_time'] ?? time();
$last_activity = $_SESSION['last_activity'] ?? time();

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get current session info
$stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE session_id = ? AND is_active = 1");
$stmt->execute([session_id()]);
$current_session = $stmt->fetch();

// Get user's active sessions
$stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND is_active = 1 ORDER BY last_activity DESC");
$stmt->execute([$user_id]);
$active_sessions = $stmt->fetchAll();

// Get recent login attempts (admin only)
$recent_logins = [];
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM login_attempts ORDER BY attempt_time DESC LIMIT 10");
    $stmt->execute();
    $recent_logins = $stmt->fetchAll();
}

// Get user statistics (admin only)
$user_stats = [];
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY role");
    $stmt->execute();
    $user_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_sessions WHERE is_active = 1");
    $stmt->execute();
    $active_sessions_count = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute();
    $login_attempts_24h = $stmt->fetchColumn();
}

// Calculate session duration
$session_duration = time() - $login_time;
$time_until_timeout = SESSION_TIMEOUT - (time() - $last_activity);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure Login System</title>
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
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light-gray) 0%, #f8f9fa 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: var(--white);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-left .icon {
            font-size: 1.5rem;
            color: var(--orange);
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .role-badge {
            background: var(--orange);
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            gap: 2rem;
        }

        /* Welcome Section */
        .welcome-section {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-title {
            color: var(--primary-blue);
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .welcome-text {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            font-size: 1.5rem;
            color: var(--orange);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .stat-desc {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Session Info */
        .session-info {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .session-title {
            color: var(--primary-blue);
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .session-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .session-item {
            padding: 1rem;
            background: var(--light-gray);
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .session-label {
            color: var(--text-dark);
            font-weight: 600;
        }

        .session-value {
            color: var(--text-light);
            font-family: 'Courier New', monospace;
        }

        /* Timeout Warning */
        .timeout-warning {
            background: linear-gradient(45deg, var(--warning-color), #ff9500);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        /* Admin Section */
        .admin-section {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .admin-title {
            color: var(--danger-color);
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .data-table th,
        .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .data-table th {
            background: var(--light-gray);
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .data-table td {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .success-indicator {
            color: var(--success-color);
        }

        .failure-indicator {
            color: var(--danger-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .main-content {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .session-details {
                grid-template-columns: 1fr;
            }

            .session-item {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .welcome-title {
                font-size: 1.5rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-content > * {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Auto-refresh indicator */
        .refresh-indicator {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--success-color);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            display: none;
            align-items: center;
            gap: 0.5rem;
        }

        .refresh-indicator.show {
            display: flex;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-left">
                <i class="fas fa-tachometer-alt icon"></i>
                <div class="header-title">Secure Dashboard</div>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    <span><?= htmlspecialchars($username) ?></span>
                    <span class="role-badge"><?= htmlspecialchars($role) ?></span>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <div class="refresh-indicator" id="refreshIndicator">
        <i class="fas fa-sync fa-spin"></i>
        Auto-refreshing...
    </div>

    <?php if ($time_until_timeout < 300): // 5 minutes warning ?>
    <div class="timeout-warning">
        <i class="fas fa-clock"></i>
        Session will expire in <span id="timeoutCounter"><?= gmdate("i:s", $time_until_timeout) ?></span> minutes. 
        <a href="#" onclick="refreshSession()" style="color: var(--white); text-decoration: underline; margin-left: 1rem;">Extend Session</a>
    </div>
    <?php endif; ?>

    <main class="main-content">
        <section class="welcome-section">
            <h1 class="welcome-title">
                <i class="fas fa-home"></i>
                Welcome back, <?= htmlspecialchars($username) ?>!
            </h1>
            <p class="welcome-text">
                You are successfully logged in to the secure dashboard. Your session is protected with advanced security features including automatic timeout, session tracking, and role-based access control.
            </p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Session Duration</span>
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                    <div class="stat-value" id="sessionDuration"><?= gmdate("H:i:s", $session_duration) ?></div>
                    <div class="stat-desc">Time since login</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Active Sessions</span>
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                    <div class="stat-value"><?= count($active_sessions) ?></div>
                    <div class="stat-desc">Your concurrent sessions</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Last Activity</span>
                        <i class="fas fa-activity stat-icon"></i>
                    </div>
                    <div class="stat-value" id="lastActivity"><?= gmdate("H:i:s", time() - $last_activity) ?></div>
                    <div class="stat-desc">Seconds ago</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Access Level</span>
                        <i class="fas fa-shield-alt stat-icon"></i>
                    </div>
                    <div class="stat-value"><?= ucfirst(htmlspecialchars($role)) ?></div>
                    <div class="stat-desc">User role</div>
                </div>
            </div>
        </section>

        <section class="session-info">
            <h2 class="session-title">
                <i class="fas fa-info-circle"></i>
                Session Information
            </h2>
            <div class="session-details">
                <div class="session-item">
                    <span class="session-label">Session ID:</span>
                    <span class="session-value"><?= substr(session_id(), 0, 16) ?>...</span>
                </div>
                <div class="session-item">
                    <span class="session-label">IP Address:</span>
                    <span class="session-value"><?= htmlspecialchars($current_session['ip_address'] ?? 'Unknown') ?></span>
                </div>
                <div class="session-item">
                    <span class="session-label">Login Time:</span>
                    <span class="session-value"><?= date('Y-m-d H:i:s', $login_time) ?></span>
                </div>
                <div class="session-item">
                    <span class="session-label">Last Login:</span>
                    <span class="session-value"><?= $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : 'Never' ?></span>
                </div>
                <div class="session-item">
                    <span class="session-label">Timeout In:</span>
                    <span class="session-value" id="timeoutIn"><?= gmdate("i:s", $time_until_timeout) ?></span>
                </div>
                <div class="session-item">
                    <span class="session-label">Auto Login:</span>
                    <span class="session-value"><?= isset($_SESSION['auto_login']) ? 'Yes (Remember Me)' : 'No' ?></span>
                </div>
            </div>
        </section>

        <?php if ($role === 'admin'): ?>
        <section class="admin-section">
            <h2 class="admin-title">
                <i class="fas fa-user-shield"></i>
                Administrator Dashboard
            </h2>
            
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Users</span>
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                    <div class="stat-value"><?= array_sum($user_stats) ?></div>
                    <div class="stat-desc">Registered users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Admin Users</span>
                        <i class="fas fa-user-shield stat-icon"></i>
                    </div>
                    <div class="stat-value"><?= $user_stats['admin'] ?? 0 ?></div>
                    <div class="stat-desc">Administrator accounts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Active Sessions</span>
                        <i class="fas fa-wifi stat-icon"></i>
                    </div>
                    <div class="stat-value"><?= $active_sessions_count ?></div>
                    <div class="stat-desc">Currently online</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Login Attempts (24h)</span>
                        <i class="fas fa-chart-line stat-icon"></i>
                    </div>
                    <div class="stat-value"><?= $login_attempts_24h ?></div>
                    <div class="stat-desc">Past 24 hours</div>
                </div>
            </div>

            <h3 style="color: var(--text-dark); margin-bottom: 1rem;">
                <i class="fas fa-history"></i>
                Recent Login Attempts
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>IP Address</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logins as $login): ?>
                        <tr>
                            <td><?= htmlspecialchars($login['username']) ?></td>
                            <td><?= htmlspecialchars($login['ip_address']) ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($login['attempt_time'])) ?></td>
                            <td>
                                <?php if ($login['success']): ?>
                                    <span class="success-indicator"><i class="fas fa-check"></i> Success</span>
                                <?php else: ?>
                                    <span class="failure-indicator"><i class="fas fa-times"></i> Failed</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(substr($login['user_agent'], 0, 50)) ?>...</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_logins)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-light);">No recent login attempts found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <script>
        let sessionStartTime = <?= $login_time ?>;
        let lastActivity = <?= $last_activity ?>;
        let sessionTimeout = <?= SESSION_TIMEOUT ?>;
        let timeoutWarning = <?= $time_until_timeout ?>;

        // Update session duration and activity counters
        function updateCounters() {
            const now = Math.floor(Date.now() / 1000);
            const duration = now - sessionStartTime;
            const activityAge = now - lastActivity;
            const timeLeft = sessionTimeout - activityAge;

            // Update session duration
            const durationElement = document.getElementById('sessionDuration');
            if (durationElement) {
                durationElement.textContent = formatTime(duration);
            }

            // Update last activity
            const activityElement = document.getElementById('lastActivity');
            if (activityElement) {
                activityElement.textContent = formatTime(activityAge);
            }

            // Update timeout counter
            const timeoutElement = document.getElementById('timeoutIn');
            if (timeoutElement) {
                timeoutElement.textContent = formatTime(Math.max(0, timeLeft));
            }

            const timeoutCounter = document.getElementById('timeoutCounter');
            if (timeoutCounter) {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timeoutCounter.textContent = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            }

            // Redirect if session expired
            if (timeLeft <= 0) {
                alert('Your session has expired. You will be redirected to the login page.');
                window.location.href = 'login.php?timeout=1';
            }

            // Show warning if less than 5 minutes
            if (timeLeft <= 300 && timeLeft > 0) {
                document.body.style.borderTop = '5px solid var(--warning-color)';
            }
        }

        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            
            if (hours > 0) {
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            } else {
                return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
        }

        function refreshSession() {
            fetch('refresh_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    lastActivity = Math.floor(Date.now() / 1000);
                    alert('Session extended successfully!');
                    location.reload();
                } else {
                    alert('Failed to extend session. Please login again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error extending session. Please try again.');
            });
        }

        // Update counters every second
        setInterval(updateCounters, 1000);

        // Update last activity on any user interaction
        ['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
            document.addEventListener(event, function() {
                lastActivity = Math.floor(Date.now() / 1000);
            }, { passive: true });
        });

        // Auto-refresh page every 5 minutes to keep session active
        setInterval(() => {
            const indicator = document.getElementById('refreshIndicator');
            indicator.classList.add('show');
            
            setTimeout(() => {
                indicator.classList.remove('show');
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(() => {
                        lastActivity = Math.floor(Date.now() / 1000);
                    });
            }, 2000);
        }, 300000); // 5 minutes

        // Warn before leaving page if session is active
        window.addEventListener('beforeunload', function(e) {
            if (timeoutWarning > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Initialize on page load
        updateCounters();
    </script>
</body>
</html>