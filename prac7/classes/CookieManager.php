<?php
class CookieManager {
    
    public static function setRememberMeCookie($userId, $token) {
        $cookieValue = $userId . ':' . $token;
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Check if we're running on HTTPS
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        
        setcookie('remember_me', $cookieValue, [
            'expires' => $expiry,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    public static function clearRememberMeCookie() {
        // Check if we're running on HTTPS
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    public static function checkRememberMeCookie() {
        if (!isset($_COOKIE['remember_me'])) {
            return false;
        }
        
        $cookieValue = $_COOKIE['remember_me'];
        $parts = explode(':', $cookieValue);
        
        if (count($parts) !== 2) {
            self::clearRememberMeCookie();
            return false;
        }
        
        $userId = $parts[0];
        $token = $parts[1];
        
        // Verify token against database
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT id, username FROM users WHERE id = :id AND remember_token = :token";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        
        self::clearRememberMeCookie();
        return false;
    }
}
?>