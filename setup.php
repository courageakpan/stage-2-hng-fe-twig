<?php
require_once 'config/database.php';

echo "Setting up Ticket Management System...\n\n";

$database = new Database();

try {
    echo "Setting up SQLite database...\n";
    
    // Create tables
    $database->createTables();
    echo "\nTables created successfully!\n";
    
    // Seed sample data
    $database->seedData();
    echo "\nSample data inserted successfully!\n";
    
    echo "\n✅ Setup completed successfully!\n";
    echo "You can now access the application at: http://localhost/ticket_management_twig/\n";
    echo "\nLogin credentials:\n";
    echo "Admin: john@example.com / password\n";
    echo "Agent: jane@example.com / password\n";
    echo "User: tom@example.com / password\n";
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "\nPlease make sure:\n";
    echo "1. MySQL is running\n";
    echo "2. Database credentials are correct in config/database.php\n";
    echo "3. User has CREATE DATABASE privileges\n";
}
?>