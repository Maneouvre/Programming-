<?php
require_once __DIR__ . '/../config/load-env.php';

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    require __DIR__ . '/require-admin.php';
}

$adminConfig = [
    'site_name' => 'Manoeuvre Admin',
    'logo_path' => '../images/logo.png',
    'username' => getenv('ECOMMERCE_ADMIN_USERNAME') ?: 'admin',
    'password_hash' => getenv('ECOMMERCE_ADMIN_PASSWORD_HASH') ?: '',
    'name' => getenv('ECOMMERCE_ADMIN_NAME') ?: 'Admin User'
];
