<?php

class SprintController {
    private $db;
    private $sprintModel;
    private $workflowModel;  // Add workflow model

    public function __construct($db) {
        $this->db = $db;
        $this->sprintModel = new Sprint($db);
        $this->workflowModel = new Workflow($db);  // Initialize workflow model
    }

    public function index() {
        // Get projectId from query string if available
        $projectId = isset($_GET['projectId']) ? intval($_GET['projectId']) : null;
        
        // Get all necessary data
        $sprints = $this->sprintModel->getAll($projectId);
        
        // If project ID is set, get project details
        $project = null;
        if ($projectId) {
            $projectModel = new Project($this->db);
            $project = $projectModel->getProjectById($projectId);
            $pageTitle = $project ? "Sprints - " . $project['PNAME'] : "Sprints";
        } else {
            $pageTitle = "All Sprints";
        }

        // Pass data to view
        $viewData = [
            'pageTitle' => $pageTitle,
            'sprints' => $sprints,
            'project' => $project
        ];
        
        // Extract variables for the view
        extract($viewData);
        
        include 'views/sprints/list.php';
    }

    public function board($id) {
        $sprint = $this->sprintModel->getById($id);
        if (!$sprint) {
            throw new Exception("Sprint not found");
        }

        // Get project details
        $projectModel = new Project($this->db);
        $project = $projectModel->getProjectById($sprint['PROJECT_ID']);
        
        if (isset($_GET['api'])) {
            // API endpoint for board data
            $workflowSteps = $this->workflowModel->getWorkflowSteps($sprint['PROJECT_ID']);
            $userModel = new User($this->db);
            
            echo json_encode([
                'workflow' => $workflowSteps,
                'issues' => $this->sprintModel->getSprintIssues($id),
                'sprint' => $sprint,
                'project' => $project,
                'users' => $userModel->getAllUsers()
            ]);
            exit;
        }

        // For initial page load
        $viewData = [
            'sprint' => $sprint,
            'projectName' => $project['PNAME'],
            'workflowSteps' => $this->workflowModel->getWorkflowSteps($sprint['PROJECT_ID'])  // For workflow details section
        ];
        extract($viewData);
        
        include 'views/sprints/board.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Invalid request method");
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        
        if (empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'Sprint name is required']);
            exit;
        }
        
        $response = $this->sprintModel->create($data);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Invalid request method");
        }
        
        $response = $this->sprintModel->update($id, $_POST);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
