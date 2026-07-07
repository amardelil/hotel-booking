<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Grand Ocean View'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Google Maps API (optional) -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo env('GOOGLE_MAPS_API_KEY'); ?>&libraries=places"></script>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="/hotel-booking/public/uploads/images/logo.png" alt="Grand Ocean View Logo">
                <h1>Grand Ocean View</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo url('home'); ?>">Home</a></li>
                    <li><a href="<?php echo url('home'); ?>#rooms">Rooms</a></li>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <li><a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a></li>
                        <li><a href="<?php echo url('admin/logout'); ?>">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo url('admin/login'); ?>">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <?php if ($flash = sessionFlash('success')): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flash); ?></div>
        <?php endif; ?>
        <?php if ($flash = sessionFlash('error')): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($flash); ?></div>
        <?php endif; ?>

