<?php
// 1. Load the Header (This loads your CSS)
$pageTitle = 'Home - Hotel Booking';
include ROOT_DIR . '/app/views/layout/header.php';
?>

<!-- Hero Section -->
<section id="hero" class="hero-section">
    <div class="container">
        <h1>Where the Horizon Meets Your Peace.</h1>
        <p>Luxury beachfront resort in Miami Beach</p>
        <a href="#rooms" class="btn" onclick="document.getElementById('rooms').scrollIntoView({behavior:'smooth'}); return false;">Explore Rooms</a>
    </div>
</section>

<!-- Rooms Section -->
<section id="rooms" class="rooms-section">
    <div class="container">
        <h2>Our Rooms</h2>
        <div class="room-grid">
        <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <!-- FIXED IMAGE PATH: I removed the broken "uploads()" function -->
                <img src="/uploads/<?php echo $room['cover_image']; ?>" alt="<?php echo $room['room_type']; ?>">
                <div class="room-info">
                    <h3><?php echo $room['room_type']; ?></h3>
                    <p><?php echo substr($room['description'], 0, 100) . '...'; ?></p>
                    <div class="room-meta">
                        <span class="price">$<?php echo number_format($room['price_per_night'], 2); ?></span>
                        <span class="capacity">👤 <?php echo $room['max_occupancy']; ?> guests</span>
                    </div>
                    <a href="<?php echo url('room/'. $room['id']); ?>" class="btn btn-small">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.querySelector('a[href="#rooms"]');
    if (btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // prevent default anchor behavior
            var target = document.getElementById('rooms');
            if (target) {
                var offset = 70; // adjust if you have a fixed header
                var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    }
});
</script>
<?php
// 2. Load the Footer
include ROOT_DIR . '/app/views/layout/footer.php';
?>