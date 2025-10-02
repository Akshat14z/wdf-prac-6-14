-- ========================================
-- Practice 15 - Complete Web Portal Database
-- Database Setup Script for XAMPP/MySQL
-- ========================================

-- Create database
CREATE DATABASE IF NOT EXISTS `complete_web_portal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `complete_web_portal`;

-- ========================================
-- TABLE STRUCTURE
-- ========================================

-- Users table with role-based access
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('super_admin','admin','manager','user','student') DEFAULT 'user',
  `status` enum('active','inactive','suspended','pending') DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Students table (extended from users)
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `student_id` varchar(20) NOT NULL,
  `department` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_of_study` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `gpa` decimal(3,2) DEFAULT 0.00,
  `enrollment_date` date NOT NULL,
  `graduation_date` date DEFAULT NULL,
  `status` enum('enrolled','graduated','dropped','suspended') DEFAULT 'enrolled',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `student_id` (`student_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` enum('academic','cultural','sports','workshop','seminar','conference') NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(200) NOT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `registration_deadline` datetime DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `status` enum('planned','active','completed','cancelled') DEFAULT 'planned',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event registrations
DROP TABLE IF EXISTS `event_registrations`;
CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('registered','attended','cancelled','no_show') DEFAULT 'registered',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_registration` (`event_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registration forms (general purpose)
DROP TABLE IF EXISTS `form_submissions`;
CREATE TABLE `form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_type` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`form_data`)),
  `status` enum('pending','approved','rejected','processing') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `form_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `form_submissions_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics and statistics
DROP TABLE IF EXISTS `analytics_data`;
CREATE TABLE `analytics_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(15,2) NOT NULL,
  `metric_type` enum('counter','gauge','percentage') DEFAULT 'counter',
  `category` varchar(50) NOT NULL,
  `date_recorded` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_metric_date` (`metric_name`,`date_recorded`),
  KEY `idx_category_date` (`category`,`date_recorded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System logs
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File uploads
DROP TABLE IF EXISTS `file_uploads`;
CREATE TABLE `file_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `file_uploads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SAMPLE DATA INSERTION
-- ========================================

-- Insert demo users (password for all: admin123)
INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `status`) VALUES
('admin', 'admin@webportal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Admin', 'super_admin', 'active'),
('manager', 'manager@webportal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Portal', 'Manager', 'manager', 'active'),
('student1', 'student1@webportal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'student', 'active'),
('student2', 'student2@webportal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'student', 'active'),
('user1', 'user1@webportal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Johnson', 'user', 'active'),
('professor1', 'prof1@webportal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Robert', 'Wilson', 'manager', 'active'),
('student3', 'student3@webportal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Brown', 'student', 'active'),
('student4', 'student4@webportal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Davis', 'student', 'active');

-- Insert sample students
INSERT INTO `students` (`user_id`, `student_id`, `department`, `course`, `year_of_study`, `semester`, `gpa`, `enrollment_date`, `status`) VALUES
(3, 'STU001', 'Computer Science', 'B.Tech CSE', 3, 6, 3.75, '2022-08-15', 'enrolled'),
(4, 'STU002', 'Information Technology', 'B.Tech IT', 2, 4, 3.42, '2023-08-15', 'enrolled'),
(7, 'STU003', 'Computer Science', 'B.Tech CSE', 4, 8, 3.89, '2021-08-15', 'enrolled'),
(8, 'STU004', 'Electronics Engineering', 'B.Tech ECE', 1, 2, 3.21, '2024-08-15', 'enrolled');

-- Insert sample events
INSERT INTO `events` (`title`, `description`, `event_type`, `start_date`, `end_date`, `location`, `max_participants`, `registration_deadline`, `fee`, `status`, `created_by`) VALUES
('Web Development Workshop', 'Learn modern web development techniques including HTML5, CSS3, JavaScript, and PHP. This hands-on workshop covers frontend and backend development.', 'workshop', '2025-10-15 10:00:00', '2025-10-15 16:00:00', 'Computer Lab 1', 30, '2025-10-13 23:59:59', 0.00, 'active', 1),
('Annual Tech Fest 2025', 'Technology festival with competitions, exhibitions, and tech talks. Features coding competitions, robotics showcase, and industry expert sessions.', 'cultural', '2025-11-01 09:00:00', '2025-11-03 18:00:00', 'Main Auditorium', 500, '2025-10-25 23:59:59', 150.00, 'active', 2),
('Database Management Seminar', 'Advanced database concepts and best practices. Topics include normalization, indexing, query optimization, and modern database technologies.', 'seminar', '2025-10-20 14:00:00', '2025-10-20 17:00:00', 'Seminar Hall', 50, '2025-10-18 23:59:59', 50.00, 'active', 6),
('AI & Machine Learning Conference', 'Explore the latest trends in artificial intelligence and machine learning. Industry experts share insights and practical applications.', 'conference', '2025-12-05 09:00:00', '2025-12-06 17:00:00', 'Convention Center', 200, '2025-11-30 23:59:59', 300.00, 'planned', 1),
('Sports Day 2025', 'Annual sports competition featuring various indoor and outdoor games. Open to all students and faculty members.', 'sports', '2025-10-28 08:00:00', '2025-10-28 18:00:00', 'Sports Complex', 300, '2025-10-25 23:59:59', 0.00, 'active', 2);

-- Insert sample event registrations
INSERT INTO `event_registrations` (`event_id`, `user_id`, `status`, `payment_status`) VALUES
(1, 3, 'registered', 'paid'),
(1, 4, 'registered', 'paid'),
(1, 7, 'registered', 'paid'),
(2, 3, 'registered', 'pending'),
(2, 4, 'registered', 'paid'),
(2, 7, 'registered', 'paid'),
(2, 8, 'registered', 'pending'),
(3, 3, 'attended', 'paid'),
(3, 7, 'registered', 'paid'),
(5, 3, 'registered', 'paid'),
(5, 4, 'registered', 'paid'),
(5, 7, 'registered', 'paid'),
(5, 8, 'registered', 'paid');

-- Insert sample form submissions
INSERT INTO `form_submissions` (`form_type`, `user_id`, `form_data`, `status`, `submitted_at`) VALUES
('scholarship_application', 3, '{"name":"John Doe","student_id":"STU001","academic_year":"2024-25","gpa":"3.75","financial_need":"Yes","reason":"Family financial difficulties due to medical expenses"}', 'pending', '2025-10-01 10:30:00'),
('internship_request', 4, '{"name":"Jane Smith","student_id":"STU002","company":"TechCorp Solutions","duration":"3 months","start_date":"2025-11-01","supervisor":"Dr. Wilson"}', 'approved', '2025-09-28 14:20:00'),
('course_registration', 7, '{"name":"Michael Brown","student_id":"STU003","semester":"8","courses":["Advanced Algorithms","Machine Learning","Software Engineering","Database Systems"],"credits":"18"}', 'approved', '2025-09-25 11:15:00'),
('leave_application', 8, '{"name":"Sarah Davis","student_id":"STU004","leave_type":"Medical","start_date":"2025-10-10","end_date":"2025-10-15","reason":"Surgery and recovery period"}', 'pending', '2025-10-02 09:45:00');

-- Insert sample analytics data
INSERT INTO `analytics_data` (`metric_name`, `metric_value`, `metric_type`, `category`, `date_recorded`) VALUES
('total_users', 8.00, 'gauge', 'users', '2025-10-02'),
('total_students', 4.00, 'gauge', 'students', '2025-10-02'),
('total_events', 5.00, 'gauge', 'events', '2025-10-02'),
('daily_logins', 12.00, 'counter', 'activity', '2025-10-02'),
('active_sessions', 5.00, 'gauge', 'activity', '2025-10-02'),
('event_registrations', 13.00, 'counter', 'events', '2025-10-02'),
('form_submissions', 4.00, 'counter', 'forms', '2025-10-02'),
('system_uptime', 99.95, 'percentage', 'performance', '2025-10-02');

-- Insert sample system logs
INSERT INTO `system_logs` (`user_id`, `action`, `table_name`, `ip_address`, `user_agent`) VALUES
(1, 'login_success', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, 'login_success', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(3, 'login_success', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(1, 'event_created', 'events', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, 'student_added', 'students', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(3, 'form_submitted', 'form_submissions', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(1, 'dashboard_access', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, 'analytics_accessed', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

-- Insert sample notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `is_read`) VALUES
(3, 'Welcome to Portal!', 'Your student account has been created successfully. Explore the features and manage your academic information.', 'success', 0),
(4, 'Event Registration Confirmed', 'You have successfully registered for the Annual Tech Fest 2025. Check your email for more details.', 'info', 0),
(7, 'Scholarship Application', 'Your scholarship application has been received and is under review. You will be notified of the decision soon.', 'info', 1),
(8, 'Course Registration Deadline', 'Reminder: Course registration deadline is approaching. Please complete your registration by October 15, 2025.', 'warning', 0),
(1, 'System Update', 'Portal maintenance is scheduled for this weekend. Expected downtime: 2 hours on Sunday 2 AM.', 'info', 1),
(2, 'New Analytics Report', 'Monthly analytics report is now available. Check the analytics dashboard for detailed insights.', 'success', 0);

-- ========================================
-- INDEXES FOR PERFORMANCE
-- ========================================

-- Additional indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_students_department ON students(department);
CREATE INDEX idx_students_year ON students(year_of_study);
CREATE INDEX idx_events_type ON events(event_type);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_events_start_date ON events(start_date);
CREATE INDEX idx_form_submissions_type ON form_submissions(form_type);
CREATE INDEX idx_form_submissions_status ON form_submissions(status);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_system_logs_action ON system_logs(action);
CREATE INDEX idx_system_logs_created_at ON system_logs(created_at);

-- ========================================
-- VIEWS FOR REPORTING
-- ========================================

-- View for active students with user details
CREATE VIEW active_students AS
SELECT 
    s.id,
    s.student_id,
    u.first_name,
    u.last_name,
    u.email,
    s.department,
    s.course,
    s.year_of_study,
    s.semester,
    s.gpa,
    s.enrollment_date,
    u.last_login
FROM students s
JOIN users u ON s.user_id = u.id
WHERE u.status = 'active' AND s.status = 'enrolled';

-- View for event statistics
CREATE VIEW event_stats AS
SELECT 
    e.id,
    e.title,
    e.event_type,
    e.start_date,
    e.max_participants,
    COUNT(er.id) as registered_count,
    COUNT(CASE WHEN er.status = 'attended' THEN 1 END) as attended_count,
    CASE 
        WHEN e.max_participants > 0 THEN ROUND((COUNT(er.id) / e.max_participants) * 100, 2)
        ELSE 0 
    END as fill_percentage
FROM events e
LEFT JOIN event_registrations er ON e.id = er.event_id
GROUP BY e.id;

-- ========================================
-- SUCCESS MESSAGE
-- ========================================

SELECT 'Database setup completed successfully!' as Status,
       'You can now access the portal at: http://localhost/prac15/' as Message,
       'Default login credentials are in the README.md file' as Note;