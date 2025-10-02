<?php
// Simplified Database Configuration for Practice 11 - Student Data Management System
// Focus on core requirements: Store/retrieve data, correct SQL queries, normalized schema

class SimpleDatabase {
    private $host = "localhost";
    private $port = "3306"; // XAMPP default port  
    private $db_name = "student_db";
    private $username = "root";
    private $password = "";
    private $conn = null;

    public function getConnection() {
        if ($this->conn === null) {
            try {
                // Try to connect to existing database
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                // Create database if it doesn't exist
                try {
                    $this->conn = new PDO(
                        "mysql:host=" . $this->host . ";port=" . $this->port,
                        $this->username,
                        $this->password
                    );
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Create database
                    $this->conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $this->conn->exec("USE " . $this->db_name);
                    
                    // Create normalized schema
                    $this->createTables();
                    $this->insertSampleData();
                    
                } catch (PDOException $e) {
                    die("Database connection failed: " . $e->getMessage());
                }
            }
        }
        return $this->conn;
    }

    private function createTables() {
        // Normalized schema - separate departments table
        $departmentsTable = "
            CREATE TABLE IF NOT EXISTS departments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                code VARCHAR(10) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ";
        $this->conn->exec($departmentsTable);

        // Main students table with foreign key to departments
        $studentsTable = "
            CREATE TABLE IF NOT EXISTS students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id VARCHAR(20) NOT NULL UNIQUE,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                phone VARCHAR(20),
                department_id INT NOT NULL,
                admission_date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
                INDEX idx_name (first_name, last_name),
                INDEX idx_student_id (student_id)
            ) ENGINE=InnoDB
        ";
        $this->conn->exec($studentsTable);
    }

    private function insertSampleData() {
        // Check if data already exists
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM students");
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) return;

        // Insert departments
        $deptStmt = $this->conn->prepare("INSERT INTO departments (name, code) VALUES (?, ?)");
        $departments = [
            ['Computer Science', 'CS'],
            ['Information Technology', 'IT'],
            ['Electronics', 'ECE'],
            ['Mechanical Engineering', 'ME'],
            ['Business Administration', 'MBA']
        ];
        foreach ($departments as $dept) {
            $deptStmt->execute($dept);
        }

        // Insert sample students
        $studentStmt = $this->conn->prepare("
            INSERT INTO students (student_id, first_name, last_name, email, phone, department_id, admission_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $students = [
            ['STU001', 'John', 'Doe', 'john.doe@email.com', '555-0101', 1, '2023-09-01'],
            ['STU002', 'Jane', 'Smith', 'jane.smith@email.com', '555-0102', 1, '2023-09-01'],
            ['STU003', 'Mike', 'Johnson', 'mike.johnson@email.com', '555-0103', 2, '2023-09-01'],
            ['STU004', 'Sarah', 'Wilson', 'sarah.wilson@email.com', '555-0104', 3, '2023-09-01'],
            ['STU005', 'David', 'Brown', 'david.brown@email.com', '555-0105', 4, '2023-09-01'],
            ['STU006', 'Emily', 'Davis', 'emily.davis@email.com', '555-0106', 5, '2023-09-01'],
            ['STU007', 'Alex', 'Miller', 'alex.miller@email.com', '555-0107', 1, '2023-09-01'],
            ['STU008', 'Lisa', 'Taylor', 'lisa.taylor@email.com', '555-0108', 2, '2023-09-01'],
            ['STU009', 'Robert', 'Anderson', 'robert.anderson@email.com', '555-0109', 3, '2023-09-01'],
            ['STU010', 'Amanda', 'Garcia', 'amanda.garcia@email.com', '555-0110', 4, '2023-09-01']
        ];

        foreach ($students as $student) {
            $studentStmt->execute($student);
        }
    }
}

// Simple Student Manager Class
class SimpleStudentManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // INSERT - Add new student
    public function insertStudent($student_id, $first_name, $last_name, $email, $phone, $department_id, $admission_date) {
        try {
            $sql = "INSERT INTO students (student_id, first_name, last_name, email, phone, department_id, admission_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$student_id, $first_name, $last_name, $email, $phone, $department_id, $admission_date]);
        } catch (PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    // SELECT - Get all students
    public function selectAllStudents() {
        try {
            $sql = "SELECT s.*, d.name as department_name, d.code as department_code 
                    FROM students s 
                    JOIN departments d ON s.department_id = d.id 
                    ORDER BY s.last_name, s.first_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Select Error: " . $e->getMessage());
            return [];
        }
    }

    // SELECT - Get student by ID
    public function selectStudent($id) {
        try {
            $sql = "SELECT s.*, d.name as department_name, d.code as department_code 
                    FROM students s 
                    JOIN departments d ON s.department_id = d.id 
                    WHERE s.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Select Student Error: " . $e->getMessage());
            return false;
        }
    }

    // SELECT - Search students by name
    public function searchByName($name) {
        try {
            $sql = "SELECT s.*, d.name as department_name, d.code as department_code 
                    FROM students s 
                    JOIN departments d ON s.department_id = d.id 
                    WHERE s.first_name LIKE ? OR s.last_name LIKE ?
                    ORDER BY s.last_name, s.first_name";
            $searchTerm = '%' . $name . '%';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Search Error: " . $e->getMessage());
            return [];
        }
    }

    // DELETE - Remove student
    public function deleteStudent($id) {
        try {
            $sql = "DELETE FROM students WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }

    // Get departments for dropdowns
    public function getDepartments() {
        try {
            $sql = "SELECT * FROM departments ORDER BY name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Departments Error: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize database
try {
    $database = new SimpleDatabase();
    $db = $database->getConnection();
    $studentManager = new SimpleStudentManager($db);
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>