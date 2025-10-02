<?php
require_once '../config.php';

// Require login and appropriate role
requireLogin();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Check if user has access to student management
if (!hasRole('student') && !hasRole('manager')) {
    header('Location: ../access_denied.php');
    exit();
}

$action = $_GET['action'] ?? 'list';
$student_id = $_GET['id'] ?? null;
$error_message = '';
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token.';
    } else {
        try {
            switch ($_POST['action']) {
                case 'add':
                    if (hasRole('manager')) {
                        // Add new student
                        $username = sanitizeInput($_POST['username']);
                        $email = sanitizeInput($_POST['email']);
                        $first_name = sanitizeInput($_POST['first_name']);
                        $last_name = sanitizeInput($_POST['last_name']);
                        $student_number = sanitizeInput($_POST['student_number']);
                        $department = sanitizeInput($_POST['department']);
                        $course = sanitizeInput($_POST['course']);
                        $year_of_study = (int)$_POST['year_of_study'];
                        $semester = (int)$_POST['semester'];
                        $password = $_POST['password'] ?? 'student123';
                        
                        // Validate required fields
                        if (empty($username) || empty($email) || empty($first_name) || empty($last_name) || 
                            empty($student_number) || empty($department) || empty($course)) {
                            throw new Exception('All fields are required.');
                        }
                        
                        $pdo->beginTransaction();
                        
                        // Insert user
                        $password_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
                        $stmt = $pdo->prepare("
                            INSERT INTO users (username, email, password_hash, first_name, last_name, role, status) 
                            VALUES (?, ?, ?, ?, ?, 'student', 'active')
                        ");
                        $stmt->execute([$username, $email, $password_hash, $first_name, $last_name]);
                        $new_user_id = $pdo->lastInsertId();
                        
                        // Insert student record
                        $stmt = $pdo->prepare("
                            INSERT INTO students (user_id, student_id, department, course, year_of_study, semester, enrollment_date) 
                            VALUES (?, ?, ?, ?, ?, ?, CURDATE())
                        ");
                        $stmt->execute([$new_user_id, $student_number, $department, $course, $year_of_study, $semester]);
                        
                        $pdo->commit();
                        
                        logActivity('student_added', 'students', $new_user_id);
                        addNotification($new_user_id, 'Welcome!', 'Your student account has been created successfully.', 'success');
                        
                        $success_message = 'Student added successfully!';
                        $action = 'list';
                        
                    } else {
                        throw new Exception('Access denied.');
                    }
                    break;
                    
                case 'edit':
                    if (hasRole('manager') || $_SESSION['user_id'] == $student_id) {
                        // Edit student
                        $stmt = $pdo->prepare("
                            UPDATE students s
                            JOIN users u ON s.user_id = u.id
                            SET s.department = ?, s.course = ?, s.year_of_study = ?, s.semester = ?,
                                u.first_name = ?, u.last_name = ?, u.email = ?
                            WHERE s.id = ?
                        ");
                        $stmt->execute([
                            sanitizeInput($_POST['department']),
                            sanitizeInput($_POST['course']),
                            (int)$_POST['year_of_study'],
                            (int)$_POST['semester'],
                            sanitizeInput($_POST['first_name']),
                            sanitizeInput($_POST['last_name']),
                            sanitizeInput($_POST['email']),
                            $student_id
                        ]);
                        
                        logActivity('student_updated', 'students', $student_id);
                        $success_message = 'Student updated successfully!';
                        $action = 'list';
                    }
                    break;
                    
                case 'delete':
                    if (hasRole('admin')) {
                        $stmt = $pdo->prepare("
                            UPDATE users u 
                            JOIN students s ON u.id = s.user_id 
                            SET u.status = 'inactive' 
                            WHERE s.id = ?
                        ");
                        $stmt->execute([$student_id]);
                        
                        logActivity('student_deleted', 'students', $student_id);
                        $success_message = 'Student deactivated successfully!';
                    }
                    break;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message = $e->getMessage();
        }
    }
}

// Get students list with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$department_filter = $_GET['department'] ?? '';

$where_conditions = ["u.status = 'active'"];
$params = [];

if ($search) {
    $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR s.student_id LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($department_filter) {
    $where_conditions[] = "s.department = ?";
    $params[] = $department_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM students s JOIN users u ON s.user_id = u.id WHERE $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

// Get students
$sql = "
    SELECT s.*, u.first_name, u.last_name, u.email, u.username, u.created_at, u.last_login
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    WHERE $where_clause
    ORDER BY s.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get departments for filter
$stmt = $pdo->query("SELECT DISTINCT department FROM students ORDER BY department");
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get specific student for edit/view
$current_student = null;
if ($student_id && in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("
        SELECT s.*, u.first_name, u.last_name, u.email, u.username, u.created_at, u.last_login
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$student_id]);
    $current_student = $stmt->fetch();
    
    if (!$current_student) {
        $error_message = 'Student not found.';
        $action = 'list';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    <title><?= APP_NAME ?> - Student Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <a href="students.php" class="nav-link active">
                            <i class="fas fa-users"></i> Students
                        </a>
                        <a href="events.php" class="nav-link">
                            <i class="fas fa-calendar"></i> Events
                        </a>
                        <a href="forms.php" class="nav-link">
                            <i class="fas fa-file-alt"></i> Forms
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
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($action === 'list'): ?>
                        <!-- Students List -->
                        <div class="page-header mb-3">
                            <div class="d-flex justify-between align-center">
                                <div>
                                    <h1><i class="fas fa-users"></i> Student Management</h1>
                                    <p class="text-muted">Manage student records and information</p>
                                </div>
                                <?php if (hasRole('manager')): ?>
                                <a href="?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Student
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Search and Filter -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <form method="GET" class="d-flex gap-2 align-center">
                                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                                           placeholder="Search students..." class="form-control" style="max-width: 300px;">
                                    
                                    <select name="department" class="form-control" style="max-width: 200px;">
                                        <option value="">All Departments</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= htmlspecialchars($dept) ?>" <?= $department_filter === $dept ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($dept) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    
                                    <?php if ($search || $department_filter): ?>
                                    <a href="students.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Students (<?= number_format($total_records) ?> total)</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($students)): ?>
                                    <p class="text-muted text-center">No students found.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table data-table">
                                            <thead>
                                                <tr>
                                                    <th data-sortable>Student ID</th>
                                                    <th data-sortable>Name</th>
                                                    <th data-sortable>Email</th>
                                                    <th data-sortable>Department</th>
                                                    <th data-sortable>Course</th>
                                                    <th>Year/Sem</th>
                                                    <th>GPA</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                                    <td><?= htmlspecialchars($student['department']) ?></td>
                                                    <td><?= htmlspecialchars($student['course']) ?></td>
                                                    <td>Year <?= $student['year_of_study'] ?>, Sem <?= $student['semester'] ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= $student['gpa'] >= 3.5 ? 'success' : ($student['gpa'] >= 2.5 ? 'warning' : 'danger') ?>">
                                                            <?= number_format($student['gpa'], 2) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1">
                                                            <a href="?action=view&id=<?= $student['id'] ?>" class="btn btn-sm btn-secondary" data-tooltip="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if (hasRole('manager') || $_SESSION['user_id'] == $student['user_id']): ?>
                                                            <a href="?action=edit&id=<?= $student['id'] ?>" class="btn btn-sm btn-warning" data-tooltip="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            <?php if (hasRole('admin')): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                                <input type="hidden" name="action" value="delete">
                                                                <button type="submit" class="btn btn-sm btn-danger confirm-action" 
                                                                        data-confirm="Are you sure you want to deactivate this student?"
                                                                        data-tooltip="Deactivate">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <?php if ($total_pages > 1): ?>
                                    <div class="d-flex justify-between align-center mt-3">
                                        <p class="text-muted">
                                            Showing <?= number_format($offset + 1) ?> to <?= number_format(min($offset + $limit, $total_records)) ?> of <?= number_format($total_records) ?> entries
                                        </p>
                                        <div class="pagination">
                                            <?php if ($page > 1): ?>
                                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&department=<?= urlencode($department_filter) ?>" class="btn btn-sm btn-secondary">Previous</a>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&department=<?= urlencode($department_filter) ?>" 
                                                   class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>">
                                                    <?= $i ?>
                                                </a>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $total_pages): ?>
                                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&department=<?= urlencode($department_filter) ?>" class="btn btn-sm btn-secondary">Next</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif ($action === 'add' && hasRole('manager')): ?>
                        <!-- Add Student Form -->
                        <div class="page-header mb-3">
                            <h1><i class="fas fa-plus"></i> Add New Student</h1>
                            <p class="text-muted">Create a new student account</p>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3>Student Information</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="ajax-form">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="action" value="add">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="first_name" class="form-label">First Name *</label>
                                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="last_name" class="form-label">Last Name *</label>
                                                <input type="text" id="last_name" name="last_name" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="username" class="form-label">Username *</label>
                                                <input type="text" id="username" name="username" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" id="email" name="email" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="student_number" class="form-label">Student ID *</label>
                                                <input type="text" id="student_number" name="student_number" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="department" class="form-label">Department *</label>
                                                <select id="department" name="department" class="form-control" required>
                                                    <option value="">Select Department</option>
                                                    <option value="Computer Science">Computer Science</option>
                                                    <option value="Information Technology">Information Technology</option>
                                                    <option value="Electronics Engineering">Electronics Engineering</option>
                                                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                                                    <option value="Civil Engineering">Civil Engineering</option>
                                                    <option value="Business Administration">Business Administration</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="course" class="form-label">Course *</label>
                                                <input type="text" id="course" name="course" class="form-control" required placeholder="e.g., B.Tech CSE">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="year_of_study" class="form-label">Year of Study *</label>
                                                <select id="year_of_study" name="year_of_study" class="form-control" required>
                                                    <option value="">Select Year</option>
                                                    <option value="1">1st Year</option>
                                                    <option value="2">2nd Year</option>
                                                    <option value="3">3rd Year</option>
                                                    <option value="4">4th Year</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="semester" class="form-label">Semester *</label>
                                                <select id="semester" name="semester" class="form-control" required>
                                                    <option value="">Select Semester</option>
                                                    <option value="1">1st Semester</option>
                                                    <option value="2">2nd Semester</option>
                                                    <option value="3">3rd Semester</option>
                                                    <option value="4">4th Semester</option>
                                                    <option value="5">5th Semester</option>
                                                    <option value="6">6th Semester</option>
                                                    <option value="7">7th Semester</option>
                                                    <option value="8">8th Semester</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Add Student
                                        </button>
                                        <a href="students.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php elseif ($action === 'view' && $current_student): ?>
                        <!-- View Student -->
                        <div class="page-header mb-3">
                            <div class="d-flex justify-between align-center">
                                <div>
                                    <h1><i class="fas fa-eye"></i> Student Details</h1>
                                    <p class="text-muted">Viewing <?= htmlspecialchars($current_student['first_name'] . ' ' . $current_student['last_name']) ?></p>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if (hasRole('manager') || $_SESSION['user_id'] == $current_student['user_id']): ?>
                                    <a href="?action=edit&id=<?= $current_student['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php endif; ?>
                                    <a href="students.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h3>Personal Information</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Student ID:</strong> <?= htmlspecialchars($current_student['student_id']) ?></p>
                                                <p><strong>First Name:</strong> <?= htmlspecialchars($current_student['first_name']) ?></p>
                                                <p><strong>Last Name:</strong> <?= htmlspecialchars($current_student['last_name']) ?></p>
                                                <p><strong>Email:</strong> <?= htmlspecialchars($current_student['email']) ?></p>
                                                <p><strong>Username:</strong> <?= htmlspecialchars($current_student['username']) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Department:</strong> <?= htmlspecialchars($current_student['department']) ?></p>
                                                <p><strong>Course:</strong> <?= htmlspecialchars($current_student['course']) ?></p>
                                                <p><strong>Year of Study:</strong> <?= $current_student['year_of_study'] ?></p>
                                                <p><strong>Semester:</strong> <?= $current_student['semester'] ?></p>
                                                <p><strong>Status:</strong> 
                                                    <span class="badge badge-<?= $current_student['status'] === 'enrolled' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($current_student['status']) ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3>Academic Information</h3>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>GPA:</strong> 
                                            <span class="badge badge-<?= $current_student['gpa'] >= 3.5 ? 'success' : ($current_student['gpa'] >= 2.5 ? 'warning' : 'danger') ?>">
                                                <?= number_format($current_student['gpa'], 2) ?>
                                            </span>
                                        </p>
                                        <p><strong>Enrollment Date:</strong> <?= date('M j, Y', strtotime($current_student['enrollment_date'])) ?></p>
                                        <p><strong>Account Created:</strong> <?= date('M j, Y', strtotime($current_student['created_at'])) ?></p>
                                        <p><strong>Last Login:</strong> 
                                            <?= $current_student['last_login'] ? date('M j, Y g:i A', strtotime($current_student['last_login'])) : 'Never' ?>
                                        </p>
                                    </div>
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

    <script src="../js/script.js"></script>
</body>
</html>