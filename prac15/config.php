<?php
/**
 * Practice 15 - Complete Web Portal Configuration
 * Full-stack integration of all semester modules
 * 
 * Features:
 * - User Authentication & Authorization
 * - Student Management System
 * - Event Management
 * - Registration Forms
 * - Analytics Dashboard
 * - Security Features
 * - Data Export/Import
 * - Session Management
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database Configuration for XAMPP
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'complete_web_portal');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('APP_NAME', 'Complete Web Portal');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/prac15/');
define('ADMIN_EMAIL', 'admin@webportal.com');

// Security Configuration
define('HASH_COST', 12);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes
define('REMEMBER_ME_DURATION', 604800); // 7 days

// File Upload Configuration
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/assets/uploads/');

// Pagination Configuration
define('RECORDS_PER_PAGE', 10);

// User Roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_USER', 'user');
define('ROLE_STUDENT', 'student');

// User Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');
define('STATUS_PENDING', 'pending');

/**
 * Database Connection Class with Singleton Pattern
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            // Try to connect to existing database
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Create database if it doesn't exist
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
                $this->connection = new PDO($dsn, DB_USER, DB_PASS);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create database
                $this->connection->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Reconnect to the new database
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $this->connection = new PDO($dsn, DB_USER, DB_PASS);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // Initialize database tables
                $this->initializeTables();
                $this->insertSampleData();
                
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function initializeTables() {
        $sql = "
        -- Users table with role-based access
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            role ENUM('super_admin', 'admin', 'manager', 'user', 'student') DEFAULT 'user',
            status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'active',
            avatar VARCHAR(255) NULL,
            phone VARCHAR(20) NULL,
            address TEXT NULL,
            date_of_birth DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            login_attempts INT DEFAULT 0,
            locked_until TIMESTAMP NULL,
            remember_token VARCHAR(255) NULL,
            email_verified BOOLEAN DEFAULT FALSE,
            verification_token VARCHAR(255) NULL
        );

        -- User sessions table
        CREATE TABLE IF NOT EXISTS user_sessions (
            id VARCHAR(128) PRIMARY KEY,
            user_id INT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            data TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            expires_at TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        -- Students table (extended from users)
        CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNIQUE,
            student_id VARCHAR(20) UNIQUE NOT NULL,
            department VARCHAR(100) NOT NULL,
            course VARCHAR(100) NOT NULL,
            year_of_study INT NOT NULL,
            semester INT NOT NULL,
            gpa DECIMAL(3,2) DEFAULT 0.00,
            enrollment_date DATE NOT NULL,
            graduation_date DATE NULL,
            status ENUM('enrolled', 'graduated', 'dropped', 'suspended') DEFAULT 'enrolled',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        -- Events table
        CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            event_type ENUM('academic', 'cultural', 'sports', 'workshop', 'seminar', 'conference') NOT NULL,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            location VARCHAR(200) NOT NULL,
            max_participants INT DEFAULT NULL,
            registration_deadline DATETIME NULL,
            fee DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('planned', 'active', 'completed', 'cancelled') DEFAULT 'planned',
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        );

        -- Event registrations
        CREATE TABLE IF NOT EXISTS event_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('registered', 'attended', 'cancelled', 'no_show') DEFAULT 'registered',
            payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
            notes TEXT NULL,
            UNIQUE KEY unique_registration (event_id, user_id),
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        -- Registration forms (general purpose)
        CREATE TABLE IF NOT EXISTS form_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            form_type VARCHAR(50) NOT NULL,
            user_id INT NULL,
            form_data JSON NOT NULL,
            status ENUM('pending', 'approved', 'rejected', 'processing') DEFAULT 'pending',
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            processed_at TIMESTAMP NULL,
            processed_by INT NULL,
            notes TEXT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
        );

        -- Analytics and statistics
        CREATE TABLE IF NOT EXISTS analytics_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            metric_name VARCHAR(100) NOT NULL,
            metric_value DECIMAL(15,2) NOT NULL,
            metric_type ENUM('counter', 'gauge', 'percentage') DEFAULT 'counter',
            category VARCHAR(50) NOT NULL,
            date_recorded DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_metric_date (metric_name, date_recorded),
            INDEX idx_category_date (category, date_recorded)
        );

        -- System logs
        CREATE TABLE IF NOT EXISTS system_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            action VARCHAR(100) NOT NULL,
            table_name VARCHAR(50) NULL,
            record_id INT NULL,
            old_values JSON NULL,
            new_values JSON NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );

        -- File uploads
        CREATE TABLE IF NOT EXISTS file_uploads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            stored_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INT NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        -- Notifications
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
            is_read BOOLEAN DEFAULT FALSE,
            action_url VARCHAR(500) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        ";
        
        $this->connection->exec($sql);
    }
    
    private function insertSampleData() {
        // Insert super admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        $sql = "INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role, status) VALUES 
                ('admin', 'admin@webportal.com', ?, 'Super', 'Admin', 'super_admin', 'active'),
                ('manager', 'manager@webportal.com', ?, 'Portal', 'Manager', 'manager', 'active'),
                ('student1', 'student1@webportal.com', ?, 'John', 'Doe', 'student', 'active'),
                ('student2', 'student2@webportal.com', ?, 'Jane', 'Smith', 'student', 'active'),
                ('user1', 'user1@webportal.com', ?, 'Alice', 'Johnson', 'user', 'active')";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$password, $password, $password, $password, $password]);
        
        // Insert sample students
        $sql = "INSERT IGNORE INTO students (user_id, student_id, department, course, year_of_study, semester, enrollment_date) VALUES 
                (3, 'STU001', 'Computer Science', 'B.Tech CSE', 3, 6, '2022-08-15'),
                (4, 'STU002', 'Information Technology', 'B.Tech IT', 2, 4, '2023-08-15')";
        
        $this->connection->exec($sql);
        
        // Insert sample events
        $sql = "INSERT IGNORE INTO events (title, description, event_type, start_date, end_date, location, max_participants, created_by) VALUES 
                ('Web Development Workshop', 'Learn modern web development techniques', 'workshop', '2025-10-15 10:00:00', '2025-10-15 16:00:00', 'Computer Lab 1', 30, 1),
                ('Annual Tech Fest', 'Technology festival with competitions and exhibitions', 'cultural', '2025-11-01 09:00:00', '2025-11-03 18:00:00', 'Main Auditorium', 500, 1),
                ('Database Management Seminar', 'Advanced database concepts and best practices', 'seminar', '2025-10-20 14:00:00', '2025-10-20 17:00:00', 'Seminar Hall', 50, 2)";
        
        $this->connection->exec($sql);
        
        // Insert sample analytics data
        $today = date('Y-m-d');
        $sql = "INSERT IGNORE INTO analytics_data (metric_name, metric_value, metric_type, category, date_recorded) VALUES 
                ('total_users', 5, 'gauge', 'users', ?),
                ('total_students', 2, 'gauge', 'students', ?),
                ('total_events', 3, 'gauge', 'events', ?),
                ('daily_logins', 8, 'counter', 'activity', ?),
                ('active_sessions', 3, 'gauge', 'activity', ?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$today, $today, $today, $today, $today]);
    }
}

// Initialize database connection
$db = Database::getInstance();
$pdo = $db->getConnection();

// Utility Functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function hasRole($required_role) {
    if (!isLoggedIn()) return false;
    
    $roles = [
        'user' => 1,
        'student' => 1,
        'manager' => 2,
        'admin' => 3,
        'super_admin' => 4
    ];
    
    $user_level = $roles[$_SESSION['user_role'] ?? 'user'] ?? 0;
    $required_level = $roles[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

function requireRole($required_role) {
    if (!hasRole($required_role)) {
        header('Location: access_denied.php');
        exit();
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function logActivity($action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    global $pdo;
    
    $sql = "INSERT INTO system_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        $action,
        $table_name,
        $record_id,
        $old_values ? json_encode($old_values) : null,
        $new_values ? json_encode($new_values) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

function addNotification($user_id, $title, $message, $type = 'info', $action_url = null) {
    global $pdo;
    
    $sql = "INSERT INTO notifications (user_id, title, message, type, action_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $title, $message, $type, $action_url]);
}

// Auto-create upload directory
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
?>