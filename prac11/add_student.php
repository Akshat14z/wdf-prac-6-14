<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Student Data Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    require_once 'config.php';
    $studentManager = new StudentManager($db);
    
    $success_message = '';
    $error_message = '';
    $departments = $studentManager->getDepartments();
    $courses = $studentManager->getCourses();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate required fields
        $required_fields = ['student_id', 'first_name', 'last_name', 'email', 'department_id', 'course_id', 'admission_date'];
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
        
        // Validate date format
        if (!empty($_POST['date_of_birth'])) {
            $dob = DateTime::createFromFormat('Y-m-d', $_POST['date_of_birth']);
            if (!$dob || $dob->format('Y-m-d') !== $_POST['date_of_birth']) {
                $errors[] = 'Please enter a valid birth date.';
            }
        }
        
        // Validate GPA range
        if (!empty($_POST['gpa']) && ($_POST['gpa'] < 0 || $_POST['gpa'] > 4.0)) {
            $errors[] = 'GPA must be between 0.00 and 4.00.';
        }
        
        if (empty($errors)) {
            // Prepare data for insertion
            $student_data = [
                'student_id' => trim($_POST['student_id']),
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
                'admission_date' => $_POST['admission_date'],
                'graduation_year' => !empty($_POST['graduation_year']) ? (int)$_POST['graduation_year'] : null,
                'gpa' => !empty($_POST['gpa']) ? (float)$_POST['gpa'] : 0.00,
                'status' => $_POST['status'] ?: 'Active'
            ];
            
            if ($studentManager->createStudent($student_data)) {
                $success_message = 'Student added successfully!';
                // Clear form data after successful submission
                $_POST = [];
            } else {
                $error_message = 'Error adding student. Please check if student ID or email already exists.';
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
                <h1><i class="fas fa-user-plus"></i> Add New Student</h1>
                <p>Register a new student in the system</p>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="students.php" class="nav-item">
                    <i class="fas fa-users"></i> All Students
                </a>
                <a href="add_student.php" class="nav-item active">
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
            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
                <div style="margin-top: 1rem;">
                    <a href="students.php" class="btn btn-primary">
                        <i class="fas fa-users"></i> View All Students
                    </a>
                    <a href="add_student.php" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Add Another Student
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

            <!-- Add Student Form -->
            <section class="form-section">
                <div class="form-container">
                    <h2><i class="fas fa-form"></i> Student Information</h2>
                    
                    <form method="POST" action="add_student.php">
                        <!-- Personal Information -->
                        <h3 style="color: #706D54; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #C9B194;">
                            <i class="fas fa-user"></i> Personal Information
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="student_id">Student ID <span style="color: #A08963;">*</span></label>
                                <input type="text" id="student_id" name="student_id" 
                                       value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" 
                                       placeholder="e.g., STU001" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="first_name">First Name <span style="color: #A08963;">*</span></label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" 
                                       placeholder="Enter first name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name <span style="color: #A08963;">*</span></label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" 
                                       placeholder="Enter last name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address <span style="color: #A08963;">*</span></label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       placeholder="student@email.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                       placeholder="+1-555-0123">
                            </div>
                            
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" 
                                       value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="Other" <?php echo ($_POST['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Prefer not to say</option>
                                    <option value="Male" <?php echo ($_POST['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
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
                                          placeholder="Enter complete street address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" 
                                       placeholder="Enter city">
                            </div>
                            
                            <div class="form-group">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>" 
                                       placeholder="Enter state">
                            </div>
                            
                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>" 
                                       placeholder="Enter postal code">
                            </div>
                            
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" 
                                       value="<?php echo htmlspecialchars($_POST['country'] ?? 'USA'); ?>" 
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
                                            <?php echo ($_POST['department_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
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
                                            <?php echo ($_POST['course_id'] ?? '') == $course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_name']) . ' (' . htmlspecialchars($course['course_code']) . ')'; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="admission_date">Admission Date <span style="color: #A08963;">*</span></label>
                                <input type="date" id="admission_date" name="admission_date" 
                                       value="<?php echo htmlspecialchars($_POST['admission_date'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="graduation_year">Expected Graduation Year</label>
                                <input type="number" id="graduation_year" name="graduation_year" 
                                       min="2024" max="2030" 
                                       value="<?php echo htmlspecialchars($_POST['graduation_year'] ?? ''); ?>" 
                                       placeholder="2026">
                            </div>
                            
                            <div class="form-group">
                                <label for="gpa">Current GPA</label>
                                <input type="number" id="gpa" name="gpa" 
                                       min="0" max="4" step="0.01" 
                                       value="<?php echo htmlspecialchars($_POST['gpa'] ?? ''); ?>" 
                                       placeholder="3.50">
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="Active" <?php echo ($_POST['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Graduated" <?php echo ($_POST['status'] ?? '') === 'Graduated' ? 'selected' : ''; ?>>Graduated</option>
                                    <option value="Dropped" <?php echo ($_POST['status'] ?? '') === 'Dropped' ? 'selected' : ''; ?>>Dropped</option>
                                    <option value="Suspended" <?php echo ($_POST['status'] ?? '') === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #C9B194; display: flex; gap: 1rem; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Student
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset Form
                            </button>
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
                <p>Add new students with comprehensive information</p>
            </div>
        </footer>
    </div>

    <script>
        // Filter courses based on selected department
        document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department_id');
            const courseSelect = document.getElementById('course_id');
            const allCourses = Array.from(courseSelect.options);
            
            function filterCourses() {
                const selectedDepartment = departmentSelect.value;
                
                // Clear current options except the first one
                courseSelect.innerHTML = '<option value="">Select Course</option>';
                
                if (selectedDepartment) {
                    // Add courses for selected department
                    allCourses.forEach(option => {
                        if (option.dataset.department === selectedDepartment && option.value) {
                            courseSelect.appendChild(option.cloneNode(true));
                        }
                    });
                } else {
                    // Add all courses if no department selected
                    allCourses.forEach(option => {
                        if (option.value) {
                            courseSelect.appendChild(option.cloneNode(true));
                        }
                    });
                }
            }
            
            departmentSelect.addEventListener('change', filterCourses);
            
            // Set expected graduation year based on admission date
            const admissionDateInput = document.getElementById('admission_date');
            const graduationYearInput = document.getElementById('graduation_year');
            
            admissionDateInput.addEventListener('change', function() {
                if (this.value && !graduationYearInput.value) {
                    const admissionYear = new Date(this.value).getFullYear();
                    graduationYearInput.value = admissionYear + 4; // Assuming 4-year program
                }
            });
            
            // Auto-generate student ID
            const firstNameInput = document.getElementById('first_name');
            const lastNameInput = document.getElementById('last_name');
            const studentIdInput = document.getElementById('student_id');
            
            function generateStudentId() {
                if (!studentIdInput.value && firstNameInput.value && lastNameInput.value) {
                    const firstName = firstNameInput.value.substring(0, 2).toUpperCase();
                    const lastName = lastNameInput.value.substring(0, 2).toUpperCase();
                    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                    studentIdInput.value = firstName + lastName + random;
                }
            }
            
            firstNameInput.addEventListener('blur', generateStudentId);
            lastNameInput.addEventListener('blur', generateStudentId);
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#A08963';
                    valid = false;
                } else {
                    field.style.borderColor = '#DBDBDB';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>