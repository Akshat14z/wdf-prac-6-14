<?php
/**
 * Practice 14 - Admin Dashboard Configuration
 * Role-based access control with user management
 */

// Database configuration for XAMPP
define('DB_HOST', 'localhost');
define('DB_PORT', '3306'); // XAMPP MySQL port
define('DB_NAME', 'prac14_admin_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default password

// Security constants
define('HASH_COST', 12);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// User roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_MODERATOR', 'moderator');
define('ROLE_USER', 'user');

// User status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Database Connection and Setup Class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connect();
        $this->createDatabase();
        $this->setupTables();
        $this->insertSampleData();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            // First connect without database to create it
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function createDatabase() {
        try {
            $this->connection->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->connection->exec("USE " . DB_NAME);
        } catch (PDOException $e) {
            die("Database creation failed: " . $e->getMessage());
        }
    }
    
    private function setupTables() {
        // Users table with role and status
        $usersTable = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                role ENUM('super_admin', 'admin', 'moderator', 'user') DEFAULT 'user',
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                last_login DATETIME NULL,
                login_attempts INT DEFAULT 0,
                locked_until DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB";
        
        // User sessions table
        $sessionsTable = "
            CREATE TABLE IF NOT EXISTS user_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                session_id VARCHAR(128) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_session_id (session_id),
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB";
        
        // Activity log table
        $activityTable = "
            CREATE TABLE IF NOT EXISTS activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                admin_id INT NULL,
                action_type VARCHAR(50) NOT NULL,
                description TEXT NOT NULL,
                target_user_id INT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_user_id (user_id),
                INDEX idx_admin_id (admin_id),
                INDEX idx_action_type (action_type)
            ) ENGINE=InnoDB";
        
        // CSRF tokens table
        $csrfTable = "
            CREATE TABLE IF NOT EXISTS csrf_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                token_hash VARCHAR(64) NOT NULL,
                session_id VARCHAR(128) NOT NULL,
                used BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NOT NULL,
                INDEX idx_token_hash (token_hash),
                INDEX idx_session_id (session_id)
            ) ENGINE=InnoDB";
        
        // Admin audit log
        $auditTable = "
            CREATE TABLE IF NOT EXISTS admin_audit_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                action VARCHAR(100) NOT NULL,
                table_name VARCHAR(50),
                record_id INT,
                old_values JSON,
                new_values JSON,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_admin_id (admin_id),
                INDEX idx_action (action)
            ) ENGINE=InnoDB";
        
        try {
            $this->connection->exec($usersTable);
            $this->connection->exec($sessionsTable);
            $this->connection->exec($activityTable);
            $this->connection->exec($csrfTable);
            $this->connection->exec($auditTable);
        } catch (PDOException $e) {
            die("Table creation failed: " . $e->getMessage());
        }
    }
    
    private function insertSampleData() {
        // Check if data already exists
        $checkQuery = "SELECT COUNT(*) FROM users";
        $result = $this->connection->query($checkQuery);
        if ($result->fetchColumn() > 0) {
            return; // Data already exists
        }
        
        // Sample users with different roles and statuses
        $users = [
            [
                'username' => 'superadmin',
                'email' => 'superadmin@demo.com',
                'password' => 'SuperAdmin123!',
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'role' => 'super_admin',
                'status' => 'active'
            ],
            [
                'username' => 'admin',
                'email' => 'admin@demo.com',
                'password' => 'AdminPass123!',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'role' => 'admin',
                'status' => 'active'
            ],
            [
                'username' => 'moderator',
                'email' => 'moderator@demo.com',
                'password' => 'ModeratorPass123!',
                'first_name' => 'Content',
                'last_name' => 'Moderator',
                'role' => 'moderator',
                'status' => 'active'
            ],
            [
                'username' => 'johndoe',
                'email' => 'john@demo.com',
                'password' => 'UserPass123!',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'role' => 'user',
                'status' => 'active'
            ],
            [
                'username' => 'janedoe',
                'email' => 'jane@demo.com',
                'password' => 'UserPass456!',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'role' => 'user',
                'status' => 'active'
            ],
            [
                'username' => 'testuser',
                'email' => 'test@demo.com',
                'password' => 'TestPass789!',
                'first_name' => 'Test',
                'last_name' => 'User',
                'role' => 'user',
                'status' => 'inactive'
            ],
            [
                'username' => 'suspendeduser',
                'email' => 'suspended@demo.com',
                'password' => 'SuspendedPass123!',
                'first_name' => 'Suspended',
                'last_name' => 'User',
                'role' => 'user',
                'status' => 'suspended'
            ],
            [
                'username' => 'alice',
                'email' => 'alice@demo.com',
                'password' => 'AlicePass123!',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'role' => 'user',
                'status' => 'active'
            ],
            [
                'username' => 'bob',
                'email' => 'bob@demo.com',
                'password' => 'BobPass123!',
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'role' => 'user',
                'status' => 'active'
            ],
            [
                'username' => 'charlie',
                'email' => 'charlie@demo.com',
                'password' => 'CharliePass123!',
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'role' => 'user',
                'status' => 'inactive'
            ]
        ];
        
        $insertQuery = "
            INSERT INTO users (username, email, password_hash, first_name, last_name, role, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->connection->prepare($insertQuery);
        
        foreach ($users as $user) {
            $passwordHash = password_hash($user['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            $stmt->execute([
                $user['username'],
                $user['email'],
                $passwordHash,
                $user['first_name'],
                $user['last_name'],
                $user['role'],
                $user['status']
            ]);
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

/**
 * Security Utility Class
 */
class SecurityUtil {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public static function sanitizeInput($input, $type = 'string') {
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validatePassword($password) {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/\d/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }
    
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][] = $token;
        
        // Keep only last 10 tokens
        if (count($_SESSION['csrf_tokens']) > 10) {
            array_shift($_SESSION['csrf_tokens']);
        }
        
        return $token;
    }
    
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
            return false;
        }
        
        $index = array_search($token, $_SESSION['csrf_tokens']);
        if ($index !== false) {
            // Remove used token
            unset($_SESSION['csrf_tokens'][$index]);
            $_SESSION['csrf_tokens'] = array_values($_SESSION['csrf_tokens']);
            return true;
        }
        
        return false;
    }
    
    public function logActivity($userId, $adminId, $actionType, $description, $targetUserId = null) {
        $query = "
            INSERT INTO activity_log (user_id, admin_id, action_type, description, target_user_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $userId,
            $adminId,
            $actionType,
            $description,
            $targetUserId,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    public function logAdminAudit($adminId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        $query = "
            INSERT INTO admin_audit_log (admin_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $adminId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
}

/**
 * User Authentication Class
 */
class UserAuth {
    private $db;
    private $securityUtil;
    
    public function __construct($database) {
        $this->db = $database;
        $this->securityUtil = new SecurityUtil($database);
    }
    
    public function login($username, $password, $rememberMe = false) {
        // Check if user is locked
        $lockQuery = "SELECT locked_until FROM users WHERE (username = ? OR email = ?) AND locked_until > NOW()";
        $lockStmt = $this->db->prepare($lockQuery);
        $lockStmt->execute([$username, $username]);
        if ($lockStmt->fetch()) {
            return ['success' => false, 'error' => 'Account is temporarily locked due to multiple failed attempts.'];
        }
        
        // Get user
        $userQuery = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status != 'suspended'";
        $userStmt = $this->db->prepare($userQuery);
        $userStmt->execute([$username, $username]);
        $user = $userStmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'inactive') {
                return ['success' => false, 'error' => 'Account is inactive. Please contact administrator.'];
            }
            
            // Reset login attempts
            $resetQuery = "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?";
            $resetStmt = $this->db->prepare($resetQuery);
            $resetStmt->execute([$user['id']]);
            
            // Create session
            $this->createSession($user, $rememberMe);
            
            // Log activity
            $this->securityUtil->logActivity($user['id'], null, 'login', 'User logged in successfully');
            
            return ['success' => true, 'user' => $user];
        } else {
            // Failed login
            if ($user) {
                $attempts = $user['login_attempts'] + 1;
                $lockUntil = null;
                
                if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                    $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
                }
                
                $updateQuery = "UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->execute([$attempts, $lockUntil, $user['id']]);
                
                // Log failed attempt
                $this->securityUtil->logActivity($user['id'], null, 'failed_login', 'Failed login attempt');
            }
            
            return ['success' => false, 'error' => 'Invalid username/email or password.'];
        }
    }
    
    private function createSession($user, $rememberMe) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_status'] = $user['status'];
        $_SESSION['login_time'] = time();
        
        // Create session record
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
        if ($rememberMe) {
            $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 3600)); // 30 days
        }
        
        $sessionQuery = "
            INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ";
        $sessionStmt = $this->db->prepare($sessionQuery);
        $sessionStmt->execute([
            $user['id'],
            session_id(),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $expiresAt
        ]);
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Remove session from database
            $deleteQuery = "DELETE FROM user_sessions WHERE session_id = ?";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->execute([session_id()]);
        }
        
        session_destroy();
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
            return false;
        }
        
        // Check session timeout
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $userRole = $_SESSION['user_role'] ?? '';
        
        // Role hierarchy
        $roleHierarchy = [
            'super_admin' => ['super_admin', 'admin', 'moderator', 'user'],
            'admin' => ['admin', 'moderator', 'user'],
            'moderator' => ['moderator', 'user'],
            'user' => ['user']
        ];
        
        return in_array($role, $roleHierarchy[$userRole] ?? []);
    }
    
    public function requireRole($role) {
        if (!$this->hasRole($role)) {
            header('HTTP/1.0 403 Forbidden');
            header('Location: login.php?error=access_denied');
            exit;
        }
    }
    
    public function requireAdmin() {
        $this->requireRole('admin');
    }
}

/**
 * Admin Management Class
 */
class AdminManager {
    private $db;
    private $securityUtil;
    private $userAuth;
    
    public function __construct($database, $userAuth) {
        $this->db = $database;
        $this->userAuth = $userAuth;
        $this->securityUtil = new SecurityUtil($database);
    }
    
    public function getAllUsers($page = 1, $limit = 10, $search = '', $roleFilter = '', $statusFilter = '') {
        $offset = ($page - 1) * $limit;
        
        $conditions = [];
        $params = [];
        
        if (!empty($search)) {
            $conditions[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($roleFilter)) {
            $conditions[] = "role = ?";
            $params[] = $roleFilter;
        }
        
        if (!empty($statusFilter)) {
            $conditions[] = "status = ?";
            $params[] = $statusFilter;
        }
        
        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        
        // Get total count
        $countQuery = "SELECT COUNT(*) FROM users $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get users
        $query = "
            SELECT id, username, email, first_name, last_name, role, status, last_login, created_at
            FROM users 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        return [
            'users' => $users,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateUserStatus($userId, $newStatus, $adminId) {
        $user = $this->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found.'];
        }
        
        $oldStatus = $user['status'];
        
        $query = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute([$newStatus, $userId])) {
            // Log the change
            $this->securityUtil->logActivity(null, $adminId, 'user_status_change', 
                "Changed user {$user['username']} status from {$oldStatus} to {$newStatus}", $userId);
            
            $this->securityUtil->logAdminAudit($adminId, 'UPDATE_USER_STATUS', 'users', $userId, 
                ['status' => $oldStatus], ['status' => $newStatus]);
            
            return ['success' => true, 'message' => 'User status updated successfully.'];
        }
        
        return ['success' => false, 'error' => 'Failed to update user status.'];
    }
    
    public function updateUserRole($userId, $newRole, $adminId) {
        $user = $this->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found.'];
        }
        
        // Prevent changing super admin role unless current user is super admin
        if ($user['role'] === 'super_admin' && !$this->userAuth->hasRole('super_admin')) {
            return ['success' => false, 'error' => 'Only super admins can modify super admin roles.'];
        }
        
        $oldRole = $user['role'];
        
        $query = "UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute([$newRole, $userId])) {
            // Log the change
            $this->securityUtil->logActivity(null, $adminId, 'user_role_change', 
                "Changed user {$user['username']} role from {$oldRole} to {$newRole}", $userId);
            
            $this->securityUtil->logAdminAudit($adminId, 'UPDATE_USER_ROLE', 'users', $userId, 
                ['role' => $oldRole], ['role' => $newRole]);
            
            return ['success' => true, 'message' => 'User role updated successfully.'];
        }
        
        return ['success' => false, 'error' => 'Failed to update user role.'];
    }
    
    public function deleteUser($userId, $adminId) {
        $user = $this->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found.'];
        }
        
        // Prevent deleting super admin unless current user is super admin
        if ($user['role'] === 'super_admin' && !$this->userAuth->hasRole('super_admin')) {
            return ['success' => false, 'error' => 'Only super admins can delete super admin accounts.'];
        }
        
        // Prevent self-deletion
        if ($userId == $this->userAuth->getCurrentUserId()) {
            return ['success' => false, 'error' => 'You cannot delete your own account.'];
        }
        
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute([$userId])) {
            // Log the deletion
            $this->securityUtil->logActivity(null, $adminId, 'user_deletion', 
                "Deleted user {$user['username']} (ID: {$userId})", $userId);
            
            $this->securityUtil->logAdminAudit($adminId, 'DELETE_USER', 'users', $userId, 
                $user, null);
            
            return ['success' => true, 'message' => 'User deleted successfully.'];
        }
        
        return ['success' => false, 'error' => 'Failed to delete user.'];
    }
    
    public function getActivityLog($limit = 50) {
        $query = "
            SELECT 
                al.*,
                u.username as user_username,
                a.username as admin_username,
                t.username as target_username
            FROM activity_log al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN users a ON al.admin_id = a.id  
            LEFT JOIN users t ON al.target_user_id = t.id
            ORDER BY al.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getDashboardStats() {
        $stats = [];
        
        // Total users
        $totalQuery = "SELECT COUNT(*) FROM users";
        $stats['total_users'] = $this->db->query($totalQuery)->fetchColumn();
        
        // Active users
        $activeQuery = "SELECT COUNT(*) FROM users WHERE status = 'active'";
        $stats['active_users'] = $this->db->query($activeQuery)->fetchColumn();
        
        // Inactive users
        $inactiveQuery = "SELECT COUNT(*) FROM users WHERE status = 'inactive'";
        $stats['inactive_users'] = $this->db->query($inactiveQuery)->fetchColumn();
        
        // Suspended users
        $suspendedQuery = "SELECT COUNT(*) FROM users WHERE status = 'suspended'";
        $stats['suspended_users'] = $this->db->query($suspendedQuery)->fetchColumn();
        
        // Role distribution
        $roleQuery = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
        $roleStmt = $this->db->query($roleQuery);
        $stats['roles'] = $roleStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Recent activity count
        $activityQuery = "SELECT COUNT(*) FROM activity_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stats['recent_activity'] = $this->db->query($activityQuery)->fetchColumn();
        
        return $stats;
    }
}

// Initialize database and classes
$db = Database::getInstance()->getConnection();
$userAuth = new UserAuth($db);
$adminManager = new AdminManager($db, $userAuth);
$securityUtil = new SecurityUtil($db);

// Clean up old sessions and tokens
$cleanupQuery = "DELETE FROM user_sessions WHERE expires_at < NOW()";
$db->exec($cleanupQuery);

$cleanupTokens = "DELETE FROM csrf_tokens WHERE expires_at < NOW()";
$db->exec($cleanupTokens);

?>