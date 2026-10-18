<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($_SESSION['admin_logged_in'] ?? false) !== true) {
    header('Location: /ecommerce_shop/admin/login.php');
    exit;
}
