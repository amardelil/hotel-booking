<?php
require_once APP_PATH . '/models/Reservation.php';
require_once APP_PATH . '/models/Room.php';

class ReservationController {
    private $reservationModel;
    private $roomModel;
    
    public function __construct() {
        $this->reservationModel = new Reservation();
        $this->roomModel = new Room();
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('home');
            return;
        }
        
        // Basic validation
        $errors = [];
        $required = ['room_id', 'customer_name', 'customer_email', 'check_in', 'check_out', 'adults'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
            }
        }
        
        // Date validation
        $checkIn = $_POST['check_in'] ?? '';
        $checkOut = $_POST['check_out'] ?? '';
        if ($checkIn && $checkOut) {
            if (strtotime($checkIn) >= strtotime($checkOut)) {
                $errors[] = "Check-out date must be after check-in date.";
            }
            // Max stay 14 nights
            $diff = (strtotime($checkOut) - strtotime($checkIn)) / (60*60*24);
            if ($diff > 14) {
                $errors[] = "Maximum stay is 14 nights.";
            }
        }
        
        if (!empty($errors)) {
            // For simplicity, redirect back with error message (we'll use session flash)
            sessionFlash('error', implode(' ', $errors));
            redirect('room/' . $_POST['room_id']);
            return;
        }
        
        // Get room details
        $room = $this->roomModel->find($_POST['room_id']);
        if (!$room) {
            sessionFlash('error', "Room not found.");
            redirect('home');
        }
        
        // Calculate total price
        $nights = (strtotime($checkOut) - strtotime($checkIn)) / (60*60*24);
        $totalPrice = $room['price_per_night'] * $nights;
        
        // Prepare reservation data
        $data = [
            'room_id' => $_POST['room_id'],
            'customer_name' => $_POST['customer_name'],
            'customer_email' => $_POST['customer_email'],
            'customer_phone' => $_POST['customer_phone'] ?? '',
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults' => $_POST['adults'],
            'children' => $_POST['children'] ?? 0,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'reservation_type' => 'online'
        ];
        
        // Attempt to create reservation (with concurrency control)
        $result = $this->reservationModel->create($data);
        
        if ($result['success']) {
            // Send confirmation email
            $this->sendConfirmationEmail($data, $room);
            
            sessionFlash('success', "Reservation successful! Confirmation sent to your email.");
            redirect('success');
        } else {
            sessionFlash('error', $result['message']);
            redirect('room/' . $_POST['room_id']);
        }
    }
    
    private function sendConfirmationEmail($data, $room) {
        $to = $data['customer_email'];
        $subject = "Reservation Confirmation - Grand Ocean View";
        $message = "
            <html>
            <head><title>Reservation Confirmation</title></head>
            <body>
                <h2>Thank you, {$data['customer_name']}!</h2>
                <p>Your reservation at <strong>Grand Ocean View</strong> has been confirmed.</p>
                <h3>Reservation Details</h3>
                <ul>
                    <li><strong>Room:</strong> {$room['room_type']}</li>
                    <li><strong>Check-in:</strong> " . formatDate($data['check_in_date']) . "</li>
                    <li><strong>Check-out:</strong> " . formatDate($data['check_out_date']) . "</li>
                    <li><strong>Total Price:</strong> " . formatPrice($data['total_price']) . "</li>
                    <li><strong>Guests:</strong> {$data['adults']} adults, {$data['children']} children</li>
                </ul>
                <p>You will pay at the hotel upon arrival.</p>
                <p>Cancellation Policy: Free cancellation up to 48 hours before check-in.</p>
                <p>We look forward to welcoming you!</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . env('ADMIN_EMAIL', 'reservations@grandoceanview.com') . "\r\n";
        
        mail($to, $subject, $message, $headers);
    }
}

