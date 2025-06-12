<?php
class User {
    private $db;
    private static $currentUser = 'scrumviewer';

    public function __construct($db) {
        $this->db = $db;
    }

    public static function getCurrentUser() {
        return self::$currentUser;
    }

    public static function isLoggedIn() {
        return true;
    }

    public static function getName() {
        return self::$currentUser;
    }
    // public static function getCurrentUser() {
    //     return $_SESSION['user'] ?? null;
    // }
    public function getAllUsers() {
        $query = "
            SELECT 
                u.USER_KEY,
                COALESCE(cu.DISPLAY_NAME, u.USER_KEY) as DISPLAY_NAME,
                cu.email_address,
                cu.active
            FROM APP_USER u
            LEFT JOIN CWD_USER cu ON u.USER_KEY = cu.USER_NAME
            ORDER BY cu.display_name, u.USER_KEY
        ";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }
    public function getUserById($id) {
        $query = "
            SELECT 
                u.USER_KEY,
                COALESCE(cu.DISPLAY_NAME, u.USER_KEY) as DISPLAY_NAME,
                cu.email_address,
                cu.active
            FROM APP_USER u
            LEFT JOIN CWD_USER cu ON u.USER_KEY = cu.user_name
            WHERE u.USER_KEY = :id
        ";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return null;
        }
    }
    
    public function createUser($data) {
        try {
            $this->db->beginTransaction();
            
            // Get next ID for APP_USER
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(ID), 0) + 1 FROM APP_USER");
            $stmt->execute();
            $newId = $stmt->fetchColumn();
            
            // Insert into APP_USER
            $stmt = $this->db->prepare("
                INSERT INTO APP_USER (ID, USER_KEY, LOWER_USER_NAME)
                VALUES (:id, :user_key, :lower_user_name)
            ");
            
            $stmt->execute([
                ':id' => $newId,
                ':user_key' => $data['USER_KEY'],
                ':lower_user_name' => strtolower($data['USER_KEY'])
            ]);
            
            // Insert into CWD_USER if we have additional data
            if (!empty($data['DISPLAY_NAME']) || !empty($data['email_address'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO CWD_USER (USER_NAME, DISPLAY_NAME, email_address, active)
                    VALUES (:user_name, :display_name, :email_address, :active)
                ");
                
                $stmt->execute([
                    ':user_name' => $data['USER_KEY'],
                    ':display_name' => $data['DISPLAY_NAME'] ?? $data['USER_KEY'],
                    ':email_address' => $data['email_address'] ?? null,
                    ':active' => isset($data['active']) ? 1 : 1
                ]);
            }
            
            $this->db->commit();
            return $newId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function updateUser($userKey, $data) {
        try {
            $this->db->beginTransaction();
            
            // Update APP_USER
            $stmt = $this->db->prepare("
                UPDATE APP_USER 
                SET LOWER_USER_NAME = :lower_user_name
                WHERE USER_KEY = :user_key
            ");
            
            $stmt->execute([
                ':lower_user_name' => strtolower($data['USER_KEY']),
                ':user_key' => $userKey
            ]);
            
            // Update or insert CWD_USER record
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM CWD_USER WHERE USER_NAME = :user_name
            ");
            $stmt->execute([':user_name' => $userKey]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $stmt = $this->db->prepare("
                    UPDATE CWD_USER 
                    SET DISPLAY_NAME = :display_name, 
                        email_address = :email_address,
                        active = :active
                    WHERE USER_NAME = :user_name
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO CWD_USER (USER_NAME, DISPLAY_NAME, email_address, active)
                    VALUES (:user_name, :display_name, :email_address, :active)
                ");
            }
            
            $params = [
                ':user_name' => $userKey,
                ':display_name' => $data['DISPLAY_NAME'] ?? $userKey,
                ':email_address' => $data['email_address'] ?? null,
                ':active' => isset($data['active']) ? 1 : 0
            ];
            
            $stmt->execute($params);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function deleteUser($userKey) {
        try {
            $this->db->beginTransaction();
            
            // Delete from CWD_USER first (if exists)
            $stmt = $this->db->prepare("DELETE FROM CWD_USER WHERE USER_NAME = :user_name");
            $stmt->execute([':user_name' => $userKey]);
            
            // Delete from APP_USER
            $stmt = $this->db->prepare("DELETE FROM APP_USER WHERE USER_KEY = :user_key");
            $stmt->execute([':user_key' => $userKey]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function userKeyExists($userKey, $excludeUserKey = null) {
        $query = "SELECT COUNT(*) FROM APP_USER WHERE USER_KEY = :user_key";
        $params = [':user_key' => $userKey];
        
        if ($excludeUserKey) {
            $query .= " AND USER_KEY != :exclude_key";
            $params[':exclude_key'] = $excludeUserKey;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
