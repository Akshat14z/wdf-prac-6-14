<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students - Student Data Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    require_once 'config.php';
    $studentManager = new StudentManager($db);

    // Handle filters and search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $department = isset($_GET['department']) ? $_GET['department'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $students = $studentManager->getStudents($search, $department, $status, $limit, $offset);
    $departments = $studentManager->getDepartments();

    // Handle delete action
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        if ($studentManager->deleteStudent($_GET['delete'])) {
            $success_message = "Student deleted successfully!";
        } else {
            $error_message = "Error deleting student.";
        }
        // Redirect to avoid resubmission
        header("Location: students.php" . ($search || $department || $status ? '?' . http_build_query(['search' => $search, 'department' => $department, 'status' => $status]) : ''));
        exit();
    }
    ?>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-users"></i> All Students</h1>
                <p>Complete student database with search and filter options</p>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="students.php" class="nav-item active">
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
            <!-- Alerts -->
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <!-- Search and Filter Section -->
            <section class="search-section">
                <div class="form-container">
                    <h2><i class="fas fa-filter"></i> Search & Filter Students</h2>
                    
                    <form method="GET" action="students.php" class="search-container">
                        <div class="form-group search-input">
                            <label for="search">Search Students</label>
                            <input type="text" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, student ID, or email...">
                        </div>
                        
                        <div class="form-group">
                            <label for="department">Department</label>
                            <select id="department" name="department">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                        <?php echo $department == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Status</option>
                                <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Graduated" <?php echo $status === 'Graduated' ? 'selected' : ''; ?>>Graduated</option>
                                <option value="Dropped" <?php echo $status === 'Dropped' ? 'selected' : ''; ?>>Dropped</option>
                                <option value="Suspended" <?php echo $status === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <a href="students.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Students Table -->
            <section class="students-section">
                <div class="section-header">
                    <h2><i class="fas fa-table"></i> Students List (<?php echo count($students); ?> found)</h2>
                    <div>
                        <a href="add_student.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Add New Student
                        </a>
                        <a href="export.php<?php echo ($search || $department || $status) ? '?' . http_build_query(['search' => $search, 'department' => $department, 'status' => $status]) : ''; ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                    </div>
                </div>
                
                <div class="table-container">
                    <?php if (empty($students)): ?>
                    <div style="padding: 3rem; text-align: center; color: #A08963;">
                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h3>No Students Found</h3>
                        <p>Try adjusting your search criteria or add a new student.</p>
                        <a href="add_student.php" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-user-plus"></i> Add First Student
                        </a>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Course</th>
                                <th>GPA</th>
                                <th>Status</th>
                                <th>Admission Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                <td>
                                    <div class="student-info">
                                        <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                        <br>
                                        <small style="color: #A08963;">
                                            <?php echo htmlspecialchars($student['city'] . ', ' . $student['state']); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" 
                                       style="color: #706D54; text-decoration: none;">
                                        <?php echo htmlspecialchars($student['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($student['phone']); ?>" 
                                       style="color: #706D54; text-decoration: none;">
                                        <?php echo htmlspecialchars($student['phone'] ?: 'N/A'); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="dept-tag"><?php echo htmlspecialchars($student['department_code']); ?></span>
                                    <br>
                                    <small><?php echo htmlspecialchars($student['department_name']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['course_code']); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($student['course_name']); ?></small>
                                </td>
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
                                <td><?php echo date('M d, Y', strtotime($student['admission_date'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <a href="view_student.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" 
                                           class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="students.php?delete=<?php echo $student['id']; ?><?php echo ($search || $department || $status) ? '&' . http_build_query(['search' => $search, 'department' => $department, 'status' => $status]) : ''; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.')" 
                                           class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if (count($students) >= $limit): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="students.php?page=<?php echo $page - 1; ?><?php echo ($search || $department || $status) ? '&' . http_build_query(['search' => $search, 'department' => $department, 'status' => $status]) : ''; ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <?php endif; ?>
                    
                    <span class="active">Page <?php echo $page; ?></span>
                    
                    <?php if (count($students) == $limit): ?>
                    <a href="students.php?page=<?php echo $page + 1; ?><?php echo ($search || $department || $status) ? '&' . http_build_query(['search' => $search, 'department' => $department, 'status' => $status]) : ''; ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p>&copy; 2024 Student Data Management System - Practice 11</p>
                <p>Comprehensive student database with advanced search capabilities</p>
            </div>
        </footer>
    </div>

    <script>
        // Auto-submit search form on department/status change
        document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department');
            const statusSelect = document.getElementById('status');
            
            function autoSubmit() {
                this.form.submit();
            }
            
            departmentSelect.addEventListener('change', autoSubmit);
            statusSelect.addEventListener('change', autoSubmit);
        });

        // Confirm delete action
        function confirmDelete(studentName) {
            return confirm(`Are you sure you want to delete ${studentName}? This action cannot be undone.`);
        }
    </script>
</body>
</html>