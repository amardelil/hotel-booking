<?php
function url($path = '') {
    return '/' . ltrim($path, '/');
}
function asset($path) {
    return '/assets/' . ltrim($path, '/');
}
function uploads($filename) {
    // Use BASE_PATH to build absolute URL
    return BASE_PATH . '/public/uploads/' . ltrim($filename, '/');
}
function env($key, $default = null) {
    $value = getenv($key);
    return ($value === false) ? $default : $value;
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
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}