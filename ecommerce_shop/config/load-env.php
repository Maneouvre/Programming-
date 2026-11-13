<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__, ['.env', '.env.local'], false)->safeLoad();

function shopEnv(string $name, string $default = ''): string
{
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? $default;

    return is_scalar($value) ? (string)$value : $default;
}
