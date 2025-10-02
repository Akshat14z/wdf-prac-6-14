<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Data Management System - Practice 11</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    require_once 'config.php';
    $studentManager = new StudentManager($db);
    $stats = $studentManager->getStatistics();
    $recentStudents = $studentManager->getStudents('', '', '', 5, 0);
    ?>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-graduation-cap"></i> Student Data Management System</h1>
                <p>Practice 11 - MySQL Database Operations</p>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="students.php" class="nav-item">
                    <i class="fas fa-users"></i> All Students
                </a>
                <a href="add_student.php" class="nav-item">
                    <i class="fas fa-user-plus"></i> Add Student
                </a>
                <a href="search.php" class="nav-item">
                    <i class="fas fa-search"></i> Search
                </a>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Statistics Cards -->
            <section class="stats-section">
                <h2><i class="fas fa-chart-bar"></i> System Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['total_students']); ?></h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['active_students']); ?></h3>
                            <p>Active Students</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon graduated">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['graduated_students']); ?></h3>
                            <p>Graduated Students</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon gpa">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['average_gpa']; ?></h3>
                            <p>Average GPA</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Department Statistics -->
            <section class="department-section">
                <h2><i class="fas fa-building"></i> Department Statistics</h2>
                <div class="department-stats">
                    <?php foreach ($stats['department_stats'] as $dept): ?>
                    <div class="dept-card">
                        <h4><?php echo htmlspecialchars($dept['department_name']); ?></h4>
                        <p class="dept-count"><?php echo $dept['student_count']; ?> Students</p>
                        <div class="dept-bar">
                            <div class="dept-fill" style="width: <?php echo ($dept['student_count'] / max(1, $stats['total_students'])) * 100; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Recent Students -->
            <section class="recent-section">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Recent Students</h2>
                    <a href="students.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Course</th>
                                <th>GPA</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentStudents as $student): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                <td>
                                    <div class="student-info">
                                        <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <span class="dept-tag"><?php echo htmlspecialchars($student['department_code']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                                <td>
                                    <span class="gpa-badge <?php echo $student['gpa'] >= 3.5 ? 'high' : ($student['gpa'] >= 3.0 ? 'medium' : 'low'); ?>">
                                        <?php echo number_format($student['gpa'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($student['status']); ?>">
                                        <?php echo htmlspecialchars($student['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="actions-section">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="actions-grid">
                    <a href="add_student.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3>Add New Student</h3>
                        <p>Register a new student in the system</p>
                    </a>
                    
                    <a href="search.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Search Students</h3>
                        <p>Find students by name, ID, or department</p>
                    </a>
                    
                    <a href="students.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h3>View All Students</h3>
                        <p>Browse complete student database</p>
                    </a>
                    
                    <a href="reports.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Generate Reports</h3>
                        <p>Create detailed academic reports</p>
                    </a>
                </div>
            </section>

            <!-- System Information -->
            <section class="info-section">
                <h2><i class="fas fa-info-circle"></i> System Information</h2>
                <div class="info-grid">
                    <div class="info-card">
                        <h4>Database Connection</h4>
                        <p class="status-success">
                            <i class="fas fa-check-circle"></i> Connected to MySQL
                        </p>
                    </div>
                    
                    <div class="info-card">
                        <h4>Last Updated</h4>
                        <p><?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h4>Color Palette</h4>
                        <div class="color-palette">
                            <span class="color-sample" style="background: #706D54;" title="#706D54"></span>
                            <span class="color-sample" style="background: #A08963;" title="#A08963"></span>
                            <span class="color-sample" style="background: #C9B194;" title="#C9B194"></span>
                            <span class="color-sample" style="background: #DBDBDB;" title="#DBDBDB"></span>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p>&copy; 2024 Student Data Management System - Practice 11</p>
                <p>MySQL Database Operations with Normalized Schema</p>
            </div>
        </footer>
    </div>

    <script src="js/script.js"></script>
</body>
</html>