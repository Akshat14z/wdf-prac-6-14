<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Data Management - Practice 11</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        .section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, button { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; }
        input[type="text"], input[type="email"], input[type="date"], select { width: 200px; }
        button { background-color: #007cba; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; }
        button:hover { background-color: #005a87; }
        .btn-delete { background-color: #dc3545; }
        .btn-delete:hover { background-color: #c82333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .alert { padding: 15px; margin: 15px 0; border-radius: 4px; }
        .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .nav { margin-bottom: 20px; }
        .nav a { display: inline-block; padding: 10px 15px; margin: 5px; background-color: #e9ecef; text-decoration: none; color: #495057; border-radius: 4px; }
        .nav a:hover, .nav a.active { background-color: #007cba; color: white; }
    </style>
</head>
<body>
<?php 
require_once 'simple_config.php';

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'insert':
                $result = $studentManager->insertStudent(
                    $_POST['student_id'],
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['department_id'],
                    $_POST['admission_date']
                );
                if ($result) {
                    $message = "Student inserted successfully!";
                    $message_type = 'success';
                } else {
                    $message = "Error inserting student. Check if Student ID or Email already exists.";
                    $message_type = 'error';
                }
                break;
                
            case 'delete':
                $result = $studentManager->deleteStudent($_POST['student_id']);
                if ($result) {
                    $message = "Student deleted successfully!";
                    $message_type = 'success';
                } else {
                    $message = "Error deleting student.";
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Handle search
$students = [];
$search_query = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $students = $studentManager->searchByName($search_query);
} else {
    $students = $studentManager->selectAllStudents();
}

$departments = $studentManager->getDepartments();
?>

<div class="container">
    <h1>Student Data Management System</h1>
    <p><strong>Practice 11:</strong> Store and retrieve student data from MySQL DB</p>
    
    <div class="nav">
        <a href="?section=all" class="<?php echo (!isset($_GET['section']) || $_GET['section'] === 'all') ? 'active' : ''; ?>">All Students</a>
        <a href="?section=add" class="<?php echo (isset($_GET['section']) && $_GET['section'] === 'add') ? 'active' : ''; ?>">Add Student</a>
        <a href="?section=search" class="<?php echo (isset($_GET['section']) && $_GET['section'] === 'search') ? 'active' : ''; ?>">Search by Name</a>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <?php if (!isset($_GET['section']) || $_GET['section'] === 'all'): ?>
    <!-- Display All Students (SELECT operation) -->
    <div class="section">
        <h2>All Students (SELECT Query)</h2>
        <p>Demonstrates correct SELECT query with JOIN for normalized data</p>
        
        <?php if (empty($students)): ?>
            <p>No students found in the database.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Admission Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['phone']); ?></td>
                        <td><?php echo htmlspecialchars($student['department_name'] . ' (' . $student['department_code'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars($student['admission_date']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['section']) && $_GET['section'] === 'add'): ?>
    <!-- Add New Student (INSERT operation) -->
    <div class="section">
        <h2>Add New Student (INSERT Query)</h2>
        <p>Demonstrates correct INSERT query with prepared statements</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="insert">
            
            <div class="form-group">
                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" required>
            </div>
            
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="department_id">Department:</label>
                <select id="department_id" name="department_id" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>">
                            <?php echo htmlspecialchars($dept['name'] . ' (' . $dept['code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="admission_date">Admission Date:</label>
                <input type="date" id="admission_date" name="admission_date" required>
            </div>
            
            <button type="submit">Add Student</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['section']) && $_GET['section'] === 'search'): ?>
    <!-- Search by Name -->
    <div class="section">
        <h2>Search Students by Name</h2>
        <p>Supplementary Problem: Search functionality using LIKE queries</p>
        
        <form method="GET">
            <input type="hidden" name="section" value="search">
            <div class="form-group">
                <label for="search">Search by Name:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Enter first name or last name">
                <button type="submit">Search</button>
                <?php if ($search_query): ?>
                    <a href="?section=search" style="margin-left: 10px;">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($search_query): ?>
            <h3>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
            <?php if (empty($students)): ?>
                <p>No students found matching your search.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Admission Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['department_name'] . ' (' . $student['department_code'] . ')'); ?></td>
                            <td><?php echo htmlspecialchars($student['admission_date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Database Schema Information -->
    <div class="section">
        <h2>Database Schema (Normalized)</h2>
        <p>Demonstrates proper database normalization with separate tables</p>
        
        <h3>Tables:</h3>
        <ul>
            <li><strong>departments:</strong> id, name, code, created_at</li>
            <li><strong>students:</strong> id, student_id, first_name, last_name, email, phone, department_id (FK), admission_date, created_at</li>
        </ul>
        
        <h3>Key Features:</h3>
        <ul>
            <li>✅ Normalized schema (separate departments table)</li>
            <li>✅ Foreign key relationships</li>
            <li>✅ Prepared statements for SQL injection protection</li>
            <li>✅ Proper INSERT queries</li>
            <li>✅ Correct SELECT queries with JOINs</li>
            <li>✅ DELETE operations</li>
            <li>✅ Search by name functionality</li>
        </ul>
    </div>
</div>

</body>
</html>