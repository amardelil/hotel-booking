<?php
require_once APP_PATH . '/models/Room.php';

class RoomController {
    private $roomModel;
    
    public function __construct() {
        $this->roomModel = new Room();
    }
    
    public function index() {
        $rooms = $this->roomModel->getAll();
        $pageTitle = "Home - Grand Ocean View";
        require_once APP_PATH . '/views/layout/header.php';
        require_once APP_PATH . '/views/frontend/home.php';
        require_once APP_PATH . '/views/layout/footer.php';
    }
    
    public function show($id) {
        $room = $this->roomModel->find($id);
        if (!$room) {
            http_response_code(404);
            echo "Room not found";
            return;
        }
        $gallery = $this->roomModel->getGallery($room);
        $pageTitle = $room['room_type'] . " - Grand Ocean View";
        require_once APP_PATH . '/views/layout/header.php';
        require_once APP_PATH . '/views/frontend/room-detail.php';
        require_once APP_PATH . '/views/layout/footer.php';
    }
}

