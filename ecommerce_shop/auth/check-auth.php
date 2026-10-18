<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if the user is authenticated
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Handle an immediate dynamic AJAX authentication check
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json');
    echo json_encode(['logged_in' => isLoggedIn()]);
    exit;
}
?>
