<?php
declare(strict_types=1);

date_default_timezone_set('Africa/Nairobi');

$config = require __DIR__ . '/config/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    global $config;
    $host = $config['db']['host'];
    $name = $config['db']['name'];
    $user = $config['db']['user'];
    $password = $config['db']['password'];
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET time_zone = '+03:00'");

    return $pdo;
}

function clean_text(mixed $value, int $maxLength = 180): string
{
    if (!is_string($value)) {
        return '';
    }

    $text = trim(strip_tags($value));
    return mb_substr($text, 0, $maxLength, 'UTF-8');
}

function valid_money(mixed $value): ?string
{
    if (!is_string($value) && !is_int($value) && !is_float($value)) {
        return null;
    }

    $value = trim((string)$value);
    if (!preg_match('/^\d{1,9}(?:\.\d{1,2})?$/', $value)) {
        return null;
    }

    return number_format((float)$value, 2, '.', '');
}

function valid_id(mixed $value): ?int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}
