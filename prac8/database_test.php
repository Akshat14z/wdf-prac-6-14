<?php
require_once 'config.php';
require_once 'EventManager.php';

// Test database connection and operations
$tests = [];
$overall_status = true;

// Test 1: Database Connection
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $tests['connection'] = ['status' => 'success', 'message' => 'Database connection established successfully'];
    } else {
        $tests['connection'] = ['status' => 'error', 'message' => 'Failed to establish database connection'];
        $overall_status = false;
    }
} catch (Exception $e) {
    $tests['connection'] = ['status' => 'error', 'message' => 'Connection error: ' . $e->getMessage()];
    $overall_status = false;
}

// Test 2: Table Existence
if ($db) {
    try {
        $tables = ['users', 'events', 'event_registrations'];
        $existing_tables = [];
        
        foreach ($tables as $table) {
            $stmt = $db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() > 0) {
                $existing_tables[] = $table;
            }
        }
        
        if (count($existing_tables) === count($tables)) {
            $tests['tables'] = ['status' => 'success', 'message' => 'All required tables exist: ' . implode(', ', $existing_tables)];
        } else {
            $missing = array_diff($tables, $existing_tables);
            $tests['tables'] = ['status' => 'warning', 'message' => 'Missing tables: ' . implode(', ', $missing)];
        }
    } catch (Exception $e) {
        $tests['tables'] = ['status' => 'error', 'message' => 'Error checking tables: ' . $e->getMessage()];
        $overall_status = false;
    }
}

// Test 3: CRUD Operations
if ($db) {
    try {
        $eventManager = new EventManager($db);
        
        // Test SELECT (Read)
        $events = $eventManager->getAllEvents(1, 5);
        if ($events !== false) {
            $tests['select'] = ['status' => 'success', 'message' => 'SELECT operation working. Found ' . count($events) . ' events'];
        } else {
            $tests['select'] = ['status' => 'error', 'message' => 'SELECT operation failed'];
            $overall_status = false;
        }
        
        // Test INSERT (Create)
        $test_event_data = [
            'title' => 'Test Event ' . date('Y-m-d H:i:s'),
            'description' => 'This is a test event created during database testing',
            'event_date' => date('Y-m-d', strtotime('+1 week')),
            'event_time' => '14:00:00',
            'location' => 'Test Location',
            'organizer' => 'Test Organizer',
            'capacity' => 25,
            'created_by' => 1
        ];
        
        $new_event_id = $eventManager->createEvent($test_event_data);
        if ($new_event_id) {
            $tests['insert'] = ['status' => 'success', 'message' => 'INSERT operation working. Created event ID: ' . $new_event_id];
            
            // Test UPDATE
            $update_data = $test_event_data;
            $update_data['title'] = 'Updated Test Event';
            $update_data['status'] = 'active';
            
            $update_result = $eventManager->updateEvent($new_event_id, $update_data);
            if ($update_result) {
                $tests['update'] = ['status' => 'success', 'message' => 'UPDATE operation working. Updated event ID: ' . $new_event_id];
            } else {
                $tests['update'] = ['status' => 'error', 'message' => 'UPDATE operation failed'];
                $overall_status = false;
            }
            
            // Test DELETE
            $delete_result = $eventManager->deleteEvent($new_event_id);
            if ($delete_result) {
                $tests['delete'] = ['status' => 'success', 'message' => 'DELETE operation working. Deleted test event'];
            } else {
                $tests['delete'] = ['status' => 'error', 'message' => 'DELETE operation failed'];
                $overall_status = false;
            }
        } else {
            $tests['insert'] = ['status' => 'error', 'message' => 'INSERT operation failed'];
            $overall_status = false;
        }
        
    } catch (Exception $e) {
        $tests['crud'] = ['status' => 'error', 'message' => 'CRUD operations error: ' . $e->getMessage()];
        $overall_status = false;
    }
}

// Test 4: Statistics and Advanced Queries
if ($db) {
    try {
        $eventManager = new EventManager($db);
        $stats = $eventManager->getEventStats();
        
        if ($stats !== false) {
            $tests['stats'] = ['status' => 'success', 'message' => 'Statistics queries working. Total events: ' . $stats['total_events']];
        } else {
            $tests['stats'] = ['status' => 'error', 'message' => 'Statistics queries failed'];
            $overall_status = false;
        }
    } catch (Exception $e) {
        $tests['stats'] = ['status' => 'error', 'message' => 'Statistics error: ' . $e->getMessage()];
        $overall_status = false;
    }
}

// Test 5: Error Handling
try {
    // Intentionally cause an error to test error handling
    $stmt = $db->prepare("SELECT * FROM non_existent_table");
    $stmt->execute();
    $tests['error_handling'] = ['status' => 'warning', 'message' => 'Error handling may not be working properly'];
} catch (Exception $e) {
    $tests['error_handling'] = ['status' => 'success', 'message' => 'Error handling working properly: ' . substr($e->getMessage(), 0, 50) . '...'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Portal - Database Test</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .overall-status {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
        }

        .overall-message {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .test-results {
            display: grid;
            gap: 1.5rem;
        }

        .test-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
        }

        .test-success {
            border-left-color: #28a745;
        }

        .test-error {
            border-left-color: #dc3545;
        }

        .test-warning {
            border-left-color: #ffc107;
        }

        .test-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .test-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .test-icon.success {
            background: #d4edda;
            color: #155724;
        }

        .test-icon.error {
            background: #f8d7da;
            color: #721c24;
        }

        .test-icon.warning {
            background: #fff3cd;
            color: #856404;
        }

        .test-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .test-message {
            color: #666;
            line-height: 1.6;
        }

        .database-info {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .info-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .info-value {
            color: #667eea;
            font-weight: 500;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            cursor: pointer;
            margin: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .actions {
            text-align: center;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-database"></i> Database Connection Test</h1>
            <p>Testing MySQL connection and CRUD operations</p>
        </div>

        <!-- Overall Status -->
        <div class="overall-status">
            <div class="status-icon <?php echo $overall_status ? 'status-success' : 'status-error'; ?>">
                <i class="fas <?php echo $overall_status ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            </div>
            <div class="overall-message <?php echo $overall_status ? 'text-success' : 'text-error'; ?>">
                <?php echo $overall_status ? 'Database Connection Successful!' : 'Database Connection Issues Detected'; ?>
            </div>
            <p><?php echo $overall_status ? 'All database operations are working correctly.' : 'Please check the issues below and fix them.'; ?></p>
        </div>

        <!-- Test Results -->
        <div class="test-results">
            <?php foreach ($tests as $test_name => $test_result): ?>
                <div class="test-card test-<?php echo $test_result['status']; ?>">
                    <div class="test-header">
                        <div class="test-icon <?php echo $test_result['status']; ?>">
                            <?php
                            $icons = [
                                'success' => 'fas fa-check',
                                'error' => 'fas fa-times',
                                'warning' => 'fas fa-exclamation-triangle'
                            ];
                            echo '<i class="' . $icons[$test_result['status']] . '"></i>';
                            ?>
                        </div>
                        <div>
                            <div class="test-title">
                                <?php
                                $titles = [
                                    'connection' => 'Database Connection',
                                    'tables' => 'Table Structure',
                                    'select' => 'SELECT Operations',
                                    'insert' => 'INSERT Operations',
                                    'update' => 'UPDATE Operations',
                                    'delete' => 'DELETE Operations',
                                    'stats' => 'Advanced Queries',
                                    'error_handling' => 'Error Handling'
                                ];
                                echo $titles[$test_name] ?? ucfirst($test_name);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="test-message"><?php echo $test_result['message']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Database Information -->
        <div class="database-info">
            <h3><i class="fas fa-info-circle"></i> Database Configuration</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Host</div>
                    <div class="info-value"><?php echo DB_HOST; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Port</div>
                    <div class="info-value"><?php echo DB_PORT; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Database</div>
                    <div class="info-value"><?php echo DB_NAME; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">User</div>
                    <div class="info-value"><?php echo DB_USER; ?></div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Dashboard
            </a>
            <a href="events.php" class="btn btn-primary">
                <i class="fas fa-list"></i> View Events
            </a>
            <a href="database_test.php" class="btn btn-primary">
                <i class="fas fa-redo"></i> Rerun Tests
            </a>
        </div>
    </div>
</body>
</html>