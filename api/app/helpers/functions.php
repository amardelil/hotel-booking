<?php
/**
 * Helper functions - FIXED for XAMPP localhost
 */

function url($path = '') {
    // Always use the full base path for localhost
    $base = '/hotel-booking/public';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function asset($path) {
    return '/hotel-booking/public/assets/' . ltrim($path, '/');
}

function uploads($filename) {
    return '/public/uploads/' . $filename;
}

function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

function sessionFlash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = isset($_SESSION['flash'][$key]) ? $_SESSION['flash'][$key] : null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

function formatPrice($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($date, $format = 'm/d/Y') {
    return date($format, strtotime($date));
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

