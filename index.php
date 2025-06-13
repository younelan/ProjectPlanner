<?php
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/UserController.php';
require_once 'classes/Project.php';
require_once 'classes/Issue.php';
require_once 'classes/ProjectController.php';
require_once 'classes/IssueController.php';
require_once 'classes/Workflow.php';
require_once 'classes/WorkflowController.php';
require_once 'models/Sprint.php';
require_once 'classes/SprintController.php';

// Define application constants
$appConfig=$config ?? [];
$appConfig['name'] = $config['name'] ?? 'Project Agile';

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
    else if ($page === 'users') {
        $controller = new UserController($db, $appConfig);
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'create':
                $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'edit':
                if ($id === null) throw new Exception("User ID required");
                $controller->edit($id);
                break;
            case 'update':
                if ($id === null) throw new Exception("User ID required");
                $controller->update($id);
                break;
            case 'delete':
                if ($id === null) throw new Exception("User ID required");
                $controller->delete($id);
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
                header('Content-Type: application/json');
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                    exit;
                }
                if (!isset($input['ids']) || !is_array($input['ids'])) {
                    echo json_encode(['success' => false, 'error' => 'Invalid or missing issue IDs']);
                    exit;
                }
                echo json_encode($controller->delete($input['ids']));
                exit;
                break;
            case 'updateStatus':
                header('Content-Type: application/json');
                $controller->updateStatus();
                break;
            case 'assign':
            case 'status':
            case 'move':
            case 'type':
            case 'bulkLink':    // Add explicit case for bulkLink
                header('Content-Type: application/json');
                $result = $controller->$action();
                echo json_encode($result);
                exit;
                break;
            default:
                throw new Exception("Invalid action: $action");
        }
    } 
    else if ($page === 'workflows') {
        $controller = new WorkflowController($db, $appConfig);
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'view':
                if ($id === null) throw new Exception("Workflow ID required");
                $controller->view($id);
                break;
            case 'edit':
                if ($id === null) throw new Exception("Workflow ID required");
                $controller->edit($id);
                break;
            case 'update':
                if ($id === null) throw new Exception("Workflow ID required");
                $controller->update($id);
                break;
            case 'duplicate':
                if ($id === null) throw new Exception("Workflow ID required");
                $controller->duplicate($id);
                break;
            case 'export':
                if ($id === null) throw new Exception("Workflow ID required");
                $controller->export($id);
                break;
            case 'import':
                $controller->import();
                break;
            default:
                $controller->index();
                break;
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
    error_log(print_r($_POST,true));
    error_log("Error: " . $e->getMessage());
    error_log($_SERVER['REQUEST_URI']);
    echo "Error: " . htmlspecialchars($e->getMessage());
}


