<?php
// Debug script to test login functionality
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Login Debug Information</h2>";

// Test database connection
echo "<h3>1. Database Connection Test:</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test User class
echo "<h3>2. User Class Test:</h3>";
try {
    require_once 'classes/User.php';
    $user = new User($db);
    echo "✅ User class instantiated successfully<br>";
} catch (Exception $e) {
    echo "❌ User class failed: " . $e->getMessage() . "<br>";
}

// Test session functionality
echo "<h3>3. Session Test:</h3>";
try {
    require_once 'classes/SessionManager.php';
    
    // Check if we're running on HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    echo "HTTPS Status: " . ($isHttps ? "✅ HTTPS" : "❌ HTTP") . "<br>";
    
    // Test session start
    SessionManager::startSecureSession();
    echo "✅ Session started successfully<br>";
    echo "Session ID: " . session_id() . "<br>";
    
} catch (Exception $e) {
    echo "❌ Session failed: " . $e->getMessage() . "<br>";
}

// Test user authentication with a sample (if provided)
echo "<h3>4. Authentication Test:</h3>";
if (isset($_POST['test_username']) && isset($_POST['test_password'])) {
    $test_user = $user->authenticate($_POST['test_username'], $_POST['test_password']);
    if ($test_user) {
        echo "✅ Authentication successful for user: " . htmlspecialchars($test_user['username']) . "<br>";
        
        // Test session variables
        $_SESSION['user_id'] = $test_user['id'];
        $_SESSION['username'] = $test_user['username'];
        echo "✅ Session variables set<br>";
        
        // Test redirect (but don't actually redirect for debugging)
        echo "✅ Would redirect to dashboard.php<br>";
        echo '<a href="dashboard.php">Go to Dashboard</a><br>';
        
    } else {
        echo "❌ Authentication failed<br>";
    }
} else {
    echo "No test credentials provided. Use the form below to test.<br>";
}

// List all users in database for debugging
echo "<h3>5. Users in Database:</h3>";
try {
    $query = "SELECT id, username, email, created_at FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($u['id']) . "</td>";
            echo "<td>" . htmlspecialchars($u['username']) . "</td>";
            echo "<td>" . htmlspecialchars($u['email']) . "</td>";
            echo "<td>" . htmlspecialchars($u['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No users found in database.";
    }
} catch (Exception $e) {
    echo "❌ Failed to list users: " . $e->getMessage();
}

?>

<h3>Test Authentication</h3>
<form method="POST">
    <label>Username: <input type="text" name="test_username" required></label><br><br>
    <label>Password: <input type="password" name="test_password" required></label><br><br>
    <button type="submit">Test Login</button>
</form>

<hr>
<a href="login.php">Back to Login</a> | <a href="register.php">Register New User</a>