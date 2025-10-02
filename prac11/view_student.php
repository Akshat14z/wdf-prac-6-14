<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - Student Data Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    require_once 'config.php';
    $studentManager = new StudentManager($db);
    
    $student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $student = null;
    
    if ($student_id > 0) {
        $student = $studentManager->getStudent($student_id);
    }
    
    if (!$student) {
        header("Location: students.php");
        exit();
    }
    ?>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-user"></i> Student Profile</h1>
                <p>Detailed information for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
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
                    <a href="students.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Students
                    </a>
                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Student
                    </a>
                    <a href="students.php?delete=<?php echo $student['id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.')" 
                       class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Student
                    </a>
                    <div style="margin-left: auto;">
                        <span class="status-badge status-<?php echo strtolower($student['status']); ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                            <?php echo htmlspecialchars($student['status']); ?>
                        </span>
                    </div>
                </div>
            </section>

            <!-- Student Overview Card -->
            <section class="overview-section">
                <div class="form-container">
                    <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 2rem; align-items: center;">
                        <div class="student-avatar">
                            <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #706D54, #A08963); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: bold;">
                                <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                            </div>
                        </div>
                        
                        <div class="student-summary">
                            <h1 style="color: #706D54; margin-bottom: 0.5rem; font-size: 2.5rem;">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            </h1>
                            <p style="color: #A08963; font-size: 1.2rem; margin-bottom: 1rem;">
                                <strong><?php echo htmlspecialchars($student['student_id']); ?></strong> • 
                                <?php echo htmlspecialchars($student['department_name']); ?>
                            </p>
                            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                                <div>
                                    <span style="color: #706D54; font-weight: 600;">GPA:</span>
                                    <span class="gpa-badge <?php echo $student['gpa'] >= 3.5 ? 'high' : ($student['gpa'] >= 3.0 ? 'medium' : 'low'); ?>" style="margin-left: 0.5rem;">
                                        <?php echo number_format($student['gpa'], 2); ?>
                                    </span>
                                </div>
                                <div>
                                    <span style="color: #706D54; font-weight: 600;">Admission:</span>
                                    <span style="color: #A08963; margin-left: 0.5rem;">
                                        <?php echo date('M Y', strtotime($student['admission_date'])); ?>
                                    </span>
                                </div>
                                <?php if ($student['graduation_year']): ?>
                                <div>
                                    <span style="color: #706D54; font-weight: 600;">Expected Graduation:</span>
                                    <span style="color: #A08963; margin-left: 0.5rem;">
                                        <?php echo $student['graduation_year']; ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="contact-quick">
                            <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" 
                               class="btn btn-primary" style="margin-bottom: 0.5rem; width: 100%;">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <?php if ($student['phone']): ?>
                            <a href="tel:<?php echo htmlspecialchars($student['phone']); ?>" 
                               class="btn btn-secondary" style="width: 100%;">
                                <i class="fas fa-phone"></i> Call
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Detailed Information Tabs -->
            <section class="details-section">
                <div class="tab-container">
                    <div class="tab-buttons" style="display: flex; border-bottom: 3px solid #C9B194; margin-bottom: 2rem;">
                        <button class="tab-btn active" data-tab="personal">
                            <i class="fas fa-user"></i> Personal Info
                        </button>
                        <button class="tab-btn" data-tab="academic">
                            <i class="fas fa-graduation-cap"></i> Academic Info
                        </button>
                        <button class="tab-btn" data-tab="contact">
                            <i class="fas fa-map-marker-alt"></i> Contact Info
                        </button>
                        <button class="tab-btn" data-tab="timeline">
                            <i class="fas fa-clock"></i> Timeline
                        </button>
                    </div>

                    <!-- Personal Information Tab -->
                    <div class="tab-content active" id="personal">
                        <div class="form-container">
                            <h3><i class="fas fa-user"></i> Personal Information</h3>
                            <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                                <div class="info-item">
                                    <label>Full Name</label>
                                    <p><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                </div>
                                
                                <div class="info-item">
                                    <label>Student ID</label>
                                    <p><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></p>
                                </div>
                                
                                <div class="info-item">
                                    <label>Gender</label>
                                    <p><?php echo htmlspecialchars($student['gender'] ?: 'Not specified'); ?></p>
                                </div>
                                
                                <?php if ($student['date_of_birth']): ?>
                                <div class="info-item">
                                    <label>Date of Birth</label>
                                    <p><?php echo date('F d, Y', strtotime($student['date_of_birth'])); ?>
                                        <small style="color: #A08963; display: block;">
                                            Age: <?php echo date_diff(date_create($student['date_of_birth']), date_create('today'))->y; ?> years
                                        </small>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <label>Nationality</label>
                                    <p><?php echo htmlspecialchars($student['country'] ?: 'Not specified'); ?></p>
                                </div>
                                
                                <div class="info-item">
                                    <label>Account Created</label>
                                    <p><?php echo date('F d, Y H:i', strtotime($student['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information Tab -->
                    <div class="tab-content" id="academic">
                        <div class="form-container">
                            <h3><i class="fas fa-graduation-cap"></i> Academic Information</h3>
                            <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                                <div class="academic-card" style="background: rgba(201, 177, 148, 0.1); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #A08963;">
                                    <h4 style="color: #706D54; margin-bottom: 1rem;">
                                        <i class="fas fa-building"></i> Department
                                    </h4>
                                    <p><strong><?php echo htmlspecialchars($student['department_name']); ?></strong></p>
                                    <p>Code: <span class="dept-tag"><?php echo htmlspecialchars($student['department_code']); ?></span></p>
                                </div>
                                
                                <div class="academic-card" style="background: rgba(160, 137, 99, 0.1); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #706D54;">
                                    <h4 style="color: #706D54; margin-bottom: 1rem;">
                                        <i class="fas fa-book"></i> Course
                                    </h4>
                                    <p><strong><?php echo htmlspecialchars($student['course_name']); ?></strong></p>
                                    <p>Code: <strong><?php echo htmlspecialchars($student['course_code']); ?></strong></p>
                                </div>
                                
                                <div class="academic-card" style="background: rgba(112, 109, 84, 0.1); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #C9B194;">
                                    <h4 style="color: #706D54; margin-bottom: 1rem;">
                                        <i class="fas fa-star"></i> Academic Performance
                                    </h4>
                                    <p>Current GPA: 
                                        <span class="gpa-badge <?php echo $student['gpa'] >= 3.5 ? 'high' : ($student['gpa'] >= 3.0 ? 'medium' : 'low'); ?>">
                                            <?php echo number_format($student['gpa'], 2); ?>
                                        </span>
                                    </p>
                                    <p>Status: <span class="status-badge status-<?php echo strtolower($student['status']); ?>"><?php echo htmlspecialchars($student['status']); ?></span></p>
                                </div>
                            </div>
                            
                            <div style="margin-top: 2rem;">
                                <h4 style="color: #706D54; margin-bottom: 1rem;">Academic Timeline</h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                    <div class="timeline-item">
                                        <label>Admission Date</label>
                                        <p><?php echo date('F d, Y', strtotime($student['admission_date'])); ?></p>
                                    </div>
                                    
                                    <?php if ($student['graduation_year']): ?>
                                    <div class="timeline-item">
                                        <label>Expected Graduation</label>
                                        <p><?php echo $student['graduation_year']; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="timeline-item">
                                        <label>Duration</label>
                                        <p><?php 
                                        $admission = new DateTime($student['admission_date']);
                                        $now = new DateTime();
                                        $duration = $admission->diff($now);
                                        echo $duration->y . ' years, ' . $duration->m . ' months';
                                        ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Tab -->
                    <div class="tab-content" id="contact">
                        <div class="form-container">
                            <h3><i class="fas fa-map-marker-alt"></i> Contact Information</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                                <div class="contact-section">
                                    <h4 style="color: #706D54; margin-bottom: 1rem;">
                                        <i class="fas fa-envelope"></i> Digital Contact
                                    </h4>
                                    <div class="info-item">
                                        <label>Email Address</label>
                                        <p>
                                            <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" 
                                               style="color: #706D54; text-decoration: none;">
                                                <?php echo htmlspecialchars($student['email']); ?>
                                            </a>
                                        </p>
                                    </div>
                                    
                                    <?php if ($student['phone']): ?>
                                    <div class="info-item">
                                        <label>Phone Number</label>
                                        <p>
                                            <a href="tel:<?php echo htmlspecialchars($student['phone']); ?>" 
                                               style="color: #706D54; text-decoration: none;">
                                                <?php echo htmlspecialchars($student['phone']); ?>
                                            </a>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="address-section">
                                    <h4 style="color: #706D54; margin-bottom: 1rem;">
                                        <i class="fas fa-home"></i> Address
                                    </h4>
                                    <?php if ($student['address'] || $student['city'] || $student['state']): ?>
                                    <div class="address-block" style="background: rgba(219, 219, 219, 0.3); padding: 1rem; border-radius: 8px;">
                                        <?php if ($student['address']): ?>
                                        <p><?php echo htmlspecialchars($student['address']); ?></p>
                                        <?php endif; ?>
                                        
                                        <p>
                                            <?php 
                                            $location_parts = array_filter([
                                                $student['city'],
                                                $student['state'],
                                                $student['postal_code']
                                            ]);
                                            if (!empty($location_parts)) {
                                                echo htmlspecialchars(implode(', ', $location_parts));
                                            }
                                            ?>
                                        </p>
                                        
                                        <?php if ($student['country']): ?>
                                        <p><strong><?php echo htmlspecialchars($student['country']); ?></strong></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <p style="color: #A08963; font-style: italic;">No address information available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Tab -->
                    <div class="tab-content" id="timeline">
                        <div class="form-container">
                            <h3><i class="fas fa-clock"></i> Student Timeline</h3>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker" style="background: #706D54;"></div>
                                    <div class="timeline-content">
                                        <h4>Student Registration</h4>
                                        <p><?php echo date('F d, Y H:i', strtotime($student['created_at'])); ?></p>
                                        <small>Student account created in the system</small>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker" style="background: #A08963;"></div>
                                    <div class="timeline-content">
                                        <h4>Admission</h4>
                                        <p><?php echo date('F d, Y', strtotime($student['admission_date'])); ?></p>
                                        <small>Enrolled in <?php echo htmlspecialchars($student['course_name']); ?></small>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker" style="background: #C9B194;"></div>
                                    <div class="timeline-content">
                                        <h4>Current Status</h4>
                                        <p><?php echo date('F d, Y'); ?></p>
                                        <small>Status: <?php echo htmlspecialchars($student['status']); ?> • GPA: <?php echo number_format($student['gpa'], 2); ?></small>
                                    </div>
                                </div>
                                
                                <?php if ($student['graduation_year'] && $student['status'] !== 'Graduated'): ?>
                                <div class="timeline-item future">
                                    <div class="timeline-marker" style="background: #DBDBDB; border: 2px dashed #A08963;"></div>
                                    <div class="timeline-content">
                                        <h4>Expected Graduation</h4>
                                        <p><?php echo $student['graduation_year']; ?></p>
                                        <small>Projected completion of studies</small>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p>&copy; 2024 Student Data Management System - Practice 11</p>
                <p>Detailed student profile view</p>
            </div>
        </footer>
    </div>

    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.dataset.tab;
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and target content
                    this.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                });
            });
        });
    </script>

    <style>
        .tab-btn {
            background: transparent;
            border: none;
            padding: 1rem 1.5rem;
            cursor: pointer;
            color: #A08963;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab-btn:hover,
        .tab-btn.active {
            color: #706D54;
            border-bottom-color: #706D54;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .info-item {
            margin-bottom: 1.5rem;
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
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #C9B194;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 2rem;
        }
        
        .timeline-marker {
            position: absolute;
            left: -2rem;
            top: 0.5rem;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            background: #706D54;
        }
        
        .timeline-content h4 {
            color: #706D54;
            margin-bottom: 0.5rem;
        }
        
        .timeline-content p {
            color: #A08963;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .timeline-content small {
            color: #DBDBDB;
        }
        
        .timeline-item.future {
            opacity: 0.7;
        }
    </style>
</body>
</html>