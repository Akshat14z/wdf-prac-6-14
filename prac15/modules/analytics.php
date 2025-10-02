<?php
require_once '../config.php';

// Require login and manager role
requireLogin();
requireRole('manager');

$user_id = $_SESSION['user_id'];

// Get analytics data
$analytics = [];

try {
    // User statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as total_students,
            SUM(CASE WHEN role = 'admin' OR role = 'super_admin' THEN 1 ELSE 0 END) as admin_users,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users_30d
        FROM users
    ");
    $analytics['users'] = $stmt->fetch();

    // Event statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_events,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_events,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_events,
            SUM(CASE WHEN start_date > NOW() THEN 1 ELSE 0 END) as upcoming_events
        FROM events
    ");
    $analytics['events'] = $stmt->fetch();

    // Registration statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_registrations,
            SUM(CASE WHEN status = 'registered' THEN 1 ELSE 0 END) as active_registrations,
            SUM(CASE WHEN status = 'attended' THEN 1 ELSE 0 END) as attended_registrations,
            SUM(CASE WHEN registration_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as registrations_7d
        FROM event_registrations
    ");
    $analytics['registrations'] = $stmt->fetch();

    // Form submissions statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_submissions,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_submissions,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_submissions,
            SUM(CASE WHEN submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as submissions_7d
        FROM form_submissions
    ");
    $analytics['forms'] = $stmt->fetch();

    // Monthly user registration trend (last 12 months)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $analytics['user_trend'] = $stmt->fetchAll();

    // Event registration trend (last 6 months)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(registration_date, '%Y-%m') as month,
            COUNT(*) as count
        FROM event_registrations 
        WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(registration_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $analytics['registration_trend'] = $stmt->fetchAll();

    // Top departments by student count
    $stmt = $pdo->query("
        SELECT 
            s.department,
            COUNT(*) as student_count,
            AVG(s.gpa) as avg_gpa
        FROM students s
        JOIN users u ON s.user_id = u.id
        WHERE u.status = 'active'
        GROUP BY s.department
        ORDER BY student_count DESC
        LIMIT 10
    ");
    $analytics['departments'] = $stmt->fetchAll();

    // Recent system activity (last 50 activities)
    $stmt = $pdo->query("
        SELECT 
            sl.action,
            sl.table_name,
            sl.created_at,
            u.first_name,
            u.last_name,
            sl.ip_address
        FROM system_logs sl
        LEFT JOIN users u ON sl.user_id = u.id
        ORDER BY sl.created_at DESC
        LIMIT 50
    ");
    $analytics['recent_activity'] = $stmt->fetchAll();

    // Popular events by registration count
    $stmt = $pdo->query("
        SELECT 
            e.title,
            e.event_type,
            e.start_date,
            COUNT(er.id) as registration_count,
            e.max_participants,
            CASE 
                WHEN e.max_participants > 0 THEN (COUNT(er.id) / e.max_participants * 100)
                ELSE 0 
            END as fill_percentage
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        WHERE e.status IN ('active', 'completed')
        GROUP BY e.id
        ORDER BY registration_count DESC
        LIMIT 10
    ");
    $analytics['popular_events'] = $stmt->fetchAll();

    // Daily active users (last 30 days)
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as activity_date,
            COUNT(DISTINCT user_id) as active_users
        FROM system_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND user_id IS NOT NULL
        GROUP BY DATE(created_at)
        ORDER BY activity_date ASC
    ");
    $analytics['daily_activity'] = $stmt->fetchAll();

    // Performance metrics
    $stmt = $pdo->query("
        SELECT 
            AVG(CASE WHEN action = 'login_success' THEN 1 ELSE 0 END) * 100 as login_success_rate,
            COUNT(CASE WHEN action = 'login_failed' THEN 1 END) as failed_logins,
            COUNT(CASE WHEN action = 'account_locked' THEN 1 END) as account_lockouts
        FROM system_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $analytics['performance'] = $stmt->fetch();

} catch (PDOException $e) {
    $error_message = "Error loading analytics data: " . $e->getMessage();
}

// Log analytics access
logActivity('analytics_accessed');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    <title><?= APP_NAME ?> - Analytics Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="main-wrapper">
        <!-- Header -->
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <a href="../index.php" class="logo">
                        <i class="fas fa-globe"></i>
                        <?= APP_NAME ?>
                    </a>
                    
                    <nav class="nav-menu">
                        <a href="../index.php" class="nav-link">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="students.php" class="nav-link">
                            <i class="fas fa-users"></i> Students
                        </a>
                        <a href="events.php" class="nav-link">
                            <i class="fas fa-calendar"></i> Events
                        </a>
                        <a href="forms.php" class="nav-link">
                            <i class="fas fa-file-alt"></i> Forms
                        </a>
                        <a href="analytics.php" class="nav-link active">
                            <i class="fas fa-chart-bar"></i> Analytics
                        </a>
                        
                        <div class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="profile.php" class="dropdown-item">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="../logout.php" class="dropdown-item confirm-action" data-confirm="Are you sure you want to log out?">
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
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Page Header -->
                    <div class="page-header mb-3">
                        <div class="d-flex justify-between align-center">
                            <div>
                                <h1><i class="fas fa-chart-bar"></i> Analytics Dashboard</h1>
                                <p class="text-muted">Comprehensive insights and performance metrics</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button onclick="exportData()" class="btn btn-secondary">
                                    <i class="fas fa-download"></i> Export Data
                                </button>
                                <button onclick="refreshData()" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Key Metrics -->
                    <div class="dashboard-grid mb-3">
                        <div class="stat-card">
                            <span class="stat-number"><?= number_format($analytics['users']['total_users'] ?? 0) ?></span>
                            <span class="stat-label">
                                <i class="fas fa-users"></i> Total Users
                                <small class="text-success">
                                    +<?= number_format($analytics['users']['new_users_30d'] ?? 0) ?> this month
                                </small>
                            </span>
                        </div>
                        
                        <div class="stat-card">
                            <span class="stat-number"><?= number_format($analytics['events']['total_events'] ?? 0) ?></span>
                            <span class="stat-label">
                                <i class="fas fa-calendar"></i> Total Events
                                <small class="text-info">
                                    <?= number_format($analytics['events']['upcoming_events'] ?? 0) ?> upcoming
                                </small>
                            </span>
                        </div>
                        
                        <div class="stat-card">
                            <span class="stat-number"><?= number_format($analytics['registrations']['total_registrations'] ?? 0) ?></span>
                            <span class="stat-label">
                                <i class="fas fa-user-plus"></i> Event Registrations
                                <small class="text-success">
                                    +<?= number_format($analytics['registrations']['registrations_7d'] ?? 0) ?> this week
                                </small>
                            </span>
                        </div>
                        
                        <div class="stat-card">
                            <span class="stat-number"><?= number_format($analytics['forms']['total_submissions'] ?? 0) ?></span>
                            <span class="stat-label">
                                <i class="fas fa-file-alt"></i> Form Submissions
                                <small class="text-warning">
                                    <?= number_format($analytics['forms']['pending_submissions'] ?? 0) ?> pending
                                </small>
                            </span>
                        </div>
                    </div>

                    <!-- Charts Row 1 -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-chart-line"></i> User Registration Trend</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="userTrendChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-chart-pie"></i> User Roles</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="userRolesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row 2 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-chart-bar"></i> Departments by Student Count</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="departmentsChart" height="150"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-chart-area"></i> Daily Active Users (30 days)</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="dailyActivityChart" height="150"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Tables Row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <!-- Popular Events -->
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-star"></i> Popular Events</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($analytics['popular_events'])): ?>
                                        <p class="text-muted">No events data available.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Event</th>
                                                        <th>Type</th>
                                                        <th>Registrations</th>
                                                        <th>Fill %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($analytics['popular_events'] as $event): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= htmlspecialchars($event['title']) ?></strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?= date('M j, Y', strtotime($event['start_date'])) ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-secondary">
                                                                <?= ucfirst($event['event_type']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= number_format($event['registration_count']) ?></td>
                                                        <td>
                                                            <?php if ($event['max_participants'] > 0): ?>
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-primary" 
                                                                         style="width: <?= min(100, $event['fill_percentage']) ?>%">
                                                                        <?= round($event['fill_percentage']) ?>%
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted">No limit</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Department Performance -->
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-graduation-cap"></i> Department Performance</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($analytics['departments'])): ?>
                                        <p class="text-muted">No department data available.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Department</th>
                                                        <th>Students</th>
                                                        <th>Avg GPA</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($analytics['departments'] as $dept): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($dept['department']) ?></td>
                                                        <td>
                                                            <span class="badge badge-primary">
                                                                <?= number_format($dept['student_count']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-<?= $dept['avg_gpa'] >= 3.5 ? 'success' : ($dept['avg_gpa'] >= 2.5 ? 'warning' : 'danger') ?>">
                                                                <?= number_format($dept['avg_gpa'], 2) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Performance -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3><i class="fas fa-server"></i> System Performance Metrics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success">
                                            <?= number_format($analytics['performance']['login_success_rate'] ?? 0, 1) ?>%
                                        </h4>
                                        <p class="text-muted">Login Success Rate</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning">
                                            <?= number_format($analytics['performance']['failed_logins'] ?? 0) ?>
                                        </h4>
                                        <p class="text-muted">Failed Logins (7 days)</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-danger">
                                            <?= number_format($analytics['performance']['account_lockouts'] ?? 0) ?>
                                        </h4>
                                        <p class="text-muted">Account Lockouts (7 days)</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-info">
                                            <?= number_format(count($analytics['recent_activity'] ?? [])) ?>
                                        </h4>
                                        <p class="text-muted">Recent Activities</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Recent System Activity</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($analytics['recent_activity'])): ?>
                                <p class="text-muted">No recent activity.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Action</th>
                                                <th>Table</th>
                                                <th>IP Address</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($analytics['recent_activity'], 0, 20) as $activity): ?>
                                            <tr>
                                                <td>
                                                    <?= $activity['first_name'] ? htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) : 'System' ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">
                                                        <?= htmlspecialchars(str_replace('_', ' ', $activity['action'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= $activity['table_name'] ? htmlspecialchars($activity['table_name']) : '-' ?>
                                                </td>
                                                <td>
                                                    <code><?= htmlspecialchars($activity['ip_address']) ?></code>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('M j, g:i A', strtotime($activity['created_at'])) ?>
                                                    </small>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container"></div>

    <script src="../js/script.js"></script>
    <script>
        // Chart.js configuration and data
        const chartConfig = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
            },
        };

        // User Registration Trend Chart
        const userTrendData = <?= json_encode($analytics['user_trend'] ?? []) ?>;
        new Chart(document.getElementById('userTrendChart'), {
            type: 'line',
            data: {
                labels: userTrendData.map(item => item.month),
                datasets: [{
                    label: 'New Users',
                    data: userTrendData.map(item => item.count),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                ...chartConfig,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // User Roles Pie Chart
        const userStats = <?= json_encode($analytics['users'] ?? []) ?>;
        new Chart(document.getElementById('userRolesChart'), {
            type: 'doughnut',
            data: {
                labels: ['Students', 'Admins', 'Other Users'],
                datasets: [{
                    data: [
                        userStats.total_students || 0,
                        userStats.admin_users || 0,
                        (userStats.total_users || 0) - (userStats.total_students || 0) - (userStats.admin_users || 0)
                    ],
                    backgroundColor: [
                        'rgb(16, 185, 129)',
                        'rgb(59, 130, 246)',
                        'rgb(107, 114, 128)'
                    ]
                }]
            },
            options: chartConfig
        });

        // Departments Chart
        const departmentData = <?= json_encode($analytics['departments'] ?? []) ?>;
        new Chart(document.getElementById('departmentsChart'), {
            type: 'bar',
            data: {
                labels: departmentData.map(item => item.department),
                datasets: [{
                    label: 'Students',
                    data: departmentData.map(item => item.student_count),
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                }]
            },
            options: {
                ...chartConfig,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Daily Activity Chart
        const dailyActivityData = <?= json_encode($analytics['daily_activity'] ?? []) ?>;
        new Chart(document.getElementById('dailyActivityChart'), {
            type: 'line',
            data: {
                labels: dailyActivityData.map(item => new Date(item.activity_date).toLocaleDateString()),
                datasets: [{
                    label: 'Active Users',
                    data: dailyActivityData.map(item => item.active_users),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                ...chartConfig,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Analytics functions
        function refreshData() {
            portal.showNotification('Refreshing analytics data...', 'info');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        function exportData() {
            portal.showNotification('Preparing data export...', 'info');
            // Implementation for data export would go here
            setTimeout(() => {
                portal.showNotification('Export functionality coming soon!', 'warning');
            }, 1000);
        }

        // Auto-refresh every 5 minutes
        setInterval(refreshData, 300000);
    </script>
</body>
</html>