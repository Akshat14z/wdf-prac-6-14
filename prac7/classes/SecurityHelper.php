<?php
class SecurityHelper {
    
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        return $errors;
    }
    
    public static function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    public static function logLoginAttempt($username, $success = false) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "INSERT INTO login_attempts (username, ip_address, user_agent, success) 
                      VALUES (:username, :ip_address, :user_agent, :success)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':ip_address', self::getRealIpAddr());
            $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
            $stmt->bindParam(':success', $success, PDO::PARAM_BOOL);
            
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to log login attempt: " . $e->getMessage());
        }
    }
    
    public static function checkBruteForce($username, $maxAttempts = 5, $timeWindow = 900) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "SELECT COUNT(*) as attempts FROM login_attempts 
                      WHERE username = :username 
                      AND success = FALSE 
                      AND created_at > DATE_SUB(NOW(), INTERVAL :timeWindow SECOND)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':timeWindow', $timeWindow, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['attempts'] >= $maxAttempts;
        } catch (Exception $e) {
            error_log("Failed to check brute force: " . $e->getMessage());
            return false;
        }
    }
    
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>