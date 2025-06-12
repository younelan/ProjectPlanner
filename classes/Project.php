<?php
class Project {
    private $db;
    private $workflowModel;
    private $config;

    public function __construct($db, $config = null) {
        $this->db = $db;
        $this->config = $config;
        $this->workflowModel = new Workflow($db);
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

            // Handle workflow creation
            if (!empty($data['clone_project_id'])) {
                // Clone from existing project
                $this->workflowModel->cloneWorkflow($data['clone_project_id'], $newId, $data['PNAME']);
            } elseif (!empty($data['use_default_workflow'])) {
                // Create default workflow from XML
                $this->createDefaultWorkflow($newId, $data['PNAME']);
            }

            $this->db->commit();
            return $newId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function createDefaultWorkflow($projectId, $projectName) {
        // Read the default workflow XML
        $xmlPath = $this->config['default_workflow_file'];
        if (!$xmlPath || !file_exists($xmlPath)) {
            throw new Exception("Default workflow file not found at: " . $xmlPath);
        }
        
        $xmlContent = file_get_contents($xmlPath);
        if ($xmlContent === false) {
            throw new Exception("Failed to read default workflow file");
        }

        // Create workflow from XML template instead of cloning from existing project
        $this->workflowModel->createWorkflowFromXMLTemplate($projectId, $projectName, $xmlContent);
    }

    public function validateProjectKey($key) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM PROJECT WHERE PKEY = :key");
        $stmt->execute([':key' => $key]);
        return $stmt->fetchColumn() == 0;
    }
}