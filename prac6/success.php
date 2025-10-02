<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .message-container {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        .user-info {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
        .back-link {
            margin-top: 20px;
        }
        .back-link a {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-link a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <div class="success-icon">✅</div>
        <h2>Registration Successful!</h2>
        <p>Thank you for registering. Your account has been created successfully.</p>
        
        <div class="user-info">
            <h3>Registration Details:</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <?php if (!empty($phone)): ?>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
            <?php endif; ?>
            <p><strong>Registration Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <div class="back-link">
            <a href="index.html">Register Another User</a>
            <a href="view_registrations.php" style="margin-left: 10px; background-color: #007bff;">View All Registrations</a>
        </div>
    </div>
</body>
</html>