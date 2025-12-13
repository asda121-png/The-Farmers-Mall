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
    
    // Get all products for this retailer (using retailer_id from retailers table)
    $allProducts = $api->select('products', ['retailer_id' => $retailerId]);
    $totalProducts = count($allProducts); // Count ALL products
    $outOfStockProducts = [];
    
    foreach ($allProducts as $product) {
        $stock = intval($product['stock_quantity'] ?? 0);
        // Count out of stock products (stock = 0)
        if ($stock === 0) {
            $outOfStockProducts[] = [
                'id' => $product['id'],
                'name' => $product['name'] ?? 'Unknown Product',
                'stock' => $stock,
                'unit' => $product['unit'] ?? 'units',
                'category' => $product['category'] ?? 'Uncategorized'
            ];
        }
    }
    
    // Get all orders for this retailer
    $orders = $api->select('orders', ['retailer_id' => $userId]);
    
    $totalRevenue = 0;
    $newOrders = 0;
    $productSales = [];
    
    foreach ($orders as $order) {
        $orderStatus = $order['status'] ?? '';
        
        // Count new/pending orders
        if (in_array($orderStatus, ['pending', 'processing', 'confirmed'])) {
            $newOrders++;
        }
        
        // Calculate revenue from completed orders
        if (in_array($orderStatus, ['completed', 'delivered'])) {
            $totalRevenue += floatval($order['total_amount'] ?? 0);
            
            // Get order items for sales data
            $orderItems = $api->select('order_items', ['order_id' => $order['id']]);
            
            foreach ($orderItems as $item) {
                $productId = $item['product_id'];
                $quantity = intval($item['quantity'] ?? 0);
                $price = floatval($item['price'] ?? 0);
                $total = $quantity * $price;
                
                // Initialize if not exists
                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'quantity' => 0,
                        'revenue' => 0,
                        'product_name' => $item['product_name'] ?? 'Unknown Product',
                        'product_id' => $productId
                    ];
                }
                
                // Accumulate sales data
                $productSales[$productId]['quantity'] += $quantity;
                $productSales[$productId]['revenue'] += $total;
            }
        }
    }
    
    // Sort by quantity sold (descending)
    usort($productSales, function($a, $b) {
        return $b['quantity'] - $a['quantity'];
    });
    
    // Get product details for top products
    $topProducts = array_slice($productSales, 0, 10);
    
    // Enrich with product details
    foreach ($topProducts as &$product) {
        $products = $api->select('products', ['id' => $product['product_id']]);
        if (!empty($products)) {
            $productDetails = $products[0];
            $product['image_url'] = $productDetails['image_url'] ?? null;
            $product['category'] = $productDetails['category'] ?? 'Uncategorized';
            $product['price'] = floatval($productDetails['price'] ?? 0);
        }
    }
    
    // Calculate max quantity for percentage calculation
    $maxQuantity = !empty($topProducts) ? $topProducts[0]['quantity'] : 1;
    
    // Add percentage to each product
    foreach ($topProducts as &$product) {
        $product['percentage'] = $maxQuantity > 0 ? round(($product['quantity'] / $maxQuantity) * 100) : 0;
    }
    
    echo json_encode([
        'success' => true,
        'dashboardStats' => [
            'totalRevenue' => $totalRevenue,
            'newOrders' => $newOrders,
            'outOfStockCount' => count($outOfStockProducts), // Out of stock items count
            'activeProducts' => $totalProducts // Total products in retailer's inventory
        ],
        'outOfStockProducts' => array_slice($outOfStockProducts, 0, 10),
        'topProducts' => $topProducts,
        'totalProducts' => count($productSales)
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching retailer dashboard stats: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching dashboard stats: ' . $e->getMessage()
    ]);
}
