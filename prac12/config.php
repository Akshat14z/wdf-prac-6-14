<?php
// Database configuration for Practice 12 - Event Management CRUD System
class Database {
    private $host = "localhost";
    private $port = "3306"; // XAMPP default port
    private $db_name = "event_management_prac12";
    private $username = "root";
    private $password = ""; // XAMPP default password
    private $conn = null;

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                // Try to create database if it doesn't exist
                try {
                    $this->conn = new PDO(
                        "mysql:host=" . $this->host . ";port=" . $this->port,
                        $this->username,
                        $this->password
                    );
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Create database
                    $this->conn->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    
                    // Connect to the created database
                    $this->conn = new PDO(
                        "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                        $this->username,
                        $this->password
                    );
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    
                    // Create tables
                    $this->createTables();
                    
                } catch (PDOException $e) {
                    throw new Exception("Connection failed: " . $e->getMessage());
                }
            }
        }
        return $this->conn;
    }
    
    private function createTables() {
        $sql = "
        -- Table structure for table events
        CREATE TABLE IF NOT EXISTS events (
            id int(11) NOT NULL AUTO_INCREMENT,
            title varchar(200) NOT NULL,
            description text,
            event_date date NOT NULL,
            event_time time NOT NULL,
            location varchar(200) NOT NULL,
            capacity int(11) DEFAULT 0,
            status enum('open','closed') DEFAULT 'open',
            organizer varchar(100) NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Insert sample events
        INSERT IGNORE INTO events (id, title, description, event_date, event_time, location, capacity, status, organizer) VALUES
        (1, 'Tech Conference 2025', 'Annual technology conference featuring latest innovations', '2025-10-15', '09:00:00', 'Convention Center Hall A', 500, 'open', 'Tech Society'),
        (2, 'Web Development Workshop', 'Hands-on workshop for modern web development techniques', '2025-10-22', '14:00:00', 'Computer Lab 101', 50, 'open', 'Dev Community'),
        (3, 'Database Design Seminar', 'Learn advanced database design principles and best practices', '2025-11-05', '10:30:00', 'Lecture Hall B', 100, 'closed', 'Database Guild'),
        (4, 'Mobile App Development Bootcamp', 'Intensive 3-day bootcamp for mobile app development', '2025-11-20', '09:00:00', 'Training Center', 30, 'open', 'Mobile Dev Academy');
        ";
        
        $this->conn->exec($sql);
    }
}

// Global database connection function
function getDbConnection() {
    $database = new Database();
    return $database->getConnection();
}
?>