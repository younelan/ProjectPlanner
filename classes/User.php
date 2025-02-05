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

    public function getAllUsers() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT 
                a.USER_KEY as username,
                a.LOWER_USER_NAME
            FROM APP_USER a
            ORDER BY a.LOWER_USER_NAME ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Only return the username column
    }
}
