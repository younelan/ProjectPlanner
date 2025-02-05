<?php
// Include configuration
require_once 'config.php';

// Explicitly include necessary classes
require_once 'classes/Database.php';
require_once 'classes/Project.php';
require_once 'classes/Issue.php';
require_once 'classes/ProjectController.php';
require_once 'classes/IssueController.php';

// Initialize the database connection
$db = Database::getInstance($config)->getConnection();

// Routing logic
$page = $_GET['page'] ?? 'projects'; // Default page
$action = $_GET['action'] ?? 'index'; // Default action
$id = isset($_GET['id']) ? intval($_GET['id']) : null; // Ensure ID is an integer

// Basic routing and controller dispatch
try {
    // if ($page === 'issues' && $action === 'view') {
    //     $controller = new IssueController($db);
    //     $controller->view($_GET['id']);
    // }
    
    if ($page === 'projects') {
        $controller = new ProjectController($db);
        if ($action === 'index') {
            $controller->index();
        } elseif ($action === 'view' && $id !== null) {
            $controller->view($id);
        } else {
            throw new Exception("Invalid action '$action' for projects.");
        }
    } elseif ($page === 'issues') {
        $controller = new IssueController($db);
        if ($action === 'view' && $id !== null) {
            $controller->view($id);
        }elseif ($action === 'list' && $id !== null) {
            $controller->list($id);
        } elseif ($action === 'search') {
            $controller->search();
        } else {
            throw new Exception("Invalid action '$action' for issues.");
        }
    } else {
        throw new Exception("Page '$page' not found.");
    }
} catch (Exception $e) {
    http_response_code(404);
    echo "Error: " . htmlspecialchars($e->getMessage());
}


