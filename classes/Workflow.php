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
}