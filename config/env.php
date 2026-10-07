<?php

/**
 * Loads a local .env file and exposes env().
 *
 * Included before the Yii application boots, so config files can read
 * environment-specific values without a Composer package.
 */

if (!function_exists('env')) {
    function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return is_string($value) ? $value : (string) $value;
    }
}

$envFile = dirname(__DIR__) . '/.env';
if (!is_file($envFile)) {
    return;
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
    return;
}

foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    $parts = explode('=', $line, 2);
    if (count($parts) !== 2) {
        continue;
    }

    $name = trim($parts[0]);
    if ($name === '' || getenv($name) !== false) {
        continue;
    }

    $value = trim($parts[1]);
    $value = trim($value, "\"'");
    putenv($name . '=' . $value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}
