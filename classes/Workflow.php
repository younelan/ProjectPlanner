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
}