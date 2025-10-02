<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for storing messages
session_start();

// Check if form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Initialize variables
$errors = [];
$data = [];
$confirmation_message = '';

// Sanitize and validate form data
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    // Remove all non-digit characters for validation
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($cleanPhone) >= 10;
}

// Process form fields
$fields = [
    'full_name' => ['required' => true, 'label' => 'Full Name'],
    'first_name' => ['required' => true, 'label' => 'First Name'],
    'last_name' => ['required' => true, 'label' => 'Last Name'],
    'email' => ['required' => true, 'label' => 'Email Address'],
    'phone' => ['required' => false, 'label' => 'Phone Number'],
    'age' => ['required' => false, 'label' => 'Age'],
    'gender' => ['required' => false, 'label' => 'Gender'],
    'occupation' => ['required' => false, 'label' => 'Occupation'],
    'city' => ['required' => false, 'label' => 'City'],
    'country' => ['required' => false, 'label' => 'Country'],
    'website' => ['required' => false, 'label' => 'Website'],
    'interests' => ['required' => false, 'label' => 'Interests'],
    'comments' => ['required' => false, 'label' => 'Comments']
];

// Validate and sanitize each field
foreach ($fields as $fieldName => $fieldConfig) {
    $value = isset($_POST[$fieldName]) ? sanitizeInput($_POST[$fieldName]) : '';
    
    // Check required fields
    if ($fieldConfig['required'] && empty($value)) {
        $errors[] = $fieldConfig['label'] . ' is required.';
    }
    
    // Additional validation based on field type
    switch ($fieldName) {
        case 'email':
            if (!empty($value) && !validateEmail($value)) {
                $errors[] = 'Please enter a valid email address.';
            }
            break;
        case 'phone':
            if (!empty($value) && !validatePhone($value)) {
                $errors[] = 'Please enter a valid phone number.';
            }
            break;
        case 'age':
            if (!empty($value) && (!is_numeric($value) || $value < 1 || $value > 120)) {
                $errors[] = 'Please enter a valid age between 1 and 120.';
            }
            break;
        case 'website':
            if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[] = 'Please enter a valid website URL.';
            }
            break;
    }
    
    $data[$fieldName] = $value;
}

// If no errors, process the data
if (empty($errors)) {
    try {
        // Create data directory if it doesn't exist
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) {
            if (!mkdir($dataDir, 0755, true)) {
                throw new Exception("Could not create data directory: " . $dataDir);
            }
        }
        
        // Verify directory is writable
        if (!is_writable($dataDir)) {
            throw new Exception("Data directory is not writable: " . $dataDir);
        }
        
        // Prepare data for storage
        $timestamp = date('Y-m-d H:i:s');
        $submissionId = uniqid('sub_');
        
        // Create formatted data string for text file
        $dataString = "=== FORM SUBMISSION ===\n";
        $dataString .= "Submission ID: {$submissionId}\n";
        $dataString .= "Timestamp: {$timestamp}\n";
        $dataString .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
        $dataString .= "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
        $dataString .= str_repeat('-', 50) . "\n";
        
        foreach ($fields as $fieldName => $fieldConfig) {
            $value = !empty($data[$fieldName]) ? $data[$fieldName] : 'Not provided';
            $dataString .= "{$fieldConfig['label']}: {$value}\n";
        }
        
        $dataString .= str_repeat('=', 50) . "\n\n";
        
        // Save to text file (append mode)
        $filename = $dataDir . '/form_submissions.txt';
        $bytesWritten = file_put_contents($filename, $dataString, FILE_APPEND | LOCK_EX);
        
        if ($bytesWritten !== false) {
            error_log("SUCCESS: Wrote $bytesWritten bytes to $filename");
            
            // Also save as CSV for supplementary problem
            $csvFilename = $dataDir . '/form_submissions.csv';
            $csvData = [];
            
            // Create CSV header if file doesn't exist
            if (!file_exists($csvFilename)) {
                $csvHeader = ['Submission_ID', 'Timestamp', 'IP_Address'];
                foreach ($fields as $fieldName => $fieldConfig) {
                    $csvHeader[] = str_replace(' ', '_', $fieldConfig['label']);
                }
                
                $csvFile = fopen($csvFilename, 'w');
                fputcsv($csvFile, $csvHeader);
                fclose($csvFile);
            }
            
            // Append CSV data
            $csvRow = [$submissionId, $timestamp, $_SERVER['REMOTE_ADDR']];
            foreach ($fields as $fieldName => $fieldConfig) {
                $csvRow[] = $data[$fieldName];
            }
            
            $csvFile = fopen($csvFilename, 'a');
            fputcsv($csvFile, $csvRow);
            fclose($csvFile);
            
            // Save individual submission as JSON for demo trace
            $jsonData = [
                'submission_id' => $submissionId,
                'timestamp' => $timestamp,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'form_data' => $data,
                'validation_status' => 'SUCCESS',
                'errors' => []
            ];
            
            $jsonFilename = $dataDir . "/submission_{$submissionId}.json";
            file_put_contents($jsonFilename, json_encode($jsonData, JSON_PRETTY_PRINT));
            
            error_log("SUCCESS: All files written successfully for submission $submissionId");
            
            $confirmation_message = "Thank you! Your form has been successfully submitted.";
            $_SESSION['submission_success'] = true;
            $_SESSION['submission_id'] = $submissionId;
            $_SESSION['confirmation_message'] = $confirmation_message;
            
        } else {
            error_log("ERROR: Failed to write to file: $filename");
            $errors[] = "Error: Could not save form data. Please try again.";
        }
        
    } catch (Exception $e) {
        $errors[] = "An error occurred while processing your form: " . $e->getMessage();
        
        // Log error for debugging
        error_log("Form processing error: " . $e->getMessage());
    }
}

// If there are errors, store them in session and redirect back
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $data; // Preserve form data
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submitted Successfully - Practice 9</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #001BB7 0%, #0046FF 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #FF8040 0%, #0046FF 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: bounce 1s ease-in-out;
        }

        .success-icon i {
            font-size: 3rem;
            color: white;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        h1 {
            color: #001BB7;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .confirmation-message {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .submission-details {
            background: #E9E9E9;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: left;
        }

        .submission-details h3 {
            color: #001BB7;
            margin-bottom: 1rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
        }

        .detail-value {
            color: #666;
        }

        .btn {
            background: linear-gradient(135deg, #FF8040 0%, #0046FF 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 128, 64, 0.3);
        }

        .btn-secondary {
            background: #E9E9E9;
            color: #001BB7;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Form Submitted Successfully!</h1>
        
        <div class="confirmation-message">
            <?php echo htmlspecialchars($confirmation_message); ?>
            <br><br>
            Your submission has been recorded and stored securely. You can view all submissions or download the data using the links below.
        </div>

        <?php if (isset($_SESSION['submission_id'])): ?>
        <div class="submission-details">
            <h3><i class="fas fa-info-circle"></i> Submission Details</h3>
            <div class="detail-row">
                <span class="detail-label">Submission ID:</span>
                <span class="detail-value"><?php echo htmlspecialchars($_SESSION['submission_id']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span class="detail-value"><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value" style="color: #28a745; font-weight: 600;">Successfully Processed</span>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem;">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-plus"></i> Submit Another Form
            </a>
            <a href="view_data.php" class="btn">
                <i class="fas fa-eye"></i> View All Submissions
            </a>
            <a href="download_csv.php" class="btn">
                <i class="fas fa-download"></i> Download CSV
            </a>
            <a href="demo_trace.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Submission Trace
            </a>
        </div>
    </div>
</body>
</html>

<?php
// Clear session messages after displaying
unset($_SESSION['submission_success']);
unset($_SESSION['confirmation_message']);
?>