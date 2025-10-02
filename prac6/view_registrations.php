<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrations - User Registration System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .stats {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .back-link a:hover {
            background-color: #0056b3;
        }
        .id-column {
            font-family: monospace;
            font-size: 12px;
            max-width: 80px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .date-column {
            white-space: nowrap;
        }
        .actions {
            margin-bottom: 20px;
            text-align: right;
        }
        .refresh-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .refresh-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Registrations</h1>
        
        <?php
        $registrations = [];
        $totalUsers = 0;
        
        // Read and parse registration data
        if (file_exists('registrations.txt') && filesize('registrations.txt') > 0) {
            $lines = file('registrations.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if ($data !== null) {
                    $registrations[] = $data;
                    $totalUsers++;
                }
            }
        }
        ?>
        
        <div class="actions">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="refresh-btn">🔄 Refresh</a>
        </div>
        
        <div class="stats">
            <strong>Total Registered Users: <?php echo $totalUsers; ?></strong>
        </div>
        
        <?php if (empty($registrations)): ?>
            <div class="no-data">
                <h3>No registrations found</h3>
                <p>No users have registered yet. Be the first to register!</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Sort registrations by date (newest first)
                    usort($registrations, function($a, $b) {
                        return strtotime($b['registrationDate']) - strtotime($a['registrationDate']);
                    });
                    
                    foreach ($registrations as $user): ?>
                        <tr>
                            <td class="id-column" title="<?php echo htmlspecialchars($user['id']); ?>">
                                <?php echo htmlspecialchars(substr($user['id'], 0, 8)); ?>...
                            </td>
                            <td><?php echo htmlspecialchars($user['firstName']); ?></td>
                            <td><?php echo htmlspecialchars($user['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<em>Not provided</em>'; ?></td>
                            <td class="date-column"><?php echo date('M j, Y g:i A', strtotime($user['registrationDate'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.html">← Back to Registration Form</a>
        </div>
    </div>
</body>
</html>
