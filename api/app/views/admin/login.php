<?php
require_once APP_PATH . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $db;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 1. Check if we can connect to the database
    if (!$db) {
        die("Database connection failed.");
    }

    // 2. Query
    $stmt = mysqli_prepare($db, "SELECT * FROM admins WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($db));
    }
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);

    // 3. Debug output – shown directly on the page
    echo "<pre style='background:#f4f4f4;padding:10px;'>";
    echo "Email entered: " . htmlspecialchars($email) . "\n";
    echo "Password entered: " . htmlspecialchars($password) . "\n";
    echo "Admin found: " . ($admin ? 'YES' : 'NO') . "\n";
    if ($admin) {
        echo "Hash from DB: " . htmlspecialchars($admin['password_hash']) . "\n";
        $verify = password_verify($password, $admin['password_hash']);
        echo "password_verify result: " . ($verify ? 'TRUE' : 'FALSE') . "\n";
    }
    echo "</pre>";

    // 4. Actual login logic
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $admin['email'];
        header('Location: /admin/dashboard');
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
</head>
<body>
<div class="admin-login">
    <div class="container">
        <div class="login-box">
            <h2>Admin Login</h2>
            <?php if (isset($error)): ?>
                <div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="/admin/login" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>