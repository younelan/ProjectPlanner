<?php
class Workflow {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getWorkflowSteps($id) {
        $stmt = $this->db->prepare("
            SELECT DESCRIPTOR 
            FROM JIRAWORKFLOWS
            WHERE ID = :id
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['DESCRIPTOR'])) {
            return [];
        }

        $xml = new SimpleXMLElement($row['DESCRIPTOR']);
        $allStatuses = [];
        foreach ($xml->steps->step as $step) {
            $allStatuses[(string)$step->meta[0]] = [
                'ID' => (string)$step->meta[0],  // e.g. 10000
                'PNAME' => (string)$step['name'], // e.g. "To Do"
                'DESCRIPTION' => '',  // Can be added if needed
                'ICONURL' => '',     // Can be added if needed
                'SEQUENCE' => (string)$step['id'] // Using step ID as sequence
            ];
        }
        return $allStatuses;
    }

    public function cloneWorkflow($sourceProjectId, $newProjectId, $projectName) {
        try {
            // Get workflow from source project's workflow scheme
            $stmt = $this->db->prepare("
                SELECT w.* 
                FROM JIRAWORKFLOWS w
                JOIN WORKFLOWSCHEMEENTITY wse ON wse.WORKFLOW = w.WORKFLOWNAME
                JOIN WORKFLOWSCHEME ws ON ws.ID = wse.SCHEME
                WHERE ws.ID = :sourceProjectId
                LIMIT 1
            ");
            
            $stmt->execute([':sourceProjectId' => $sourceProjectId]);
            $sourceWorkflow = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sourceWorkflow) {
                error_log("No workflow found for project ID: $sourceProjectId");
                throw new Exception("No workflow found in source project");
            }

            $workflowName = "Software Simplified Workflow for " . $projectName;

            // 1. Create workflow scheme
            $stmt = $this->db->prepare("
                INSERT INTO WORKFLOWSCHEME (ID, NAME, DESCRIPTION)
                VALUES (:id, :name, :description)
            ");
            
            $stmt->execute([
                ':id' => $newProjectId,
                ':name' => $workflowName,
                ':description' => 'Default workflow scheme'
            ]);

            // 2. Create workflow scheme entity
            $stmt = $this->db->prepare("
                INSERT INTO WORKFLOWSCHEMEENTITY (SCHEME, WORKFLOW, ISSUETYPE)
                VALUES (:scheme, :workflow, 0)
            ");
            
            $stmt->execute([
                ':scheme' => $newProjectId,
                ':workflow' => $workflowName
            ]);

            // 3. Create workflow
            $stmt = $this->db->prepare("
                INSERT INTO JIRAWORKFLOWS (
                    ID, WORKFLOWNAME, CREATORNAME, DESCRIPTOR, ISLOCKED
                ) VALUES (
                    :id, :workflowName, :creatorName, :descriptor, :isLocked
                )
            ");

            $result = $stmt->execute([
                ':id' => $newProjectId,
                ':workflowName' => $workflowName,
                ':creatorName' => $sourceWorkflow['CREATORNAME'],
                ':descriptor' => $sourceWorkflow['DESCRIPTOR'],
                ':isLocked' => $sourceWorkflow['ISLOCKED']
            ]);

            if (!$result) {
                throw new Exception("Failed to create workflow");
            }

            return $newProjectId;
        } catch (Exception $e) {
            error_log("Workflow creation error: " . $e->getMessage());
            throw $e;
        }
    }

    public function createWorkflowFromXMLTemplate($projectId, $projectName, $xmlContent) {
        try {
            $workflowName = "Software Simplified Workflow for " . $projectName;

            // 1. Create workflow scheme
            $stmt = $this->db->prepare("
                INSERT INTO WORKFLOWSCHEME (ID, NAME, DESCRIPTION)
                VALUES (:id, :name, :description)
            ");
            
            $stmt->execute([
                ':id' => $projectId,
                ':name' => $workflowName,
                ':description' => 'Default workflow scheme'
            ]);

            // 2. Create workflow scheme entity
            $stmt = $this->db->prepare("
                INSERT INTO WORKFLOWSCHEMEENTITY (SCHEME, WORKFLOW, ISSUETYPE)
                VALUES (:scheme, :workflow, 0)
            ");
            
            $stmt->execute([
                ':scheme' => $projectId,
                ':workflow' => $workflowName
            ]);

            // 3. Create workflow with XML from template file
            $stmt = $this->db->prepare("
                INSERT INTO JIRAWORKFLOWS (
                    ID, WORKFLOWNAME, CREATORNAME, DESCRIPTOR, ISLOCKED
                ) VALUES (
                    :id, :workflowName, :creatorName, :descriptor, :isLocked
                )
            ");

            $result = $stmt->execute([
                ':id' => $projectId,
                ':workflowName' => $workflowName,
                ':creatorName' => 'system',
                ':descriptor' => $xmlContent,
                ':isLocked' => 'N'
            ]);

            if (!$result) {
                throw new Exception("Failed to create workflow");
            }

            return $projectId;
        } catch (Exception $e) {
            error_log("Workflow creation error: " . $e->getMessage());
            throw $e;
        }
    }

    private function createWorkflowScheme($projectId, $workflowName) {
        $stmt = $this->db->prepare("
            INSERT INTO WORKFLOWSCHEME (ID, NAME, DESCRIPTION)
            VALUES (:id, :name, :description)
        ");
        
        $result = $stmt->execute([
            ':id' => $projectId,
            ':name' => $workflowName,
            ':description' => 'Default workflow scheme'
        ]);

        if (!$result) {
            throw new Exception("Failed to create workflow scheme");
        }

        // Create workflow scheme entity
        $stmt = $this->db->prepare("
            INSERT INTO WORKFLOWSCHEMEENTITY (SCHEME, WORKFLOW, ISSUETYPE)
            VALUES (:scheme, :workflow, 0)
        ");
        
        $result = $stmt->execute([
            ':scheme' => $projectId,
            ':workflow' => $workflowName
        ]);

        if (!$result) {
            throw new Exception("Failed to create workflow scheme entity");
        }
    }

    private function createWorkflow($projectId, $workflowName, $sourceWorkflow) {
        $stmt = $this->db->prepare("
            INSERT INTO JIRAWORKFLOWS (
                ID, WORKFLOWNAME, CREATORNAME, DESCRIPTOR, ISLOCKED
            ) VALUES (
                :id, :workflowName, :creatorName, :descriptor, :isLocked
            )
        ");

        $result = $stmt->execute([
            ':id' => $projectId,
            ':workflowName' => $workflowName,
            ':creatorName' => $sourceWorkflow['CREATORNAME'],
            ':descriptor' => $sourceWorkflow['DESCRIPTOR'],
            ':isLocked' => $sourceWorkflow['ISLOCKED']
        ]);

        if (!$result) {
            $error = $stmt->errorInfo();
            throw new Exception("Failed to create workflow: " . $error[2]);
        }
    }

    public function getWorkflowDetails($id) {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM JIRAWORKFLOWS
            WHERE ID = :id
        ");
        $stmt->execute([':id' => $id]);
        $workflow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$workflow) {
            return null;
        }

        // Parse the XML descriptor
        if (!empty($workflow['DESCRIPTOR'])) {
            $xml = new SimpleXMLElement($workflow['DESCRIPTOR']);
            $workflow['parsed_xml'] = $this->parseWorkflowXML($xml);
        }

        return $workflow;
    }

    public function parseWorkflowXML($xml) {
        $parsed = [
            'meta' => [],
            'initial_actions' => [],
            'common_actions' => [],
            'steps' => []
        ];

        // Parse meta information
        if (isset($xml->meta)) {
            foreach ($xml->meta as $meta) {
                $parsed['meta'][(string)$meta['name']] = (string)$meta;
            }
        }

        // Parse initial actions
        if (isset($xml->{'initial-actions'}->action)) {
            foreach ($xml->{'initial-actions'}->action as $action) {
                $parsed['initial_actions'][] = $this->parseAction($action);
            }
        }

        // Parse common actions
        if (isset($xml->{'common-actions'}->action)) {
            foreach ($xml->{'common-actions'}->action as $action) {
                $parsed['common_actions'][] = $this->parseAction($action);
            }
        }

        // Parse steps
        if (isset($xml->steps->step)) {
            foreach ($xml->steps->step as $step) {
                $parsed['steps'][] = $this->parseStep($step);
            }
        }

        return $parsed;
    }

    private function parseAction($action) {
        $actionData = [
            'id' => (string)$action['id'],
            'name' => (string)$action['name'],
            'view' => (string)$action['view'],
            'meta' => [],
            'validators' => [],
            'conditions' => [],
            'results' => []
        ];

        // Parse meta
        if (isset($action->meta)) {
            foreach ($action->meta as $meta) {
                $actionData['meta'][(string)$meta['name']] = (string)$meta;
            }
        }

        // Parse validators
        if (isset($action->validators)) {
            foreach ($action->validators->validator as $validator) {
                $actionData['validators'][] = [
                    'name' => (string)$validator['name'],
                    'type' => (string)$validator['type'],
                    'args' => $this->parseArgs($validator)
                ];
            }
        }

        // Parse conditions
        if (isset($action->{'restrict-to'})) {
            $actionData['conditions'] = $this->parseConditions($action->{'restrict-to'});
        }

        // Parse results
        if (isset($action->results->{'unconditional-result'})) {
            foreach ($action->results->{'unconditional-result'} as $result) {
                $actionData['results'][] = [
                    'old_status' => (string)$result['old-status'],
                    'status' => (string)$result['status'],
                    'step' => (string)$result['step'],
                    'post_functions' => $this->parsePostFunctions($result)
                ];
            }
        }

        return $actionData;
    }

    private function parseStep($step) {
        $stepData = [
            'id' => (string)$step['id'],
            'name' => (string)$step['name'],
            'meta' => [],
            'actions' => []
        ];

        // Parse meta
        if (isset($step->meta)) {
            foreach ($step->meta as $meta) {
                $stepData['meta'][(string)$meta['name']] = (string)$meta;
            }
        }

        // Parse actions
        if (isset($step->actions)) {
            foreach ($step->actions->children() as $actionRef) {
                if ($actionRef->getName() === 'common-action') {
                    $stepData['actions'][] = [
                        'type' => 'common',
                        'id' => (string)$actionRef['id']
                    ];
                } else if ($actionRef->getName() === 'action') {
                    $stepData['actions'][] = [
                        'type' => 'step',
                        'action' => $this->parseAction($actionRef)
                    ];
                }
            }
        }

        return $stepData;
    }

    private function parseArgs($element) {
        $args = [];
        if (isset($element->arg)) {
            foreach ($element->arg as $arg) {
                $args[(string)$arg['name']] = (string)$arg;
            }
        }
        return $args;
    }

    private function parseConditions($restrictTo) {
        $conditions = [];
        if (isset($restrictTo->conditions->condition)) {
            foreach ($restrictTo->conditions->condition as $condition) {
                $conditions[] = [
                    'type' => (string)$condition['type'],
                    'args' => $this->parseArgs($condition)
                ];
            }
        }
        return $conditions;
    }

    private function parsePostFunctions($result) {
        $functions = [];
        if (isset($result->{'post-functions'}->function)) {
            foreach ($result->{'post-functions'}->function as $function) {
                $functions[] = [
                    'type' => (string)$function['type'],
                    'args' => $this->parseArgs($function)
                ];
            }
        }
        return $functions;
    }

    public function updateWorkflow($id, $data) {
        try {
            $this->db->beginTransaction();

            // Update basic workflow info
            $stmt = $this->db->prepare("
                UPDATE JIRAWORKFLOWS 
                SET WORKFLOWNAME = :name, 
                    DESCRIPTOR = :descriptor,
                    ISLOCKED = :locked
                WHERE ID = :id
            ");

            $result = $stmt->execute([
                ':id' => $id,
                ':name' => $data['name'],
                ':descriptor' => $data['descriptor'],
                ':locked' => $data['locked'] ?? 'N'
            ]);

            if (!$result) {
                throw new Exception("Failed to update workflow");
            }

            // Update workflow scheme if name changed
            if (isset($data['scheme_name'])) {
                $stmt = $this->db->prepare("
                    UPDATE WORKFLOWSCHEME 
                    SET NAME = :name 
                    WHERE ID = :id
                ");
                $stmt->execute([
                    ':id' => $id,
                    ':name' => $data['scheme_name']
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Workflow update error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllWorkflows() {
        $stmt = $this->db->prepare("
            SELECT w.*, ws.NAME as SCHEME_NAME 
            FROM JIRAWORKFLOWS w
            LEFT JOIN WORKFLOWSCHEME ws ON ws.ID = w.ID
            ORDER BY w.WORKFLOWNAME
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}