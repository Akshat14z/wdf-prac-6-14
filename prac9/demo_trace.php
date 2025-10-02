<?php
// Form Submission Trace Demo for Practice 9
session_start();

// Function to get JSON submission files for detailed trace
function getSubmissionTrace() {
    $dataDir = __DIR__ . '/data';
    $traceData = [];
    
    if (!is_dir($dataDir)) {
        return $traceData;
    }
    
    // Get all JSON files (individual submissions)
    $jsonFiles = glob($dataDir . '/submission_*.json');
    
    foreach ($jsonFiles as $file) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        
        if ($data) {
            $traceData[] = $data;
        }
    }
    
    // Sort by timestamp (newest first)
    usort($traceData, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return $traceData;
}

$traceData = getSubmissionTrace();
$totalSubmissions = count($traceData);

// Also check if regular text file exists for additional info
$textFile = __DIR__ . '/data/form_submissions.txt';
$textFileExists = file_exists($textFile);
$textFileSize = $textFileExists ? filesize($textFile) : 0;

$csvFile = __DIR__ . '/data/form_submissions.csv';
$csvFileExists = file_exists($csvFile);
$csvFileSize = $csvFileExists ? filesize($csvFile) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submission Trace - Practice 9</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FF8040 0%, #0046FF 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #001BB7;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        .trace-timeline {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E9E9E9;
        }

        .timeline-title {
            font-size: 1.8rem;
            color: #001BB7;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            background: linear-gradient(135deg, #FF8040 0%, #0046FF 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
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
            box-shadow: 0 6px 20px rgba(255, 128, 64, 0.3);
        }

        .btn-secondary {
            background: #E9E9E9;
            color: #001BB7;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .timeline-item {
            display: flex;
            margin-bottom: 2rem;
            position: relative;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 20px;
            top: 50px;
            width: 2px;
            height: calc(100% + 1rem);
            background: #E9E9E9;
        }

        .timeline-marker {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #FF8040 0%, #0046FF 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .timeline-marker i {
            color: white;
            font-size: 1rem;
        }

        .timeline-content {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            flex: 1;
            border-left: 4px solid #FF8040;
        }

        .timeline-header-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .submission-id {
            font-size: 1.2rem;
            font-weight: 700;
            color: #001BB7;
        }

        .submission-time {
            color: #666;
            font-size: 0.9rem;
        }

        .submission-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
            font-size: 0.85rem;
        }

        .detail-value {
            color: #666;
            font-size: 0.9rem;
        }

        .status-badge {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-data i {
            font-size: 4rem;
            color: #E9E9E9;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .timeline-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .submission-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-list-alt"></i> Form Submission Trace</h1>
            <p>Detailed trace of all form submissions and processing status</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-number"><?php echo $totalSubmissions; ?></div>
                <div class="stat-label">Total Submissions</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-number"><?php echo $textFileExists ? number_format($textFileSize / 1024, 1) : '0'; ?> KB</div>
                <div class="stat-label">Text File Size</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-csv"></i>
                </div>
                <div class="stat-number"><?php echo $csvFileExists ? number_format($csvFileSize / 1024, 1) : '0'; ?> KB</div>
                <div class="stat-label">CSV File Size</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $totalSubmissions; ?></div>
                <div class="stat-label">Successful Processes</div>
            </div>
        </div>

        <div class="trace-timeline">
            <div class="timeline-header">
                <div class="timeline-title">
                    <i class="fas fa-history"></i> Submission Timeline
                </div>
                <div class="action-buttons">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> New Form
                    </a>
                    <a href="view_data.php" class="btn">
                        <i class="fas fa-eye"></i> View Data
                    </a>
                </div>
            </div>

            <?php if (empty($traceData)): ?>
                <div class="no-data">
                    <i class="fas fa-clock"></i>
                    <h3>No Submission Trace Available</h3>
                    <p>Submit forms to see detailed processing trace information here.</p>
                    <div class="action-buttons">
                        <a href="index.php" class="btn">
                            <i class="fas fa-plus"></i> Submit Your First Form
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($traceData as $index => $trace): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header-info">
                                <div>
                                    <div class="submission-id"><?php echo htmlspecialchars($trace['submission_id']); ?></div>
                                    <div class="submission-time">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo htmlspecialchars($trace['timestamp']); ?>
                                    </div>
                                </div>
                                <div class="status-badge"><?php echo htmlspecialchars($trace['validation_status']); ?></div>
                            </div>

                            <div class="submission-details">
                                <div class="detail-item">
                                    <div class="detail-label">IP Address</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($trace['ip_address']); ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Full Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($trace['form_data']['full_name'] ?? 'Not provided'); ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($trace['form_data']['email'] ?? 'Not provided'); ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Processing Method</div>
                                    <div class="detail-value">POST Request</div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Data Validation</div>
                                    <div class="detail-value">
                                        <?php echo empty($trace['errors']) ? 'Passed ✓' : 'Failed ✗'; ?>
                                    </div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Storage Status</div>
                                    <div class="detail-value">Text File + CSV ✓</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn">
                <i class="fas fa-plus"></i> Submit New Form
            </a>
            <a href="view_data.php" class="btn-secondary">
                <i class="fas fa-database"></i> View All Data
            </a>
            <a href="download_csv.php" class="btn">
                <i class="fas fa-download"></i> Download CSV
            </a>
        </div>
    </div>
</body>
</html>