<?php
/**
 * Database connection for TiDB Cloud on Vercel
 * Uses multiple fallback methods to read environment variables
 */

// Try multiple ways to get environment variables (Vercel compatibility)
$host = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '';
$port = getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '4000';
$user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? '';
$pass = getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '';
$dbname = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? '';

// DEBUG: Remove this line after it works (logs to Vercel function logs)
error_log("DB_HOST: $host, DB_USER: $user, DB_NAME: $dbname, DB_PASSWORD: " . (empty($pass) ? 'EMPTY!' : 'SET'));

// Check if any required variable is missing
if (empty($host) || empty($user) || empty($pass) || empty($dbname)) {
    die("❌ Database configuration error: Missing environment variables. Check Vercel settings.");
}

// Initialize connection
$conn = mysqli_init();
if (!$conn) {
    die("❌ Failed to initialize MySQL connection");
}

// Enable SSL for TiDB Cloud (required)
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Set connection timeout (TiDB Cloud can be slow)
mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 15);

// Establish connection
$link = mysqli_real_connect(
    $conn,
    $host,
    $user,
    $pass,
    $dbname,
    (int)$port,
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$link) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
if (!mysqli_set_charset($conn, 'utf8mb4')) {
    error_log("Warning: Could not set charset: " . mysqli_error($conn));
}

// Make sure your project variables match what your controllers expect
// If your project uses $db or $pdo, change $conn below to match it
$db = $conn;

// Optional: Test query (remove after confirming it works)
// $result = mysqli_query($conn, "SELECT 1");
// if (!$result) error_log("Test query failed: " . mysqli_error($conn));
?>

