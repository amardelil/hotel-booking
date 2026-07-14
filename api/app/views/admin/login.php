<?php
require_once APP_PATH . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $db;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($db, "SELECT * FROM admins WHERE email = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        // --- DEBUG ---
        error_log("Login attempt for email: " . $email);
        error_log("Hash from DB: " . ($admin['password_hash'] ?? 'NULL'));
        error_log("Password entered: " . $password);
        error_log("password_verify result: " . (password_verify($password, $admin['password_hash']) ? 'true' : 'false'));
        // --- END DEBUG ---

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $admin['email'];
            header('Location: /admin/dashboard');
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Database error";
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