<?php
/**
 * Application Configuration
 * Central config file for consistent paths across all team members
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Auto-detect base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the directory where the app is installed
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $scriptPath = rtrim($scriptPath, '/');
    
    // Remove common subdirectories to get to root
    $scriptPath = preg_replace('#/(user|admin|auth|public|api|config)$#', '', $scriptPath);
    
    return $protocol . $host . $scriptPath;
}

// Define constants
define('BASE_URL', getBaseUrl());
define('IMAGES_URL', BASE_URL . '/images');
define('PRODUCTS_IMAGES_URL', IMAGES_URL . '/products');
define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');

/**
 * Get product image URL
 * Handles various image path formats and provides fallback
 */
function getProductImageUrl($imageUrl) {
    if (empty($imageUrl)) {
        return 'https://via.placeholder.com/300x200?text=No+Image';
    }
    
    // If already a full URL, return as is
    if (strpos($imageUrl, 'http://') === 0 || strpos($imageUrl, 'https://') === 0) {
        return $imageUrl;
    }
    
    // If it starts with ../ remove it
    $imageUrl = str_replace('../', '', $imageUrl);
    
    // If it starts with images/, use it directly
    if (strpos($imageUrl, 'images/') === 0) {
        return BASE_URL . '/' . $imageUrl;
    }
    
    // Otherwise assume it's just the filename
    return PRODUCTS_IMAGES_URL . '/' . basename($imageUrl);
}

/**
 * Get profile picture URL
 */
function getProfileImageUrl($imageUrl) {
    if (empty($imageUrl)) {
        return IMAGES_URL . '/default-profile.png';
    }
    
    // If already a full URL, return as is
    if (strpos($imageUrl, 'http://') === 0 || strpos($imageUrl, 'https://') === 0) {
        return $imageUrl;
    }
    
    // Remove ../
    $imageUrl = str_replace('../', '', $imageUrl);
    
    return BASE_URL . '/' . $imageUrl;
}

/**
 * Debug mode check
 */
function isDebugMode() {
    return getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'development';
}

// Log base URL for debugging (only in dev mode)
if (isDebugMode() && php_sapi_name() !== 'cli') {
    error_log("[Config] BASE_URL: " . BASE_URL);
    error_log("[Config] PRODUCTS_IMAGES_URL: " . PRODUCTS_IMAGES_URL);
}
?>
