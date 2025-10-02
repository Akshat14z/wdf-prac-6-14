<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .message-container {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
        .error-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        .error-list {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
        .error-list ul {
            margin: 0;
            padding-left: 20px;
        }
        .back-link {
            margin-top: 20px;
        }
        .back-link a {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-link a:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <div class="error-icon">❌</div>
        <h2>Registration Failed!</h2>
        <p>There were errors with your registration. Please check the details below and try again.</p>
        
        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <h3>Errors:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.html">Try Again</a>
        </div>
    </div>
</body>
</html>