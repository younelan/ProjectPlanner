<?php

class Sprint {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($projectId = null) {
        $sql = "SELECT 
                    ao.*,
                    COUNT(DISTINCT ji.ID) as issue_count
                FROM 
                    AO_60DB71_SPRINT ao
                LEFT JOIN 
                    CUSTOMFIELDVALUE cfv ON CAST(cfv.STRINGVALUE AS UNSIGNED) = ao.ID
                LEFT JOIN 
                    CUSTOMFIELD cf ON cf.ID = cfv.CUSTOMFIELD AND cf.CFNAME = 'Sprint'
                LEFT JOIN 
                    JIRAISSUE ji ON ji.ID = cfv.ISSUE";
        
        if ($projectId) {
            $sql .= " WHERE ji.PROJECT = ?";
        }
        
        $sql .= " GROUP BY ao.ID ORDER BY ao.START_DATE DESC";
        
        $stmt = $this->db->prepare($sql);
        if ($projectId) {
            $stmt->execute([$projectId]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSprintIssues($sprintId) {
        $sql = "SELECT 
                    ji.*,
                    p.PKEY as ProjectKey,
                    CONCAT(p.PKEY, '-', ji.ISSUENUM) as IssueKey,
                    cu.display_name as ASSIGNEE
                FROM 
                    CUSTOMFIELDVALUE cfv
                JOIN 
                    CUSTOMFIELD cf ON cf.ID = cfv.CUSTOMFIELD
                JOIN 
                    JIRAISSUE ji ON ji.ID = cfv.ISSUE
                JOIN 
                    PROJECT p ON ji.PROJECT = p.ID
                LEFT JOIN
                    APP_USER au ON ji.ASSIGNEE = au.USER_KEY
                LEFT JOIN
                    CWD_USER cu ON au.USER_KEY = cu.user_name
                WHERE 
                    cf.CFNAME = 'Sprint'
                    AND cfv.STRINGVALUE = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sprintId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO AO_60DB71_SPRINT (
                        NAME, GOAL, START_DATE, END_DATE, 
                        CLOSED, COMPLETE_DATE, SEQUENCE,
                        STARTED, RAPID_VIEW_ID
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['goal'] ?? '',
                strtotime($data['startDate']) * 1000,
                strtotime($data['endDate']) * 1000,
                0,  // CLOSED (boolean)
                null, // COMPLETE_DATE
                time(), // SEQUENCE
                0,  // STARTED (boolean)
                null  // RAPID_VIEW_ID
            ]);
            
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function update($data) {
        try {
            $sql = "UPDATE AO_60DB71_SPRINT 
                    SET NAME = ?, GOAL = ?, START_DATE = ?, 
                        END_DATE = ?, STARTED = ?, CLOSED = ?
                    WHERE ID = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['goal'],
                strtotime($data['startDate']) * 1000,
                strtotime($data['endDate']) * 1000,
                $data['started'] ?? false,
                $data['closed'] ?? false,
                $data['id']
            ]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getById($id) {
        $sql = "SELECT 
                    s.*,
                    COUNT(DISTINCT ji.ID) as issue_count,
                    MAX(ji.PROJECT) as PROJECT_ID  -- Using MAX to get a single value
                FROM 
                    AO_60DB71_SPRINT s
                LEFT JOIN 
                    CUSTOMFIELDVALUE cfv ON CAST(cfv.STRINGVALUE AS UNSIGNED) = s.ID
                LEFT JOIN 
                    CUSTOMFIELD cf ON cf.ID = cfv.CUSTOMFIELD AND cf.CFNAME = 'Sprint'
                LEFT JOIN 
                    JIRAISSUE ji ON ji.ID = cfv.ISSUE
                WHERE 
                    s.ID = ?
                GROUP BY 
                    s.ID, s.NAME, s.GOAL, s.START_DATE, s.END_DATE, 
                    s.STARTED, s.CLOSED, s.SEQUENCE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
