<?php
// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: /admin/login');
    exit;
}

global $db;

// Fetch reservations with room details
$reservations = [];
$query = "
    SELECT r.*, rm.room_type,
           DATEDIFF(r.check_out_date, r.check_in_date) * rm.price_per_night AS total_price
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.id
    ORDER BY r.created_at DESC
";
$result = mysqli_query($db, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Add default values if columns don't exist
        $row['reservation_type'] = $row['reservation_type'] ?? 'online';
        $row['status'] = $row['status'] ?? 'pending';
        $reservations[] = $row;
    }
} else {
    error_log("Dashboard query failed: " . mysqli_error($db));
}
?>
<div class="admin-dashboard">
    <div class="container">
        <div class="admin-header">
            <h2>Dashboard</h2>
            <a href="<?php echo url('admin/add-walkin'); ?>" class="btn btn-primary">Add Walk-in</a>
        </div>

        <?php if (empty($reservations)): ?>
            <p>No reservations found.</p>
        <?php else: ?>
        <table class="reservations-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Total</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td>#<?php echo $res['id']; ?></td>
                        <td><?php echo htmlspecialchars($res['guest_name']); ?><br>
                            <small><?php echo htmlspecialchars($res['guest_email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($res['room_type']); ?></td>
                        <td><?php echo formatDate($res['check_in_date']); ?></td>
                        <td><?php echo formatDate($res['check_out_date']); ?></td>
                        <td><?php echo formatPrice($res['total_price']); ?></td>
                        <td><span class="badge badge-<?php echo $res['reservation_type']; ?>"><?php echo $res['reservation_type']; ?></span></td>
                        <td><span class="badge badge-<?php echo $res['status']; ?>"><?php echo $res['status']; ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>