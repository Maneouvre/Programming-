<?php
session_start();
$_SESSION = array(); // Clear all session variables

if (ini_get("session_use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy(); // Destroy backend session profile
header("Location: ../index.html"); // Moves up one step out of /auth/ to reach your index file
exit;
?>
