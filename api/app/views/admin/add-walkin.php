<div class="admin-walkin">
    <div class="container">
        <h2>Add Walk-in Reservation</h2>
        <form action="<?php echo url('admin/add-walkin'); ?>" method="POST">
            <div class="form-group">
                <label>Room *</label>
                <select name="room_id" required>
                    <option value="">Select Room</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>">
                            <?php echo $room['room_type']; ?> - <?php echo formatPrice($room['price_per_night']); ?>/night
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Customer Name *</label>
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
                <input type="date" name="check_in" required>
            </div>
            <div class="form-group">
                <label>Check-out Date *</label>
                <input type="date" name="check_out" required>
            </div>
            <div class="form-group">
                <label>Adults *</label>
                <input type="number" name="adults" min="1" required>
            </div>
            <div class="form-group">
                <label>Children</label>
                <input type="number" name="children" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Add Reservation</button>
        </form>
    </div>
</div>

