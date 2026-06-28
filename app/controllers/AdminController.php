<?php
require_once APP_PATH . '/models/Reservation.php';
require_once APP_PATH . '/models/Room.php';

class AdminController {
    private $reservationModel;
    private $roomModel;
    
    public function __construct() {
        $this->reservationModel = new Reservation();
        $this->roomModel = new Room();
        // Check authentication for all methods except login
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['admin_logged_in']) && $this->getAction() !== 'login') {
            redirect('admin/login');
            exit;
        }
    }
    
    private function getAction() {
        $path = $_SERVER['REQUEST_URI'];
        $path = trim(parse_url($path, PHP_URL_PATH), '/');
        $segments = explode('/', $path);
        return end($segments);
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Hardcoded for demo - you should use a database with hashed passwords
            if ($email === 'manager@grandoceanview.com' && $password === 'Admin@2026') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_email'] = $email;
                sessionFlash('success', 'Logged in successfully.');
                redirect('admin/dashboard');
            } else {
                sessionFlash('error', 'Invalid credentials.');
                redirect('admin/login');
            }
            return;
        }
        
        // Show login form
        $pageTitle = "Admin Login";
        require_once APP_PATH . '/views/layout/header.php';
        require_once APP_PATH . '/views/admin/login.php';
        require_once APP_PATH . '/views/layout/footer.php';
    }
    
    public function logout() {
        session_destroy();
        redirect('admin/login');
    }
    
    public function index() {
        redirect('admin/dashboard');
    }
    
    public function dashboard() {
        $reservations = $this->reservationModel->getAll();
        $pageTitle = "Admin Dashboard";
        require_once APP_PATH . '/views/layout/header.php';
        require_once APP_PATH . '/views/admin/dashboard.php';
        require_once APP_PATH . '/views/layout/footer.php';
    }
    
    public function addWalkin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate similar to online reservation
            $errors = [];
            $required = ['room_id', 'customer_name', 'customer_email', 'check_in', 'check_out', 'adults'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
                }
            }
            
            if (empty($errors)) {
                $checkIn = $_POST['check_in'];
                $checkOut = $_POST['check_out'];
                $room = $this->roomModel->find($_POST['room_id']);
                
                if ($room) {
                    $nights = (strtotime($checkOut) - strtotime($checkIn)) / (60*60*24);
                    $totalPrice = $room['price_per_night'] * $nights;
                    
                    $data = [
                        'room_id' => $_POST['room_id'],
                        'customer_name' => $_POST['customer_name'],
                        'customer_email' => $_POST['customer_email'],
                        'customer_phone' => $_POST['customer_phone'] ?? '',
                        'check_in_date' => $checkIn,
                        'check_out_date' => $checkOut,
                        'adults' => $_POST['adults'],
                        'children' => $_POST['children'] ?? 0,
                        'total_price' => $totalPrice,
                        'status' => 'confirmed', // walk-in is confirmed immediately
                        'reservation_type' => 'walk_in'
                    ];
                    
                    $result = $this->reservationModel->create($data);
                    if ($result['success']) {
                        sessionFlash('success', 'Walk-in reservation added successfully.');
                        redirect('admin/dashboard');
                    } else {
                        sessionFlash('error', $result['message']);
                        redirect('admin/add-walkin');
                    }
                }
            } else {
                sessionFlash('error', implode(' ', $errors));
                redirect('admin/add-walkin');
            }
            return;
        }
        
        // Show form
        $rooms = $this->roomModel->getAll();
        $pageTitle = "Add Walk-in Reservation";
        require_once APP_PATH . '/views/layout/header.php';
        require_once APP_PATH . '/views/admin/add-walkin.php';
        require_once APP_PATH . '/views/layout/footer.php';
    }
}

