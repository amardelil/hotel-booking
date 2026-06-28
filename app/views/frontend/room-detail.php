<div class="room-detail">
    <div class="container">
        <div class="room-header">
            <h2><?php echo $room['room_type']; ?></h2>
            <div class="room-price-large"><?php echo formatPrice($room['price_per_night']); ?> / night</div>
        </div>
        
        <div class="room-gallery">
            <div class="main-image">
                <img src="<?php echo uploads($room['cover_image']); ?>" alt="<?php echo $room['room_type']; ?>">
            </div>
            <div class="thumbnails">
                <?php foreach ($gallery as $img): ?>
                    <img src="<?php echo uploads($img); ?>" alt="Gallery image">
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="room-details-grid">
            <div class="room-description">
                <h3>Description</h3>
                <p><?php echo $room['description']; ?></p>
                <h3>Amenities</h3>
                <ul>
                    <?php 
                    $amenities = explode(',', $room['amenities']);
                    foreach ($amenities as $item): ?>
                        <li><?php echo trim($item); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Max Occupancy:</strong> <?php echo $room['max_occupancy']; ?> guests</p>
            </div>
            
            <div class="booking-form">
                <h3>Reserve Now</h3>
                <form action="<?php echo url('reserve'); ?>" method="POST">
                    <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="customer_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="customer_email" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="customer_phone">
                    </div>
                    <div class="form-group">
                        <label>Check-in Date *</label>
                        <input type="date" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Check-out Date *</label>
                        <input type="date" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    <div class="form-group">
                        <label>Adults *</label>
                        <input type="number" name="adults" min="1" max="<?php echo $room['max_occupancy']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Children</label>
                        <input type="number" name="children" min="0" max="2">
                    </div>
                    <button type="submit" class="btn btn-primary">Reserve Now – Pay at Hotel</button>
                    <p class="policy-note">Free cancellation up to 48 hours before check-in.</p>
                </form>
            </div>
        </div>
        
        <!-- Google Maps -->
        <div class="room-map">
            <h3>Location</h3>
            <div id="map" style="height: 400px; width: 100%;" 
                 data-lat="25.7907" data-lng="-80.1300"
                 data-address="1200 Ocean Drive, Miami Beach, FL 33139">
            </div>
        </div>
    </div>
</div>

