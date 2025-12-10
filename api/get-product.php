<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
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
        echo json_encode(['success' => false, 'message' => 'Retailer not found']);
        exit;
    }
    $retailerId = $retailers[0]['id'];
    
    $productId = $_GET['id'] ?? null;
    
    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }
    
    // Get product data
    $products = $api->select('products', ['id' => $productId, 'retailer_id' => $retailerId]);
    
    if (empty($products)) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'product' => $products[0]]);
    
} catch (Exception $e) {
    error_log("Error fetching product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching product: ' . $e->getMessage()]);
}
