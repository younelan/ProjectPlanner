<?php
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Project.php';
require_once 'classes/Issue.php';
require_once 'classes/ProjectController.php';
require_once 'classes/IssueController.php';
require_once 'classes/Workflow.php';
require_once 'models/Sprint.php';
require_once 'classes/SprintController.php';

// Define application constants
$appConfig = [
    'name' => 'Project Agile'
];

$db = Database::getInstance($config)->getConnection();
$page = $_GET['page'] ?? 'projects';
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

try {
    if ($page === 'projects') {
        $controller = new ProjectController($db, $appConfig);
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'view':
                if ($id === null) throw new Exception("Project ID required");
                $controller->view($id);
                break;
            case 'create':
                $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'edit':
                if ($id === null) throw new Exception("Project ID required");
                $controller->edit($id);
                break;
            case 'update':
                if ($id === null) throw new Exception("Project ID required");
                $controller->update($id);
                break;
            case 'board':
                if ($id === null) throw new Exception("Project ID required");
                $controller->board($id);
                break;
            default:
                throw new Exception("Invalid action: $action");
        }
    } 
    else if ($page === 'issues') {
        $controller = new IssueController($db, $appConfig);
        switch ($action) {
            case 'view':
                if ($id === null) throw new Exception("Issue ID required");
                $controller->view($id);
                break;
            case 'edit':
                if ($id === null) throw new Exception("Issue ID required");
                $controller->edit($id);
                break;
            case 'update':
                if ($id === null) throw new Exception("Issue ID required");
                $controller->update($id);
                break;
            case 'addLink':
                if ($id === null) throw new Exception("Issue ID required");
                $controller->addLink($id);
                break;
            case 'deleteLink':
                if ($id === null) throw new Exception("Issue ID required");
                $linkId = isset($_GET['linkId']) ? intval($_GET['linkId']) : null;
                header('Content-Type: application/json');
                echo json_encode($controller->deleteLink($id, $linkId));
                exit;
                break;
            case 'list':
                if ($id === null) throw new Exception("Project ID required");
                $controller->list($id);
                break;
            case 'autocompleteIssues':
                $controller->autocompleteIssues();
                break;
            case 'search':
                $controller->search();
                break;
            case 'addComment':
                if ($id === null) throw new Exception("Issue ID required");
                $controller->addComment($id);
                break;
            case 'new':
                $controller->new();
                break;
            case 'create':
                $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'delete':
                if ($id === null) throw new Exception("Issue ID required");
                header('Content-Type: application/json');
                echo json_encode($controller->delete($id));
                exit;
                break;
            case 'updateStatus':
                header('Content-Type: application/json');
                $controller->updateStatus();
                break;
            default:
                throw new Exception("Invalid action: $action");
        }
    } 
    else if ($page === 'sprints') {
        $controller = new SprintController($db, $appConfig);
        switch ($action) {
            case 'list':
                $controller->index();
                break;
            case 'board':
                if ($id === null) throw new Exception("Sprint ID required");
                $controller->board($id);
                break;
            case 'create':
                $controller->create();
                break;
            case 'update':
                if ($id === null) throw new Exception("Sprint ID required");
                $controller->update($id);
                break;
            default:
                $controller->index();
                break;
        }
    } 
    else {
        throw new Exception("Invalid page: $page");
    }
} catch (Exception $e) {
    http_response_code(404);
    echo "Error: " . htmlspecialchars($e->getMessage());
}


