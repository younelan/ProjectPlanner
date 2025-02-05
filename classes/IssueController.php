<?php
class IssueController {
    private $issueModel;
    private $projectModel;
    private $db = null;

    public function __construct($db) {
        $this->db = $db;
        $this->issueModel = new Issue($db);
        $this->projectModel = new Project($db);
    }

    public function view($id) {
        $issue = $this->issueModel->getIssueById($id);
        if (!$issue) {
            throw new Exception("Issue not found");
        }
        
        // Get project details for breadcrumb
        $project = $this->projectModel->getProjectById($issue['PROJECT']);
        $linkedIssues = $this->issueModel->getLinkedIssues($issue['ID']);
        $history = $this->issueModel->getIssueHistory($id);
        $linkTypes = $this->issueModel->getAllLinkTypes();
        
        include 'views/issues/view.php';
    }
    
    public function list($projectId) {
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project) {
            throw new Exception("Project not found");
        }
        
        $issues = $this->issueModel->getProjectIssuesWithSubcomponents($projectId);
        include 'views/issues/list.php';
    }

    public function search() {
        $searchTerm = $_GET['q'] ?? '';
        $projectId = $_GET['project'] ?? null;
        
        if ($searchTerm || $projectId) {
            $issues = $this->issueModel->searchIssues($searchTerm, $projectId);
        } else {
            $issues = [];
        }
        
        $projects = $this->projectModel->getAllProjects();
        include 'views/issues/search.php';
    }

    public function addComment($id) {
        if (!isset($_POST['comment']) || empty(trim($_POST['comment']))) {
            throw new Exception("Comment cannot be empty");
        }

        $comment = trim($_POST['comment']);
        $this->issueModel->addHistoryEntry($id, $comment, User::getCurrentUser());

        // Redirect back to issue view
        header("Location: index.php?page=issues&action=view&id=" . $id);
        exit;
    }

    public function edit($id) {
        $issue = $this->issueModel->getIssueById($id);
        if (!$issue) {
            throw new Exception("Issue not found");
        }

        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();
        $priorities = $this->issueModel->getAllPriorities();
        $issueTypes = $this->issueModel->getAllIssueTypes();
        
        include 'views/issues/edit.php';
    }

    public function update($id) {
        $issue = $this->issueModel->getIssueById($id);
        if (!$issue) {
            throw new Exception("Issue not found");
        }

        $changes = [
            'summary' => ['old' => $issue['SUMMARY'], 'new' => $_POST['summary']],
            'description' => ['old' => $issue['DESCRIPTION'], 'new' => $_POST['description']],
            'assignee' => ['old' => $issue['ASSIGNEE'], 'new' => $_POST['assignee']],
            'reporter' => ['old' => $issue['REPORTER'], 'new' => $_POST['reporter']],
            'priority' => ['old' => $issue['PRIORITY'], 'new' => $_POST['priority']],
            'issuetype' => ['old' => $issue['ISSUETYPE'], 'new' => $_POST['issuetype']],
        ];

        $data = array_merge($_POST, ['changes' => $changes]);
        $this->issueModel->updateIssue($id, $data);
        
        header("Location: index.php?page=issues&action=view&id=" . $id);
        exit;
    }

    public function addLink($id) {
        if (!isset($_POST['linkedIssueId']) || !isset($_POST['linkType'])) {
            throw new Exception("Missing required fields");
        }

        $this->issueModel->addIssueLink(
            $id,
            $_POST['linkedIssueId'],
            $_POST['linkType']
        );

        header("Location: index.php?page=issues&action=view&id=" . $id);
        exit;
    }

    public function deleteLink($id) {
        if (!isset($_POST['linkId'])) {
            throw new Exception("Link ID is required");
        }

        $this->issueModel->deleteIssueLink($_POST['linkId']);
        
        // Return JSON response for AJAX request
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function autocompleteIssues() {
        $term = $_GET['term'] ?? '';
        $projectId = $_GET['projectId'] ?? null;
        
        $issues = $this->issueModel->searchIssuesForAutocomplete($term, $projectId);
        
        header('Content-Type: application/json');
        echo json_encode($issues);
        exit;
    }
}
?>
