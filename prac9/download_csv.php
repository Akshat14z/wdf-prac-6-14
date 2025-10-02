<?php
// CSV Download Script for Practice 9
session_start();

$csvFile = __DIR__ . '/data/form_submissions.csv';

// Check if CSV file exists
if (!file_exists($csvFile)) {
    // If no CSV file exists, create a sample one or redirect
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>No Data Available - Practice 9</title>
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
                max-width: 500px;
                text-align: center;
            }

            .error-icon {
                width: 80px;
                height: 80px;
                background: #E9E9E9;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 2rem;
            }

            .error-icon i {
                font-size: 2.5rem;
                color: #001BB7;
            }

            h1 {
                color: #001BB7;
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            p {
                color: #666;
                font-size: 1.1rem;
                margin-bottom: 2rem;
                line-height: 1.6;
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
            <div class="error-icon">
                <i class="fas fa-file-excel"></i>
            </div>
            
            <h1>No CSV Data Available</h1>
            
            <p>
                There are no form submissions to download yet. Please submit at least one form to generate CSV data.
            </p>

            <div style="margin-top: 2rem;">
                <a href="index.php" class="btn">
                    <i class="fas fa-plus"></i> Submit Form
                </a>
                <a href="view_data.php" class="btn-secondary">
                    <i class="fas fa-eye"></i> View Data
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Get file info
$fileSize = filesize($csvFile);
$fileName = 'form_submissions_' . date('Y-m-d_H-i-s') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Output the CSV file
readfile($csvFile);
exit();
?>