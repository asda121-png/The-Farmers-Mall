<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase-api.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    $api = getSupabaseAPI();
    $userId = $_SESSION['user_id'];
    
    if ($method === 'POST') {
        $action = $data['action'] ?? '';
        $notificationId = $data['notification_id'] ?? '';
        
        if ($action === 'mark_read' && $notificationId) {
            // Mark notification as read
            $result = $api->update('notifications', 
                ['id' => $notificationId, 'user_id' => $userId], 
                ['is_read' => true]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
            
        } elseif ($action === 'mark_all_read') {
            // Mark all notifications as read for this user
            $result = $api->update('notifications', 
                ['user_id' => $userId, 'is_read' => false], 
                ['is_read' => true]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
            
        } elseif ($action === 'delete' && $notificationId) {
            // Delete notification
            $result = $api->delete('notifications', 
                ['id' => $notificationId, 'user_id' => $userId]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
            
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action or missing notification_id'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid request method'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error updating notification: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update notification',
        'details' => $e->getMessage()
    ]);
}
