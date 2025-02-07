<?php
// class ProjectController {


//     public function view($id) {
//         $project = $this->projectModel->getProjectById($id);
//         if (!$project) {
//             die('Project not found');
//         }
//         $issues = $this->issueModel->getIssuesByProject($id);
//         include __DIR__ . '/../views/projects/view.php';
//     }
// }

class ProjectController {
    private $projectModel;
    private $issueModel;
    private $db;
    private $config;

    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
        $this->projectModel = new Project($db);
        $this->issueModel = new Issue($db);
        $this->pdo = $db;
    }

    public function index() {
        $projects = $this->projectModel->getAllProjects();
        $appName = $this->config['name'];
        $viewData = [
            'appName' => $appName,
            'projects' => $projects
        ];
        extract($viewData);
        include __DIR__ . '/../views/projects/list.php';
    }

    public function view($id) {
        $project = $this->projectModel->getProjectById($id);
        if (!$project) {
            throw new Exception("Project not found");
        }
        
        $issues = $this->issueModel->getIssuesForBoard($id);

        // Get data needed for bulk actions
        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();
        $allProjects = $this->projectModel->getAllProjects();
        
        // Get link types for issue linking
        $linkTypes = $this->issueModel->getAllLinkTypes();
        
        // Get workflow statuses properly
        $workflowModel = new Workflow($this->db);
        $statuses = $workflowModel->getWorkflowSteps($id);
        
        // Ensure we have proper status data for the project
        if (empty($statuses)) {
            // Fallback to default statuses if no workflow is defined
            $statuses = [
                ['ID' => 1, 'PNAME' => 'Open'],
                ['ID' => 2, 'PNAME' => 'In Progress'],
                ['ID' => 3, 'PNAME' => 'Resolved'],
                ['ID' => 4, 'PNAME' => 'Closed']
            ];
        }

        // Get available issue types
        $issueTypes = $this->issueModel->getAllIssueTypes();

        $appName = $this->config['name'];
        include 'views/projects/view.php';
    }

    public function board($id) {
        $project = $this->projectModel->getProjectById($id);
        if (!$project) {
            throw new Exception("Project not found");
        }

        // Get workflow steps and users for both API and page load
        $workflowModel = new Workflow($this->db);
        $workflowSteps = $workflowModel->getWorkflowSteps($project['ID']);
        
        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();

        $issues = $this->issueModel->getIssuesForBoard($id);

        if (isset($_GET['api'])) {
            // API endpoint for board data
            echo json_encode([
                'workflow' => $workflowSteps,
                'issues' => $issues,
                'project' => $project,
                'users' => $users  // Add users to API response
            ]);
            exit;
        }

        // For initial page load, pass data to template
        $workflow = $workflowSteps; // Make workflow available to the view
        $appName = $this->config['name'];
        include 'views/projects/board.php';
    }

    public function edit($id) {
        $project = $this->projectModel->getProjectById($id);
        if (!$project) {
            throw new Exception("Project not found");
        }
        // Load users for the project lead select box
        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();
        
        // [To Do #1] Load workflow phases and count tasks per phase and per task type
        $workflowModel = new Workflow($this->db);
        $workflowPhases = $workflowModel->getWorkflowSteps($project['ID']); // use phases; PNAME is used as title in board.php
        $issues = $this->issueModel->getIssuesForBoard($project['ID']);
        $totalTasks = 0;
        foreach ($workflowPhases as &$phase) {
            $phase['taskCount'] = 0;
            $phase['tasksByType'] = [];
            foreach ($issues as $issue) {
                if (isset($issue['STATUS_ID']) && $issue['STATUS_ID'] == $phase['ID']) {
                    $phase['taskCount']++;
                    $totalTasks++;
                    $type = $issue['TYPE'] ?? 'Other';
                    if (isset($phase['tasksByType'][$type])) {
                        $phase['tasksByType'][$type]++;
                    } else {
                        $phase['tasksByType'][$type] = 1;
                    }
                }
            }
        }
        // Aggregate tasks by type over all phases
        $tasksByTypeAggregate = [];
        foreach ($workflowPhases as $phase) {
            foreach ($phase['tasksByType'] as $type => $count) {
                $tasksByTypeAggregate[$type] = ($tasksByTypeAggregate[$type] ?? 0) + $count;
            }
        }
        $totalPhases = count($workflowPhases);
        $averageTasks = $totalPhases > 0 ? round($totalTasks / $totalPhases, 2) : 0;
        $workflowStats = [
            'totalTasks'   => $totalTasks,
            'totalPhases'  => $totalPhases,
            'averageTasks' => $averageTasks,
            'tasksByTypeAggregate' => $tasksByTypeAggregate
        ];
        
        $appName = $this->config['name'];
        // Pass $workflowPhases and $workflowStats to the view
        include __DIR__ . '/../views/projects/edit.php';
    }
    
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Invalid request method");
        }
        $data = [
            'PNAME'       => $_POST['PNAME'] ?? '',
            'URL'         => $_POST['URL'] ?? '',
            'DESCRIPTION' => $_POST['DESCRIPTION'] ?? '',
            'LEAD'        => $_POST['LEAD'] ?? '',
            'PKEY'        => $_POST['PKEY'] ?? '',
            'PROJECTTYPE' => $_POST['PROJECTTYPE'] ?? '',
            'ORIGINALKEY' => $_POST['ORIGINALKEY'] ?? '',
        ];
        $updated = $this->projectModel->updateProject($id, $data);
        if ($updated) {
            header("Location: index.php?page=projects&action=view&id=" . $id);
            exit;
        } else {
            throw new Exception("Update failed");
        }
    }

    public function create() {
        // Get all projects for workflow cloning dropdown
        $projects = $this->projectModel->getAllProjects();
        // Get all users for project lead dropdown
        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();
        
        // Include any error messages from session
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        
        $appName = $this->config['name'];
        include 'views/projects/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->create();
            return;
        }

        try {
            $this->validateProjectData($_POST);
            $newProjectId = $this->projectModel->createProject($_POST);
            
            if (!$newProjectId) {
                throw new Exception('Failed to create project');
            }
            
            // Only redirect on success
            header('Location: index.php?page=projects&action=view&id=' . $newProjectId);
            exit;
        } catch (Exception $e) {
            // On failure, show form again with error
            $projects = $this->projectModel->getAllProjects(); // Fixed syntax error here
            $userModel = new User($this->db);
            $users = $userModel->getAllUsers();
            $error = $e->getMessage();
            
            $appName = $this->config['name'];
            include 'views/projects/create.php';
        }
    }

    private function redirectWithError($action, $error, $formData = null) {
        $params = ['page' => 'projects', 'action' => $action];
        if ($error) {
            $params['error'] = $error;
        }
        if ($formData) {
            // Store form data in query string for repopulation
            $params = array_merge($params, $formData);
        }
        header('Location: index.php?' . http_build_query($params));
        exit;
    }

    private function validateProjectData($data) {
        $errors = [];

        if (empty($data['PNAME'])) {
            $errors[] = 'Project name is required';
        }

        if (empty($data['PKEY'])) {
            $errors[] = 'Project key is required';
        } elseif (!preg_match('/^[A-Z0-9]+$/', $data['PKEY'])) {
            $errors[] = 'Project key must contain only uppercase letters and numbers';
        } elseif (!$this->projectModel->validateProjectKey($data['PKEY'])) {
            $errors[] = 'Project key already exists';
        }

        if (empty($data['LEAD'])) {
            $errors[] = 'Project lead is required';
        }

        if (empty($data['clone_project_id'])) {
            $errors[] = 'Source project for workflow is required';
        }

        if (!empty($errors)) {
            throw new Exception(implode("\n", $errors));
        }
    }

    private function getProjectDetails($projectId) {
        // Example SQL to fetch project details
        $query = "SELECT * FROM PROJECT WHERE ID = :projectId";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':projectId' => $projectId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getIssueLinks($issues) {
        $issueLinks = [];

        // Loop over issues and fetch links for each
        foreach ($issues as $issue) {
            $query = "
                SELECT 
                    IL.ID AS link_id,
                    IL.LINKTYPE,
                    IL.SOURCE,
                    IL.DESTINATION,
                    ILT.LINKNAME,
                    ILT.INWARD,
                    ILT.OUTWARD
                FROM ISSUELINK AS IL
                JOIN ISSUELINKTYPE AS ILT ON IL.LINKTYPE = ILT.ID
                WHERE IL.SOURCE = :issueId OR IL.DESTINATION = :issueId
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':issueId' => $issue['ID']]);
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $issueLinks[$issue['ID']] = $links;
        }

        return $issueLinks;
    }
}
