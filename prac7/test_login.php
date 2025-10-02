<?php
// Enhanced debug script to test login step by step
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Login Debug - Step by Step Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Request Received</h3>";
    echo "Username: " . htmlspecialchars($_POST['username']) . "<br>";
    echo "Password: " . (isset($_POST['password']) ? '[PROVIDED]' : '[MISSING]') . "<br><br>";
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Step 1: Database connection
    echo "<h3>Step 1: Database Connection</h3>";
    try {
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        echo "✅ Database connected successfully<br><br>";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "<br><br>";
        exit;
    }
    
    // Step 2: User authentication
    echo "<h3>Step 2: User Authentication</h3>";
    try {
        require_once 'classes/User.php';
        $user = new User($db);
        $authenticated_user = $user->authenticate($username, $password);
        
        if ($authenticated_user) {
            echo "✅ Authentication successful<br>";
            echo "User ID: " . $authenticated_user['id'] . "<br>";
            echo "Username: " . $authenticated_user['username'] . "<br><br>";
        } else {
            echo "❌ Authentication failed<br>";
            echo "Please check your username and password<br><br>";
            exit;
        }
    } catch (Exception $e) {
        echo "❌ Authentication error: " . $e->getMessage() . "<br><br>";
        exit;
    }
    
    // Step 3: Session handling
    echo "<h3>Step 3: Session Handling</h3>";
    try {
        require_once 'classes/SessionManager.php';
        
        // Check HTTPS status
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        echo "HTTPS Status: " . ($isHttps ? "✅ HTTPS" : "⚠️ HTTP (local development)") . "<br>";
        
        SessionManager::startSecureSession();
        echo "✅ Session started successfully<br>";
        echo "Session ID: " . session_id() . "<br>";
        
        // Set session variables
        $_SESSION['user_id'] = $authenticated_user['id'];
        $_SESSION['username'] = $authenticated_user['username'];
        echo "✅ Session variables set<br>";
        echo "Session user_id: " . $_SESSION['user_id'] . "<br>";
        echo "Session username: " . $_SESSION['username'] . "<br><br>";
        
    } catch (Exception $e) {
        echo "❌ Session error: " . $e->getMessage() . "<br><br>";
        exit;
    }
    
    // Step 4: Test redirect capability
    echo "<h3>Step 4: Redirect Test</h3>";
    if (headers_sent($file, $line)) {
        echo "❌ Headers already sent in $file on line $line<br>";
        echo "Cannot redirect automatically. <a href='dashboard.php'>Click here to go to dashboard</a><br>";
    } else {
        echo "✅ Headers not sent yet - redirect should work<br>";
        echo "Redirecting to dashboard in 3 seconds...<br>";
        echo "<script>setTimeout(function(){ window.location.href = 'dashboard.php'; }, 3000);</script>";
        echo "<a href='dashboard.php'>Or click here to go now</a><br>";
    }
    
} else {
    echo "<p>Use the form below to test login credentials:</p>";
}

// List available users for testing
echo "<h3>Available Test Users</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT username, email FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th style='padding: 8px;'>Username</th><th style='padding: 8px;'>Email</th><th style='padding: 8px;'>Test Password</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($u['username']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($u['email']) . "</td>";
            echo "<td style='padding: 8px;'>password</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><em>Note: All test users should have the password 'password'</em></p>";
    }
} catch (Exception $e) {
    echo "Error loading users: " . $e->getMessage();
}
?>

<h3>Test Login Form</h3>
<form method="POST" style="border: 1px solid #ccc; padding: 20px; max-width: 400px;">
    <div style="margin-bottom: 15px;">
        <label>Username:</label><br>
        <input type="text" name="username" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Password:</label><br>
        <input type="password" name="password" required style="width: 100%; padding: 8px;">
    </div>
    
    <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;">Test Login</button>
</form>

<hr>
<p><a href="login.php">Back to Regular Login</a> | <a href="dashboard.php">Go to Dashboard</a></p>