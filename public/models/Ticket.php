<?php

class Ticket {
    private $conn;
    private $table_name = "tickets";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllTickets() {
        $query = "SELECT t.*, u.name as assignee_name, creator.name as creator_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.assigned_to = u.id
                  LEFT JOIN users creator ON t.created_by = creator.id
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketById($id) {
        $query = "SELECT t.*, u.name as assignee_name, creator.name as creator_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.assigned_to = u.id
                  LEFT JOIN users creator ON t.created_by = creator.id
                  WHERE t.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createTicket($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (ticket_id, subject, description, priority, status, assigned_to, created_by) 
                  VALUES (:ticket_id, :subject, :description, :priority, :status, :assigned_to, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        // Generate unique ticket ID
        $ticket_id = 'T' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt->bindParam(':ticket_id', $ticket_id);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':assigned_to', $data['assigned_to']);
        $stmt->bindParam(':created_by', $data['created_by']);
        
        if($stmt->execute()) {
            return $ticket_id;
        }
        
        return false;
    }

    public function updateTicket($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                  subject = :subject,
                  description = :description,
                  priority = :priority,
                  status = :status,
                  assigned_to = :assigned_to,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':assigned_to', $data['assigned_to']);
        
        return $stmt->execute();
    }

    public function deleteTicket($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function getTicketStats() {
        $stats = [];
        
        // Get all tickets to count manually
        $tickets = $this->getAllTickets();
        
        $stats['total'] = count($tickets);
        $stats['open'] = 0;
        $stats['in_progress'] = 0;
        $stats['closed'] = 0;
        
        foreach ($tickets as $ticket) {
            $status = $ticket['status'] ?? 'Open';
            switch ($status) {
                case 'Open':
                    $stats['open']++;
                    break;
                case 'In Progress':
                    $stats['in_progress']++;
                    break;
                case 'Closed':
                    $stats['closed']++;
                    break;
            }
        }
        
        return $stats;
    }

    public function getPriorityClass($priority) {
        switch($priority) {
            case 'Critical':
                return 'bg-red-100 text-red-800';
            case 'High':
                return 'bg-red-100 text-red-800';
            case 'Medium':
                return 'bg-orange-100 text-orange-800';
            case 'Low':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    public function getStatusClass($status) {
        switch($status) {
            case 'Open':
                return 'bg-blue-100 text-blue-800';
            case 'In Progress':
                return 'bg-yellow-100 text-yellow-800';
            case 'Closed':
                return 'bg-gray-100 text-gray-800';
            case 'Resolved':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
}