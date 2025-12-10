<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Load Supabase API
require_once __DIR__ . '/../config/supabase-api.php';

try {
    $api = getSupabaseAPI();
    $userId = $_SESSION['user_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $productIds = $input['product_ids'] ?? [];
    $action = $input['action'] ?? null;
    
    if (empty($productIds) || !$action) {
        echo json_encode(['success' => false, 'message' => 'Product IDs and action are required']);
        exit;
    }
    
    // Get retailer_id
    $retailers = $api->select('retailers', ['user_id' => $userId]);
    if (empty($retailers)) {
        echo json_encode(['success' => false, 'message' => 'Retailer profile not found']);
        exit;
    }
    
    $retailerId = $retailers[0]['id'];
    $successCount = 0;
    $failCount = 0;
    
    foreach ($productIds as $productId) {
        try {
            // Verify product belongs to this retailer
            $product = $api->select('products', ['id' => $productId]);
            
            if (empty($product) || $product[0]['retailer_id'] != $retailerId) {
                $failCount++;
                continue;
            }
            
            if ($action === 'delete') {
                // Delete the product
                $api->delete('products', ['id' => $productId]);
                $successCount++;
            } elseif ($action === 'edit') {
                // For edit, we just verify access (actual edit happens on edit page)
                $successCount++;
            }
        } catch (Exception $e) {
            error_log("Bulk action error for product $productId: " . $e->getMessage());
            $failCount++;
        }
    }
    
    if ($action === 'edit' && $successCount > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Redirecting to edit page', 
            'redirect' => true,
            'product_ids' => $productIds
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'message' => "$successCount product(s) processed successfully" . ($failCount > 0 ? ", $failCount failed" : ""),
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);
    }
    
} catch (Exception $e) {
    error_log("Bulk Action Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing bulk action: ' . $e->getMessage()]);
}
