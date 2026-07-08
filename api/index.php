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

// Filter out empty segments so trailing slashes ("/room/1/") don't break numeric ID detection
$segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
$route = $segments[0] ?? 'home';

if (isset($segments[1]) && is_numeric($segments[1])) {
    $_GET['id'] = $segments[1];
}

// --- DEBUG BLOCK (temporary — remove once routing is confirmed working) ---
error_log("DEBUG: REQUEST_URI = " . $_SERVER['REQUEST_URI']);
error_log("DEBUG: path        = " . $path);
error_log("DEBUG: route       = " . $route);
error_log("DEBUG: ROOT_DIR    = " . ROOT_DIR);
error_log("DEBUG: segments    = " . print_r($segments, true));

// --- ADMIN ROUTING (CHECKED FIRST BEFORE ANY 404) ---
if ($route === 'admin') {
    $admin_page = $segments[1] ?? 'login';

    // Guard against path traversal via the URL (e.g. /admin/../../etc)
    $admin_page = basename($admin_page);

    $admin_file = ROOT_DIR . "/app/views/admin/{$admin_page}.php";
    $header_file = ROOT_DIR . '/app/views/layout/header.php';
    $footer_file = ROOT_DIR . '/app/views/layout/footer.php';

    error_log("DEBUG: admin_file  = " . $admin_file . " | exists=" . (is_file($admin_file) ? 'YES' : 'NO'));
    error_log("DEBUG: header_file = " . $header_file . " | exists=" . (is_file($header_file) ? 'YES' : 'NO'));
    error_log("DEBUG: footer_file = " . $footer_file . " | exists=" . (is_file($footer_file) ? 'YES' : 'NO'));

    if (is_file($admin_file)) {
        if (is_file($header_file)) include $header_file;
        include $admin_file;
        if (is_file($footer_file)) include $footer_file;
    } else {
        // Show the EXACT path we looked for, so case/typo issues are obvious in the browser too
        http_response_code(500);
        echo "<pre>Admin view not found.\nLooked for: {$admin_file}\n";
        echo "This is almost always a filename case mismatch (Linux servers are case-sensitive)\n";
        echo "or the file wasn't actually committed/deployed to Vercel. Check:\n";
        echo "1. The exact casing of every folder/file in the path above.\n";
        echo "2. That the file is committed to git and included in the deployment.</pre>";
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

error_log("DEBUG: controller_file = {$controller_file} | exists=" . (is_file($controller_file) ? 'YES' : 'NO'));
error_log("DEBUG: view_file       = {$view_file} | exists=" . (is_file($view_file) ? 'YES' : 'NO'));

if (is_file($controller_file)) {
    require_once $controller_file;
} elseif (is_file($view_file)) {
    require_once $view_file;
} else {
    $error_view = ROOT_DIR . '/app/views/404.php';
    if (is_file($error_view)) {
        require_once $error_view;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "404 - Page Not Found";
        echo "<!-- Looked for controller: {$controller_file} and view: {$view_file} -->";
    }
}
