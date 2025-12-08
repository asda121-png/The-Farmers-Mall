<?php
/**
 * Search API - Provides product search with autocomplete
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/supabase-api.php';

$query = $_GET['q'] ?? '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

if (empty($query)) {
    echo json_encode(['success' => true, 'results' => []]);
    exit();
}

try {
    $api = getSupabaseAPI();
    
    // Get all products and filter by name (case-insensitive)
    $all_products = $api->select('products');
    
    $results = [];
    $query_lower = strtolower($query);
    
    foreach ($all_products as $product) {
        $name = strtolower($product['name'] ?? '');
        $description = strtolower($product['description'] ?? '');
        $category = strtolower($product['category'] ?? '');
        
        // Check if query matches name, description, or category
        if (strpos($name, $query_lower) !== false || 
            strpos($description, $query_lower) !== false || 
            strpos($category, $query_lower) !== false) {
            
            // Resolve image path
            $image_url = $product['image_url'] ?? '';
            if (!empty($image_url)) {
                if (!preg_match('#^https?://#i', $image_url)) {
                    if (file_exists(__DIR__ . '/../' . $image_url)) {
                        $image_url = '../' . $image_url;
                    } elseif (file_exists(__DIR__ . '/../images/products/' . basename($image_url))) {
                        $image_url = '../images/products/' . basename($image_url);
                    }
                }
            } else {
                $image_url = '../images/products/placeholder.png';
            }
            
            $results[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image_url' => $image_url,
                'category' => $product['category']
            ];
            
            if (count($results) >= $limit) {
                break;
            }
        }
    }
    
    echo json_encode(['success' => true, 'results' => $results]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

