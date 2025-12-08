<?php
/**
 * UUID Helper Functions
 * Provides validation and utility functions for UUID handling
 */

// Guard against multiple includes
if (defined('UUID_HELPER_LOADED')) {
    return;
}
define('UUID_HELPER_LOADED', true);

/**
 * Validate if a string is a valid UUID format
 * @param string $uuid The string to validate
 * @return bool True if valid UUID format, false otherwise
 */
function isValidUUID($uuid) {
    if (empty($uuid) || !is_string($uuid)) {
        return false;
    }
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
}

/**
 * Safely get user data from database, handling invalid UUIDs gracefully
 * @param string $user_id The user ID to fetch
 * @param SupabaseAPI $api The Supabase API instance
 * @return array|null User data array or null if invalid/not found
 */
function safeGetUser($user_id, $api) {
    if (!isValidUUID($user_id)) {
        return null;
    }
    
    try {
        $users = $api->select('users', ['id' => $user_id]);
        return !empty($users) ? $users[0] : null;
    } catch (Exception $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return null;
    }
}




