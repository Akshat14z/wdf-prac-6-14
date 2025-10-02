<?php
require_once 'config.php';

class EventManager {
    private $conn;
    private $table_name = "events";
    
    public function __construct() {
        $this->conn = getDbConnection();
    }
    
    // Create new event
    public function createEvent($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET title=:title, description=:description, event_date=:event_date, 
                          event_time=:event_time, location=:location, organizer=:organizer, 
                          capacity=:capacity, status=:status";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":event_date", $data['event_date']);
            $stmt->bindParam(":event_time", $data['event_time']);
            $stmt->bindParam(":location", $data['location']);
            $stmt->bindParam(":organizer", $data['organizer']);
            $stmt->bindParam(":capacity", $data['capacity']);
            $stmt->bindParam(":status", $data['status']);
            
            if($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Event created successfully!',
                    'id' => $this->conn->lastInsertId()
                ];
            }
            return ['success' => false, 'message' => 'Failed to create event.'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Read all events
    public function getAllEvents() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Read single event
    public function getEventById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
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
            
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":event_date", $data['event_date']);
            $stmt->bindParam(":event_time", $data['event_time']);
            $stmt->bindParam(":location", $data['location']);
            $stmt->bindParam(":organizer", $data['organizer']);
            $stmt->bindParam(":capacity", $data['capacity']);
            $stmt->bindParam(":status", $data['status']);
            
            if($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Event updated successfully!'
                ];
            }
            return ['success' => false, 'message' => 'Failed to update event.'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Delete event
    public function deleteEvent($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            
            if($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Event deleted successfully!'
                ];
            }
            return ['success' => false, 'message' => 'Failed to delete event.'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Get events by status
    public function getEventsByStatus($status) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE status = :status ORDER BY event_date ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Get upcoming events
    public function getUpcomingEvents() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE event_date >= CURDATE() 
                      ORDER BY event_date ASC, event_time ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Search events
    public function searchEvents($searchTerm) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE title LIKE :search 
                      OR description LIKE :search 
                      OR location LIKE :search 
                      OR organizer LIKE :search
                      ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $searchTerm = '%' . $searchTerm . '%';
            $stmt->bindParam(":search", $searchTerm);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
}
?>