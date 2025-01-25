<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct($config) {
        $this->pdo = new PDO(
            $config['dsn'], 
            $config['username'], 
            $config['password']
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

