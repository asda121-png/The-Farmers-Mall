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
    $productId = $input['product_id'] ?? null;
    
    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }
    
    // Verify product belongs to this retailer
    $product = $api->select('products', ['id' => $productId]);
    
    if (empty($product)) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Get retailer_id from retailers table
    $retailers = $api->select('retailers', ['user_id' => $userId]);
    if (empty($retailers)) {
        echo json_encode(['success' => false, 'message' => 'Retailer profile not found']);
        exit;
    }
    
    $retailerId = $retailers[0]['id'];
    
    if ($product[0]['retailer_id'] != $retailerId) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this product']);
        exit;
    }
    
    // Delete the product
    $result = $api->delete('products', ['id' => $productId]);
    
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    
} catch (Exception $e) {
    error_log("Delete Product Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
}
