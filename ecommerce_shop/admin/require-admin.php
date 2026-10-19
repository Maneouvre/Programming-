<?php
declare(strict_types=1);

require_once __DIR__ . '/admin-session.php';

if (!adminSessionIsValid()) {
    header('Location: /ecommerce_shop/admin/login.php');
    exit;
}
