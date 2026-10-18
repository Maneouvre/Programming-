<?php
declare(strict_types=1);

function connectShopUsers(): PDO
{
    $host = getenv('ECOMMERCE_DB_HOST') ?: 'localhost';
    $username = getenv('ECOMMERCE_DB_USER') ?: 'root';
    $password = getenv('ECOMMERCE_DB_PASSWORD') ?: '';
    $server = new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $server->exec('CREATE DATABASE IF NOT EXISTS ecommerceusers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo = new PDO(
        "mysql:host={$host};dbname=ecommerceusers;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(32) NOT NULL UNIQUE,
            pwd VARCHAR(255) NOT NULL,
            email VARCHAR(254) NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    return $pdo;
}
