<?php
class IssueController {
    private $issueModel;
    private $projectModel;
    private $db;
    private $config;

    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
        $this->issueModel = new Issue($db);
        $this->projectModel = new Project($db);
    }

    public function view($id) {
        $issue = $this->issueModel->getIssueById($id);
        if (!$issue) {
            throw new Exception("Issue not found");
        }
        
        // Convert JIRA markup to HTML for the description
        $issue['DESCRIPTION'] = $this->convertJiraMarkupToHtml($issue['DESCRIPTION']);
        
        // Get project details for breadcrumb
        $project = $this->projectModel->getProjectById($issue['PROJECT']);
        $projectName = $project['PNAME']; // Get actual project name
        
        $linkedIssues = $this->issueModel->getLinkedIssues($issue['ID']);
        $history = $this->issueModel->getIssueHistory($id);
        $linkTypes = $this->issueModel->getAllLinkTypes();
        
        $appName = $this->config['name'];
        include 'views/issues/view.php';
    }
    
    public function list($projectId) {
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project) {
            throw new Exception("Project not found");
        }
        
        $issues = $this->issueModel->getProjectIssuesWithSubcomponents($projectId);
        $appName = $this->config['name'];  // Add this line
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
        $appName = $this->config['name'];  // Add this line
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

        // Get project details for breadcrumb
        $project = $this->projectModel->getProjectById($issue['PROJECT']);
        $projectName = $project['PNAME']; // Get actual project name

        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();
        $priorities = $this->issueModel->getAllPriorities();
        $issueTypes = $this->issueModel->getAllIssueTypes();
        $workflowModel = new Workflow($this->db);
        $statuses = $workflowModel->getWorkflowSteps($issue['PROJECT']);
        
        $appName = $this->config['name'];
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
            'status' => ['old' => $issue['ISSUESTATUS'], 'new' => $_POST['status']]  // Add status changes
        ];

        $data = array_merge($_POST, ['changes' => $changes]);
        $this->issueModel->updateIssue($id, $data);
        
        header("Location: index.php?page=issues&action=view&id=" . $id);
        exit;
    }

    public function addLink($id) {
        header('Content-Type: application/json');
        
        if (!isset($_POST['linkedIssueId']) || empty($_POST['linkedIssueId']) || 
            !isset($_POST['linkType']) || empty($_POST['linkType'])) {
            echo json_encode(['success' => false, 'error' => 'Please select an issue and link type']);
            exit;
        }

        try {
            $this->issueModel->addIssueLink(
                $id,
                $_POST['linkedIssueId'],
                $_POST['linkType']
            );
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to create link']);
        }
        exit;
    }

    public function deleteLink($issueId, $linkId) {
        if (!$linkId) {
            return ['success' => false, 'error' => 'Link ID is required'];
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM ISSUELINK WHERE ID = :linkId AND (SOURCE = :issueId OR DESTINATION = :issueId)");
            $result = $stmt->execute([
                ':linkId' => $linkId,
                ':issueId' => $issueId
            ]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Link not found'];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'error' => 'Database error while deleting link'];
        }
    }

    public function autocompleteIssues() {
        $term = $_GET['term'] ?? '';
        $projectId = $_GET['projectId'] ?? null;
        
        $issues = $this->issueModel->searchIssuesForAutocomplete($term, $projectId);
        
        header('Content-Type: application/json');
        echo json_encode($issues);
        exit;
    }

    // Keep existing create() method untouched for API usage
    public function create() {
        header('Content-Type: application/json');
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            exit;
        }
        
        // Get the issuetype ID from the type name
        $stmt = $this->db->prepare("SELECT ID FROM ISSUETYPE WHERE PNAME = ?");
        $stmt->execute([$data['ISSUETYPE']]);
        $issueTypeId = $stmt->fetchColumn();
        
        if (!$issueTypeId) {
            echo json_encode(['success' => false, 'message' => 'Invalid issue type']);
            exit;
        }
        
        // Map the incoming API data to match form-based creation format
        $mappedData = [
            'projectId' => $data['projectId'],
            'summary' => $data['SUMMARY'],
            'description' => $data['DESCRIPTION'] ?? '',
            'issuetype' => $issueTypeId,  // Use the ID instead of the name
            'priority' => $data['PRIORITY'],
            'reporter' => User::getCurrentUser(),
            'assignee' => $data['ASSIGNEE'],
            'status' => $data['STATUS_ID']  // This maps to issuestatus in the database
        ];
        
        try {
            $issueId = $this->issueModel->createIssue($mappedData);
            if ($issueId) {
                echo json_encode(['success' => true, 'issueId' => $issueId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error creating task']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Add new method for form-based creation
    public function new() {
        if (!isset($_GET['projectId'])) {
            throw new Exception("Project ID is required");
        }
        
        $projectId = $_GET['projectId'];
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project) {
            throw new Exception("Project not found");
        }

        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();
        $priorities = $this->issueModel->getAllPriorities();
        $issueTypes = $this->issueModel->getAllIssueTypes();
        $workflowModel = new Workflow($this->db);
        $statuses = $workflowModel->getWorkflowSteps($projectId);

        $appName = $this->config['name'];
        include 'views/issues/create.php';
    }

    public function store() {
        if (!isset($_POST['projectId'])) {
            throw new Exception("Project ID is required");
        }

        // Add status to the data being saved
        $_POST['status'] = $_POST['status'] ?? 'Open';  // Default to 'Open' if not set
        
        $issueId = $this->issueModel->createIssue($_POST);
        
        header("Location: index.php?page=issues&action=view&id=" . $issueId);
        exit;
    }
    private function convertJiraMarkupToHtml($text) {
        if (empty($text)) return '';
        
        // Convert headings (process first to avoid conflicts)
        $text = preg_replace('/h([1-6])\.\s*(.*?)(?:\n|$)/', '<h$1>$2</h$1>', $text);
        
        // Convert text formatting
        $text = preg_replace('/\*([^*\n]+)\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\_([^_\n]+)\_/', '<em>$1</em>', $text);
        $text = preg_replace('/\?\?([^?\n]+)\?\?/', '<cite>$1</cite>', $text);
        $text = preg_replace('/\-([^-\n]+)\-/', '<del>$1</del>', $text);
        $text = preg_replace('/\+([^+\n]+)\+/', '<ins>$1</ins>', $text);
        $text = preg_replace('/\^([^^\n]+)\^/', '<sup>$1</sup>', $text);
        $text = preg_replace('/\~([^~\n]+)\~/', '<sub>$1</sub>', $text);
        
        // Convert quotes
        $text = preg_replace('/\{quote\}(.*?)\{quote\}/s', '<blockquote>$1</blockquote>', $text);
        
        // Convert images
        $text = preg_replace('/!([^!\s]+)!/', '<img src="$1" alt="" />', $text);
        
        // Process links line by line with non-greedy matching
        $lines = explode("\n", $text);
        foreach ($lines as &$line) {
            // Process named links [text|url] first
            while (preg_match('/\[([^|\]]+)\|([^\]]+?)\]/', $line, $matches)) {
                $replacement = '<a href="' . htmlspecialchars($matches[2]) . '">' . htmlspecialchars($matches[1]) . '</a>';
                $line = substr_replace($line, $replacement, strpos($line, $matches[0]), strlen($matches[0]));
            }
            
            // Then process simple URL links [url]
            while (preg_match('/\[(https?:\/\/[^\]]+?)\]/', $line, $matches)) {
                $url = $matches[1];
                $replacement = '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>';
                $line = substr_replace($line, $replacement, strpos($line, $matches[0]), strlen($matches[0]));
            }
        }
        $text = implode("\n", $lines);
        
        // Process links - handle both formats and multiple links per line
        $text = preg_replace_callback('/\[([^|]+?)\|([^\]]+?)\]/', function($matches) {
            // Named links: [text|url]
            return '<a href="' . htmlspecialchars($matches[2]) . '">' . htmlspecialchars($matches[1]) . '</a>';
        }, $text);
        
        $text = str_replace("\r", "", $text); // Normalize line endings
        $lines = explode("\n", $text);
        
        foreach ($lines as &$line) {
            // Simple URL links: [http://example.com]
            $line = preg_replace_callback('/\[(http[s]?:\/\/[^\]]+)\]/', function($matches) {
                $url = trim($matches[1]);
                return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>';
            }, $line);
        }
        $text = implode("\n", $lines);
        
        // Fix link conversions - Handle both types separately and in correct order
        $text = preg_replace_callback('/(?:^|\s)\[([^|]+)\|([^\]]+)\]/', function($matches) {
            return ' <a href="' . $matches[2] . '">' . $matches[1] . '</a>';
        }, $text);  // Named links: [text|url]
        
        $text = preg_replace_callback('/(?:^|\s)\[(https?:\/\/[^\]\s]+)\]/', function($matches) {
            return ' <a href="' . $matches[1] . '">' . $matches[1] . '</a>';
        }, $text);  // Bare URLs: [url]
        
        // Convert nested mixed lists
        $lines = explode("\n", $text);
        $listHtml = '';
        $inList = false;
        $currentLevel = 0;
        $listStack = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^([#*-]+)\s+(.+)$/', $line, $matches)) {
                $markers = str_split($matches[1]);
                $level = count($markers);
                $content = $matches[2];
                
                if (!$inList) {
                    $inList = true;
                    $currentLevel = 1;
                    $listType = ($markers[0] === '#') ? 'ol' : 'ul';
                    $listHtml .= "<$listType class='jira-list'>";
                    $listStack[$currentLevel] = $listType;
                }
                
                // Handle level changes
                while ($currentLevel < $level) {
                    $currentLevel++;
                    $listType = ($markers[$currentLevel - 1] === '#') ? 'ol' : 'ul';
                    $listHtml .= "<$listType class='jira-list'>";
                    $listStack[$currentLevel] = $listType;
                }
                
                // Handle same level but different list type
                if ($currentLevel == $level) {
                    $newType = ($markers[$level - 1] === '#') ? 'ol' : 'ul';
                    if ($listStack[$currentLevel] !== $newType) {
                        $listHtml .= "</{$listStack[$currentLevel]}><$newType class='jira-list'>";
                        $listStack[$currentLevel] = $newType;
                    }
                }
                
                $listHtml .= "<li>$content</li>";
            } else {
                if ($inList) {
                    while ($currentLevel > 0) {
                        $listHtml .= "</{$listStack[$currentLevel]}>";
                        unset($listStack[$currentLevel]);
                        $currentLevel--;
                    }
                    $inList = false;
                }
                $listHtml .= $line . "\n";
            }
        }
        
        // Close any remaining open lists
        if ($inList) {
            while ($currentLevel > 0) {
                $listHtml .= "</{$listStack[$currentLevel]}>";
                $currentLevel--;
            }
        }
        
        $text = $listHtml;
        
        // Add CSS for list spacing directly in the HTML
        $text = '<style>
            .jira-list { margin: 0; padding-left: 25px; }
            .jira-list li { margin: 0; padding: 0; }
        </style>' . $text;
        
        // Convert line breaks last
        $text = nl2br($text);
        
        return $text;
    }

    public function delete($id) {
        try {
            $result = $this->issueModel->deleteIssue($id);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function board($id) {
        $project = $this->projectModel->getProjectById($id);
        if (!$project) {
            throw new Exception("Project not found");
        }
        
        // Get workflow steps for this project
        $workflowModel = new Workflow($this->db);
        $workflow = $workflowModel->getWorkflowSteps($id);
        
        // Get all users for assignment
        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();
        
        // Get all issues for the project
        $issues = $this->issueModel->getIssuesForBoard($id);
        
        // Prepare data array for view
        $data = [
            'project' => $project,
            'workflow' => $workflow,
            'users' => $users,
            'issues' => $issues
        ];
        
        $appName = $this->config['name'];
        include 'views/projects/board.php';
    }

    public function updateStatus() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['issueId']) || !isset($data['statusId'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        try {
            // Only update ISSUESTATUS field - don't try to update non-existent STATUS field
            $stmt = $this->db->prepare("UPDATE JIRAISSUE SET ISSUESTATUS = ? WHERE ID = ?");
            $stmt->execute([$data['statusId'], $data['issueId']]);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
