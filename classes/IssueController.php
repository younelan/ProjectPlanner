<?php
class IssueController {
    private $issueModel;
    private $db = null;
    public function __construct($db) {
        $this->db = $db;
        $this->issueModel = new Issue($db);
    }
    public function view($id) {
        $issueModel = new Issue($this->db); // Pass the database connection to the Issue class.
        //$issueDetails = $issueModel->getIssueById($id);
        $issue = $issueModel->getIssueById($id);
        $linkedIssues = $this->issueModel->getLinkedIssues($issue['ID']); // Fetch linked issues
        //print_r($linkedIssues);exit;
        include 'views/issues/view.php';
    }
    
    // Display all issues for a project
    public function list($projectId) {
        //$issues = $this->issueModel->getIssuesByProject($projectId);
        $issues = $issueClass->getProjectIssuesWithSubcomponents($projectId);

        include 'views/issues/list.php';
    }

    // Display details for a specific issue
    public function details($issueId) {
        $issue = $this->issueModel->getIssueDetails($issueId);
        $linkedIssues = $this->issueModel->getLinkedIssues($issue['ID']); // Fetch linked issues
        include 'views/issues/details.php';
    }
}
?>
