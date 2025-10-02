<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Student Data Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    require_once 'config.php';
    $studentManager = new StudentManager($db);
    
    $student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $student = null;
    $success_message = '';
    $error_message = '';
    
    if ($student_id > 0) {
        $student = $studentManager->getStudent($student_id);
    }
    
    if (!$student) {
        header("Location: students.php");
        exit();
    }
    
    $departments = $studentManager->getDepartments();
    $courses = $studentManager->getCourses();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email', 'department_id', 'course_id'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required.';
            }
        }
        
        // Validate email format
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Validate GPA range
        if (!empty($_POST['gpa']) && ($_POST['gpa'] < 0 || $_POST['gpa'] > 4.0)) {
            $errors[] = 'GPA must be between 0.00 and 4.00.';
        }
        
        if (empty($errors)) {
            // Prepare data for update
            $student_data = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']) ?: null,
                'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                'gender' => $_POST['gender'] ?: 'Other',
                'address' => trim($_POST['address']) ?: null,
                'city' => trim($_POST['city']) ?: null,
                'state' => trim($_POST['state']) ?: null,
                'postal_code' => trim($_POST['postal_code']) ?: null,
                'country' => trim($_POST['country']) ?: 'USA',
                'department_id' => (int)$_POST['department_id'],
                'course_id' => (int)$_POST['course_id'],
                'graduation_year' => !empty($_POST['graduation_year']) ? (int)$_POST['graduation_year'] : null,
                'gpa' => !empty($_POST['gpa']) ? (float)$_POST['gpa'] : 0.00,
                'status' => $_POST['status'] ?: 'Active'
            ];
            
            if ($studentManager->updateStudent($student_id, $student_data)) {
                $success_message = 'Student information updated successfully!';
                // Refresh student data
                $student = $studentManager->getStudent($student_id);
            } else {
                $error_message = 'Error updating student information. Please check if email already exists for another student.';
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
    ?>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-user-edit"></i> Edit Student</h1>
                <p>Update information for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
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
                <a href="search.php" class="nav-item">
                    <i class="fas fa-search"></i> Search
                </a>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Action Buttons -->
            <section class="actions-bar" style="margin-bottom: 2rem;">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                    <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                    <a href="students.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> All Students
                    </a>
                    <div style="margin-left: auto;">
                        <span style="color: #A08963; font-weight: 600;">Student ID: </span>
                        <span style="color: #706D54; font-weight: bold;"><?php echo htmlspecialchars($student['student_id']); ?></span>
                    </div>
                </div>
            </section>

            <!-- Alerts -->
            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
                <div style="margin-top: 1rem;">
                    <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> View Profile
                    </a>
                    <a href="students.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> All Students
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <!-- Edit Student Form -->
            <section class="form-section">
                <div class="form-container">
                    <h2><i class="fas fa-form"></i> Student Information</h2>
                    
                    <form method="POST" action="edit_student.php?id=<?php echo $student['id']; ?>">
                        <!-- Personal Information -->
                        <h3 style="color: #706D54; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #C9B194;">
                            <i class="fas fa-user"></i> Personal Information
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name <span style="color: #A08963;">*</span></label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($student['first_name']); ?>" 
                                       placeholder="Enter first name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name <span style="color: #A08963;">*</span></label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($student['last_name']); ?>" 
                                       placeholder="Enter last name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address <span style="color: #A08963;">*</span></label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($student['email']); ?>" 
                                       placeholder="student@email.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" 
                                       placeholder="+1-555-0123">
                            </div>
                            
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" 
                                       value="<?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="Other" <?php echo ($student['gender'] ?? 'Other') === 'Other' ? 'selected' : ''; ?>>Prefer not to say</option>
                                    <option value="Male" <?php echo ($student['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($student['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <h3 style="color: #706D54; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #C9B194;">
                            <i class="fas fa-map-marker-alt"></i> Address Information
                        </h3>
                        <div class="form-grid">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="address">Street Address</label>
                                <textarea id="address" name="address" rows="2" 
                                          placeholder="Enter complete street address"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($student['city'] ?? ''); ?>" 
                                       placeholder="Enter city">
                            </div>
                            
                            <div class="form-group">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($student['state'] ?? ''); ?>" 
                                       placeholder="Enter state">
                            </div>
                            
                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars($student['postal_code'] ?? ''); ?>" 
                                       placeholder="Enter postal code">
                            </div>
                            
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" 
                                       value="<?php echo htmlspecialchars($student['country'] ?? 'USA'); ?>" 
                                       placeholder="Enter country">
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <h3 style="color: #706D54; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #C9B194;">
                            <i class="fas fa-graduation-cap"></i> Academic Information
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="department_id">Department <span style="color: #A08963;">*</span></label>
                                <select id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" 
                                            <?php echo $student['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']) . ' (' . htmlspecialchars($dept['department_code']) . ')'; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="course_id">Course <span style="color: #A08963;">*</span></label>
                                <select id="course_id" name="course_id" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" 
                                            data-department="<?php echo $course['department_id']; ?>"
                                            <?php echo $student['course_id'] == $course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_name']) . ' (' . htmlspecialchars($course['course_code']) . ')'; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="graduation_year">Expected Graduation Year</label>
                                <input type="number" id="graduation_year" name="graduation_year" 
                                       min="2024" max="2030" 
                                       value="<?php echo htmlspecialchars($student['graduation_year'] ?? ''); ?>" 
                                       placeholder="2026">
                            </div>
                            
                            <div class="form-group">
                                <label for="gpa">Current GPA</label>
                                <input type="number" id="gpa" name="gpa" 
                                       min="0" max="4" step="0.01" 
                                       value="<?php echo htmlspecialchars($student['gpa'] ?? ''); ?>" 
                                       placeholder="3.50">
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="Active" <?php echo ($student['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Graduated" <?php echo ($student['status'] ?? '') === 'Graduated' ? 'selected' : ''; ?>>Graduated</option>
                                    <option value="Dropped" <?php echo ($student['status'] ?? '') === 'Dropped' ? 'selected' : ''; ?>>Dropped</option>
                                    <option value="Suspended" <?php echo ($student['status'] ?? '') === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>
                        </div>

                        <!-- Read-only Information -->
                        <h3 style="color: #706D54; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #C9B194;">
                            <i class="fas fa-info-circle"></i> System Information
                        </h3>
                        <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; background: rgba(219, 219, 219, 0.2); padding: 1.5rem; border-radius: 12px;">
                            <div class="info-item">
                                <label>Student ID</label>
                                <p><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></p>
                            </div>
                            
                            <div class="info-item">
                                <label>Admission Date</label>
                                <p><?php echo date('F d, Y', strtotime($student['admission_date'])); ?></p>
                            </div>
                            
                            <div class="info-item">
                                <label>Account Created</label>
                                <p><?php echo date('F d, Y H:i', strtotime($student['created_at'])); ?></p>
                            </div>
                            
                            <div class="info-item">
                                <label>Last Updated</label>
                                <p><?php echo date('F d, Y H:i', strtotime($student['updated_at'])); ?></p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #C9B194; display: flex; gap: 1rem; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Student
                            </button>
                            <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                            <a href="students.php" class="btn btn-danger">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p>&copy; 2024 Student Data Management System - Practice 11</p>
                <p>Edit and update comprehensive student information</p>
            </div>
        </footer>
    </div>

    <script src="js/script.js"></script>
    
    <style>
        .info-item {
            margin-bottom: 1rem;
        }
        
        .info-item label {
            display: block;
            color: #A08963;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-item p {
            color: #706D54;
            font-size: 1rem;
            margin: 0;
        }
        
        /* Highlight changed fields */
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #706D54;
            box-shadow: 0 0 0 3px rgba(112, 109, 84, 0.1);
        }
        
        .form-group input.changed,
        .form-group select.changed,
        .form-group textarea.changed {
            border-color: #A08963;
            background-color: rgba(160, 137, 99, 0.05);
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store original values to detect changes
            const formElements = document.querySelectorAll('input, select, textarea');
            const originalValues = {};
            
            formElements.forEach(element => {
                if (element.name) {
                    originalValues[element.name] = element.value;
                }
            });
            
            // Highlight changed fields
            formElements.forEach(element => {
                if (element.name) {
                    element.addEventListener('input', function() {
                        if (this.value !== originalValues[this.name]) {
                            this.classList.add('changed');
                        } else {
                            this.classList.remove('changed');
                        }
                    });
                }
            });
            
            // Confirm navigation away if form has changes
            let formChanged = false;
            formElements.forEach(element => {
                element.addEventListener('input', function() {
                    formChanged = true;
                });
            });
            
            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
            
            // Don't warn when submitting the form
            document.querySelector('form').addEventListener('submit', function() {
                formChanged = false;
            });
        });
    </script>
</body>
</html>