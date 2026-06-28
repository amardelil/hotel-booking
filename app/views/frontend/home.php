<section class="hero" style="background-image: url('<?php echo uploads('exterior.jpg'); ?>');">
    <div class="hero-content">
        <h2>Where the Horizon Meets Your Peace.</h2>
        <p>Luxury beachfront resort in Miami Beach</p>
        <a href="#rooms" class="btn">Explore Rooms</a>
    </div>
</section>

<section id="rooms" class="rooms-section">
    <div class="container">
        <h2>Our Rooms</h2>
        <div class="room-grid">
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <img src="<?php echo uploads($room['cover_image']); ?>" alt="<?php echo $room['room_type']; ?>">
                    <div class="room-info">
                        <h3><?php echo $room['room_type']; ?></h3>
                        <p><?php echo substr($room['description'], 0, 100) . '...'; ?></p>
                        <div class="room-meta">
                            <span class="price"><?php echo formatPrice($room['price_per_night']); ?> / night</span>
                            <span class="capacity">👤 <?php echo $room['max_occupancy']; ?></span>
                        </div>
                        <a href="<?php echo url('room/' . $room['id']); ?>" class="btn btn-small">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

