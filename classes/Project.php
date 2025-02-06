<?php
class Project {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all projects
    public function getAllProjects() {
        $stmt = $this->db->prepare("
            SELECT 
                p.ID,
                p.PNAME,
                p.PKEY,
                p.LEAD,
                p.DESCRIPTION
            FROM PROJECT p
            ORDER BY p.PKEY ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a specific project by ID
    public function getProjectById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*
            FROM PROJECT p
            WHERE p.ID = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all issues for a specific project
    public function getIssuesByProjectId($projectId) {
        $stmt = $this->db->prepare("SELECT `ID`, `PKEY`, `ISSUENUM`, `SUMMARY`, `ASSIGNEE`, `PRIORITY`, `ISSUESTATUS` 
                                    FROM JIRAISSUE 
                                    LEFT JOIN ISSUETYPE t ON i.ISSUETYPE = t.ID
                                    WHERE PROJECT = :projectId");
        $stmt->bindParam(':projectId', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProject($id, $data) {
        $query = "UPDATE PROJECT SET 
                    PNAME = :PNAME, 
                    URL = :URL,
                    DESCRIPTION = :DESCRIPTION, 
                    `LEAD` = :LEAD, 
                    `PKEY` = :PKEY,
                    PROJECTTYPE = :PROJECTTYPE,
                    ORIGINALKEY = :ORIGINALKEY
                  WHERE ID = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':PNAME'       => $data['PNAME'],
            ':URL'         => $data['URL'],
            ':DESCRIPTION' => $data['DESCRIPTION'],
            ':LEAD'        => $data['LEAD'],
            ':PKEY'        => $data['PKEY'],
            ':PROJECTTYPE' => $data['PROJECTTYPE'],
            ':ORIGINALKEY' => $data['ORIGINALKEY'],
            ':id'          => $id
        ]);
    }

    public function createProject($data) {
        try {
            $this->db->beginTransaction();

            // Get next ID for project (MAX + 100)
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(ID), 0) + 100 FROM PROJECT");
            $stmt->execute();
            $newId = $stmt->fetchColumn();

            // Insert project
            $stmt = $this->db->prepare("
                INSERT INTO PROJECT (
                    ID, PNAME, PKEY, `LEAD`, DESCRIPTION, URL, 
                    PCOUNTER, ASSIGNEETYPE, PROJECTTYPE
                ) VALUES (
                    :id, :PNAME, :PKEY, :LEAD, :DESCRIPTION, :URL,
                    0, 1, 'software'
                )
            ");

            $result = $stmt->execute([
                ':id' => $newId,
                ':PNAME' => $data['PNAME'],
                ':PKEY' => $data['PKEY'],
                ':LEAD' => $data['LEAD'],
                ':DESCRIPTION' => $data['DESCRIPTION'] ?? '',
                ':URL' => $data['URL'] ?? ''
            ]);

            if (!$result) {
                throw new Exception("Failed to create project");
            }

            // Clone workflow in same transaction
            if (!empty($data['clone_project_id'])) {
                $newWorkflowId = $this->cloneWorkflow($data['clone_project_id'], $newId);
                
                // Associate workflow with project
                $stmt = $this->db->prepare("
                    INSERT INTO WORKFLOWSCHEME (ID, NAME, DESCRIPTION)
                    VALUES (:id, :name, :description)
                ");
                
                $stmt->execute([
                    ':id' => $newId,
                    ':name' => $data['PKEY'] . ' Workflow Scheme',
                    ':description' => 'Workflow scheme for ' . $data['PNAME']
                ]);
                
                // Add workflow associations
                $stmt = $this->db->prepare("
                    INSERT INTO NODEASSOCIATION (
                        SOURCE_NODE_ID, SOURCE_NODE_ENTITY, 
                        SINK_NODE_ID, SINK_NODE_ENTITY, 
                        ASSOCIATION_TYPE, SEQUENCE
                    ) VALUES (
                        :projectId, 'Project',
                        :workflowId, 'Workflow',
                        'ProjectWorkflow', 1
                    )
                ");
                
                $stmt->execute([
                    ':projectId' => $newId,
                    ':workflowId' => $newWorkflowId
                ]);
            }

            $this->db->commit();
            return $newId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function cloneWorkflow($sourceProjectId, $newProjectId) {
        try {
            // First, get the workflow from source project
            $stmt = $this->db->prepare("
                SELECT DISTINCT w.ID, w.WORKFLOWNAME, w.CREATORNAME, w.DESCRIPTOR, w.ISLOCKED
                FROM JIRAWORKFLOWS w
                JOIN JIRAISSUE i ON i.WORKFLOW_ID = w.ID
                WHERE i.PROJECT = :sourceProjectId
                ORDER BY w.ID
                LIMIT 1
            ");
            
            $stmt->execute([':sourceProjectId' => $sourceProjectId]);
            $sourceWorkflow = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sourceWorkflow) {
                throw new Exception("No workflow found in source project");
            }

            // Check if workflow ID already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM JIRAWORKFLOWS WHERE ID = :id");
            $stmt->execute([':id' => $newProjectId]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Workflow ID {$newProjectId} already exists. Cannot create duplicate workflow.");
            }

            // Insert new workflow using project ID
            $stmt = $this->db->prepare("
                INSERT INTO JIRAWORKFLOWS (
                    ID, WORKFLOWNAME, CREATORNAME, DESCRIPTOR, ISLOCKED
                ) VALUES (
                    :id, :workflowName, :creatorName, :descriptor, :isLocked
                )
            ");

            $result = $stmt->execute([
                ':id' => $newProjectId,
                ':workflowName' => $newProjectId . '_workflow',
                ':creatorName' => $sourceWorkflow['CREATORNAME'],
                ':descriptor' => $sourceWorkflow['DESCRIPTOR'],
                ':isLocked' => $sourceWorkflow['ISLOCKED']
            ]);

            if (!$result) {
                throw new Exception("Failed to create workflow for project ID {$newProjectId}");
            }

            return $newProjectId;
        } catch (Exception $e) {
            // Re-throw with more context
            throw new Exception("Workflow creation failed: " . $e->getMessage());
        }
    }

    public function validateProjectKey($key) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM PROJECT WHERE PKEY = :key");
        $stmt->execute([':key' => $key]);
        return $stmt->fetchColumn() == 0;
    }
}
?>
