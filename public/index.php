<?php
// Load environment variables
$env = parse_ini_file(__DIR__ . '/../.env');
foreach ($env as $key => $value) {
    putenv("$key=$value");
}

// Define root paths
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', ROOT_PATH . '/app');

// Load helper functions
require_once APP_PATH . '/helpers/functions.php';

// ===== SIMPLE PATH EXTRACTION =====
$request_uri = $_SERVER['REQUEST_URI'];
$request_uri = strtok($request_uri, '?'); // Remove query strings
$base_path = '/hotel-booking/public/';

if (strpos($request_uri, $base_path) === 0) {
    $path = substr($request_uri, strlen($base_path));
} else {
    $path = ltrim($request_uri, '/');
}

$path = rtrim($path, '/');
if (empty($path)) {
    $path = 'home';
}
// ==================================

// Route the request
switch ($path) {
    case 'home':
        require_once APP_PATH . '/controllers/RoomController.php';
        $controller = new RoomController();
        $controller->index();
        break;
        
    case (preg_match('/^room\/(\d+)$/', $path, $matches) ? true : false):
        $roomId = $matches[1];
        require_once APP_PATH . '/controllers/RoomController.php';
        $controller = new RoomController();
        $controller->show($roomId);
        break;
        
    case 'reserve':
        require_once APP_PATH . '/controllers/ReservationController.php';
        $controller = new ReservationController();
        $controller->store();
        break;
        
    case 'admin/login':
        require_once APP_PATH . '/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->login();
        break;
        
    case 'admin/dashboard':
        require_once APP_PATH . '/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->dashboard();
        break;
        
    case 'admin/add-walkin':
        require_once APP_PATH . '/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->addWalkin();
        break;
        
    case 'admin/logout':
        require_once APP_PATH . '/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->logout();
        break;
        
    case 'admin':
        require_once APP_PATH . '/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->login();
        break;
        
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}

