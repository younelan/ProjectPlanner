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
    public function __construct($db) {
        // Ensure we store $db into $this->db so that $this->db is not null
        $this->db = $db;
        $this->projectModel = new Project($db);
        $this->issueModel = new Issue($db);
        $this->pdo=$db;
    }

    public function index() {
        $projects = $this->projectModel->getAllProjects();
        include __DIR__ . '/../views/projects/list.php';
    }

    public function view($id) {
        $project = $this->projectModel->getProjectById($id);
        if (!$project) {
            throw new Exception("Project not found");
        }
        
        if (empty($project['PKEY'])) {
            throw new Exception("Invalid project configuration: missing PKEY");
        }
        
        $issues = $this->issueModel->getIssuesByProject($project['ID']);
        $issuesWithLinks = $this->getIssueLinks($issues);
        
        include 'views/projects/view.php';
    }

    public function board($id) {
        $project = $this->projectModel->getProjectById($id);
        if (!$project) {
            throw new Exception("Project not found");
        }

        if (isset($_GET['api'])) {
            // API endpoint for board data
            $workflowModel = new Workflow($this->db);
            $workflowSteps = $workflowModel->getWorkflowSteps($project['ID']);
            $issues = $this->issueModel->getIssuesForBoard($id);
            
            echo json_encode([
                'workflow' => $workflowSteps,
                'issues' => $issues,
                'project' => $project
            ]);
            exit;
        }

        // For initial page load, get workflow steps for the template
        $workflowModel = new Workflow($this->db);
        $workflowSteps = $workflowModel->getWorkflowSteps($project['ID']);
        
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
        $projects = $this->projectModel->getAllProjects();
        include 'views/projects/create.php';
    }

    public function store() {
        try {
            $this->validateProjectData($_POST);
            
            // Create the project
            $newProjectId = $this->projectModel->createProject($_POST);
            
            $_SESSION['message'] = 'Project created successfully';
            header('Location: index.php?page=projects');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?page=projects&action=create');
            exit;
        }
    }

    private function validateProjectData($data) {
        if (empty($data['PNAME']) || empty($data['PKEY']) || empty($data['LEAD'])) {
            throw new Exception('Required fields are missing');
        }

        if (!preg_match('/^[A-Z0-9]+$/', $data['PKEY'])) {
            throw new Exception('Project key must contain only uppercase letters and numbers');
        }

        if (!$this->projectModel->validateProjectKey($data['PKEY'])) {
            throw new Exception('Project key already exists');
        }
    }

    private function getProjectDetails($projectId) {
        // Example SQL to fetch project details
        $query = "SELECT * FROM PROJECT WHERE ID = :projectId";
        $stmt = $this->pdo->prepare($query);
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
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':issueId' => $issue['ID']]);
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $issueLinks[$issue['ID']] = $links;
        }

        return $issueLinks;
    }
}
