<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-session.php';
unset(
    $_SESSION['admin_logged_in'],
    $_SESSION['admin_username'],
    $_SESSION['admin_name'],
    $_SESSION['admin_csrf'],
    $_SESSION['admin_login_csrf'],
    $_SESSION['admin_upload_csrf'],
    $_SESSION['admin_last_activity'],
    $_SESSION['admin_created_at'],
    $_SESSION['admin_last_regenerated']
);
if ($_SESSION === []) {
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax'
        ]);
    }
    session_destroy();
} else {
    session_regenerate_id(true);
}
header('Location: /ecommerce_shop/admin/login.php');
exit;
