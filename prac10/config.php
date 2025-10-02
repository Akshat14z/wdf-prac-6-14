<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prac10_login_system');

// Session configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('REMEMBER_ME_DURATION', 604800); // 7 days in seconds

// Security settings
define('HASH_COST', 12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes in seconds

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Create database if it doesn't exist
    try {
        $temp_pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
        $temp_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $temp_pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Reconnect to the new database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Create tables
        createTables($pdo);
        
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Create necessary tables
function createTables($pdo) {
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        login_attempts INT DEFAULT 0,
        locked_until TIMESTAMP NULL
    )");
    
    // Sessions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_sessions (
        session_id VARCHAR(128) PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Login attempts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50),
        ip_address VARCHAR(45),
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        success BOOLEAN DEFAULT FALSE,
        user_agent TEXT
    )");
    
    // Remember me tokens table
    $pdo->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Insert default users
    insertDefaultUsers($pdo);
}

// Insert default demo users
function insertDefaultUsers($pdo) {
    $users = [
        ['username' => 'admin', 'email' => 'admin@example.com', 'password' => 'admin123', 'role' => 'admin'],
        ['username' => 'user', 'email' => 'user@example.com', 'password' => 'user123', 'role' => 'user'],
        ['username' => 'demo', 'email' => 'demo@example.com', 'password' => 'demo123', 'role' => 'user']
    ];
    
    foreach ($users as $user) {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user['username']]);
        
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user['username'], $user['email'], $hashedPassword, $user['role']]);
        }
    }
}

// Utility functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            logout();
            header('Location: login.php?timeout=1');
            exit();
        }
    }
    
    $_SESSION['last_activity'] = time();
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied. Insufficient privileges.');
    }
}

function logout() {
    global $pdo;
    
    // Deactivate session in database
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE session_id = ?");
        $stmt->execute([session_id()]);
    }
    
    // Remove remember me cookie if exists
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    // Start new session to prevent session fixation
    session_start();
    session_regenerate_id(true);
}

function cleanupExpiredSessions() {
    global $pdo;
    
    // Remove expired sessions
    $timeout_limit = date('Y-m-d H:i:s', time() - SESSION_TIMEOUT);
    $stmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE last_activity < ? AND is_active = 1");
    $stmt->execute([$timeout_limit]);
    
    // Remove expired remember me tokens
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE expires_at < NOW()");
    $stmt->execute();
}

function logLoginAttempt($username, $success, $ip = null, $userAgent = null) {
    global $pdo;
    
    $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $userAgent = $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
    
    $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address, success, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $ip, $success ? 1 : 0, $userAgent]);
}

function getLoginAttempts($username, $ip, $timeframe = 900) { // 15 minutes
    global $pdo;
    
    $since = date('Y-m-d H:i:s', time() - $timeframe);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE (username = ? OR ip_address = ?) AND success = 0 AND attempt_time > ?");
    $stmt->execute([$username, $ip, $since]);
    
    return $stmt->fetchColumn();
}

function isUserLocked($username) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT locked_until FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && $user['locked_until']) {
        return strtotime($user['locked_until']) > time();
    }
    
    return false;
}

function lockUser($username) {
    global $pdo;
    
    $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
    $stmt = $pdo->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE username = ?");
    $stmt->execute([MAX_LOGIN_ATTEMPTS, $lockUntil, $username]);
}

function resetLoginAttempts($username) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE username = ?");
    $stmt->execute([$username]);
}

function recordSession($userId, $sessionId, $ip = null, $userAgent = null) {
    global $pdo;
    
    $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $userAgent = $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
    
    // Deactivate other sessions for this user if needed (optional)
    // $stmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE user_id = ? AND session_id != ?");
    // $stmt->execute([$userId, $sessionId]);
    
    $stmt = $pdo->prepare("INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE last_activity = CURRENT_TIMESTAMP, is_active = 1");
    $stmt->execute([$sessionId, $userId, $ip, $userAgent]);
}

// Run cleanup on every request (with small probability to avoid performance impact)
if (rand(1, 100) <= 5) { // 5% chance
    cleanupExpiredSessions();
}
?>