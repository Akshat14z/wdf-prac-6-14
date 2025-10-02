<?php
class User {
    private $conn;
    private $table_name = "users";
    
    public function __construct($db) {
        $this->conn = $db;
        
        // Check if connection is valid
        if ($this->conn === null) {
            throw new Exception("Database connection is null");
        }
    }
    
    public function authenticate($username, $password) {
        try {
            $query = "SELECT id, username, password FROM " . $this->table_name . " 
                      WHERE username = :username LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $user['password'])) {
                    return [
                        'id' => $user['id'],
                        'username' => $user['username']
                    ];
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    public function register($username, $password, $email) {
        try {
            // Check if database connection exists
            if ($this->conn === null) {
                throw new Exception("No database connection available");
            }
            
            // Check if username already exists
            $check_query = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':username', $username);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return false; // Username already exists
            }
            
            // Insert new user
            $query = "INSERT INTO " . $this->table_name . " 
                      (username, password, email, created_at) 
                      VALUES (:username, :password, :email, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':email', $email);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateRememberToken($userId, $token) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET remember_token = :token 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':id', $userId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update token error: " . $e->getMessage());
            return false;
        }
    }
    
    public function clearRememberToken($userId) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET remember_token = NULL 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Clear token error: " . $e->getMessage());
            return false;
        }
    }
}
?>