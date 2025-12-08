<?php
/**
 * API Endpoint: Get User Profile Data
 * Returns current user's profile information including profile picture
 */

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

require_once __DIR__ . '/../config/supabase-api.php';

try {
    $api = getSupabaseAPI();
    $userId = $_SESSION['user_id'];
    
    // Fetch user data
    $users = $api->select('users', ['id' => $userId]);
    
    if (empty($users)) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $userData = $users[0];
    
    // Prepare profile picture URL
    $profilePicture = '../images/default-avatar.svg';
    if (!empty($userData['profile_picture'])) {
        $profilePath = '../' . ltrim($userData['profile_picture'], '/');
        if (file_exists(__DIR__ . '/../' . ltrim($userData['profile_picture'], '/'))) {
            $profilePicture = $profilePath;
        }
    }
    
    // Get shop name if retailer
    $shopName = null;
    if ($userData['user_type'] === 'retailer') {
        $retailers = $api->select('retailers', ['user_id' => $userId]);
        if (!empty($retailers)) {
            $shopName = $retailers[0]['shop_name'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $userData['id'],
            'full_name' => $userData['full_name'],
            'email' => $userData['email'],
            'profile_picture' => $profilePicture,
            'user_type' => $userData['user_type'],
            'shop_name' => $shopName
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
