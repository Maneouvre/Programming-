<?php
declare(strict_types=1);

function connectAdminDatabase(): PDO
{
    $host = getenv('ECOMMERCE_DB_HOST') ?: 'localhost';
    $username = getenv('ECOMMERCE_DB_USER') ?: 'root';
    $password = getenv('ECOMMERCE_DB_PASSWORD') ?: '';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    $server = new PDO("mysql:host={$host};charset=utf8mb4", $username, $password, $options);
    $server->exec('CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo = new PDO("mysql:host={$host};dbname=ecommerce_db;charset=utf8mb4", $username, $password, $options);
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS products (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            product_name VARCHAR(255) NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            rating_stars DECIMAL(2,1) NOT NULL DEFAULT 0,
            rating_count INT UNSIGNED NOT NULL DEFAULT 0,
            price_cents BIGINT UNSIGNED NOT NULL,
            keywords LONGTEXT NOT NULL,
            is_sold_out TINYINT(1) NOT NULL DEFAULT 0,
            UNIQUE KEY uq_products_product_name (product_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    if (!$pdo->query("SHOW COLUMNS FROM products LIKE 'is_sold_out'")->fetch()) {
        $pdo->exec('ALTER TABLE products ADD COLUMN is_sold_out TINYINT(1) NOT NULL DEFAULT 0');
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS shop_orders (
            order_id VARCHAR(64) NOT NULL PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            customer_name VARCHAR(120) NOT NULL,
            customer_email VARCHAR(254) NOT NULL,
            county VARCHAR(80) NOT NULL,
            pickup_station VARCHAR(160) NOT NULL,
            pickup_fee_cents INT UNSIGNED NOT NULL DEFAULT 0,
            payment_method VARCHAR(40) NOT NULL,
            subtotal_cents BIGINT UNSIGNED NOT NULL,
            tax_cents BIGINT UNSIGNED NOT NULL,
            total_cents BIGINT UNSIGNED NOT NULL,
            order_status VARCHAR(32) NOT NULL DEFAULT 'Order received',
            items_json LONGTEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_shop_orders_user_id (user_id),
            INDEX idx_shop_orders_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    return $pdo;
}
