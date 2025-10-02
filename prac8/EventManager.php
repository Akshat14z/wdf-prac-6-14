<?php
require_once 'config.php';

class EventManager {
    private $conn;
    private $table_name = "events";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new event
    public function createEvent($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET title=:title, description=:description, event_date=:event_date, 
                          event_time=:event_time, location=:location, organizer=:organizer, 
                          capacity=:capacity, created_by=:created_by";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":event_date", $data['event_date']);
            $stmt->bindParam(":event_time", $data['event_time']);
            $stmt->bindParam(":location", $data['location']);
            $stmt->bindParam(":organizer", $data['organizer']);
            $stmt->bindParam(":capacity", $data['capacity']);
            $stmt->bindParam(":created_by", $data['created_by']);
            
            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Read all events with pagination
    public function getAllEvents($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT e.*, u.full_name as creator_name,
                             (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_count
                      FROM " . $this->table_name . " e 
                      LEFT JOIN users u ON e.created_by = u.id 
                      ORDER BY e.created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Get latest 5 events (for dashboard)
    public function getLatestEvents($limit = 5) {
        try {
            $query = "SELECT e.*, u.full_name as creator_name,
                             (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_count
                      FROM " . $this->table_name . " e 
                      LEFT JOIN users u ON e.created_by = u.id 
                      WHERE e.status = 'active' 
                      ORDER BY e.created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Get single event by ID
    public function getEventById($id) {
        try {
            $query = "SELECT e.*, u.full_name as creator_name,
                             (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_count
                      FROM " . $this->table_name . " e 
                      LEFT JOIN users u ON e.created_by = u.id 
                      WHERE e.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Update event
    public function updateEvent($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET title=:title, description=:description, event_date=:event_date, 
                          event_time=:event_time, location=:location, organizer=:organizer, 
                          capacity=:capacity, status=:status 
                      WHERE id=:id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":event_date", $data['event_date']);
            $stmt->bindParam(":event_time", $data['event_time']);
            $stmt->bindParam(":location", $data['location']);
            $stmt->bindParam(":organizer", $data['organizer']);
            $stmt->bindParam(":capacity", $data['capacity']);
            $stmt->bindParam(":status", $data['status']);
            $stmt->bindParam(":id", $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Delete event
    public function deleteEvent($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Get total event count
    public function getTotalEventCount() {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    }
    
    // Register user for event
    public function registerForEvent($event_id, $user_id) {
        try {
            $query = "INSERT INTO event_registrations (event_id, user_id) VALUES (:event_id, :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':event_id', $event_id);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry error
                return "already_registered";
            }
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Get event statistics
    public function getEventStats() {
        try {
            $stats = [];
            
            // Total events
            $stmt = $this->conn->query("SELECT COUNT(*) FROM events");
            $stats['total_events'] = $stmt->fetchColumn();
            
            // Active events
            $stmt = $this->conn->query("SELECT COUNT(*) FROM events WHERE status = 'active'");
            $stats['active_events'] = $stmt->fetchColumn();
            
            // Total registrations
            $stmt = $this->conn->query("SELECT COUNT(*) FROM event_registrations");
            $stats['total_registrations'] = $stmt->fetchColumn();
            
            // Events this month
            $stmt = $this->conn->query("SELECT COUNT(*) FROM events WHERE MONTH(event_date) = MONTH(CURRENT_DATE()) AND YEAR(event_date) = YEAR(CURRENT_DATE())");
            $stats['events_this_month'] = $stmt->fetchColumn();
            
            return $stats;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}

// User Management Class
class UserManager {
    private $conn;
    private $table_name = "users";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Authenticate user
    public function authenticate($username, $password) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username OR email = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    return $user;
                }
            }
            return false;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Register new user
    public function register($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET username=:username, email=:email, password=:password, full_name=:full_name";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":username", $data['username']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":password", password_hash($data['password'], PASSWORD_DEFAULT));
            $stmt->bindParam(":full_name", $data['full_name']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
?>