<?php
require_once APP_PATH . '/config/database.php';

class Room {
    private $db;
    
    public function __construct() {
        global $db;      // This is the mysqli connection from database.php
        $this->db = $db;
    }
    
    public function getAll() {
        // MySQLi query
        $result = mysqli_query($this->db, "SELECT * FROM rooms ORDER BY price_per_night ASC");
        if (!$result) {
            error_log("getAll query failed: " . mysqli_error($this->db));
            return [];
        }
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function find($id) {
        // Use prepared statement for security
        $stmt = mysqli_prepare($this->db, "SELECT * FROM rooms WHERE id = ?");
        if (!$stmt) {
            error_log("find prepare failed: " . mysqli_error($this->db));
            return null;
        }
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function getGallery($room) {
        if (!empty($room['gallery_images'])) {
            return explode(',', $room['gallery_images']);
        }
        return [];
    }
}