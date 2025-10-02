<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Students - Student Data Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    require_once 'config.php';
    $studentManager = new StudentManager($db);
    
    $search_results = [];
    $search_query = '';
    $search_performed = false;

    // Handle search
    if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
        $search_query = trim($_GET['q']);
        $search_results = $studentManager->searchStudents($search_query);
        $search_performed = true;
    }

    $departments = $studentManager->getDepartments();
    ?>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-search"></i> Search Students</h1>
                <p>Find students by name, student ID, or advanced criteria</p>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="students.php" class="nav-item">
                    <i class="fas fa-users"></i> All Students
                </a>
                <a href="add_student.php" class="nav-item">
                    <i class="fas fa-user-plus"></i> Add Student
                </a>
                <a href="search.php" class="nav-item active">
                    <i class="fas fa-search"></i> Search
                </a>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Quick Search Section -->
            <section class="search-section">
                <div class="form-container">
                    <h2><i class="fas fa-bolt"></i> Quick Search</h2>
                    <p>Search for students by name, student ID, or email address</p>
                    
                    <form method="GET" action="search.php" class="search-container">
                        <div class="form-group search-input">
                            <label for="quick_search">Search Query</label>
                            <input type="text" id="quick_search" name="q" 
                                   value="<?php echo htmlspecialchars($search_query); ?>" 
                                   placeholder="Enter name, student ID, or email..."
                                   autofocus>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                        
                        <?php if ($search_performed): ?>
                        <div class="form-group">
                            <a href="search.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </section>

            <!-- Search Results -->
            <?php if ($search_performed): ?>
            <section class="results-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-list"></i> 
                        Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                        <span style="color: #A08963; font-weight: normal;">(<?php echo count($search_results); ?> found)</span>
                    </h2>
                </div>
                
                <?php if (empty($search_results)): ?>
                <div class="form-container" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-search" style="font-size: 4rem; color: #C9B194; margin-bottom: 1rem;"></i>
                    <h3 style="color: #706D54; margin-bottom: 1rem;">No Results Found</h3>
                    <p style="color: #A08963; margin-bottom: 2rem;">
                        We couldn't find any students matching "<?php echo htmlspecialchars($search_query); ?>".
                        <br>Try different search terms or browse all students.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="students.php" class="btn btn-primary">
                            <i class="fas fa-users"></i> View All Students
                        </a>
                        <a href="add_student.php" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Add New Student
                        </a>
                    </div>
                </div>
                
                <?php else: ?>
                <div class="table-container">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $student): ?>
                            <tr>
                                <td>
                                    <strong style="background: linear-gradient(135deg, #706D54, #A08963); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                        <?php 
                                        $highlighted_id = str_ireplace($search_query, '<mark style="background: #C9B194; color: #706D54;">' . $search_query . '</mark>', $student['student_id']);
                                        echo $highlighted_id;
                                        ?>
                                    </strong>
                                </td>
                                <td>
                                    <div class="student-info">
                                        <strong>
                                            <?php 
                                            $full_name = $student['first_name'] . ' ' . $student['last_name'];
                                            $highlighted_name = str_ireplace($search_query, '<mark style="background: #C9B194; color: #706D54;">' . $search_query . '</mark>', $full_name);
                                            echo $highlighted_name;
                                            ?>
                                        </strong>
                                        <?php if ($student['date_of_birth']): ?>
                                        <br>
                                        <small style="color: #A08963;">
                                            Born: <?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" 
                                       style="color: #706D54; text-decoration: none;">
                                        <?php 
                                        $highlighted_email = str_ireplace($search_query, '<mark style="background: #C9B194; color: #706D54;">' . $search_query . '</mark>', $student['email']);
                                        echo $highlighted_email;
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($student['phone']): ?>
                                    <a href="tel:<?php echo htmlspecialchars($student['phone']); ?>" 
                                       style="color: #706D54; text-decoration: none;">
                                        <?php echo htmlspecialchars($student['phone']); ?>
                                    </a>
                                    <?php else: ?>
                                    <span style="color: #DBDBDB;">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="dept-tag"><?php echo htmlspecialchars($student['department_code'] ?? 'N/A'); ?></span>
                                    <?php if (isset($student['department_name'])): ?>
                                    <br>
                                    <small><?php echo htmlspecialchars($student['department_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($student['course_name'])): ?>
                                    <strong><?php echo htmlspecialchars($student['course_code'] ?? 'N/A'); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($student['course_name']); ?></small>
                                    <?php else: ?>
                                    <span style="color: #DBDBDB;">N/A</span>
                                    <?php endif; ?>
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
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <!-- Advanced Search Section -->
            <section class="advanced-search-section">
                <div class="form-container">
                    <h2><i class="fas fa-cog"></i> Advanced Search</h2>
                    <p>Use multiple criteria to find specific students</p>
                    
                    <form method="GET" action="students.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="search_name">Name or Email</label>
                                <input type="text" id="search_name" name="search" 
                                       placeholder="Search by name or email...">
                            </div>
                            
                            <div class="form-group">
                                <label for="search_department">Department</label>
                                <select id="search_department" name="department">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>">
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="search_status">Status</label>
                                <select id="search_status" name="status">
                                    <option value="">All Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Graduated">Graduated</option>
                                    <option value="Dropped">Dropped</option>
                                    <option value="Suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Advanced Search
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Search Tips -->
            <section class="tips-section">
                <div class="form-container">
                    <h2><i class="fas fa-lightbulb"></i> Search Tips</h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                        <div>
                            <h4 style="color: #706D54; margin-bottom: 1rem;">
                                <i class="fas fa-user"></i> Name Search
                            </h4>
                            <ul style="color: #A08963; line-height: 1.8;">
                                <li>Search by first name: "John"</li>
                                <li>Search by last name: "Doe"</li>
                                <li>Search by full name: "John Doe"</li>
                                <li>Partial matches work: "Jo" finds "John"</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 style="color: #706D54; margin-bottom: 1rem;">
                                <i class="fas fa-id-card"></i> ID & Email Search
                            </h4>
                            <ul style="color: #A08963; line-height: 1.8;">
                                <li>Student ID: "STU001"</li>
                                <li>Partial ID: "STU" finds all STU IDs</li>
                                <li>Email address: "student@email.com"</li>
                                <li>Email domain: "@email.com"</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 style="color: #706D54; margin-bottom: 1rem;">
                                <i class="fas fa-filter"></i> Advanced Filtering
                            </h4>
                            <ul style="color: #A08963; line-height: 1.8;">
                                <li>Filter by department</li>
                                <li>Filter by student status</li>
                                <li>Combine multiple filters</li>
                                <li>Export filtered results</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2><i class="fas fa-rocket"></i> Quick Actions</h2>
                <div class="actions-grid">
                    <a href="students.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h3>Browse All Students</h3>
                        <p>View complete student database</p>
                    </a>
                    
                    <a href="add_student.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3>Add New Student</h3>
                        <p>Register a new student</p>
                    </a>
                    
                    <a href="reports.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3>Generate Reports</h3>
                        <p>Create student reports</p>
                    </a>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p>&copy; 2024 Student Data Management System - Practice 11</p>
                <p>Advanced search functionality for comprehensive student data</p>
            </div>
        </footer>
    </div>

    <script>
        // Auto-focus search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('quick_search');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });

        // Real-time search suggestions (could be enhanced with AJAX)
        document.getElementById('quick_search').addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                // Here you could implement real-time search suggestions
                console.log('Searching for:', query);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('quick_search').focus();
            }
        });

        // Search result highlighting
        function highlightSearchTerm(text, term) {
            if (!term) return text;
            const regex = new RegExp(`(${term})`, 'gi');
            return text.replace(regex, '<mark style="background: #C9B194; color: #706D54;">$1</mark>');
        }
    </script>
</body>
</html>