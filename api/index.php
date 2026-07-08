<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ROOT_DIR', __DIR__);
define('APP_PATH', ROOT_DIR . '/app');
// ----- TEMPORARY DEBUG BLOCK (add this) -----
if (isset($_GET['debug'])) {
    header('Content-Type: text/plain');
    $request_uri = $_SERVER['REQUEST_URI'];
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = $request_uri;
    if ($base !== '/') {
        if (strpos($uri, $base) === 0) $uri = substr($uri, strlen($base));
    }
    $path = trim(parse_url($uri, PHP_URL_PATH), '/');
    $segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
    $route = $segments[0] ?? 'home';
    echo "REQUEST_URI: $request_uri\n";
    echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
    echo "Base stripped: $base\n";
    echo "URI after strip: $uri\n";
    echo "Path: $path\n";
    echo "Segments: " . print_r($segments, true);
    echo "Route: $route\n";
    echo "ROOT_DIR: " . ROOT_DIR . "\n";
    // Check admin file
    if ($route === 'admin') {
        $admin_page = basename($segments[1] ?? 'login');
        $admin_file = ROOT_DIR . "/app/views/admin/{$admin_page}.php";
        echo "admin_file: $admin_file\n";
        echo "admin_file exists? " . (is_file($admin_file) ? 'YES' : 'NO') . "\n";
    }
    // Check controller/view for room
    if ($route === 'room') {
        $controller = ROOT_DIR . "/app/controllers/" . ucfirst($route) . "Controller.php";
        $view = ROOT_DIR . "/app/views/{$route}.php";
        echo "controller: $controller\n";
        echo "controller exists? " . (is_file($controller) ? 'YES' : 'NO') . "\n";
        echo "view: $view\n";
        echo "view exists? " . (is_file($view) ? 'YES' : 'NO') . "\n";
    }
    exit;
}
// ----- END DEBUG BLOCK -----

// then your existing code continues...
// 2. Load your core system files safely using the new dynamic path
require_once ROOT_DIR . '/app/helpers/functions.php';
require_once ROOT_DIR . '/app/config/database.php';

// 3. Dynamically set your BASE_PATH so your CSS and Images load perfectly on Vercel
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
define('BASE_PATH', $protocol . $_SERVER['HTTP_HOST']);

// --- DIAGNOSTIC TOOL: visit yoursite.com/?debug_files=1 to see what's actually deployed ---
if (isset($_GET['debug_files'])) {
    header('Content-Type: text/plain');
    echo "ROOT_DIR: " . ROOT_DIR . "\n\n";
    echo "--- Contents of app/ as actually deployed on the server ---\n\n";
    function listDir($dir, $prefix = '') {
        if (!is_dir($dir)) { echo "{$prefix}[MISSING DIRECTORY] $dir\n"; return; }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $full = $dir . '/' . $item;
            echo $prefix . (is_dir($full) ? "[DIR]  " : "[FILE] ") . $item . "\n";
            if (is_dir($full)) listDir($full, $prefix . '    ');
        }
    }
    listDir(ROOT_DIR . '/app');
    echo "\n--- Specific checks ---\n";
    echo "app/views/admin/login.php exists: " . (is_file(ROOT_DIR . '/app/views/admin/login.php') ? 'YES' : 'NO') . "\n";
    echo "app/views/room.php exists: " . (is_file(ROOT_DIR . '/app/views/room.php') ? 'YES' : 'NO') . "\n";
    echo "app/controllers/roomController.php exists: " . (is_file(ROOT_DIR . '/app/controllers/roomController.php') ? 'YES' : 'NO') . "\n";
    exit;
}
// --- END DIAGNOSTIC TOOL ---
// 4. Clean up the URL string to find which page the user wants to see
$request_uri = $_SERVER['REQUEST_URI'];

// --- Fix for Vercel: SCRIPT_NAME is not reliable there ---
if (getenv('VERCEL')) {
    // On Vercel, do not strip anything – use the full request URI
    $base_script_path = '';
} else {
    // On local XAMPP, strip the subfolder (e.g., /hotel-booking/api)
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

error_log("DEBUG: REQUEST_URI = " . $_SERVER['REQUEST_URI']);
error_log("DEBUG: path        = " . $path);
error_log("DEBUG: route       = " . $route);
error_log("DEBUG: ROOT_DIR    = " . ROOT_DIR);

// --- ADMIN ROUTING (CHECKED FIRST BEFORE ANY 404) ---
if ($route === 'admin') {
    $admin_page = basename($segments[1] ?? 'login');
    $admin_file = ROOT_DIR . "/app/views/admin/{$admin_page}.php";
    $header_file = ROOT_DIR . '/app/views/layout/header.php';
    $footer_file = ROOT_DIR . '/app/views/layout/footer.php';

    error_log("DEBUG: admin_file = " . $admin_file . " | exists=" . (is_file($admin_file) ? 'YES' : 'NO'));

    if (is_file($admin_file)) {
        if (is_file($header_file)) include $header_file;
        include $admin_file;
        if (is_file($footer_file)) include $footer_file;
    } else {
        http_response_code(500);
        echo "<pre>Admin view not found.\nLooked for: {$admin_file}\n";
        echo "Visit /?debug_files=1 to see the actual deployed file tree.</pre>";
    }
    exit;
}

// --- RESERVATION HANDLER ---
// --- RESERVATION HANDLER ---
if ($route === 'reserve') {
    // Only process POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        global $db;
        
        // Collect form data
        $room_id        = (int)($_POST['room_id'] ?? 0);
        $customer_name  = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $check_in_str   = trim($_POST['check_in'] ?? '');
        $check_out_str  = trim($_POST['check_out'] ?? '');
        $adults         = (int)($_POST['adults'] ?? 1);
        $children       = (int)($_POST['children'] ?? 0);
        $guests         = $adults + $children;

        // --- Validate required fields ---
        $errors = [];
        if (empty($customer_name))  $errors[] = "Full name is required.";
        if (empty($customer_email)) $errors[] = "Email is required.";
        if (empty($check_in_str))   $errors[] = "Check-in date is required.";
        if (empty($check_out_str))  $errors[] = "Check-out date is required.";
        
        // Validate date format (must be YYYY-MM-DD)
        $check_in  = DateTime::createFromFormat('Y-m-d', $check_in_str);
        $check_out = DateTime::createFromFormat('Y-m-d', $check_out_str);
        if (!$check_in)  $errors[] = "Check-in date must be a valid date (YYYY-MM-DD).";
        if (!$check_out) $errors[] = "Check-out date must be a valid date (YYYY-MM-DD).";
        
        // If there are errors, show them and stop
        if (!empty($errors)) {
            http_response_code(400);
            echo "<h3>Reservation failed:</h3><ul>";
            foreach ($errors as $err) echo "<li>" . htmlspecialchars($err) . "</li>";
            echo "</ul><p><a href='javascript:history.back()'>Go back</a></p>";
            exit;
        }

        // Now insert
        $stmt = mysqli_prepare($db,
            "INSERT INTO reservations 
                (room_id, guest_name, guest_email, guest_phone, check_in_date, check_out_date, guests, special_requests, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        if (!$stmt) {
            http_response_code(500);
            echo "Database error: " . htmlspecialchars(mysqli_error($db));
            exit;
        }

        $special_requests = ''; // not used in form, keep empty
        mysqli_stmt_bind_param($stmt, "issssiss", $room_id, $customer_name, $customer_email, $customer_phone, $check_in_str, $check_out_str, $guests, $special_requests);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: /success');
            exit;
        } else {
            http_response_code(500);
            echo "Reservation failed: " . htmlspecialchars(mysqli_stmt_error($stmt));
            exit;
        }
    } else {
        // GET request – redirect to home
        header('Location: /');
        exit;
    }
}
// --- FETCH ROOMS ---
$rooms = [];
if ($route == 'home' && isset($db) && $db) {
    $result = mysqli_query($db, "SELECT * FROM rooms LIMIT 6");
    if ($result) {
        $rooms = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        error_log("Query failed: " . mysqli_error($db));
    }
}

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
// 5. Load and EXECUTE the Controller or fallback to View
$controller_file = ROOT_DIR . "/app/controllers/" . ucfirst($route) . "Controller.php";
$view_file = ROOT_DIR . "/app/views/{$route}.php";

error_log("DEBUG: controller_file = {$controller_file} | exists=" . (is_file($controller_file) ? 'YES' : 'NO'));
error_log("DEBUG: view_file       = {$view_file} | exists=" . (is_file($view_file) ? 'YES' : 'NO'));

if (is_file($controller_file)) {
    require_once $controller_file;
    $class_name = ucfirst($route) . 'Controller';
    if (class_exists($class_name)) {
        $controller = new $class_name();
        // Determine which method to call
        $action = $segments[1] ?? 'index';
        if (is_numeric($action)) {
            // If the second segment is a number, call show($id)
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
        echo "<!-- Looked for controller: {$controller_file} and view: {$view_file} -->";
        echo "<!-- Visit /?debug_files=1 to see the actual deployed file tree -->";
    }
    exit;
}