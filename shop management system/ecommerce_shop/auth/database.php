<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/load-env.php';

function shopDatabaseConfig(string $defaultDatabase = 'ecommerceusers'): array
{
    $getSetting = static function (string $environmentVariable, string $fallback): string {
        $environmentValue = getenv($environmentVariable);
        $value = $environmentValue !== false && $environmentValue !== '' ? $environmentValue : $fallback;
        if (!is_string($value) || $value === '') {
            throw new RuntimeException("Configure {$environmentVariable} in config/.env or hosting environment.");
        }
        return $value;
    };

    $host = $getSetting('ECOMMERCE_DB_HOST', 'localhost');
    $port = $getSetting('ECOMMERCE_DB_PORT', '3306');
    $database = $getSetting('ECOMMERCE_DB_NAME', $defaultDatabase);
    $username = $getSetting('ECOMMERCE_DB_USER', 'root');
    $password = getenv('ECOMMERCE_DB_PASSWORD');
    $password = $password === false ? '' : $password;

    if (!ctype_digit($port) || (int)$port < 1 || (int)$port > 65535) {
        throw new RuntimeException('ECOMMERCE_DB_PORT must be a valid TCP port.');
    }

    return [
        'host' => $host,
        'port' => (int)$port,
        'database' => $database,
        'username' => $username,
        'password' => $password
    ];
}

function connectShopUsers(): PDO
{
    $config = shopDatabaseConfig();
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        $options
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
