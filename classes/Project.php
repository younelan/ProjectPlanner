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

            // Insert into PROJECT table
            $stmt = $this->db->prepare("
                INSERT INTO PROJECT (
                    PNAME, PKEY, LEAD, DESCRIPTION, URL, 
                    PCOUNTER, ASSIGNEETYPE, PROJECTTYPE
                ) VALUES (
                    :PNAME, :PKEY, :LEAD, :DESCRIPTION, :URL,
                    0, 1, 'software'
                )
            ");

            $stmt->execute([
                ':PNAME' => $data['PNAME'],
                ':PKEY' => $data['PKEY'],
                ':LEAD' => $data['LEAD'],
                ':DESCRIPTION' => $data['DESCRIPTION'],
                ':URL' => $data['URL']
            ]);

            $newProjectId = $this->db->lastInsertId();

            // Clone workflow from existing project
            if (!empty($data['clone_project_id'])) {
                $this->cloneWorkflow($data['clone_project_id'], $newProjectId);
            }

            $this->db->commit();
            return $newProjectId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function cloneWorkflow($sourceProjectId, $newProjectId) {
        // Clone JIRAWORKFLOWS entries
        $stmt = $this->db->prepare("
            INSERT INTO JIRAWORKFLOWS (
                WORKFLOWNAME, CREATORNAME, DESCRIPTOR, ISLOCKED
            )
            SELECT 
                CONCAT(:newKey, '_workflow'),
                CREATORNAME,
                DESCRIPTOR,
                ISLOCKED
            FROM JIRAWORKFLOWS
            WHERE ID IN (
                SELECT WORKFLOW_ID 
                FROM JIRAISSUE 
                WHERE PROJECT = :sourceProjectId
                LIMIT 1
            )
        ");

        $stmt->execute([
            ':sourceProjectId' => $sourceProjectId,
            ':newKey' => $newProjectId
        ]);

        // You might want to clone other related workflow data here
        // Such as:
        // - WORKFLOWSCHEME
        // - NODEASSOCIATION (for workflow associations)
        // - Any other workflow-related tables
    }

    public function validateProjectKey($key) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM PROJECT WHERE PKEY = :key");
        $stmt->execute([':key' => $key]);
        return $stmt->fetchColumn() == 0;
    }
}
?>
