<?php
class Database {
    // XAMPP default settings
    private $host = "localhost";
    private $db_name = "login_system";
    private $username = "root";
    private $password = "";      // XAMPP default password is empty
    private $port = "3306";      // XAMPP default MySQL port
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // XAMPP connection string with port
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die(); // Stop execution if database fails
        }
        return $this->conn;
    }
}
?>