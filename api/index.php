<?php
// 1. Automatically fix all folder link distances from this file
define('ROOT_DIR', __DIR__);

// 2. Load your core system files safely using the new dynamic path
require_once ROOT_DIR . '/app/helpers/functions.php';
require_once ROOT_DIR . '/app/config/database.php';

// 3. Dynamically set your BASE_PATH so your CSS and Images load perfectly on Vercel
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
define('BASE_PATH', $protocol . $_SERVER['HTTP_HOST']);

// 4. Clean up the URL string to find which page the user wants to see
$request_uri = $_SERVER['REQUEST_URI'];
$base_script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

if ($base_script_path !== '/') {
    if (strpos($request_uri, $base_script_path) === 0) {
        $request_uri = substr($request_uri, strlen($base_script_path));
    }
}


$path = trim(parse_url($request_uri, PHP_URL_PATH), '/');
$segments = explode('/', $path);
$route = $segments[0] ?: 'home';

// If there is an ID (like room/1), pass it as a GET parameter
error_log(" DEBUG: Request URI = " . $_SERVER['REQUEST_URI']);
error_log(" DEBUG: Path = " . $path);
error_log(" DEBUG: Route = " . $route);
if (isset($segments[1]) && is_numeric($segments[1])) {
    $_GET['id'] = $segments[1];
}
// --- ADMIN ROUTING (CHECKED FIRST BEFORE ANY 404) ---
if ($route === 'admin' || strpos($route, 'admin/') === 0) {
    $admin_page = $segments[1] ?? 'login';
    $admin_file = ROOT_DIR . "/app/views/admin/{$admin_page}.php";
    if (file_exists($admin_file)) {
        include ROOT_DIR . '/app/views/layout/header.php';
        include $admin_file;
        include ROOT_DIR . '/app/views/layout/footer.php';
    } else {
        echo "Admin page '{$admin_page}' not found. Create app/views/admin/{$admin_page}.php";
    }
    exit; // STOP HERE - DO NOT RUN 404 LOGIC
}

// --- FETCH ROOMS (FORCED TO WORK) ---
$rooms = []; // Always defined

if ($route == 'home' && isset($db) && $db) {
    $result = mysqli_query($db, "SELECT * FROM rooms LIMIT 6");
    if ($result) {
        $rooms = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        // If query fails, log it and show dummy rooms so you see something
        error_log("Query failed: " . mysqli_error($db));
    }
}

// --- FALLBACK: If no rooms from DB, show dummy rooms for testing ---
if (empty($rooms)) {
    $rooms = [
        [
            'room_type' => 'Test Room 1',
            'description' => 'This is a test room (database not loaded).',
            'price_per_night' => 99.99,
            'max_occupancy' => 2,
            'cover_image' => 'room1.jpg'
        ],
        [
            'room_type' => 'Test Room 2',
            'description' => 'Another test room.',
            'price_per_night' => 149.99,
            'max_occupancy' => 3,
            'cover_image' => 'room2.jpg'
        ]
    ];
}

// 5. Automatically find and load the correct Controller or View file
$controller_file = ROOT_DIR . "/app/controllers/{$route}Controller.php";
$view_file = ROOT_DIR . "/app/views/{$route}.php";



if (file_exists($controller_file)) {
    require_once $controller_file;
} elseif (file_exists($view_file)) {
    require_once $view_file;
} else {
    // If a room or page doesn't exist, show your 404 page safely
    $error_view = ROOT_DIR . '/app/views/404.php';
    if (file_exists($error_view)) {
        require_once $error_view;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "404 - Page Not Found";
    }
}
