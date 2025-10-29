<?php

class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

   public function login($email, $password) {
    $query = "SELECT id, name, email, password, role FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // DEV OVERRIDE: set to '1' to accept any password for existing users
    // IMPORTANT: Only enable on your local machine / dev environment
    $allow_any = getenv('ALLOW_ANY_PASSWORD') === '1' || defined('ALLOW_ANY_PASSWORD') && ALLOW_ANY_PASSWORD === true;

    if ($user) {
        if ($allow_any) {
            // Accept any password (for testing). Do NOT use in production.
            return $user;
        }

        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }

    return false;
}


    public function register($name, $email, $password, $role = 'user') {
        // Check if email already exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return false; // Email already exists
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $query = "INSERT INTO " . $this->table_name . " (name, email, password, role) VALUES (:name, :email, :password, :role)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        
        return $stmt->execute();
    }

    public function getUserById($id) {
        $query = "SELECT id, name, email, role FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $query = "SELECT id, name, email, role FROM " . $this->table_name . " ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAgents() {
        $query = "SELECT id, name, email FROM " . $this->table_name . " WHERE role IN ('admin', 'agent') ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser($id, $name, $email, $role) {
        $query = "UPDATE " . $this->table_name . " SET name = :name, email = :email, role = :role WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        
        return $stmt->execute();
    }

    public function updatePassword($id, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $hashed_password);
        
        return $stmt->execute();
    }

    public function deleteUser($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}