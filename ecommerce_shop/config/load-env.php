<?php
declare(strict_types=1);

function loadShopEnvironment(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $environmentFiles = [
        __DIR__ . '/.env',
        __DIR__ . '/.env.local'
    ];
    $providedByProcess = [];

    foreach ($environmentFiles as $environmentFile) {
        if (!is_file($environmentFile)) {
            continue;
        }
        if (!is_readable($environmentFile)) {
            throw new RuntimeException('A private ecommerce environment file is not readable.');
        }

        $lines = file($environmentFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new RuntimeException('A private ecommerce environment file could not be read.');
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

            if (!array_key_exists($name, $providedByProcess)) {
                $providedByProcess[$name] = getenv($name) !== false || isset($_ENV[$name]) || isset($_SERVER[$name]);
            }
            if (!$providedByProcess[$name]) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
            }
        }
    }
}

loadShopEnvironment();
