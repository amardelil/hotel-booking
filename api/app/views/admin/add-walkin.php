<?php
// Check admin session
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: /admin/login');
    exit;
}

global $db;

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id        = (int)($_POST['room_id'] ?? 0);
    $customer_name  = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $check_in_str   = trim($_POST['check_in'] ?? '');
    $check_out_str  = trim($_POST['check_out'] ?? '');
    $adults         = (int)($_POST['adults'] ?? 1);
    $children       = (int)($_POST['children'] ?? 0);

    // Validate
    if (empty($customer_name) || empty($customer_email) || empty($check_in_str) || empty($check_out_str) || empty($room_id)) {
        $error = "All required fields must be filled.";
    } else {
        // Convert dates
        $check_in_date  = date('Y-m-d', strtotime($check_in_str));
        $check_out_date = date('Y-m-d', strtotime($check_out_str));
        if ($check_in_date == '1970-01-01') $check_in_date = date('Y-m-d');
        if ($check_out_date == '1970-01-01') $check_out_date = date('Y-m-d', strtotime('+1 day'));

        // Get room price
        $room_price = 0;
        $stmt_price = mysqli_prepare($db, "SELECT price_per_night FROM rooms WHERE id = ?");
        if ($stmt_price) {
            mysqli_stmt_bind_param($stmt_price, "i", $room_id);
            mysqli_stmt_execute($stmt_price);
            $result_price = mysqli_stmt_get_result($stmt_price);
            $room_data = mysqli_fetch_assoc($result_price);
            $room_price = $room_data['price_per_night'] ?? 0;
            mysqli_stmt_close($stmt_price);
        }

        $nights = (strtotime($check_out_date) - strtotime($check_in_date)) / (60 * 60 * 24);
        $total_price = $nights * $room_price;
        if ($total_price < 0) $total_price = 0;

        $total_guests = $adults + $children;

        // Insert walk-in reservation (status = 'confirmed' and reservation_type = 'walk_in')
        $stmt = mysqli_prepare($db,
            "INSERT INTO reservations 
                (room_id, customer_name, customer_email, customer_phone, check_in_date, check_out_date, adults, children, total_price, guest_name, guest_email, guest_phone, guests, status, reservation_type, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'walk_in', NOW())"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt,
                "sssssssssssss",
                $room_id,
                $customer_name,
                $customer_email,
                $customer_phone,
                $check_in_date,
                $check_out_date,
                $adults,
                $children,
                $total_price,
                $customer_name,   // guest_name
                $customer_email,  // guest_email
                $customer_phone,  // guest_phone
                $total_guests     // guests
            );
            if (mysqli_stmt_execute($stmt)) {
                $success = "Walk-in reservation added successfully!";
                // Clear POST to prevent re-submission on refresh
                $_POST = [];
            } else {
                $error = "Insert failed: " . mysqli_stmt_error($stmt);
            }
        } else {
            $error = "DB prepare error: " . mysqli_error($db);
        }
    }
}

// --- Fetch all rooms for dropdown ---
$rooms = [];
$result = mysqli_query($db, "SELECT id, room_type, price_per_night FROM rooms ORDER BY price_per_night ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rooms[] = $row;
    }
}
?>
<div class="admin-walkin">
    <div class="container">
        <h2>Add Walk-in Reservation</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="<?php echo url('admin/add-walkin'); ?>" method="POST">
            <div class="form-group">
                <label>Room *</label>
                <select name="room_id" required>
                    <option value="">Select Room</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>">
                            <?php echo htmlspecialchars($room['room_type']); ?> - <?php echo formatPrice($room['price_per_night']); ?>/night
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Customer Name *</label>
                <input type="text" name="customer_name" value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="customer_email" value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="customer_phone" value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Check-in Date *</label>
                <input type="date" name="check_in" value="<?php echo htmlspecialchars($_POST['check_in'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Check-out Date *</label>
                <input type="date" name="check_out" value="<?php echo htmlspecialchars($_POST['check_out'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Adults *</label>
                <input type="number" name="adults" min="1" value="<?php echo htmlspecialchars($_POST['adults'] ?? 1); ?>" required>
            </div>
            <div class="form-group">
                <label>Children</label>
                <input type="number" name="children" min="0" value="<?php echo htmlspecialchars($_POST['children'] ?? 0); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Add Reservation</button>
        </form>
    </div>
</div>