<?php
declare(strict_types=1);

$readEnvironment = static function (string $name): string {
    $value = getenv($name);
    return $value === false ? '' : $value;
};

$config = [
    'env' => $readEnvironment('SHOP_ENV') ?: 'production',
    'db' => [
        'host' => $readEnvironment('SHOP_DB_HOST'),
        'name' => $readEnvironment('SHOP_DB_NAME'),
        'user' => $readEnvironment('SHOP_DB_USER'),
        'password' => $readEnvironment('SHOP_DB_PASSWORD'),
    ],
];

$localPath = __DIR__ . '/config.local.php';
if (is_file($localPath)) {
    $localConfig = require $localPath;
    if (!is_array($localConfig)) {
        throw new RuntimeException('config/config.local.php must return a PHP array.');
    }
    $config = array_replace_recursive($config, $localConfig);
}

if (!in_array($config['env'] ?? null, ['local', 'production'], true)) {
    throw new RuntimeException("SHOP_ENV must be either 'local' or 'production'.");
}

foreach (['host', 'name', 'user'] as $key) {
    if (!is_string($config['db'][$key] ?? null) || trim($config['db'][$key]) === '') {
        throw new RuntimeException(
            "Database setting '{$key}' is not configured. Set SHOP_DB_" .
            strtoupper($key) . ' or add it to config/config.local.php.'
        );
    }
}

if (!is_string($config['db']['password'] ?? null)) {
    throw new RuntimeException('Database setting password must be a string.');
}

if ($config['env'] === 'production' && $config['db']['password'] === '') {
    throw new RuntimeException('Database password is required in production. Set SHOP_DB_PASSWORD or add it to the private config/config.local.php.');
}

return $config;
