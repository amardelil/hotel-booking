<?php
// SILENT mode - NO echo, NO die, NO output to browser!

// Read environment variables with fallback
$host = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '';
$port = getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '4000';
$user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? '';
$pass = getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '';
$dbname = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? '';

// Log to Vercel internal logs (this NEVER shows on your webpage)
error_log("🔍 DB Check - Host: $host, User: $user, DB: $dbname, Password Set: " . (empty($pass) ? '❌ MISSING' : '✅ YES'));

// Attempt connection
$conn = mysqli_init();
if ($conn) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 15);
    
    $link = mysqli_real_connect($conn, $host, $user, $pass, $dbname, (int)$port, NULL, MYSQLI_CLIENT_SSL);
    
    if ($link) {
        mysqli_set_charset($conn, 'utf8mb4');
        $db = $conn; // Success! Make it available.
        error_log("✅ Database CONNECTED successfully!");
    } else {
        error_log("❌ DB FAILED: " . mysqli_connect_error());
        $db = false; // Failed silently
    }
} else {
    error_log("❌ mysqli_init failed");
    $db = false;
}

// ⚠️ CRITICAL: Do NOT put any echo, print, or whitespace after this closing tag!