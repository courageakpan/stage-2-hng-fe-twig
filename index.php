<?php
// Health check endpoint for Railway (absolute first thing)
if ($_SERVER['REQUEST_URI'] === '/health' || $_SERVER['REQUEST_URI'] === '/healthz') {
    header('Content-Type: application/json');
    header('HTTP/1.1 200 OK');
    echo json_encode(['status' => 'healthy', 'timestamp' => date('c')]);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type header
header('Content-Type: text/html; charset=UTF-8');

require_once 'vendor/autoload.php';
require_once 'config/database.php';
require_once 'models/Ticket.php';
require_once 'models/User.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Start session with proper settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', getenv('RAILWAY_ENVIRONMENT') ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Debug session at start
error_log("Session started at: " . date('Y-m-d H:i:s') . " Session ID: " . session_id());

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, [
    'cache' => false,
    'debug' => true
]);

// Initialize database and models
$database = new Database();
$db = $database->getConnection();
$ticket = new Ticket($db);
$user = new User($db);

// Simple routing
$page = $_GET['page'] ?? 'landing';
$action = $_GET['action'] ?? '';
$ticket_id = $_GET['id'] ?? '';
$edit_id = $_GET['edit'] ?? '';


// Handle form submissions
error_log("Request method: " . ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $userData = $user->login($email, $password);
        if ($userData) {
            $_SESSION['ticketapp_session'] = true;
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['user_name'] = $userData['name'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_role'] = $userData['role'];
            
            // Debug: Log session data
            error_log("Login successful. Session data: " . print_r($_SESSION, true));
            
            // Redirect to dashboard
            header('Location: ?page=dashboard');
            exit;
        } else {
            $error = 'Invalid email or password';
            error_log("Login failed for email: $email");
        }
    } elseif ($page === 'signup') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($user->register($name, $email, $password)) {
            // Redirect to login page with success message
            header('Location: ?page=login&registered=1');
            exit;
        } else {
            $error = 'Registration failed. Email might already exist.';
        }
    } elseif ($page === 'tickets' && $action === 'create') {
        $ticketData = [
            'subject' => $_POST['subject'] ?? '',
            'description' => $_POST['description'] ?? '',
            'priority' => $_POST['priority'] ?? 'Medium',
            'status' => $_POST['status'] ?? 'Open',
            'assigned_to' => $_POST['assigned_to'] ?? null,
            'created_by' => $_SESSION['user_id']
        ];
        
        if ($ticket->createTicket($ticketData)) {
            header('Location: ?page=tickets&success=created');
            exit;
        } else {
            $error = 'Failed to create ticket';
        }
    } elseif ($page === 'tickets' && $action === 'update' && $edit_id) {
        $ticketData = [
            'subject' => $_POST['subject'] ?? '',
            'description' => $_POST['description'] ?? '',
            'priority' => $_POST['priority'] ?? 'Medium',
            'status' => $_POST['status'] ?? 'Open',
            'assigned_to' => $_POST['assigned_to'] ?? null
        ];
        
        if ($ticket->updateTicket($edit_id, $ticketData)) {
            header('Location: ?page=tickets&success=updated');
            exit;
        } else {
            $error = 'Failed to update ticket';
        }
    } elseif ($page === 'tickets' && $action === 'delete' && $ticket_id) {
        if ($ticket->deleteTicket($ticket_id)) {
            header('Location: ?page=tickets&success=deleted');
            exit;
        } else {
            $error = 'Failed to delete ticket';
        }
    }
}

// Check authentication for protected pages
if (in_array($page, ['dashboard', 'tickets'])) {
    error_log("Checking authentication for page: $page. Session ID: " . session_id() . " Session data: " . print_r($_SESSION, true));
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        error_log("Authentication failed, redirecting to login");
        header('Location: ?page=login');
        exit;
    } else {
        error_log("Authentication successful for page: $page");
    }
}

// Handle success messages
$success = '';
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Account created successfully! Please login with your credentials.';
} elseif (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $success = 'Ticket created successfully!';
            break;
        case 'updated':
            $success = 'Ticket updated successfully!';
            break;
        case 'deleted':
            $success = 'Ticket deleted successfully!';
            break;
    }
}

// Get data for templates
$tickets = [];
$stats = [];
$agents = [];

if (in_array($page, ['dashboard', 'tickets'])) {
    $tickets = $ticket->getAllTickets();
    $stats = $ticket->getTicketStats();
    $agents = $user->getAgents();
    
    // Add styling classes to tickets
    foreach ($tickets as &$t) {
        $priority = $t['priority'] ?? 'Medium';
        $status = $t['status'] ?? 'Open';
        $t['priorityClass'] = $ticket->getPriorityClass($priority);
        $t['statusClass'] = $ticket->getStatusClass($status);
        $t['date'] = isset($t['created_at']) ? date('Y-m-d', strtotime($t['created_at'])) : date('Y-m-d');
    }
}

// Render the appropriate template
try {
    echo $twig->render('index.twig', [
        'page' => $page,
        'tickets' => $tickets,
        'stats' => $stats,
        'agents' => $agents,
        'user' => [
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? ''
        ],
        'error' => $error ?? '',
        'success' => $success ?? ''
    ]);
} catch (Exception $e) {
    die('Error loading template: ' . $e->getMessage());
}