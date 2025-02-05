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
