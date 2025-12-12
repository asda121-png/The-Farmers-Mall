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

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

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
    
    if ($method === 'POST') {
        $notificationId = $data['notification_id'] ?? null;
        
        if ($notificationId) {
            // Mark specific notification as read
            $result = $api->update('notifications', 
                ['is_read' => true],
                ['id' => $notificationId, 'retailer_id' => $retailerId]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } else {
            // Mark all unread notifications as read
            $result = $api->update('notifications', 
                ['is_read' => true],
                ['retailer_id' => $retailerId, 'is_read' => false]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid request method'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error marking notification as read: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to mark notification as read',
        'details' => $e->getMessage()
    ]);
}
