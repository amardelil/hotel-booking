<?php
require_once APP_PATH . '/config/database.php';

class Reservation {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Check availability – does NOT start a transaction.
     * Assumes the caller (create()) has already started one.
     */
    public function checkAvailability($roomId, $checkIn, $checkOut, $excludeReservationId = null) {
        // This method is called inside a transaction, so we do NOT start a new one.
        $sql = "SELECT * FROM reservations 
                WHERE room_id = ? 
                AND status NOT IN ('cancelled')
                AND (
                    (check_in_date <= ? AND check_out_date > ?) OR
                    (check_in_date < ? AND check_out_date >= ?) OR
                    (check_in_date >= ? AND check_out_date <= ?)
                )";
        
        $params = [
            $roomId,
            $checkIn, $checkIn,
            $checkOut, $checkOut,
            $checkIn, $checkOut
        ];
        
        if ($excludeReservationId) {
            $sql .= " AND id != ?";
            $params[] = $excludeReservationId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Create a reservation – handles the transaction and lock.
     */
    public function create($data) {
        $roomId = $data['room_id'];
        $checkIn = $data['check_in_date'];
        $checkOut = $data['check_out_date'];
        
        // Start transaction ONCE
        $this->db->beginTransaction();
        
        try {
            // Lock the room (SELECT ... FOR UPDATE) – we need to do this inside the transaction.
            // We'll run a SELECT ... FOR UPDATE on the rooms table or on the reservations table.
            // Since we are checking for overlapping reservations, we can lock the relevant rows.
            // But we need a lock that prevents concurrent inserts. We'll lock the reservations table
            // for the specific room and date range using SELECT ... FOR UPDATE.
            // However, we already have a SELECT that checks overlap; we can add FOR UPDATE there.
            // But our checkAvailability() does not include FOR UPDATE because we removed transaction.
            // We'll modify: we'll run a SELECT ... FOR UPDATE to lock the rows.
            // For simplicity, we lock the entire room row in rooms table.
            $lockSql = "SELECT * FROM rooms WHERE id = ? FOR UPDATE";
            $lockStmt = $this->db->prepare($lockSql);
            $lockStmt->execute([$roomId]);
            
            // Now check availability (within the same transaction, the lock is held)
            $overlaps = $this->checkAvailability($roomId, $checkIn, $checkOut);
            if (count($overlaps) > 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Room is not available for selected dates.'];
            }
            
            // Insert the reservation
            $sql = "INSERT INTO reservations 
                    (room_id, customer_name, customer_email, customer_phone, 
                     check_in_date, check_out_date, adults, children, 
                     total_price, status, reservation_type) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['room_id'],
                $data['customer_name'],
                $data['customer_email'],
                $data['customer_phone'] ?? null,
                $data['check_in_date'],
                $data['check_out_date'],
                $data['adults'] ?? 1,
                $data['children'] ?? 0,
                $data['total_price'],
                $data['status'] ?? 'pending',
                $data['reservation_type'] ?? 'online'
            ]);
            
            $reservationId = $this->db->lastInsertId();
            
            // Commit transaction
            $this->db->commit();
            
            return ['success' => true, 'reservation_id' => $reservationId];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getAll() {
        $stmt = $this->db->query("
            SELECT r.*, rooms.room_type, rooms.cover_image 
            FROM reservations r
            JOIN rooms ON r.room_id = rooms.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}

