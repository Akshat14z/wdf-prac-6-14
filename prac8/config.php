<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');  // XAMPP default port
define('DB_NAME', 'event_portal');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP default password

// Database Connection Class
class Database {
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->exec("SET NAMES utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Initialize database and create tables if they don't exist
function initializeDatabase() {
    try {
        // First, create the database if it doesn't exist
        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $pdo = null;
        
        // Now connect to the specific database
        $database = new Database();
        $conn = $database->getConnection();
        
        // Create users table
        $users_table = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        // Create events table
        $events_table = "CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            event_date DATE NOT NULL,
            event_time TIME NOT NULL,
            location VARCHAR(200) NOT NULL,
            organizer VARCHAR(100) NOT NULL,
            capacity INT DEFAULT 100,
            status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        // Create registrations table
        $registrations_table = "CREATE TABLE IF NOT EXISTS event_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_registration (event_id, user_id)
        )";
        
        $conn->exec($users_table);
        $conn->exec($events_table);
        $conn->exec($registrations_table);
        
        // Insert sample data if tables are empty
        insertSampleData($conn);
        
        return true;
        
    } catch(PDOException $e) {
        echo "Database initialization error: " . $e->getMessage();
        return false;
    }
}

// Insert sample data
function insertSampleData($conn) {
    try {
        // Check if users table is empty
        $stmt = $conn->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() == 0) {
            // Insert sample users
            $users = [
                ['admin', 'admin@example.com', password_hash('admin123', PASSWORD_DEFAULT), 'Administrator'],
                ['john_doe', 'john@example.com', password_hash('password123', PASSWORD_DEFAULT), 'John Doe'],
                ['jane_smith', 'jane@example.com', password_hash('password123', PASSWORD_DEFAULT), 'Jane Smith']
            ];
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
            foreach ($users as $user) {
                $stmt->execute($user);
            }
        }
        
        // Check if events table is empty
        $stmt = $conn->query("SELECT COUNT(*) FROM events");
        if ($stmt->fetchColumn() == 0) {
            // Insert sample events
            $events = [
                ['Web Development Workshop', 'Learn modern web development techniques including HTML5, CSS3, and JavaScript frameworks.', '2025-10-15', '10:00:00', 'Computer Lab A', 'Tech Institute', 30, 'active', 1],
                ['Mobile App Development Seminar', 'Explore mobile app development for Android and iOS platforms.', '2025-10-20', '14:00:00', 'Auditorium', 'Mobile Developers Guild', 50, 'active', 1],
                ['Database Design Masterclass', 'Advanced database design patterns and optimization techniques.', '2025-10-25', '09:00:00', 'Conference Room B', 'Database Experts', 25, 'active', 1],
                ['UI/UX Design Workshop', 'Learn principles of user interface and user experience design.', '2025-11-01', '11:00:00', 'Design Studio', 'Creative Agency', 40, 'active', 2],
                ['Cloud Computing Conference', 'Latest trends in cloud computing and serverless architectures.', '2025-11-05', '13:00:00', 'Main Hall', 'Cloud Solutions Inc', 100, 'active', 2],
                ['Cybersecurity Awareness Session', 'Essential cybersecurity practices for developers and users.', '2025-11-10', '15:00:00', 'Security Lab', 'CyberSafe Corp', 35, 'active', 3],
                ['AI and Machine Learning Symposium', 'Introduction to artificial intelligence and machine learning concepts.', '2025-11-15', '10:30:00', 'Innovation Center', 'AI Research Lab', 60, 'active', 3]
            ];
            
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, location, organizer, capacity, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($events as $event) {
                $stmt->execute($event);
            }
        }
        
    } catch(PDOException $e) {
        echo "Sample data insertion error: " . $e->getMessage();
    }
}

// Initialize the database when this file is included
initializeDatabase();
?>