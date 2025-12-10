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
    
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? null;
    
    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }
    
    // Get original product
    $products = $api->select('products', ['id' => $productId, 'retailer_id' => $retailerId]);
    
    if (empty($products)) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $originalProduct = $products[0];
    
    // Create duplicate (matching database schema)
    $duplicateData = [
        'name' => $originalProduct['name'] . ' (Copy)',
        'description' => $originalProduct['description'],
        'price' => $originalProduct['price'],
        'stock_quantity' => $originalProduct['stock_quantity'] ?? 0,
        'unit' => $originalProduct['unit'] ?? 'kg',
        'category' => $originalProduct['category'] ?? '',
        'image_url' => $originalProduct['image_url'] ?? null,
        'retailer_id' => $retailerId,
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $result = $api->insert('products', $duplicateData);
    
    echo json_encode(['success' => true, 'message' => 'Product duplicated successfully', 'product_id' => $result[0]['id'] ?? null]);
    
} catch (Exception $e) {
    error_log("Error duplicating product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error duplicating product: ' . $e->getMessage()]);
}
