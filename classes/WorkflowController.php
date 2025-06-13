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

        // Check if this is coming from visual editor or XML editor
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] === 'visual') {
            // Process visual editor data - rebuild XML from form data
            $data = [
                'name' => $_POST['name'] ?? '',
                'locked' => $_POST['locked'] ?? 'N',
                'scheme_name' => $_POST['scheme_name'] ?? null,
                'descriptor' => $this->buildXMLFromVisualData($_POST)
            ];
        } else {
            // Process XML editor data - use raw XML
            $data = [
                'name' => $_POST['name'] ?? '',
                'descriptor' => $_POST['descriptor'] ?? '',
                'locked' => $_POST['locked'] ?? 'N',
                'scheme_name' => $_POST['scheme_name'] ?? null
            ];
        }

        try {
            $this->workflowModel->updateWorkflow($id, $data);
            header("Location: index.php?page=workflows&action=view&id=" . $id);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $workflow = $this->workflowModel->getWorkflowDetails($id);
            $appName = $this->config['name'];
            
            // Return to appropriate editor based on mode
            if (isset($_POST['edit_mode']) && $_POST['edit_mode'] === 'visual') {
                include 'views/workflows/edit_visual.php';
            } else {
                include 'views/workflows/edit.php';
            }
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

    private function buildXMLFromVisualData($postData) {
        // Start with the basic XML structure
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<!DOCTYPE workflow PUBLIC "-//OpenSymphony Group//DTD OSWorkflow 2.8//EN" "http://www.opensymphony.com/osworkflow/workflow_2_8.dtd">' . "\n";
        $xml .= '<workflow>' . "\n";

        // Add workflow metadata
        if (isset($postData['meta_names']) && isset($postData['meta_values'])) {
            $metaNames = $postData['meta_names'];
            $metaValues = $postData['meta_values'];
            for ($i = 0; $i < count($metaNames); $i++) {
                if (!empty($metaNames[$i])) {
                    $xml .= '  <meta name="' . htmlspecialchars($metaNames[$i]) . '">' . htmlspecialchars($metaValues[$i] ?? '') . '</meta>' . "\n";
                }
            }
        }

        // Add initial actions
        if (isset($postData['initial_actions']) && is_array($postData['initial_actions'])) {
            $xml .= '  <initial-actions>' . "\n";
            foreach ($postData['initial_actions'] as $action) {
                if (!empty($action['id']) || !empty($action['name'])) {
                    $xml .= '    <action id="' . htmlspecialchars($action['id'] ?? '') . '" name="' . htmlspecialchars($action['name'] ?? '') . '"';
                    if (!empty($action['view'])) {
                        $xml .= ' view="' . htmlspecialchars($action['view']) . '"';
                    }
                    $xml .= '>' . "\n";

                    // Add action meta
                    if (isset($action['meta_names']) && isset($action['meta_values'])) {
                        for ($i = 0; $i < count($action['meta_names']); $i++) {
                            if (!empty($action['meta_names'][$i])) {
                                $xml .= '      <meta name="' . htmlspecialchars($action['meta_names'][$i]) . '">' . htmlspecialchars($action['meta_values'][$i] ?? '') . '</meta>' . "\n";
                            }
                        }
                    }

                    // Add validators
                    if (isset($action['validators']) && is_array($action['validators'])) {
                        $xml .= '      <validators>' . "\n";
                        foreach ($action['validators'] as $validator) {
                            $xml .= '        <validator name="' . htmlspecialchars($validator['name'] ?? '') . '" type="' . htmlspecialchars($validator['type'] ?? '') . '">' . "\n";
                            if (isset($validator['arg_names']) && isset($validator['arg_values'])) {
                                for ($i = 0; $i < count($validator['arg_names']); $i++) {
                                    if (!empty($validator['arg_names'][$i])) {
                                        $xml .= '          <arg name="' . htmlspecialchars($validator['arg_names'][$i]) . '">' . htmlspecialchars($validator['arg_values'][$i] ?? '') . '</arg>' . "\n";
                                    }
                                }
                            }
                            $xml .= '        </validator>' . "\n";
                        }
                        $xml .= '      </validators>' . "\n";
                    }

                    // Add conditions (restrict-to)
                    if (isset($action['conditions']) && is_array($action['conditions'])) {
                        $xml .= '      <restrict-to>' . "\n";
                        $xml .= '        <conditions>' . "\n";
                        foreach ($action['conditions'] as $condition) {
                            $xml .= '          <condition type="' . htmlspecialchars($condition['type'] ?? '') . '">' . "\n";
                            // Add condition args if needed
                            $xml .= '          </condition>' . "\n";
                        }
                        $xml .= '        </conditions>' . "\n";
                        $xml .= '      </restrict-to>' . "\n";
                    }

                    // Add results
                    if (isset($action['results']) && is_array($action['results'])) {
                        $xml .= '      <results>' . "\n";
                        foreach ($action['results'] as $result) {
                            $xml .= '        <unconditional-result';
                            if (!empty($result['old_status'])) {
                                $xml .= ' old-status="' . htmlspecialchars($result['old_status']) . '"';
                            }
                            if (!empty($result['status'])) {
                                $xml .= ' status="' . htmlspecialchars($result['status']) . '"';
                            }
                            if (!empty($result['step'])) {
                                $xml .= ' step="' . htmlspecialchars($result['step']) . '"';
                            }
                            $xml .= '>' . "\n";
                            
                            // Add post-functions
                            if (isset($result['functions']) && is_array($result['functions'])) {
                                $xml .= '          <post-functions>' . "\n";
                                foreach ($result['functions'] as $function) {
                                    if (!empty($function['type'])) {
                                        $xml .= '            <function type="' . htmlspecialchars($function['type']) . '">' . "\n";
                                        if (!empty($function['class'])) {
                                            $xml .= '              <arg name="class.name">' . htmlspecialchars($function['class']) . '</arg>' . "\n";
                                        }
                                        $xml .= '            </function>' . "\n";
                                    }
                                }
                                $xml .= '          </post-functions>' . "\n";
                            }
                            
                            $xml .= '        </unconditional-result>' . "\n";
                        }
                        $xml .= '      </results>' . "\n";
                    }

                    $xml .= '    </action>' . "\n";
                }
            }
            $xml .= '  </initial-actions>' . "\n";
        }

        // Add common actions (similar structure to initial actions)
        if (isset($postData['common_actions']) && is_array($postData['common_actions'])) {
            $xml .= '  <common-actions>' . "\n";
            foreach ($postData['common_actions'] as $action) {
                if (!empty($action['id']) || !empty($action['name'])) {
                    $xml .= '    <action id="' . htmlspecialchars($action['id'] ?? '') . '" name="' . htmlspecialchars($action['name'] ?? '') . '"';
                    if (!empty($action['view'])) {
                        $xml .= ' view="' . htmlspecialchars($action['view']) . '"';
                    }
                    $xml .= '>' . "\n";

                    // Add action meta
                    if (isset($action['meta_names']) && isset($action['meta_values'])) {
                        for ($i = 0; $i < count($action['meta_names']); $i++) {
                            if (!empty($action['meta_names'][$i])) {
                                $xml .= '      <meta name="' . htmlspecialchars($action['meta_names'][$i]) . '">' . htmlspecialchars($action['meta_values'][$i] ?? '') . '</meta>' . "\n";
                            }
                        }
                    }

                    $xml .= '    </action>' . "\n";
                }
            }
            $xml .= '  </common-actions>' . "\n";
        }

        // Add workflow steps
        if (isset($postData['steps']) && is_array($postData['steps'])) {
            $xml .= '  <steps>' . "\n";
            foreach ($postData['steps'] as $step) {
                if (!empty($step['id']) || !empty($step['name'])) {
                    $xml .= '    <step id="' . htmlspecialchars($step['id'] ?? '') . '" name="' . htmlspecialchars($step['name'] ?? '') . '">' . "\n";

                    // Add step meta
                    if (isset($step['meta_names']) && isset($step['meta_values'])) {
                        for ($i = 0; $i < count($step['meta_names']); $i++) {
                            if (!empty($step['meta_names'][$i])) {
                                $xml .= '      <meta name="' . htmlspecialchars($step['meta_names'][$i]) . '">' . htmlspecialchars($step['meta_values'][$i] ?? '') . '</meta>' . "\n";
                            }
                        }
                    }

                    // Add step actions
                    if (isset($step['actions']) && is_array($step['actions'])) {
                        $xml .= '      <actions>' . "\n";
                        foreach ($step['actions'] as $stepAction) {
                            if ($stepAction['type'] === 'common' && !empty($stepAction['common_id'])) {
                                $xml .= '        <common-action id="' . htmlspecialchars($stepAction['common_id']) . '" />' . "\n";
                            } elseif ($stepAction['type'] === 'step' && (!empty($stepAction['id']) || !empty($stepAction['name']))) {
                                $xml .= '        <action id="' . htmlspecialchars($stepAction['id'] ?? '') . '" name="' . htmlspecialchars($stepAction['name'] ?? '') . '"';
                                if (!empty($stepAction['view'])) {
                                    $xml .= ' view="' . htmlspecialchars($stepAction['view']) . '"';
                                }
                                $xml .= '>' . "\n";
                                // Add step action details (meta, validators, conditions, results) if needed
                                $xml .= '        </action>' . "\n";
                            }
                        }
                        $xml .= '      </actions>' . "\n";
                    }

                    $xml .= '    </step>' . "\n";
                }
            }
            $xml .= '  </steps>' . "\n";
        }

        $xml .= '</workflow>';

        return $xml;
    }
}
?>