<?php
class Issue {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get a specific issue by ID, including its type and status details
    public function getIssueById($id) {
        $query = "
            SELECT j.*, 
                   p.PKEY as PROJECT_KEY,
                   it.PNAME as TYPE,
                   s.PNAME as STATUS_NAME,
                   s.ICONURL as STATUS_ICON,
                   pr.PNAME as PRIORITY_NAME,
                   pr.STATUS_COLOR as PRIORITY_COLOR
            FROM JIRAISSUE j
            LEFT JOIN PROJECT p ON j.PROJECT = p.ID
            LEFT JOIN ISSUETYPE it ON j.ISSUETYPE = it.ID
            LEFT JOIN ISSUESTATUS s ON j.ISSUESTATUS = s.ID
            LEFT JOIN PRIORITY pr ON j.PRIORITY = pr.ID
            WHERE j.ID = :id
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all issues for a specific project

    // Get all issues for a specific project
    public function getIssuesByProject($projectId) {
        $stmt = $this->db->prepare("
            SELECT 
                i.*, 
                t.PNAME as TYPE,
                s.PNAME as STATUS,
                s.SEQUENCE as STATUS_ORDER,
                s.ICONURL as STATUS_ICON,
                p.PKEY as PROJECT_KEY
            FROM JIRAISSUE i
            LEFT JOIN ISSUETYPE t ON i.ISSUETYPE = t.ID
            LEFT JOIN ISSUESTATUS s ON i.ISSUESTATUS = s.ID
            LEFT JOIN PROJECT p ON i.PROJECT = p.ID
            WHERE i.PROJECT = ?
            ORDER BY s.SEQUENCE ASC, i.UPDATED DESC
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

    public function searchIssues($searchTerm = '', $projectId = null) {
        $sql = "
            SELECT 
                i.*, 
                p.PKEY as PROJECT_KEY,
                t.PNAME as TYPE,
                s.PNAME as STATUS,
                s.ICONURL as STATUS_ICON
            FROM JIRAISSUE i
            LEFT JOIN PROJECT p ON i.PROJECT = p.ID
            LEFT JOIN ISSUETYPE t ON i.ISSUETYPE = t.ID
            LEFT JOIN ISSUESTATUS s ON i.ISSUESTATUS = s.ID
            WHERE 1=1
        ";
        $params = [];
        
        if ($searchTerm) {
            $sql .= " AND (i.SUMMARY LIKE ? OR i.DESCRIPTION LIKE ?)";
            $searchTerm = "%$searchTerm%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($projectId) {
            $sql .= " AND i.PROJECT = ?";
            $params[] = $projectId;
        }
        
        $sql .= " ORDER BY i.ID DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateIssueStatus($issueId, $newStatus) {
        try {
            // First, get the status ID from the status name
            $stmt = $this->db->prepare("
                SELECT ID, PNAME FROM ISSUESTATUS WHERE PNAME = :status
            ");
            $stmt->execute(['status' => $newStatus]);
            $status = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$status) {
                throw new Exception('Invalid status: ' . $newStatus);
            }

            // Get the current status
            $stmt = $this->db->prepare("
                SELECT s.PNAME as old_status 
                FROM JIRAISSUE i
                JOIN ISSUESTATUS s ON i.ISSUESTATUS = s.ID
                WHERE i.ID = :issueId
            ");
            $stmt->execute(['issueId' => $issueId]);
            $oldStatus = $stmt->fetchColumn();

            // Start transaction
            $this->db->beginTransaction();

            // Update the issue
            $stmt = $this->db->prepare("
                UPDATE JIRAISSUE 
                SET ISSUESTATUS = :statusId,
                    UPDATED = CURRENT_TIMESTAMP
                WHERE ID = :issueId
            ");
            
            $stmt->execute([
                'statusId' => $status['ID'],
                'issueId' => $issueId
            ]);

            // Log the change
            $nextGroupId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM CHANGEGROUP")->fetchColumn();
            $nextItemId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM CHANGEITEM")->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO CHANGEGROUP (ID, ISSUEID, AUTHOR, CREATED)
                VALUES (:groupId, :issueId, 'system', CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                'groupId' => $nextGroupId,
                'issueId' => $issueId
            ]);

            $stmt = $this->db->prepare("
                INSERT INTO CHANGEITEM (ID, GROUPID, FIELD, OLDSTRING, NEWSTRING)
                VALUES (:itemId, :groupId, 'status', :oldStatus, :newStatus)
            ");
            $stmt->execute([
                'itemId' => $nextItemId,
                'groupId' => $nextGroupId,
                'oldStatus' => $oldStatus,
                'newStatus' => $status['PNAME']
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getIssuesForBoard($projectId) {
        $stmt = $this->db->prepare("
            SELECT 
                i.*,
                t.PNAME AS TYPE,
                s.PNAME AS STATUS,
                s.SEQUENCE AS STATUS_ORDER,
                s.ID as STATUS_ID,
                p.PKEY AS PROJECT_KEY,
                p.ID AS PROJECT_ID,
                p.PNAME AS PROJECT_NAME
            FROM JIRAISSUE i
            JOIN PROJECT p ON i.PROJECT = p.ID
            LEFT JOIN ISSUETYPE t ON i.ISSUETYPE = t.ID
            LEFT JOIN ISSUESTATUS s ON i.ISSUESTATUS = s.ID
            WHERE i.PROJECT = :projectId
            ORDER BY s.SEQUENCE, i.UPDATED DESC
        ");
        $stmt->execute(['projectId' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addHistoryEntry($issueId, $comment, $author = null) {
        try {
            $this->db->beginTransaction();

            // Get next IDs
            $nextGroupId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM CHANGEGROUP")->fetchColumn();
            $nextItemId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM CHANGEITEM")->fetchColumn();

            // Use current user if no author specified
            $author = $author ?: User::getCurrentUser();

            // Create change group
            $stmt = $this->db->prepare("
                INSERT INTO CHANGEGROUP (ID, ISSUEID, AUTHOR, CREATED)
                VALUES (:groupId, :issueId, :author, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                'groupId' => $nextGroupId,
                'issueId' => $issueId,
                'author' => $author
            ]);

            // Create change item for comment
            $stmt = $this->db->prepare("
                INSERT INTO CHANGEITEM (ID, GROUPID, FIELD, NEWSTRING)
                VALUES (:itemId, :groupId, 'comment', :comment)
            ");
            $stmt->execute([
                'itemId' => $nextItemId,
                'groupId' => $nextGroupId,
                'comment' => $comment
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAllPriorities() {
        $stmt = $this->db->prepare("
            SELECT 
                ID,
                PNAME,
                DESCRIPTION,
                ICONURL,
                SEQUENCE,
                STATUS_COLOR
            FROM PRIORITY
            ORDER BY SEQUENCE ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllIssueTypes() {
        $stmt = $this->db->prepare("
            SELECT 
                ID,
                PNAME,
                DESCRIPTION,
                ICONURL,
                SEQUENCE
            FROM ISSUETYPE
            ORDER BY SEQUENCE ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateIssue($issueId, $data) {
        try {
            $this->db->beginTransaction();

            // Update issue
            $stmt = $this->db->prepare("
                UPDATE JIRAISSUE 
                SET 
                    SUMMARY = :summary,
                    DESCRIPTION = :description,
                    ASSIGNEE = :assignee,
                    REPORTER = :reporter,
                    PRIORITY = :priority,
                    ISSUETYPE = :issuetype,
                    UPDATED = CURRENT_TIMESTAMP
                WHERE ID = :issueId
            ");
            
            $stmt->execute([
                'summary' => $data['summary'],
                'description' => $data['description'],
                'assignee' => $data['assignee'],
                'reporter' => $data['reporter'],
                'issuetype' => $data['issuetype'],
                'priority' => $data['priority'],
                'issueId' => $issueId
            ]);

            // Log changes
            foreach ($data['changes'] as $field => $change) {
                if ($change['old'] !== $change['new']) {
                    $this->logChange($issueId, $field, $change['old'], $change['new']);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function logChange($issueId, $field, $oldValue, $newValue) {
        $nextGroupId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM CHANGEGROUP")->fetchColumn();
        $nextItemId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM CHANGEITEM")->fetchColumn();

        $stmt = $this->db->prepare("
            INSERT INTO CHANGEGROUP (ID, ISSUEID, AUTHOR, CREATED)
            VALUES (:groupId, :issueId, :author, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            'groupId' => $nextGroupId,
            'issueId' => $issueId,
            'author' => User::getCurrentUser()
        ]);

        $stmt = $this->db->prepare("
            INSERT INTO CHANGEITEM (ID, GROUPID, FIELD, OLDSTRING, NEWSTRING)
            VALUES (:itemId, :groupId, :field, :oldValue, :newValue)
        ");
        $stmt->execute([
            'itemId' => $nextItemId,
            'groupId' => $nextGroupId,
            'field' => $field,
            'oldValue' => $oldValue,
            'newValue' => $newValue
        ]);
    }

    public function getAllLinkTypes() {
        $stmt = $this->db->prepare("
            SELECT ID, LINKNAME, INWARD, OUTWARD
            FROM ISSUELINKTYPE
            ORDER BY ID
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addIssueLink($sourceId, $targetId, $linkTypeId) {
        try {
            $this->db->beginTransaction();

            // Get next ID for ISSUELINK
            $nextLinkId = $this->db->query("SELECT COALESCE(MAX(ID), 0) + 1 FROM ISSUELINK")->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO ISSUELINK (ID, LINKTYPE, SOURCE, DESTINATION)
                VALUES (:id, :linktype, :source, :destination)
            ");
            
            $stmt->execute([
                'id' => $nextLinkId,
                'linktype' => $linkTypeId,
                'source' => $sourceId,
                'destination' => $targetId
            ]);

            // Log the change
            $this->logChange($sourceId, 'link', '', "Added link to issue $targetId");

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteIssueLink($linkId) {
        $stmt = $this->db->prepare("DELETE FROM ISSUELINK WHERE ID = :linkId");
        return $stmt->execute(['linkId' => $linkId]);
    }

    public function searchIssuesForAutocomplete($term, $projectId) {
        $sql = "
            SELECT 
                i.ID,
                i.SUMMARY,
                p.PKEY as PROJECT_KEY,
                CONCAT(p.PKEY, '-', i.ID, ': ', i.SUMMARY) as LABEL
            FROM JIRAISSUE i
            JOIN PROJECT p ON i.PROJECT = p.ID
            WHERE i.PROJECT = :projectId
            AND (
                i.SUMMARY LIKE :term 
                OR CONCAT(p.PKEY, '-', i.ID) LIKE :term
            )
            LIMIT 10
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'projectId' => $projectId,
            'term' => "%$term%"
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createIssue($data) {
        try {
            // Get next ID
            $stmt = $this->db->query("SELECT MAX(ID) FROM JIRAISSUE");
            $maxId = $stmt->fetchColumn();
            $newId = ($maxId ? $maxId : 10000) + 1;

            // Get project counter and increment
            $stmt = $this->db->prepare("SELECT PCOUNTER FROM PROJECT WHERE ID = :projectId");
            $stmt->execute([':projectId' => $data['projectId']]);
            $counter = $stmt->fetchColumn();
            $newCounter = $counter + 1;

            // Update project counter
            $stmt = $this->db->prepare("UPDATE PROJECT SET PCOUNTER = :counter WHERE ID = :projectId");
            $stmt->execute([
                ':counter' => $newCounter,
                ':projectId' => $data['projectId']
            ]);

            // Insert new issue with default values
            $stmt = $this->db->prepare("
                INSERT INTO JIRAISSUE (
                    ID, PROJECT, ISSUENUM, SUMMARY, DESCRIPTION, 
                    ISSUETYPE, PRIORITY, REPORTER, ASSIGNEE, 
                    CREATED, UPDATED, ISSUESTATUS
                ) VALUES (
                    :id, :projectId, :issuenum, :summary, :description,
                    :issuetype, :priority, :reporter, :assignee,
                    NOW(), NOW(), 'Open'
                )
            ");

            $params = [
                ':id' => $newId,
                ':projectId' => $data['projectId'],
                ':issuenum' => $newCounter,
                ':summary' => $data['summary'],
                ':description' => $data['description'],
                ':issuetype' => $data['issuetype'],
                ':priority' => $data['priority'],
                ':reporter' => $data['reporter'],
                ':assignee' => empty($data['assignee']) ? null : $data['assignee']
            ];

            $stmt->execute($params);
            return $newId;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to create issue: " . $e->getMessage());
        }
    }

    public function deleteIssue($id) {
        try {
            // First delete any links to/from this issue
            $stmt = $this->db->prepare("DELETE FROM ISSUELINK WHERE SOURCE = :id OR DESTINATION = :id");
            $stmt->execute([':id' => $id]);

            // Delete any history entries
            $stmt = $this->db->prepare("
                DELETE FROM CHANGEITEM WHERE GROUPID IN (
                    SELECT ID FROM CHANGEGROUP WHERE ISSUEID = :id
                )
            ");
            $stmt->execute([':id' => $id]);
            
            $stmt = $this->db->prepare("DELETE FROM CHANGEGROUP WHERE ISSUEID = :id");
            $stmt->execute([':id' => $id]);

            // Finally delete the issue itself
            $stmt = $this->db->prepare("DELETE FROM JIRAISSUE WHERE ID = :id");
            $stmt->execute([':id' => $id]);

            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to delete issue");
        }
    }
}
