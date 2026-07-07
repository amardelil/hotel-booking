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

// --- SMART ROUTING (Fixes Admin & Room Details) ---
$path = trim(parse_url($request_uri, PHP_URL_PATH), '/');
$segments = explode('/', $path);
$route = $segments[0] ?: 'home';

// 1. Handle Admin Panel
if ($route === 'admin') {
    // Load the admin dashboard from the admin folder
    $admin_file = ROOT_DIR . '/app/views/admin/index.php';
    if (file_exists($admin_file)) {
        require_once $admin_file;
        exit;
    } else {
        echo "Admin dashboard coming soon! Please create app/views/admin/index.php";
        exit;
    }
}

// 2. Handle Room Details (e.g., /room/1)
if ($route === 'room' && isset($segments[1]) && is_numeric($segments[1])) {
    $room_id = (int)$segments[1];
    $_GET['id'] = $room_id; // Pass ID to the view if needed

    // Fetch the room from the database
    $result = mysqli_query($db, "SELECT * FROM rooms WHERE id = $room_id");
    if ($result && $room = mysqli_fetch_assoc($result)) {
        // Show the room details using your existing layout
        include ROOT_DIR . '/app/views/layout/header.php';
        ?>
        <div class="container" style="padding: 40px 0;">
            <h1><?php echo $room['room_type']; ?></h1>
            <img src="/uploads/<?php echo $room['cover_image']; ?>" alt="<?php echo $room['room_type']; ?>" style="width:100%; max-width:600px;">
            <p><?php echo $room['description']; ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($room['price_per_night'], 2); ?> / night</p>
            <p><strong>Capacity:</strong> <?php echo $room['max_occupancy']; ?> guests</p>
            <a href="/" class="btn">Back to Home</a>
        </div>
        <?php
        include ROOT_DIR . '/app/views/layout/footer.php';
        exit;
    } else {
        echo "Room not found.";
        exit;
    }
}

// 3. Regular Pages (Home, About, etc.)
$view_file = ROOT_DIR . "/app/views/{$route}.php";
if (file_exists($view_file)) {
    require_once $view_file;
} else {
    // Try to load a controller if view doesn't exist
    $controller_file = ROOT_DIR . "/app/controllers/{$route}Controller.php";
    if (file_exists($controller_file)) {
        require_once $controller_file;
    } else {
        echo "404 - Page Not Found";
    }
}

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
