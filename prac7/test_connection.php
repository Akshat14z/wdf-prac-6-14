<?php
// Test database connection and setup
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Testing Database Connection</h2>";

// First, test basic MySQL connection
try {
    $host = "localhost";
    $username = "root";
    $password = "";
    $port = "3306";
    
    // Connect without database to create it if needed
    $dsn = "mysql:host=$host;port=$port";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ MySQL connection successful!</p>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS login_system");
    echo "<p style='color: green;'>✓ Database 'login_system' created/verified!</p>";
    
    // Now test connection to the specific database
    $dsn = "mysql:host=$host;port=$port;dbname=login_system";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Connection to 'login_system' database successful!</p>";
    
    // Now test the Database class
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database class connection successful!</p>";
        
        // Check if tables exist
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tables)) {
            echo "<p style='color: orange;'>⚠ No tables found. You may need to run the setup.sql script.</p>";
            echo "<p>Run this in phpMyAdmin or MySQL command line:</p>";
            echo "<pre>" . htmlspecialchars(file_get_contents('database/setup.sql')) . "</pre>";
        } else {
            echo "<p style='color: green;'>✓ Tables found: " . implode(', ', $tables) . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>XAMPP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>Port 3306 is not blocked</li>";
    echo "</ul>";
}
?>