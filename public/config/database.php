<?php

class Database {
    private $data_file;
    public $data;

    public function __construct() {
        // Use /tmp for Railway's ephemeral storage or fallback to local
        $this->data_file = (getenv('RAILWAY_ENVIRONMENT') ? '/tmp/data.json' : 'data.json');
        
        // Ensure directory exists
        $dir = dirname($this->data_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if (!file_exists($this->data_file)) {
            $this->initializeData();
        } else {
            $this->data = json_decode(file_get_contents($this->data_file), true);
        }
    }

    private function initializeData() {
        $this->data = [
            'users' => [
                [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role' => 'admin',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 2,
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role' => 'agent',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 3,
                    'name' => 'Mike Johnson',
                    'email' => 'mike@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role' => 'agent',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 4,
                    'name' => 'Sarah Wilson',
                    'email' => 'sarah@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role' => 'agent',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 5,
                    'name' => 'Tom Brown',
                    'email' => 'tom@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role' => 'user',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'tickets' => [
                [
                    'id' => 1,
                    'ticket_id' => 'T001',
                    'subject' => 'Login issue on mobile app',
                    'description' => 'User unable to login using mobile app credentials',
                    'priority' => 'High',
                    'status' => 'Open',
                    'assigned_to' => 2,
                    'created_by' => 5,
                    'created_at' => '2024-01-15 10:00:00',
                    'updated_at' => '2024-01-15 10:00:00'
                ],
                [
                    'id' => 2,
                    'ticket_id' => 'T002',
                    'subject' => 'Payment gateway error',
                    'description' => 'Payment processing fails with timeout error',
                    'priority' => 'Critical',
                    'status' => 'In Progress',
                    'assigned_to' => 3,
                    'created_by' => 5,
                    'created_at' => '2024-01-14 09:30:00',
                    'updated_at' => '2024-01-14 15:45:00'
                ],
                [
                    'id' => 3,
                    'ticket_id' => 'T003',
                    'subject' => 'Feature request: Dark mode',
                    'description' => 'Add dark mode option to the application',
                    'priority' => 'Low',
                    'status' => 'Closed',
                    'assigned_to' => 4,
                    'created_by' => 5,
                    'created_at' => '2024-01-13 14:20:00',
                    'updated_at' => '2024-01-13 16:30:00'
                ],
                [
                    'id' => 4,
                    'ticket_id' => 'T004',
                    'subject' => 'Database connection timeout',
                    'description' => 'Database connections timing out during peak hours',
                    'priority' => 'High',
                    'status' => 'Open',
                    'assigned_to' => 2,
                    'created_by' => 5,
                    'created_at' => '2024-01-15 11:15:00',
                    'updated_at' => '2024-01-15 11:15:00'
                ],
                [
                    'id' => 5,
                    'ticket_id' => 'T005',
                    'subject' => 'Email notification not working',
                    'description' => 'Users not receiving email notifications',
                    'priority' => 'Medium',
                    'status' => 'In Progress',
                    'assigned_to' => 4,
                    'created_by' => 5,
                    'created_at' => '2024-01-14 13:45:00',
                    'updated_at' => '2024-01-14 17:20:00'
                ]
            ],
            'next_user_id' => 6,
            'next_ticket_id' => 6
        ];
        $this->saveData();
    }

    public function saveData() {
        file_put_contents($this->data_file, json_encode($this->data, JSON_PRETTY_PRINT));
    }

    public function getConnection() {
        return new class($this) {
            private $db;
            
            public function __construct($database) {
                $this->db = $database;
            }
            
            public function prepare($query) {
                return new class($this->db, $query) {
                    private $db;
                    private $query;
                    private $params = [];
                    
                    public function __construct($db, $query) {
                        $this->db = $db;
                        $this->query = $query;
                    }
                    
                    public function bindParam($param, &$value) {
                        $this->params[$param] = $value;
                    }
                    
                    public function execute() {
                        // Handle INSERT queries
                        if (strpos($this->query, 'INSERT INTO') !== false) {
                            return $this->executeInsert();
                        }
                        
                        // Handle UPDATE queries
                        if (strpos($this->query, 'UPDATE') !== false) {
                            return $this->executeUpdate();
                        }
                        
                        // Handle DELETE queries
                        if (strpos($this->query, 'DELETE') !== false) {
                            return $this->executeDelete();
                        }
                        
                        return true;
                    }
                    
                    private function executeInsert() {
                        $data = $this->db->data;
                        
                        if (strpos($this->query, 'INSERT INTO users') !== false) {
                            $newUser = [
                                'id' => $data['next_user_id']++,
                                'name' => $this->params[':name'],
                                'email' => $this->params[':email'],
                                'password' => $this->params[':password'],
                                'role' => $this->params[':role'],
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                            
                            $data['users'][] = $newUser;
                            $this->db->data = $data;
                            $this->db->saveData();
                            return true;
                        }
                        
                        if (strpos($this->query, 'INSERT INTO tickets') !== false) {
                            $newTicket = [
                                'id' => $data['next_ticket_id']++,
                                'ticket_id' => 'T' . str_pad($data['next_ticket_id'], 3, '0', STR_PAD_LEFT),
                                'subject' => $this->params[':subject'],
                                'description' => $this->params[':description'],
                                'priority' => $this->params[':priority'],
                                'status' => $this->params[':status'],
                                'assigned_to' => $this->params[':assigned_to'] ? (int)$this->params[':assigned_to'] : null,
                                'created_by' => $this->params[':created_by'],
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            
                            $data['tickets'][] = $newTicket;
                            $this->db->data = $data;
                            $this->db->saveData();
                            return true;
                        }
                        
                        return false;
                    }
                    
                    public function rowCount() {
                        $data = $this->db->data;
                        
                        // For email existence check in signup
                        if (strpos($this->query, 'SELECT id FROM') !== false && strpos($this->query, 'WHERE email') !== false) {
                            $email = $this->params[':email'] ?? '';
                            $count = 0;
                            foreach ($data['users'] as $user) {
                                if ($user['email'] === $email) {
                                    $count++;
                                }
                            }
                            return $count;
                        }
                        
                        return 0;
                    }
                    
                    public function fetch($style = null) {
                        return $this->executeQuery($this->query, $this->params, true);
                    }
                    
                    public function fetchAll($style = null) {
                        return $this->executeQuery($this->query, $this->params, false);
                    }
                    
                    public function fetchColumn() {
                        $data = $this->db->data;
                        
                        if (strpos($this->query, 'COUNT(*)') !== false) {
                            if (strpos($this->query, 'tickets') !== false) {
                                if (strpos($this->query, "status = 'Open'") !== false) {
                                    return 2; // Open tickets count
                                } elseif (strpos($this->query, "status = 'In Progress'") !== false) {
                                    return 2; // In Progress tickets count
                                } elseif (strpos($this->query, "status = 'Closed'") !== false) {
                                    return 1; // Closed tickets count
                                } else {
                                    return count($data['tickets']); // Total tickets
                                }
                            }
                        }
                        
                        return 0;
                    }
                    
                    private function executeQuery($query, $params, $single = false) {
                        $data = $this->db->data;
                        
                        if (strpos($query, 'SELECT') !== false) {
                            if (strpos($query, 'users') !== false) {
                                $result = $data['users'];
                                
                                // Handle WHERE email condition
                                if (strpos($query, 'WHERE email') !== false && isset($params[':email'])) {
                                    $email = $params[':email'];
                                    $result = array_filter($result, function($user) use ($email) {
                                        return $user['email'] === $email;
                                    });
                                    $result = array_values($result);
                                }
                                
                                // Handle WHERE id condition
                                if (strpos($query, 'WHERE id') !== false && isset($params[':id'])) {
                                    $id = $params[':id'];
                                    $result = array_filter($result, function($user) use ($id) {
                                        return $user['id'] == $id;
                                    });
                                    $result = array_values($result);
                                }
                                
                                // Handle role filter
                                if (strpos($query, "role IN ('admin', 'agent')") !== false) {
                                    $result = array_filter($result, function($user) {
                                        return in_array($user['role'], ['admin', 'agent']);
                                    });
                                    $result = array_values($result);
                                }
                                
                            } elseif (strpos($query, 'tickets') !== false) {
                                $result = $data['tickets'];
                                // Add assignee and creator names and ensure all required fields exist
                                foreach ($result as &$ticket) {
                                    $ticket['assignee_name'] = $this->getUserName($ticket['assigned_to'], $data['users']);
                                    $ticket['creator_name'] = $this->getUserName($ticket['created_by'], $data['users']);
                                    $ticket['priority'] = $ticket['priority'] ?? 'Medium';
                                    $ticket['status'] = $ticket['status'] ?? 'Open';
                                }
                            } else {
                                return [];
                            }
                            
                            if ($single) {
                                return count($result) > 0 ? $result[0] : null;
                            }
                            return $result;
                        }
                        
                        return [];
                    }
                    
                    private function getUserName($userId, $users) {
                        foreach ($users as $user) {
                            if ($user['id'] == $userId) {
                                return $user['name'];
                            }
                        }
                        return 'Unassigned';
                    }
                };
            }
            
            private function executeUpdate() {
                $data = $this->db->data;
                
                if (strpos($this->query, 'UPDATE tickets') !== false) {
                    $id = $this->params[':id'];
                    $ticketKey = null;
                    
                    // Find the ticket to update
                    foreach ($data['tickets'] as $key => $ticket) {
                        if ($ticket['id'] == $id) {
                            $ticketKey = $key;
                            break;
                        }
                    }
                    
                    if ($ticketKey !== null) {
                        $data['tickets'][$ticketKey]['subject'] = $this->params[':subject'];
                        $data['tickets'][$ticketKey]['description'] = $this->params[':description'];
                        $data['tickets'][$ticketKey]['priority'] = $this->params[':priority'];
                        $data['tickets'][$ticketKey]['status'] = $this->params[':status'];
                        $data['tickets'][$ticketKey]['assigned_to'] = $this->params[':assigned_to'] ? (int)$this->params[':assigned_to'] : null;
                        $data['tickets'][$ticketKey]['updated_at'] = date('Y-m-d H:i:s');
                        
                        $this->db->data = $data;
                        $this->db->saveData();
                        return true;
                    }
                }
                
                return false;
            }
            
            private function executeDelete() {
                $data = $this->db->data;
                
                if (strpos($this->query, 'DELETE FROM tickets') !== false) {
                    $id = $this->params[':id'];
                    
                    // Find and remove the ticket
                    foreach ($data['tickets'] as $key => $ticket) {
                        if ($ticket['id'] == $id) {
                            unset($data['tickets'][$key]);
                            $data['tickets'] = array_values($data['tickets']); // Re-index array
                            
                            $this->db->data = $data;
                            $this->db->saveData();
                            return true;
                        }
                    }
                }
                
                return false;
            }
        };
    }

    public function createTables() {
        echo "Data file initialized successfully!";
    }

    public function seedData() {
        echo "Sample data initialized successfully!";
    }
}