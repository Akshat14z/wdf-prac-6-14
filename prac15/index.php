<?php
require_once 'config.php';

// Check if user is logged in, if not redirect to login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Get dashboard statistics
$stats = [];

try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students s JOIN users u ON s.user_id = u.id WHERE u.status = 'active'");
    $stats['total_students'] = $stmt->fetch()['count'];
    
    // Total events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE status != 'cancelled'");
    $stats['total_events'] = $stmt->fetch()['count'];
    
    // Active sessions
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_sessions WHERE expires_at > NOW()");
    $stats['active_sessions'] = $stmt->fetch()['count'];
    
    // Recent activities (last 10)
    $stmt = $pdo->query("
        SELECT sl.*, u.first_name, u.last_name 
        FROM system_logs sl 
        LEFT JOIN users u ON sl.user_id = u.id 
        ORDER BY sl.created_at DESC 
        LIMIT 10
    ");
    $recent_activities = $stmt->fetchAll();
    
    // Upcoming events
    $stmt = $pdo->query("
        SELECT e.*, u.first_name, u.last_name,
               (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registration_count
        FROM events e 
        LEFT JOIN users u ON e.created_by = u.id 
        WHERE e.start_date > NOW() AND e.status = 'active'
        ORDER BY e.start_date ASC 
        LIMIT 5
    ");
    $upcoming_events = $stmt->fetchAll();
    
    // Recent registrations
    $stmt = $pdo->query("
        SELECT er.*, e.title as event_title, u.first_name, u.last_name
        FROM event_registrations er
        JOIN events e ON er.event_id = e.id
        JOIN users u ON er.user_id = u.id
        ORDER BY er.registration_date DESC
        LIMIT 5
    ");
    $recent_registrations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = "Error loading dashboard data: " . $e->getMessage();
}

// Log dashboard access
logActivity('dashboard_access');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    <title><?= APP_NAME ?> - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-wrapper">
        <!-- Header -->
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <a href="index.php" class="logo">
                        <i class="fas fa-globe"></i>
                        <?= APP_NAME ?>
                    </a>
                    
                    <nav class="nav-menu">
                        <a href="index.php" class="nav-link active">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        
                        <?php if (hasRole('student') || hasRole('manager')): ?>
                        <a href="modules/students.php" class="nav-link">
                            <i class="fas fa-users"></i> Students
                        </a>
                        <?php endif; ?>
                        
                        <a href="modules/events.php" class="nav-link">
                            <i class="fas fa-calendar"></i> Events
                        </a>
                        
                        <a href="modules/forms.php" class="nav-link">
                            <i class="fas fa-file-alt"></i> Forms
                        </a>
                        
                        <?php if (hasRole('manager')): ?>
                        <a href="modules/analytics.php" class="nav-link">
                            <i class="fas fa-chart-bar"></i> Analytics
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasRole('admin')): ?>
                        <a href="modules/admin.php" class="nav-link">
                            <i class="fas fa-cog"></i> Admin
                        </a>
                        <?php endif; ?>
                        
                        <div class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($user_name) ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="modules/profile.php" class="dropdown-item">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="modules/settings.php" class="dropdown-item">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="dropdown-item confirm-action" data-confirm="Are you sure you want to log out?">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <div class="content-wrapper">
                    <!-- Page Header -->
                    <div class="page-header mb-3">
                        <h1>Dashboard</h1>
                        <p class="text-muted">Welcome back, <?= htmlspecialchars($user_name) ?>! Here's what's happening in your portal.</p>
                    </div>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="dashboard-grid">
                        <div class="stat-card">
                            <span class="stat-number" data-live="total_users"><?= number_format($stats['total_users'] ?? 0) ?></span>
                            <span class="stat-label">
                                <i class="fas fa-users"></i> Total Users
                            </span>
                        </div>
                        
                        <div class="stat-card">
                            <span class="stat-number" data-live="total_students"><?= number_format($stats['total_students'] ?? 0) ?></span>
                            <span class="stat-label">
                                <i class="fas fa-graduation-cap"></i> Active Students
                            </span>
                        </div>
                        
                        <div class="stat-card">
                            <span class="stat-number" data-live="total_events"><?= number_format($stats['total_events'] ?? 0) ?></span>
                            <span class="stat-label">
                                <i class="fas fa-calendar"></i> Events
                            </span>
                        </div>
                        
                        <div class="stat-card">
                            <span class="stat-number" data-live="active_sessions"><?= number_format($stats['active_sessions'] ?? 0) ?></span>
                            <span class="stat-label">
                                <i class="fas fa-signal"></i> Active Sessions
                            </span>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if (hasRole('manager')): ?>
                                <a href="modules/students.php?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Student
                                </a>
                                <a href="modules/events.php?action=create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Event
                                </a>
                                <?php endif; ?>
                                
                                <a href="modules/forms.php?action=create" class="btn btn-secondary">
                                    <i class="fas fa-file-plus"></i> New Form
                                </a>
                                
                                <a href="modules/events.php" class="btn btn-secondary">
                                    <i class="fas fa-calendar"></i> View Events
                                </a>
                                
                                <?php if (hasRole('manager')): ?>
                                <a href="modules/analytics.php" class="btn btn-secondary">
                                    <i class="fas fa-chart-line"></i> View Analytics
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Upcoming Events -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3><i class="fas fa-calendar-upcoming"></i> Upcoming Events</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($upcoming_events)): ?>
                                        <p class="text-muted">No upcoming events scheduled.</p>
                                    <?php else: ?>
                                        <div class="event-list">
                                            <?php foreach ($upcoming_events as $event): ?>
                                                <div class="event-item mb-2">
                                                    <h5>
                                                        <a href="modules/events.php?id=<?= $event['id'] ?>">
                                                            <?= htmlspecialchars($event['title']) ?>
                                                        </a>
                                                    </h5>
                                                    <p class="text-muted mb-1">
                                                        <i class="fas fa-clock"></i> 
                                                        <?= date('M j, Y g:i A', strtotime($event['start_date'])) ?>
                                                    </p>
                                                    <p class="text-muted mb-1">
                                                        <i class="fas fa-map-marker-alt"></i> 
                                                        <?= htmlspecialchars($event['location']) ?>
                                                    </p>
                                                    <small class="badge badge-primary">
                                                        <?= $event['registration_count'] ?> registered
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-center mt-2">
                                            <a href="modules/events.php" class="btn btn-sm btn-secondary">View All Events</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Recent Activity -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3><i class="fas fa-history"></i> Recent Activity</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_activities)): ?>
                                        <p class="text-muted">No recent activity.</p>
                                    <?php else: ?>
                                        <div class="activity-list">
                                            <?php foreach ($recent_activities as $activity): ?>
                                                <div class="activity-item mb-2">
                                                    <div class="activity-content">
                                                        <strong>
                                                            <?= $activity['first_name'] ? htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) : 'System' ?>
                                                        </strong>
                                                        <span><?= htmlspecialchars(str_replace('_', ' ', $activity['action'])) ?></span>
                                                        <?php if ($activity['table_name']): ?>
                                                            <span class="text-muted">in <?= htmlspecialchars($activity['table_name']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('M j, g:i A', strtotime($activity['created_at'])) ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (hasRole('admin')): ?>
                                        <div class="text-center mt-2">
                                            <a href="modules/admin.php?section=logs" class="btn btn-sm btn-secondary">View All Logs</a>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Registrations -->
                    <?php if (hasRole('manager') && !empty($recent_registrations)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user-plus"></i> Recent Event Registrations</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Participant</th>
                                            <th>Registration Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_registrations as $registration): ?>
                                        <tr>
                                            <td>
                                                <a href="modules/events.php?id=<?= $registration['event_id'] ?>">
                                                    <?= htmlspecialchars($registration['event_title']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($registration['registration_date'])) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $registration['status'] === 'registered' ? 'primary' : ($registration['status'] === 'attended' ? 'success' : 'warning') ?>">
                                                    <?= ucfirst($registration['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container"></div>

    <script src="js/script.js"></script>
    <script>
        // Dashboard-specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Update dashboard stats every 30 seconds
            setInterval(function() {
                updateDashboardStats();
            }, 30000);
        });

        async function updateDashboardStats() {
            try {
                const response = await fetch('api/dashboard_stats.php');
                const data = await response.json();
                
                if (data.success) {
                    Object.entries(data.stats).forEach(([key, value]) => {
                        const element = document.querySelector(`[data-live="${key}"]`);
                        if (element) {
                            // Animate number change
                            animateNumber(element, parseInt(element.textContent.replace(/,/g, '')), value);
                        }
                    });
                }
            } catch (error) {
                console.error('Failed to update dashboard stats:', error);
            }
        }

        function animateNumber(element, from, to) {
            const duration = 1000;
            const steps = 30;
            const stepValue = (to - from) / steps;
            let current = from;
            let step = 0;

            const timer = setInterval(() => {
                current += stepValue;
                step++;
                
                element.textContent = Math.round(current).toLocaleString();
                
                if (step >= steps) {
                    clearInterval(timer);
                    element.textContent = to.toLocaleString();
                }
            }, duration / steps);
        }
    </script>
</body>
</html>