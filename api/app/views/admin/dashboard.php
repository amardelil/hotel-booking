<div class="admin-dashboard">
    <div class="container">
        <div class="admin-header">
            <h2>Dashboard</h2>
            <a href="<?php echo url('admin/add-walkin'); ?>" class="btn btn-primary">Add Walk-in</a>
        </div>
        
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
                        <td><?php echo $res['customer_name']; ?><br><small><?php echo $res['customer_email']; ?></small></td>
                        <td><?php echo $res['room_type']; ?></td>
                        <td><?php echo formatDate($res['check_in_date']); ?></td>
                        <td><?php echo formatDate($res['check_out_date']); ?></td>
                        <td><?php echo formatPrice($res['total_price']); ?></td>
                        <td><span class="badge badge-<?php echo $res['reservation_type']; ?>"><?php echo $res['reservation_type']; ?></span></td>
                        <td><span class="badge badge-<?php echo $res['status']; ?>"><?php echo $res['status']; ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

