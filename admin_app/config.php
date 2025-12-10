<?php
// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Set environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/.env');

// Get environment variables with defaults
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// Session configuration
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => false, // Set to true if using HTTPS
    'use_strict_mode' => true,
    'sid_length' => 48,
]);
?>