<div class="admin-login">
    <div class="container">
        <div class="login-box">
            <h2>Admin Login</h2>
            <form action="/admin/login" method="POST">
                            //form action="?php echo url('admin/login'); ?>" method="POST">
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