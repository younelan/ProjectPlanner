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
}
?>
