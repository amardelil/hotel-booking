<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = env('DB_HOST', 'localhost');
        $dbname = env('DB_NAME', 'hotel_booking');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');
        
        try {
            $this->connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

