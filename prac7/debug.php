<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP Debug Test</h1>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Time: " . date('Y-m-d H:i:s') . "<br>";

// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=login_system", "root", "");
    echo "<span style='color: green;'>✅ Database connection successful!</span><br>";
} catch(PDOException $e) {
    echo "<span style='color: red;'>❌ Database error: " . $e->getMessage() . "</span><br>";
}

echo "<br><a href='index.html'>Try Index.html</a> | <a href='login.php'>Try Login.php</a>";
?>