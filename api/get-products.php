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
    
    // Build filter conditions
    $conditions = ['retailer_id' => $retailerId];
    
    // Add category filter
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $conditions['category'] = $_GET['category'];
    }
    
    // Get all products for this retailer with filters
    $products = $api->select('products', $conditions);
    
    // Apply additional filters that can't be done in the query
    if (!empty($products)) {
        // Filter by stock status
        if (isset($_GET['stock_status']) && !empty($_GET['stock_status'])) {
            $stockStatus = $_GET['stock_status'];
            $products = array_filter($products, function($product) use ($stockStatus) {
                $stock = intval($product['stock_quantity'] ?? 0);
                switch ($stockStatus) {
                    case 'instock':
                        return $stock > 0;
                    case 'outofstock':
                        return $stock <= 0;
                    case 'onbackorder':
                        // For now, treat as out of stock or low stock
                        return $stock <= 5 && $stock > 0;
                    default:
                        return true;
                }
            });
            $products = array_values($products); // Re-index array
        }
        
        // Filter by product type (if you add this field later)
        if (isset($_GET['product_type']) && !empty($_GET['product_type'])) {
            $productType = $_GET['product_type'];
            $products = array_filter($products, function($product) use ($productType) {
                return isset($product['product_type']) && $product['product_type'] === $productType;
            });
            $products = array_values($products);
        }
    }
    
    echo json_encode(['success' => true, 'products' => $products]);
    
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage(), 'products' => []]);
}
