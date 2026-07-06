-- Create database
CREATE DATABASE IF NOT EXISTS hotel_booking;
USE hotel_booking;

-- Rooms table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_type VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_occupancy INT NOT NULL,
    quantity INT NOT NULL, -- total number of rooms of this type
    amenities TEXT,
    cover_image VARCHAR(255),
    gallery_images TEXT, -- comma-separated filenames
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reservations table
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    adults INT NOT NULL,
    children INT DEFAULT 0,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'walk_in') DEFAULT 'pending',
    reservation_type ENUM('online', 'walk_in') DEFAULT 'online',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_room_dates (room_id, check_in_date, check_out_date)
);

-- Admins table (for staff login)
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: Admin@2026)
-- SHA256 hashed: use PHP's password_hash() later; we'll insert via a script.
-- For now, we'll insert a placeholder that will be updated.
INSERT INTO admins (email, password_hash) VALUES ('manager@grandoceanview.com', '');
