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
        echo json_encode(['success' => false, 'message' => 'Retailer not found', 'products' => []]);
        exit;
    }
    $retailerId = $retailers[0]['id'];
    
    // Get all products for this retailer
    $products = $api->select('products', ['retailer_id' => $retailerId]);
    
    echo json_encode(['success' => true, 'products' => $products]);
    
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage(), 'products' => []]);
}
