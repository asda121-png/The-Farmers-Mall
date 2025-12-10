<?php
/**
 * API Endpoint: Update User Profile
 * Handles profile picture upload and profile data updates
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
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.'
            ]);
            exit;
        }
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode([
                'success' => false,
                'message' => 'File size must be less than 5MB.'
            ]);
            exit;
        }
        
        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $userId . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        $dbPath = 'uploads/profiles/' . $filename;
        
        // Delete old profile picture if exists
        $users = $api->select('users', ['id' => $userId]);
        if (!empty($users) && !empty($users[0]['profile_picture'])) {
            $oldFile = __DIR__ . '/../' . $users[0]['profile_picture'];
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Update database
            $result = $api->update('users', ['id' => $userId], [
                'profile_picture' => $dbPath
            ]);
            
            // Update session
            $_SESSION['profile_picture'] = $dbPath;
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'profile_picture' => '../' . $dbPath
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload file'
            ]);
        }
        exit;
    }
    
    // Handle profile data update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updateData = [];
        $shopData = [];
        
        // User data updates
        if (isset($_POST['full_name']) && !empty(trim($_POST['full_name']))) {
            $updateData['full_name'] = trim($_POST['full_name']);
            $_SESSION['full_name'] = $updateData['full_name'];
        }
        
        if (isset($_POST['phone'])) {
            $updateData['phone'] = trim($_POST['phone']);
        }
        
        if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
            $updateData['email'] = trim($_POST['email']);
        }
        
        // Shop/Retailer data updates
        if (isset($_POST['shop_name']) && !empty(trim($_POST['shop_name']))) {
            $shopData['shop_name'] = trim($_POST['shop_name']);
        }
        
        if (isset($_POST['shop_description'])) {
            $shopData['business_address'] = trim($_POST['shop_description']);
        }
        
        // Update user data if any
        if (!empty($updateData)) {
            $result = $api->update('users', ['id' => $userId], $updateData);
        }
        
        // Update retailer data if any
        if (!empty($shopData)) {
            $users = $api->select('users', ['id' => $userId]);
            
            if (!empty($users) && $users[0]['user_type'] === 'retailer') {
                $retailers = $api->select('retailers', ['user_id' => $userId]);
                
                if (!empty($retailers)) {
                    // Update existing retailer record
                    $result = $api->update('retailers', ['user_id' => $userId], $shopData);
                } else {
                    // Create new retailer record
                    $shopData['user_id'] = $userId;
                    $result = $api->insert('retailers', $shopData);
                }
            }
        }
        
        if (!empty($updateData) || !empty($shopData)) {
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => array_merge($updateData, $shopData)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No data to update'
            ]);
        }
        exit;
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'No valid data provided'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
