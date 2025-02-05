<?php
class Issue {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get a specific issue by ID, including its type and status details
    public function getIssueById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                i.*,

                s.`PNAME` AS `STATUS_NAME`, s.`ICONURL` AS `STATUS_ICON`
            FROM `JIRAISSUE` i
            JOIN `ISSUETYPE` t ON i.`ISSUETYPE` = t.`ID`
            JOIN `ISSUESTATUS` s ON i.`ISSUESTATUS` = s.`ID`
            WHERE i.`ID` = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all issues for a specific project
    public function getIssuesByProject($projectId) {
        $stmt = $this->db->prepare("
            SELECT 
                JIRAISSUE.*, 
                ISSUETYPE.PNAME AS TYPE, 
                ISSUESTATUS.PNAME AS STATUS,
                ISSUESTATUS.ID AS STATUSID
            FROM JIRAISSUE
            JOIN ISSUETYPE ON JIRAISSUE.ISSUETYPE = ISSUETYPE.ID
            LEFT JOIN ISSUESTATUS ON JIRAISSUE.ISSUESTATUS = ISSUESTATUS.ID
            WHERE JIRAISSUE.PROJECT = ?
            ORDER BY JIRAISSUE.ID
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
// Get all issues for a specific project (with relationships)
public function getProjectIssuesWithSubcomponents($projectId) {
    // Fetch Epics first, followed by stories that belong to those Epics
    $stmt = $this->db->prepare("
        SELECT 
            i.ID, 
            i.SUMMARY, 
            i.PRIORITY, 
            i.ISSUETYPE, 
            i.PARENT_ID, 
            t.PNAME AS TYPE, 
            s.PNAME AS STATUS
        FROM JIRAISSUE i
        LEFT JOIN ISSUETYPE t ON i.ISSUETYPE = t.ID
        LEFT JOIN ISSUESTATUS s ON i.ISSUESTATUS = s.ID
        WHERE i.PROJECT = ?
        ORDER BY i.PARENT_ID, i.ID
    ");
    $stmt->execute([$projectId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Get detailed information for a specific issue
    public function getIssueDetails($issueId) {
        $stmt = $this->db->prepare("
            SELECT 
                JIRAISSUE.ID, 
                JIRAISSUE.SUMMARY, 
                JIRAISSUE.DESCRIPTION, 
                ISSUETYPE.PNAME AS TYPE, 
                JIRAISSUE.PRIORITY,
                ISSUESTATUS.PNAME AS STATUS,
                ISSUESTATUS.ID AS ISSUESTATUS
            FROM JIRAISSUE
            LEFT JOIN ISSUETYPE ON JIRAISSUE.ISSUETYPE = ISSUETYPE.ID
            LEFT JOIN ISSUESTATUS ON JIRAISSUE.ISSUESTATUS = ISSUESTATUS.ID
            WHERE JIRAISSUE.ID = ?
        ");
        $stmt->execute([$issueId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    function getLinkedIssues($issueId) {
        // Assuming you have a database connection in $pdo

        // SQL query to fetch linked issues and link types
        $sql = "
        SELECT 
            il.ID, 
            il.LINKTYPE, 
            il.SOURCE, 
            il.DESTINATION, 
            ilt.LINKNAME as TYPE,
            CASE 
                WHEN il.SOURCE = :issueId THEN 'SOURCE' 
                ELSE 'DESTINATION' 
            END AS LINK_DIRECTION,
            CASE 
                WHEN il.SOURCE = :issueId THEN il.DESTINATION 
                ELSE il.SOURCE 
            END AS LINK_ID,
            CASE 
                WHEN il.SOURCE = :issueId THEN i2.SUMMARY 
                ELSE i1.SUMMARY 
            END AS LINK_NAME,

            i1.SUMMARY AS SOURCE_NAME,
            i2.SUMMARY AS DESTINATION_NAME
        FROM 
            ISSUELINK il
        JOIN 
            ISSUELINKTYPE ilt ON il.LINKTYPE = ilt.ID
        LEFT JOIN 
            JIRAISSUE i1 ON il.SOURCE = i1.ID
        LEFT JOIN 
            JIRAISSUE i2 ON il.DESTINATION = i2.ID
        WHERE 
            (il.SOURCE = :issueId OR il.DESTINATION = :issueId)
    ";

        // Prepare and execute the query
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['issueId' => $issueId]);
        $linkedIssues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch all results
        // Return the linked issues
        return $linkedIssues;
    }
    public function newgetLinkedIssues($issueId) {
        $issueLinks = [];

        // Loop over issues and fetch links for each
        //foreach ($issues as $issue) {
            $query = "
                SELECT 
                    IL.ID AS link_id,
                    IL.LINKTYPE,
                    IL.SOURCE,
                    IL.DESTINATION,
                    ILT.LINKNAME,
                    ILT.INWARD,
                    ILT.OUTWARD
                FROM ISSUELINK AS IL
                JOIN ISSUELINKTYPE AS ILT ON IL.LINKTYPE = ILT.ID
                WHERE IL.SOURCE = :issueId OR IL.DESTINATION = :issueId
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':issueId' => $issueId]);
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //$issueLinks[$issue['ID']] = $links;
        //}

        return $links;
    }        

    public function getIssueHistory($issueId) {
        $sql = "
            SELECT 
                cg.ID as change_group_id,
                cg.AUTHOR,
                cg.CREATED,
                ci.FIELD,
                ci.OLDSTRING,
                ci.NEWSTRING
            FROM CHANGEGROUP cg
            JOIN CHANGEITEM ci ON cg.ID = ci.GROUPID
            WHERE cg.ISSUEID = :issueId
            ORDER BY cg.CREATED DESC, ci.ID
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['issueId' => $issueId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
