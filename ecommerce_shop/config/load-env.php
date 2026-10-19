<?php
declare(strict_types=1);

function loadShopEnvironment(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $envPath = __DIR__ . '/.env';
    if (!is_file($envPath)) {
        return;
    }
    if (!is_readable($envPath)) {
        throw new RuntimeException('The private ecommerce environment file is not readable.');
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('The private ecommerce environment file could not be read.');
    }

    foreach ($lines as $lineNumber => $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!preg_match('/^([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
            throw new RuntimeException('Invalid setting in private ecommerce environment file at line ' . ($lineNumber + 1) . '.');
        }

        $name = $matches[1];
        $value = trim($matches[2]);
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false && !isset($_ENV[$name]) && !isset($_SERVER[$name])) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
        }
    }
}

loadShopEnvironment();
