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
            WHERE cu.active = 1
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
            FROM USER u
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
}
