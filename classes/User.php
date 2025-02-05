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
        $stmt = $this->db->query("SELECT USER_KEY, LOWER_USER_NAME FROM APP_USER ORDER BY LOWER_USER_NAME");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
