<?php
require_once 'config.php';

// Check if export is requested
if (!isset($_GET['format'])) {
    header("Location: students.php");
    exit();
}

$studentManager = new StudentManager($db);

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get all matching students (no limit for export)
$students = $studentManager->getStudents($search, $department, $status, 10000, 0);

if ($_GET['format'] === 'csv') {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer
    $output = fopen('php://output', 'w');
    
    // Add BOM for proper UTF-8 encoding in Excel
    fwrite($output, "\xEF\xBB\xBF");
    
    // CSV Headers
    $headers = [
        'Student ID',
        'First Name', 
        'Last Name',
        'Full Name',
        'Email',
        'Phone',
        'Date of Birth',
        'Gender',
        'Address',
        'City',
        'State',
        'Postal Code',
        'Country',
        'Department Code',
        'Department Name',
        'Course Code',
        'Course Name',
        'GPA',
        'Status',
        'Admission Date',
        'Graduation Year',
        'Account Created',
        'Last Updated'
    ];
    
    fputcsv($output, $headers);
    
    // CSV Data
    foreach ($students as $student) {
        $row = [
            $student['student_id'],
            $student['first_name'],
            $student['last_name'],
            $student['first_name'] . ' ' . $student['last_name'],
            $student['email'],
            $student['phone'] ?? '',
            $student['date_of_birth'] ?? '',
            $student['gender'] ?? '',
            $student['address'] ?? '',
            $student['city'] ?? '',
            $student['state'] ?? '',
            $student['postal_code'] ?? '',
            $student['country'] ?? '',
            $student['department_code'] ?? '',
            $student['department_name'] ?? '',
            $student['course_code'] ?? '',
            $student['course_name'] ?? '',
            number_format($student['gpa'], 2),
            $student['status'],
            $student['admission_date'],
            $student['graduation_year'] ?? '',
            $student['created_at'],
            $student['updated_at']
        ];
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();

} elseif ($_GET['format'] === 'json') {
    // Export as JSON
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d_H-i-s') . '.json"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $export_data = [
        'export_date' => date('Y-m-d H:i:s'),
        'total_records' => count($students),
        'filters' => [
            'search' => $search,
            'department' => $department,
            'status' => $status
        ],
        'students' => $students
    ];
    
    echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();

} else {
    // Invalid format
    header("Location: students.php?error=" . urlencode("Invalid export format"));
    exit();
}
?>