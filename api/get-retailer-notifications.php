<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a retailer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SESSION['role'] !== 'retailer' && $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase-api.php';

try {
    $api = getSupabaseAPI();
    $userId = $_SESSION['user_id'];
    
    // Get retailer info
    $retailers = $api->select('retailers', ['user_id' => $userId]);
    if (empty($retailers)) {
        echo json_encode(['success' => false, 'error' => 'Retailer not found']);
        exit;
    }
    
    $retailerId = $retailers[0]['id'];
    
    // Fetch notifications from database for this retailer
    $dbNotifications = $api->select('notifications', [
        'retailer_id' => $retailerId
    ], ['order' => ['created_at' => 'desc'], 'limit' => 50]);
    
    // Format notifications for frontend
    $notifications = [];
    foreach ($dbNotifications as $notif) {
        $notifications[] = [
            'id' => $notif['id'],
            'type' => $notif['type'] ?? 'info',
            'title' => $notif['title'],
            'message' => $notif['message'],
            'timestamp' => $notif['created_at'],
            'read' => $notif['is_read'] ?? false,
            'link' => $notif['link'] ?? '../retailer/retailernotifications.php'
        ];
    }
    
    // Calculate unread count
    $unreadCount = count(array_filter($notifications, function($n) {
        return !$n['read'];
    }));
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unreadCount' => $unreadCount
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching retailer notifications: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch notifications',
        'details' => $e->getMessage()
    ]);
}
