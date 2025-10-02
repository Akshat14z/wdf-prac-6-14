-- Practice 11: Student Data Management System
-- SQL Dump for testing database functionality

-- Create database
CREATE DATABASE IF NOT EXISTS student_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_db;

-- Drop existing tables if they exist (for fresh start)
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS departments;

-- Create departments table (normalized approach)
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create students table with foreign key to departments
CREATE TABLE students (
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
) ENGINE=InnoDB;

-- Insert sample departments
INSERT INTO departments (name, code) VALUES 
('Computer Science', 'CS'),
('Information Technology', 'IT'),
('Electronics Engineering', 'ECE'),
('Mechanical Engineering', 'ME'),
('Business Administration', 'MBA');

-- Insert sample students
INSERT INTO students (student_id, first_name, last_name, email, phone, department_id, admission_date) VALUES 
('STU001', 'John', 'Doe', 'john.doe@email.com', '555-0101', 1, '2023-09-01'),
('STU002', 'Jane', 'Smith', 'jane.smith@email.com', '555-0102', 1, '2023-09-01'),
('STU003', 'Mike', 'Johnson', 'mike.johnson@email.com', '555-0103', 2, '2023-09-01'),
('STU004', 'Sarah', 'Wilson', 'sarah.wilson@email.com', '555-0104', 3, '2023-09-01'),
('STU005', 'David', 'Brown', 'david.brown@email.com', '555-0105', 4, '2023-09-01'),
('STU006', 'Emily', 'Davis', 'emily.davis@email.com', '555-0106', 5, '2023-09-01'),
('STU007', 'Alex', 'Miller', 'alex.miller@email.com', '555-0107', 1, '2023-09-01'),
('STU008', 'Lisa', 'Taylor', 'lisa.taylor@email.com', '555-0108', 2, '2023-09-01'),
('STU009', 'Robert', 'Anderson', 'robert.anderson@email.com', '555-0109', 3, '2023-09-01'),
('STU010', 'Amanda', 'Garcia', 'amanda.garcia@email.com', '555-0110', 4, '2023-09-01');

-- Test queries to verify functionality:

-- 1. SELECT query with JOIN (normalized data retrieval)
SELECT s.student_id, s.first_name, s.last_name, s.email, d.name as department_name, d.code as department_code
FROM students s 
JOIN departments d ON s.department_id = d.id 
ORDER BY s.last_name, s.first_name;

-- 2. Search by name query
SELECT s.*, d.name as department_name 
FROM students s 
JOIN departments d ON s.department_id = d.id 
WHERE s.first_name LIKE '%John%' OR s.last_name LIKE '%Smith%';

-- 3. Count students by department (aggregation)
SELECT d.name, d.code, COUNT(s.id) as student_count 
FROM departments d 
LEFT JOIN students s ON d.id = s.department_id 
GROUP BY d.id, d.name, d.code;

-- 4. Get total number of students
SELECT COUNT(*) as total_students FROM students;