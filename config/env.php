<?php
/**
 * Environment Variable Loader
 * Loads .env file and makes variables available via getenv()
 */

// Guard against multiple includes
if (defined('ENV_LOADED')) {
    return;
}
define('ENV_LOADED', true);

if (!file_exists(__DIR__ . '/.env')) {
    die('Error: .env file not found. Please copy .env.example to .env and configure your Supabase credentials.');
}

$envFile = file_get_contents(__DIR__ . '/.env');
$lines = explode("\n", $envFile);

foreach ($lines as $line) {
    $line = trim($line);
    
    // Skip empty lines and comments
    if (empty($line) || strpos($line, '#') === 0) {
        continue;
    }
    
    // Parse KEY=VALUE
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes if present
        $value = trim($value, '"\'');
        
        // Set as environment variable
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

