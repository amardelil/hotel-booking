<?php
// --- SERVE STATIC FILES (images, CSS, JS) ---
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
if (strpos($path, '/public/') === 0) {
    $file = __DIR__ . '/..' . $path;  // go up one level to project root
    if (file_exists($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'svg'  => 'image/svg+xml',
        ];
        if (isset($mime[$ext])) header('Content-Type: ' . $mime[$ext]);
        readfile($file);
        exit;
    }
}
// --- END STATIC HANDLER ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_DIR', __DIR__);
define('APP_PATH', ROOT_DIR . '/app');
define('APP_VERSION', '2026-07-08-FINAL-V2');

require_once ROOT_DIR . '/app/helpers/functions.php';
require_once ROOT_DIR . '/app/config/database.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
define('BASE_PATH', $protocol . $_SERVER['HTTP_HOST']);

// --- version route to confirm deployment ---
if ($_SERVER['REQUEST_URI'] === '/version' || $_SERVER['REQUEST_URI'] === '/version/') {
    header('Content-Type: text/plain');
    echo APP_VERSION;
    exit;
}

// --- URI handling ---
$request_uri = $_SERVER['REQUEST_URI'];
if (getenv('VERCEL')) {
    $base_script_path = '';
} else {
    $base_script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($base_script_path === '/') $base_script_path = '';
}
if ($base_script_path && strpos($request_uri, $base_script_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_script_path));
}
$path = trim(parse_url($request_uri, PHP_URL_PATH), '/');
$segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
$route = $segments[0] ?? 'home';
if (isset($segments[1]) && is_numeric($segments[1])) {
    $_GET['id'] = $segments[1];
}

// --- ADMIN ROUTING ---
if ($route === 'admin') {
    $admin_page = basename($segments[1] ?? 'login');
    $admin_file = ROOT_DIR . "/app/views/admin/{$admin_page}.php";
    $header_file = ROOT_DIR . '/app/views/layout/header.php';
    $footer_file = ROOT_DIR . '/app/views/layout/footer.php';

    // If it's a POST request to login, handle it BEFORE any output
    if ($admin_page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST' && is_file($admin_file)) {
        // Process the login (this file contains the POST handling)
        include $admin_file;
        // If we reached here, login failed (success would have exited with redirect)
        // Now we need to display the login form with error – but we must not re-process POST
        unset($_POST);
        if (is_file($header_file)) include $header_file;
        if (is_file($admin_file)) include $admin_file;
        if (is_file($footer_file)) include $footer_file;
        exit;
    }

    // For GET requests or other admin pages: include header, view, footer
    if (is_file($header_file)) include $header_file;
    if (is_file($admin_file)) include $admin_file;
    if (is_file($footer_file)) include $footer_file;
    exit;
}
// --- RESERVATION HANDLER (FIXED FOR TABLE COLUMNS) ---
if ($route === 'reserve') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        global $db;
        $room_id        = (int)($_POST['room_id'] ?? 0);
        $customer_name  = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $check_in_str   = trim($_POST['check_in'] ?? '');
        $check_out_str  = trim($_POST['check_out'] ?? '');
        $adults         = (int)($_POST['adults'] ?? 1);
        $children       = (int)($_POST['children'] ?? 0);

        // --- Validate ---
        if (empty($customer_name) || empty($customer_email) || empty($check_in_str) || empty($check_out_str)) {
            http_response_code(400);
            echo "All required fields must be filled.";
            exit;
        }

        // --- Convert dates ---
        $check_in_date  = date('Y-m-d', strtotime($check_in_str));
        $check_out_date = date('Y-m-d', strtotime($check_out_str));
        if ($check_in_date == '1970-01-01') $check_in_date = date('Y-m-d');
        if ($check_out_date == '1970-01-01') $check_out_date = date('Y-m-d', strtotime('+1 day'));

        // --- Get room price to calculate total ---
        $room_price = 0;
        $stmt_price = mysqli_prepare($db, "SELECT price_per_night FROM rooms WHERE id = ?");
        if ($stmt_price) {
            mysqli_stmt_bind_param($stmt_price, "i", $room_id);
            mysqli_stmt_execute($stmt_price);
            $result_price = mysqli_stmt_get_result($stmt_price);
            $room_data = mysqli_fetch_assoc($result_price);
            $room_price = $room_data['price_per_night'] ?? 0;
            mysqli_stmt_close($stmt_price);
        }

        // Calculate number of nights and total price
        $nights = (strtotime($check_out_date) - strtotime($check_in_date)) / (60 * 60 * 24);
        $total_price = $nights * $room_price;
        if ($total_price < 0) $total_price = 0;

        // --- Insert into reservations ---
        $stmt = mysqli_prepare($db,
            "INSERT INTO reservations 
                (room_id, customer_name, customer_email, customer_phone, check_in_date, check_out_date, adults, children, total_price, status, reservation_type, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'online', NOW())"
        );
        if (!$stmt) {
            http_response_code(500);
            echo "DB prepare error: " . mysqli_error($db);
            exit;
        }

mysqli_stmt_bind_param($stmt, "sssssssss", $room_id, $customer_name, $customer_email, $customer_phone, $check_in_date, $check_out_date, $adults, $children, $total_price);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: /success');
            exit;
        } else {
            http_response_code(500);
            echo "Insert failed: " . mysqli_stmt_error($stmt);
            exit;
        }
    } else {
        header('Location: /');
        exit;
    }
}

// --- FETCH ROOMS for homepage ---
$rooms = [];
if ($route === 'home' && isset($db) && $db) {
    $result = mysqli_query($db, "SELECT * FROM rooms LIMIT 6");
    if ($result) $rooms = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
if (empty($rooms) && $route === 'home') {
    $rooms = [
        ['room_type' => 'Test Room 1', 'description' => 'Test description', 'price_per_night' => 99.99, 'max_occupancy' => 2, 'cover_image' => 'room1.jpg'],
        ['room_type' => 'Test Room 2', 'description' => 'Another test', 'price_per_night' => 149.99, 'max_occupancy' => 3, 'cover_image' => 'room2.jpg']
    ];
}

// --- CONTROLLER / VIEW DISPATCH ---
$controller_file = ROOT_DIR . "/app/controllers/" . ucfirst($route) . "Controller.php";
$view_file = ROOT_DIR . "/app/views/{$route}.php";

if (is_file($controller_file)) {
    require_once $controller_file;
    $class_name = ucfirst($route) . 'Controller';
    if (class_exists($class_name)) {
        $controller = new $class_name();
        $action = $segments[1] ?? 'index';
        if (is_numeric($action)) {
            if (method_exists($controller, 'show')) {
                $controller->show($action);
            } else {
                http_response_code(500);
                echo "Method show() not found in $class_name";
            }
        } elseif (method_exists($controller, $action)) {
            $controller->$action();
        } elseif (method_exists($controller, 'index')) {
            $controller->index();
        } else {
            http_response_code(404);
            echo "No suitable method in $class_name";
        }
    } else {
        http_response_code(500);
        echo "Class $class_name not found";
    }
    exit;
} elseif (is_file($view_file)) {
    require_once $view_file;
    exit;
} else {
    $error_view = ROOT_DIR . '/app/views/404.php';
    if (is_file($error_view)) {
        require_once $error_view;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "404 - Page Not Found";
    }
    exit;
}