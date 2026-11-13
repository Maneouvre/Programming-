<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    session_name('MANEUVER_ADMIN_SESSION');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/ecommerce_shop/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function adminSessionIsValid(): bool
{
    if (($_SESSION['admin_logged_in'] ?? false) !== true) {
        return false;
    }

    $now = time();
    $lastActivity = (int)($_SESSION['admin_last_activity'] ?? 0);
    $createdAt = (int)($_SESSION['admin_created_at'] ?? 0);
    if ($lastActivity === 0 || $createdAt === 0 || $now - $lastActivity > 1200 || $now - $createdAt > 28800) {
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
        session_regenerate_id(true);
        return false;
    }

    $lastRegenerated = (int)($_SESSION['admin_last_regenerated'] ?? 0);
    if ($now - $lastRegenerated >= 600) {
        session_regenerate_id(true);
        $_SESSION['admin_last_regenerated'] = $now;
    }
    $_SESSION['admin_last_activity'] = $now;
    return true;
}

function startAdminSessionLifetime(): void
{
    $now = time();
    $_SESSION['admin_created_at'] = $now;
    $_SESSION['admin_last_activity'] = $now;
    $_SESSION['admin_last_regenerated'] = $now;
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    if (!adminSessionIsValid()) {
        header('Location: /ecommerce_shop/admin/login.php');
        exit;
    }
}
