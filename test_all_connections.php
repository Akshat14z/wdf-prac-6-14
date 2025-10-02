<?php
/**
 * Database Connection Test for All Practice Folders
 * Run this script to verify all database configurations are working with XAMPP
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test for All Practice Folders</h1>";
echo "<p>Testing all practice folders to ensure database connections work with XAMPP...</p>";

$practices = [
    'prac7' => ['config/database.php', 'Database', 'login_system'],
    'prac8' => ['config.php', 'Database', 'event_portal'],
    'prac10' => ['config.php', null, 'prac10_login_system'], // Uses direct PDO
    'prac11' => ['config.php', 'Database', 'student_management'],
    'prac12' => ['config.php', 'Database', 'event_management_prac12'],
    'prac13' => ['config.php', null, 'prac13_secure_auth1'], // Uses function
    'prac14' => ['config.php', 'Database', 'prac14_admin_db'],
];

foreach ($practices as $folder => $config) {
    echo "<h2>Testing $folder</h2>";
    
    $configFile = "../$folder/" . $config[0];
    $className = $config[1];
    $dbName = $config[2];
    
    if (!file_exists($configFile)) {
        echo "<p style='color: red;'>❌ Config file not found: $configFile</p>";
        continue;
    }
    
    try {
        // Include the config file
        require_once $configFile;
        
        if ($className === 'Database') {
            // Test class-based connection
            $database = new Database();
            $conn = $database->getConnection();
            
            if ($conn) {
                echo "<p style='color: green;'>✅ $folder: Database class connection successful!</p>";
                
                // Test a simple query
                $stmt = $conn->query("SELECT DATABASE() as current_db");
                $result = $stmt->fetch();
                echo "<p style='color: blue;'>📍 Connected to database: " . $result['current_db'] . "</p>";
            } else {
                echo "<p style='color: red;'>❌ $folder: Database class connection failed!</p>";
            }
            
        } elseif ($folder === 'prac13') {
            // Test function-based connection
            $conn = getDBConnection();
            if ($conn) {
                echo "<p style='color: green;'>✅ $folder: Function-based connection successful!</p>";
                
                $stmt = $conn->query("SELECT DATABASE() as current_db");
                $result = $stmt->fetch();
                echo "<p style='color: blue;'>📍 Connected to database: " . $result['current_db'] . "</p>";
            }
            
        } elseif ($folder === 'prac10') {
            // Test direct PDO connection (using global $pdo)
            global $pdo;
            if (isset($pdo) && $pdo) {
                echo "<p style='color: green;'>✅ $folder: Direct PDO connection successful!</p>";
                
                $stmt = $pdo->query("SELECT DATABASE() as current_db");
                $result = $stmt->fetch();
                echo "<p style='color: blue;'>📍 Connected to database: " . $result['current_db'] . "</p>";
            } else {
                echo "<p style='color: red;'>❌ $folder: Direct PDO connection failed!</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ $folder: Error - " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

echo "<h2>Basic XAMPP MySQL Connection Test</h2>";
try {
    $host = "localhost";
    $port = "3306";
    $username = "root";
    $password = "";
    
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Basic XAMPP MySQL connection successful!</p>";
    echo "<p style='color: blue;'>📍 MySQL Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    
    // Show existing databases
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p style='color: blue;'>📁 Existing databases: " . implode(', ', $databases) . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Basic MySQL connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Troubleshooting steps:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running</li>";
    echo "<li>Start Apache and MySQL services in XAMPP Control Panel</li>";
    echo "<li>Check that MySQL is running on port 3306</li>";
    echo "<li>Verify that the MySQL service started without errors</li>";
    echo "</ul>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
p { margin: 5px 0; }
hr { margin: 20px 0; border: 1px solid #ccc; }
</style>