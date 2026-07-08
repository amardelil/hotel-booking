<?php
session_start();
require_once APP_PATH . '/config/database.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $db;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Prepare and execute query
    $stmt = mysqli_prepare($db, "SELECT * FROM admins WHERE email = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
        
        // Verify password (plain text for now – use password_verify() if hashed)
        if ($admin && $password === $admin['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $admin['email'];
            header('Location: /admin/dashboard');
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Database error: " . mysqli_error($db);
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
                <div style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></div>
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