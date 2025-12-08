<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../config/supabase-api.php';

$query = $_GET['q'] ?? '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query too short']);
    exit();
}

try {
    $api = getSupabaseAPI();
    
    // Fetch all products
    $products = $api->select('products') ?: [];
    
    // Filter products by query
    $lowerQuery = strtolower(trim($query));
    $suggestions = array_filter($products, function($product) use ($lowerQuery) {
        $name = strtolower($product['name'] ?? $product['product_name'] ?? '');
        $category = strtolower($product['category'] ?? '');
        $description = strtolower($product['description'] ?? '');
        
        return strpos($name, $lowerQuery) !== false || 
               strpos($category, $lowerQuery) !== false ||
               strpos($description, $lowerQuery) !== false;
    });
    
    // Limit to 8 suggestions
    $suggestions = array_slice(array_values($suggestions), 0, 8);
    
    // Helper function to format image path
    function formatImagePath($img) {
        if (empty($img)) return '../images/products/placeholder.png';
        // If already has http/https, return as is
        if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
            return $img;
        }
        // If already has ../, return as is
        if (strpos($img, '../') === 0) {
            return $img;
        }
        // If starts with images/, add ../
        if (strpos($img, 'images/') === 0) {
            return '../' . $img;
        }
        // Otherwise return as is
        return $img;
    }
    
    // Format suggestions
    $formattedSuggestions = array_map(function($product) {
        $image = $product['image'] ?? $product['image_url'] ?? $product['product_image'] ?? '';
        
        return [
            'name' => $product['name'] ?? $product['product_name'] ?? 'Product',
            'category' => $product['category'] ?? '',
            'price' => $product['price'] ?? $product['amount'] ?? 0,
            'image' => formatImagePath($image),
            'image_url' => formatImagePath($image),
            'id' => $product['id'] ?? ''
        ];
    }, $suggestions);
    
    echo json_encode([
        'success' => true,
        'suggestions' => $formattedSuggestions,
        'count' => count($formattedSuggestions)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
