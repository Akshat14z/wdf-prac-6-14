<?php
// Start session
session_start();

// Function to read and parse form submissions
function getFormSubmissions() {
    $dataFile = __DIR__ . '/data/form_submissions.txt';
    $submissions = [];
    
    if (!file_exists($dataFile)) {
        return $submissions;
    }
    
    $content = file_get_contents($dataFile);
    $submissionBlocks = explode('=== FORM SUBMISSION ===', $content);
    
    foreach ($submissionBlocks as $block) {
        if (trim($block) === '') continue;
        
        $lines = explode("\n", trim($block));
        $submission = [];
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $submission[trim($key)] = trim($value);
            }
        }
        
        if (!empty($submission)) {
            $submissions[] = $submission;
        }
    }
    
    return array_reverse($submissions); // Show newest first
}

$submissions = getFormSubmissions();
$totalSubmissions = count($submissions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Form Submissions - Practice 9</title>
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
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #001BB7;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .stats-bar {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #333;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #FF8040;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            background: linear-gradient(135deg, #FF8040 0%, #0046FF 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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

        .submissions-grid {
            display: grid;
            gap: 1.5rem;
        }

        .submission-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .submission-card:hover {
            transform: translateY(-5px);
        }

        .submission-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E9E9E9;
        }

        .submission-id {
            font-size: 1.2rem;
            font-weight: 700;
            color: #001BB7;
        }

        .submission-timestamp {
            color: #666;
            font-size: 0.9rem;
        }

        .submission-data {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .data-field {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .field-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }

        .field-value {
            color: #666;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid #FF8040;
        }

        .field-value.empty {
            color: #999;
            font-style: italic;
        }

        .no-submissions {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .no-submissions i {
            font-size: 4rem;
            color: #E9E9E9;
            margin-bottom: 1rem;
        }

        .no-submissions h3 {
            color: #001BB7;
            margin-bottom: 1rem;
        }

        .no-submissions p {
            color: #666;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .stats-bar {
                flex-direction: column;
                text-align: center;
            }

            .action-buttons {
                justify-content: center;
            }

            .submission-header {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .submission-data {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-database"></i> Form Submissions</h1>
            <p>View all submitted form data from Practice 9</p>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <i class="fas fa-chart-bar" style="color: #FF8040;"></i>
                <span>Total Submissions: <span class="stat-number"><?php echo $totalSubmissions; ?></span></span>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> New Submission
                </a>
                <a href="download_csv.php" class="btn">
                    <i class="fas fa-download"></i> Download CSV
                </a>
                <a href="demo_trace.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View Trace
                </a>
            </div>
        </div>

        <div class="submissions-grid">
            <?php if (empty($submissions)): ?>
                <div class="no-submissions">
                    <i class="fas fa-inbox"></i>
                    <h3>No Submissions Found</h3>
                    <p>No form submissions have been recorded yet. Submit your first form to see data here.</p>
                    <a href="index.php" class="btn">
                        <i class="fas fa-plus"></i> Submit First Form
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($submissions as $index => $submission): ?>
                    <div class="submission-card">
                        <div class="submission-header">
                            <div>
                                <div class="submission-id">
                                    <?php echo isset($submission['Submission ID']) ? htmlspecialchars($submission['Submission ID']) : 'Submission #' . ($index + 1); ?>
                                </div>
                                <div class="submission-timestamp">
                                    <i class="fas fa-clock"></i> 
                                    <?php echo isset($submission['Timestamp']) ? htmlspecialchars($submission['Timestamp']) : 'Unknown time'; ?>
                                </div>
                            </div>
                            <?php if (isset($submission['IP Address'])): ?>
                                <div style="font-size: 0.8rem; color: #999;">
                                    <i class="fas fa-globe"></i> <?php echo htmlspecialchars($submission['IP Address']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="submission-data">
                            <?php
                            $displayFields = [
                                'Full Name', 'First Name', 'Last Name', 'Email Address',
                                'Phone Number', 'Age', 'Gender', 'Occupation',
                                'City', 'Country', 'Website', 'Interests', 'Comments'
                            ];

                            foreach ($displayFields as $field):
                                if (isset($submission[$field])):
                            ?>
                                <div class="data-field">
                                    <div class="field-label"><?php echo htmlspecialchars($field); ?></div>
                                    <div class="field-value <?php echo (empty($submission[$field]) || $submission[$field] === 'Not provided') ? 'empty' : ''; ?>">
                                        <?php 
                                        $value = $submission[$field];
                                        if (empty($value) || $value === 'Not provided') {
                                            echo 'Not provided';
                                        } else {
                                            echo htmlspecialchars($value);
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>