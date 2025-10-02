<?php
// Key Question 1: Using POST method correctly
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Key Question 3: Input sanitization
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    // Sanitize all inputs
    $firstName = sanitizeInput($_POST["firstName"]);
    $lastName = sanitizeInput($_POST["lastName"]);
    $email = sanitizeInput($_POST["email"]);
    $phone = sanitizeInput($_POST["phone"]);
    $password = sanitizeInput($_POST["password"]);
    $confirmPassword = sanitizeInput($_POST["confirmPassword"]);
    
    // Validation
    $errors = [];
    
    // Validate required fields
    if (empty($firstName)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastName)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    if (file_exists('registrations.txt')) {
        $existingData = file_get_contents('registrations.txt');
        if (strpos($existingData, $email) !== false) {
            $errors[] = "Email already registered";
        }
    }
    
    if (empty($errors)) {
        // Key Question 2: Store data in PHP file
        $registrationData = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT), // Hash password for security
            'registrationDate' => date('Y-m-d H:i:s'),
            'id' => uniqid()
        ];
        
        // Convert to JSON and append to file
        $jsonData = json_encode($registrationData) . "\n";
        
        if (file_put_contents('registrations.txt', $jsonData, FILE_APPEND | LOCK_EX)) {
            // Success response page
            include 'success.php';
        } else {
            // Error response page
            include 'error.php';
        }
    } else {
        // Display errors
        include 'error.php';
    }
    
} else {
    // Redirect if not POST request
    header("Location: index.html");
    exit();
}
?>