<?php
class IssueStatus {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get issue status by ID
    public function getStatusById($id) {
        $stmt = $this->db->prepare("SELECT `ID`, `PNAME`, `DESCRIPTION`, `ICONURL`, `STATUSCATEGORY` 
                                    FROM `ISSUESTATUS` 
                                    WHERE `ID` = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all issue statuses
    public function getAllStatuses() {
        $stmt = $this->db->prepare("SELECT `ID`, `PNAME`, `DESCRIPTION`, `ICONURL` 
                                    FROM `ISSUESTATUS` 
                                    ORDER BY `SEQUENCE` ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

