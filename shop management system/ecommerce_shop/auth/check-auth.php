<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && (string)$_SESSION['user_id'] !== '';
}

if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, private');
    echo json_encode([
        'logged_in' => isLoggedIn(),
        'username' => isLoggedIn() ? (string)($_SESSION['user_username'] ?? '') : '',
        'email' => isLoggedIn() ? (string)($_SESSION['user_email'] ?? '') : ''
    ]);
    exit;
}
?>
