<?php
class WorkflowController {
    private $workflowModel;
    private $db;
    private $config;

    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
        $this->workflowModel = new Workflow($db);
    }

    public function index() {
        $workflows = $this->workflowModel->getAllWorkflows();
        $appName = $this->config['name'];
        include 'views/workflows/index.php';
    }

    public function view($id) {
        if (!$id) {
            throw new Exception("Workflow ID required");
        }

        $workflow = $this->workflowModel->getWorkflowDetails($id);
        if (!$workflow) {
            throw new Exception("Workflow not found");
        }

        $appName = $this->config['name'];
        include 'views/workflows/view.php';
    }

    public function edit($id) {
        if (!$id) {
            throw new Exception("Workflow ID required");
        }

        $workflow = $this->workflowModel->getWorkflowDetails($id);
        if (!$workflow) {
            throw new Exception("Workflow not found");
        }

        $appName = $this->config['name'];
        include 'views/workflows/edit.php';
    }

    public function editVisual($id) {
        if (!$id) {
            throw new Exception("Workflow ID required");
        }

        $workflow = $this->workflowModel->getWorkflowDetails($id);
        if (!$workflow) {
            throw new Exception("Workflow not found");
        }

        $appName = $this->config['name'];
        include 'views/workflows/edit_visual.php';
    }

    public function update($id) {
        if (!$id) {
            throw new Exception("Workflow ID required");
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'descriptor' => $_POST['descriptor'] ?? '',
            'locked' => $_POST['locked'] ?? 'N',
            'scheme_name' => $_POST['scheme_name'] ?? null
        ];

        try {
            $this->workflowModel->updateWorkflow($id, $data);
            header("Location: index.php?page=workflows&action=view&id=" . $id);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $workflow = $this->workflowModel->getWorkflowDetails($id);
            $appName = $this->config['name'];
            include 'views/workflows/edit.php';
        }
    }

    public function duplicate($id) {
        if (!$id) {
            throw new Exception("Workflow ID required");
        }

        $workflow = $this->workflowModel->getWorkflowDetails($id);
        if (!$workflow) {
            throw new Exception("Workflow not found");
        }

        // Create a new project ID for the duplicate
        $newProjectId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM JIRAWORKFLOWS")->fetchColumn();
        
        try {
            $this->workflowModel->cloneWorkflow($id, $newProjectId, "Copy of " . $workflow['WORKFLOWNAME']);
            header("Location: index.php?page=workflows&action=view&id=" . $newProjectId);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $workflows = $this->workflowModel->getAllWorkflows();
            $appName = $this->config['name'];
            include 'views/workflows/index.php';
        }
    }

    public function export($id) {
        if (!$id) {
            throw new Exception("Workflow ID required");
        }

        $workflow = $this->workflowModel->getWorkflowDetails($id);
        if (!$workflow) {
            throw new Exception("Workflow not found");
        }

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="workflow_' . $id . '.xml"');
        echo $workflow['DESCRIPTOR'];
        exit;
    }

    public function import() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_FILES['workflow_file']) || $_FILES['workflow_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Please select a valid XML file");
            }

            $xmlContent = file_get_contents($_FILES['workflow_file']['tmp_name']);
            
            // Validate XML
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                throw new Exception("Invalid XML format");
            }

            $projectId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM JIRAWORKFLOWS")->fetchColumn();
            $workflowName = $_POST['workflow_name'] ?? 'Imported Workflow';

            try {
                $this->workflowModel->createWorkflowFromXMLTemplate($projectId, $workflowName, $xmlContent);
                header("Location: index.php?page=workflows&action=view&id=" . $projectId);
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
                $appName = $this->config['name'];
                include 'views/workflows/import.php';
            }
        } else {
            $appName = $this->config['name'];
            include 'views/workflows/import.php';
        }
    }
}
?>