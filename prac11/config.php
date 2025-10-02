<?php
// Database configuration for Practice 11 - Student Data Management System
class Database {
    private $host = "localhost";
    private $port = "3306"; // XAMPP default port
    private $db_name = "student_management";
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
                    $this->conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $this->conn->exec("USE " . $this->db_name);
                    
                    // Create normalized database schema
                    $this->createTables();
                    $this->insertSampleData();
                    
                } catch (PDOException $e) {
                    echo "Connection failed: " . $e->getMessage();
                    die();
                }
            }
        }
        return $this->conn;
    }

    private function createTables() {
        try {
            // Create departments table (normalized)
            $departmentsTable = "
                CREATE TABLE IF NOT EXISTS departments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    department_name VARCHAR(100) NOT NULL UNIQUE,
                    department_code VARCHAR(10) NOT NULL UNIQUE,
                    head_of_department VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $this->conn->exec($departmentsTable);

            // Create courses table (normalized)
            $coursesTable = "
                CREATE TABLE IF NOT EXISTS courses (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    course_name VARCHAR(100) NOT NULL,
                    course_code VARCHAR(20) NOT NULL UNIQUE,
                    credits INT DEFAULT 3,
                    department_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
                    INDEX idx_department (department_id),
                    INDEX idx_course_code (course_code)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $this->conn->exec($coursesTable);

            // Create students table (main table)
            $studentsTable = "
                CREATE TABLE IF NOT EXISTS students (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_id VARCHAR(20) NOT NULL UNIQUE,
                    first_name VARCHAR(50) NOT NULL,
                    last_name VARCHAR(50) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    phone VARCHAR(20),
                    date_of_birth DATE,
                    gender ENUM('Male', 'Female', 'Other') DEFAULT 'Other',
                    address TEXT,
                    city VARCHAR(50),
                    state VARCHAR(50),
                    postal_code VARCHAR(10),
                    country VARCHAR(50) DEFAULT 'India',
                    department_id INT NOT NULL,
                    course_id INT NOT NULL,
                    admission_date DATE NOT NULL,
                    graduation_year INT,
                    gpa DECIMAL(3,2) DEFAULT 0.00,
                    status ENUM('Active', 'Graduated', 'Dropped', 'Suspended') DEFAULT 'Active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
                    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT,
                    INDEX idx_student_id (student_id),
                    INDEX idx_name (first_name, last_name),
                    INDEX idx_email (email),
                    INDEX idx_department (department_id),
                    INDEX idx_course (course_id),
                    INDEX idx_status (status),
                    INDEX idx_admission_date (admission_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $this->conn->exec($studentsTable);

            // Create student_grades table for academic records
            $gradesTable = "
                CREATE TABLE IF NOT EXISTS student_grades (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_id INT NOT NULL,
                    course_id INT NOT NULL,
                    semester VARCHAR(20),
                    year INT,
                    grade CHAR(2),
                    credits INT DEFAULT 3,
                    grade_points DECIMAL(3,2),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                    INDEX idx_student_grade (student_id),
                    INDEX idx_semester_year (semester, year)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $this->conn->exec($gradesTable);

        } catch (PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
            throw $e;
        }
    }

    private function insertSampleData() {
        try {
            // Check if data already exists
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM students");
            $stmt->execute();
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                return; // Data already exists
            }

            // Insert departments
            $departments = [
                ['Computer Science', 'CS', 'Dr. Smith Johnson'],
                ['Information Technology', 'IT', 'Dr. Emily Davis'],
                ['Electronics Engineering', 'ECE', 'Dr. Michael Brown'],
                ['Mechanical Engineering', 'ME', 'Dr. Sarah Wilson'],
                ['Business Administration', 'MBA', 'Dr. Robert Taylor']
            ];

            $deptStmt = $this->conn->prepare("INSERT INTO departments (department_name, department_code, head_of_department) VALUES (?, ?, ?)");
            foreach ($departments as $dept) {
                $deptStmt->execute($dept);
            }

            // Insert courses
            $courses = [
                // CS Courses
                ['Data Structures', 'CS101', 4, 1],
                ['Database Systems', 'CS201', 3, 1],
                ['Web Development', 'CS301', 3, 1],
                ['Machine Learning', 'CS401', 4, 1],
                // IT Courses
                ['Network Security', 'IT101', 3, 2],
                ['System Administration', 'IT201', 3, 2],
                ['Cloud Computing', 'IT301', 4, 2],
                // ECE Courses
                ['Digital Electronics', 'ECE101', 3, 3],
                ['Microprocessors', 'ECE201', 4, 3],
                // ME Courses
                ['Thermodynamics', 'ME101', 3, 4],
                ['Fluid Mechanics', 'ME201', 3, 4],
                // MBA Courses
                ['Marketing Management', 'MBA101', 3, 5],
                ['Financial Analysis', 'MBA201', 3, 5]
            ];

            $courseStmt = $this->conn->prepare("INSERT INTO courses (course_name, course_code, credits, department_id) VALUES (?, ?, ?, ?)");
            foreach ($courses as $course) {
                $courseStmt->execute($course);
            }

            // Insert sample students
            $students = [
                ['STU001', 'John', 'Doe', 'john.doe@email.com', '+1-555-0101', '2000-01-15', 'Male', '123 Main St', 'New York', 'NY', '10001', 'USA', 1, 1, '2022-09-01', 2026, 3.75, 'Active'],
                ['STU002', 'Jane', 'Smith', 'jane.smith@email.com', '+1-555-0102', '1999-12-20', 'Female', '456 Oak Ave', 'Los Angeles', 'CA', '90210', 'USA', 1, 2, '2022-09-01', 2026, 3.85, 'Active'],
                ['STU003', 'Mike', 'Johnson', 'mike.johnson@email.com', '+1-555-0103', '2001-03-10', 'Male', '789 Pine St', 'Chicago', 'IL', '60601', 'USA', 2, 5, '2023-09-01', 2027, 3.60, 'Active'],
                ['STU004', 'Sarah', 'Wilson', 'sarah.wilson@email.com', '+1-555-0104', '2000-07-25', 'Female', '321 Elm St', 'Houston', 'TX', '77001', 'USA', 3, 8, '2022-09-01', 2026, 3.90, 'Active'],
                ['STU005', 'David', 'Brown', 'david.brown@email.com', '+1-555-0105', '1998-11-05', 'Male', '654 Maple Dr', 'Phoenix', 'AZ', '85001', 'USA', 4, 10, '2021-09-01', 2025, 3.50, 'Active'],
                ['STU006', 'Emily', 'Davis', 'emily.davis@email.com', '+1-555-0106', '2001-09-18', 'Female', '987 Cedar Ln', 'Philadelphia', 'PA', '19101', 'USA', 5, 12, '2023-09-01', 2027, 3.70, 'Active'],
                ['STU007', 'Alex', 'Miller', 'alex.miller@email.com', '+1-555-0107', '2000-05-30', 'Other', '147 Birch Rd', 'San Antonio', 'TX', '78201', 'USA', 1, 3, '2022-09-01', 2026, 3.65, 'Active'],
                ['STU008', 'Lisa', 'Taylor', 'lisa.taylor@email.com', '+1-555-0108', '1999-08-14', 'Female', '258 Spruce Ave', 'San Diego', 'CA', '92101', 'USA', 2, 6, '2021-09-01', 2025, 3.80, 'Active'],
                ['STU009', 'Robert', 'Anderson', 'robert.anderson@email.com', '+1-555-0109', '2001-02-28', 'Male', '369 Willow St', 'Dallas', 'TX', '75201', 'USA', 3, 9, '2023-09-01', 2027, 3.55, 'Active'],
                ['STU010', 'Amanda', 'Garcia', 'amanda.garcia@email.com', '+1-555-0110', '2000-12-12', 'Female', '741 Poplar Blvd', 'San Jose', 'CA', '95101', 'USA', 4, 11, '2022-09-01', 2026, 3.95, 'Active'],
                ['STU011', 'Chris', 'Martinez', 'chris.martinez@email.com', '+1-555-0111', '1997-04-07', 'Male', '852 Ash Dr', 'Austin', 'TX', '73301', 'USA', 1, 4, '2020-09-01', 2024, 3.40, 'Graduated'],
                ['STU012', 'Jessica', 'Rodriguez', 'jessica.rodriguez@email.com', '+1-555-0112', '2001-06-22', 'Female', '963 Hickory Ave', 'Jacksonville', 'FL', '32099', 'USA', 2, 7, '2023-09-01', 2027, 3.75, 'Active']
            ];

            $studentStmt = $this->conn->prepare("
                INSERT INTO students (student_id, first_name, last_name, email, phone, date_of_birth, gender, address, city, state, postal_code, country, department_id, course_id, admission_date, graduation_year, gpa, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($students as $student) {
                $studentStmt->execute($student);
            }

        } catch (PDOException $e) {
            error_log("Error inserting sample data: " . $e->getMessage());
            throw $e;
        }
    }

    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return $conn ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Student Management Class
class StudentManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create - Insert new student
    public function createStudent($data) {
        try {
            $sql = "INSERT INTO students (student_id, first_name, last_name, email, phone, date_of_birth, gender, address, city, state, postal_code, country, department_id, course_id, admission_date, graduation_year, gpa, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['student_id'], $data['first_name'], $data['last_name'], $data['email'],
                $data['phone'], $data['date_of_birth'], $data['gender'], $data['address'],
                $data['city'], $data['state'], $data['postal_code'], $data['country'],
                $data['department_id'], $data['course_id'], $data['admission_date'],
                $data['graduation_year'], $data['gpa'], $data['status']
            ]);
        } catch (PDOException $e) {
            error_log("Error creating student: " . $e->getMessage());
            return false;
        }
    }

    // Read - Get all students with search functionality
    public function getStudents($search = '', $department = '', $status = '', $limit = 50, $offset = 0) {
        try {
            $sql = "SELECT s.*, d.department_name, d.department_code, c.course_name, c.course_code 
                    FROM students s 
                    JOIN departments d ON s.department_id = d.id 
                    JOIN courses c ON s.course_id = c.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? OR s.email LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($department)) {
                $sql .= " AND s.department_id = ?";
                $params[] = $department;
            }
            
            if (!empty($status)) {
                $sql .= " AND s.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY s.last_name, s.first_name LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching students: " . $e->getMessage());
            return [];
        }
    }

    // Read - Get single student
    public function getStudent($id) {
        try {
            $sql = "SELECT s.*, d.department_name, d.department_code, c.course_name, c.course_code 
                    FROM students s 
                    JOIN departments d ON s.department_id = d.id 
                    JOIN courses c ON s.course_id = c.id 
                    WHERE s.id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching student: " . $e->getMessage());
            return false;
        }
    }

    // Update - Update student information
    public function updateStudent($id, $data) {
        try {
            $sql = "UPDATE students SET 
                    first_name = ?, last_name = ?, email = ?, phone = ?, 
                    date_of_birth = ?, gender = ?, address = ?, city = ?, 
                    state = ?, postal_code = ?, country = ?, department_id = ?, 
                    course_id = ?, graduation_year = ?, gpa = ?, status = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['first_name'], $data['last_name'], $data['email'], $data['phone'],
                $data['date_of_birth'], $data['gender'], $data['address'], $data['city'],
                $data['state'], $data['postal_code'], $data['country'], $data['department_id'],
                $data['course_id'], $data['graduation_year'], $data['gpa'], $data['status'], $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating student: " . $e->getMessage());
            return false;
        }
    }

    // Delete - Delete student
    public function deleteStudent($id) {
        try {
            $sql = "DELETE FROM students WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting student: " . $e->getMessage());
            return false;
        }
    }

    // Get departments for dropdowns
    public function getDepartments() {
        try {
            $sql = "SELECT * FROM departments ORDER BY department_name";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching departments: " . $e->getMessage());
            return [];
        }
    }

    // Get courses for dropdowns
    public function getCourses($department_id = null) {
        try {
            $sql = "SELECT * FROM courses";
            $params = [];
            
            if ($department_id) {
                $sql .= " WHERE department_id = ?";
                $params[] = $department_id;
            }
            
            $sql .= " ORDER BY course_name";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching courses: " . $e->getMessage());
            return [];
        }
    }

    // Get statistics
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total students
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM students");
            $stats['total_students'] = $stmt->fetch()['total'];
            
            // Active students
            $stmt = $this->conn->query("SELECT COUNT(*) as active FROM students WHERE status = 'Active'");
            $stats['active_students'] = $stmt->fetch()['active'];
            
            // Graduated students
            $stmt = $this->conn->query("SELECT COUNT(*) as graduated FROM students WHERE status = 'Graduated'");
            $stats['graduated_students'] = $stmt->fetch()['graduated'];
            
            // Average GPA
            $stmt = $this->conn->query("SELECT AVG(gpa) as avg_gpa FROM students WHERE status = 'Active'");
            $stats['average_gpa'] = round($stmt->fetch()['avg_gpa'], 2);
            
            // Department wise count
            $stmt = $this->conn->query("
                SELECT d.department_name, COUNT(s.id) as student_count 
                FROM departments d 
                LEFT JOIN students s ON d.id = s.department_id 
                GROUP BY d.id, d.department_name 
                ORDER BY student_count DESC
            ");
            $stats['department_stats'] = $stmt->fetchAll();
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error fetching statistics: " . $e->getMessage());
            return [];
        }
    }

    // Search students by name
    public function searchStudents($query) {
        try {
            $sql = "SELECT s.*, d.department_name, c.course_name 
                    FROM students s 
                    JOIN departments d ON s.department_id = d.id 
                    JOIN courses c ON s.course_id = c.id 
                    WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? 
                    ORDER BY s.last_name, s.first_name";
            
            $searchTerm = '%' . $query . '%';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error searching students: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize database
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}
?>