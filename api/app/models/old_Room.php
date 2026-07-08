<?php
require_once APP_PATH . '/config/database.php';

class Room {
    private $db;
    
  public function __construct() {
    global $db; // Use the database connection from database.php
    $this->db = $db;
}
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM rooms ORDER BY price_per_night ASC");
        return $stmt->fetchAll();
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getGallery($room) {
        if (!empty($room['gallery_images'])) {
            return explode(',', $room['gallery_images']);
        }
        return [];
    }
}

