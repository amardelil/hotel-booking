<?php
// This file tests if your database is CONNECTED
require_once __DIR__ . '/app/config/database.php';

if ($db) {
    echo "✅ SUCCESS! Database is CONNECTED!<br>";
    
    // Test if we can read data
    $result = mysqli_query($db, "SELECT 1");
    if ($result) {
        echo "✅ Query works perfectly! Your TiDB is alive.";
    } else {
        echo "❌ Query failed: " . mysqli_error($db);
    }
} else {
    echo "❌ FAILED: Database connection failed. Check password or host.";
}
?>

