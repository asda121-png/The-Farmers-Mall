<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a retailer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SESSION['role'] !== 'retailer' && $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
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
        echo json_encode(['success' => false, 'error' => 'Retailer not found']);
        exit;
    }
    $retailerId = $retailers[0]['id'];
    
    $activities = [];
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    
    // 1. Get recent orders
    $orders = $api->select('orders', ['retailer_id' => $userId]);
    
    if (!empty($orders)) {
        // Sort by created_at descending
        usort($orders, function($a, $b) {
            return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
        });
        
        foreach (array_slice($orders, 0, 10) as $order) {
            $status = $order['order_status'] ?? 'pending';
            $activityType = '';
            $statusColor = '';
            
            if ($status === 'pending') {
                $activityType = 'New Order';
                $statusColor = 'green';
            } elseif ($status === 'completed' || $status === 'delivered') {
                $activityType = 'Order Completed';
                $statusColor = 'blue';
            } elseif ($status === 'processing' || $status === 'preparing') {
                $activityType = 'Order Processing';
                $statusColor = 'yellow';
            } elseif ($status === 'cancelled') {
                $activityType = 'Order Cancelled';
                $statusColor = 'red';
            } else {
                $activityType = 'Order Updated';
                $statusColor = 'gray';
            }
            
            $activities[] = [
                'type' => 'order',
                'activity_type' => $activityType,
                'color' => $statusColor,
                'order_id' => $order['id'],
                'order_number' => substr($order['id'], 0, 8),
                'amount' => floatval($order['total_amount'] ?? 0),
                'customer_name' => $order['customer_name'] ?? 'Customer',
                'timestamp' => $order['created_at'],
                'icon' => 'fa-shopping-cart'
            ];
        }
    }
    
    // 2. Get low stock products
    $products = $api->select('products', ['retailer_id' => $retailerId]);
    
    if (!empty($products)) {
        foreach ($products as $product) {
            $stock = intval($product['stock'] ?? 0);
            $stockThreshold = 10; // Consider items with stock <= 10 as low
            
            if ($stock <= $stockThreshold && $stock > 0) {
                $activities[] = [
                    'type' => 'stock',
                    'activity_type' => 'Low Stock Alert',
                    'color' => 'yellow',
                    'product_name' => $product['name'] ?? 'Product',
                    'stock' => $stock,
                    'unit' => $product['unit'] ?? 'units',
                    'timestamp' => $product['updated_at'] ?? $product['created_at'],
                    'icon' => 'fa-exclamation-triangle'
                ];
            } elseif ($stock == 0) {
                $activities[] = [
                    'type' => 'stock',
                    'activity_type' => 'Out of Stock',
                    'color' => 'red',
                    'product_name' => $product['name'] ?? 'Product',
                    'stock' => $stock,
                    'unit' => $product['unit'] ?? 'units',
                    'timestamp' => $product['updated_at'] ?? $product['created_at'],
                    'icon' => 'fa-times-circle'
                ];
            }
        }
        
        // Get recently added products (within last 7 days)
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        foreach ($products as $product) {
            $createdAt = $product['created_at'] ?? '';
            if ($createdAt >= $sevenDaysAgo) {
                $activities[] = [
                    'type' => 'product',
                    'activity_type' => 'Product Added',
                    'color' => 'green',
                    'product_name' => $product['name'] ?? 'Product',
                    'timestamp' => $createdAt,
                    'icon' => 'fa-plus-circle'
                ];
            }
        }
        
        // Get recently updated products (within last 7 days, excluding just created)
        foreach ($products as $product) {
            $updatedAt = $product['updated_at'] ?? '';
            $createdAt = $product['created_at'] ?? '';
            if ($updatedAt && $updatedAt >= $sevenDaysAgo && $updatedAt != $createdAt) {
                $activities[] = [
                    'type' => 'product',
                    'activity_type' => 'Product Updated',
                    'color' => 'blue',
                    'product_name' => $product['name'] ?? 'Product',
                    'timestamp' => $updatedAt,
                    'icon' => 'fa-edit'
                ];
            }
        }
    }
    
    // 3. Get recent reviews (if reviews table exists)
    try {
        $reviews = $api->select('reviews', ['retailer_id' => $retailerId]);
        
        if (!empty($reviews)) {
            // Sort by created_at descending
            usort($reviews, function($a, $b) {
                return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
            });
            
            foreach (array_slice($reviews, 0, 5) as $review) {
                $rating = intval($review['rating'] ?? 5);
                $activities[] = [
                    'type' => 'review',
                    'activity_type' => 'New Review',
                    'color' => $rating >= 4 ? 'green' : ($rating >= 3 ? 'yellow' : 'red'),
                    'rating' => $rating,
                    'customer_name' => $review['customer_name'] ?? 'Customer',
                    'comment' => $review['comment'] ?? '',
                    'timestamp' => $review['created_at'],
                    'icon' => 'fa-star'
                ];
            }
        }
    } catch (Exception $e) {
        // Reviews table might not exist, skip
    }
    
    // 4. Get recent messages (if messages table exists)
    try {
        $messages = $api->select('messages', ['receiver_id' => $userId]);
        
        if (!empty($messages)) {
            // Sort by timestamp descending
            usort($messages, function($a, $b) {
                return strtotime($b['timestamp'] ?? '0') - strtotime($a['timestamp'] ?? '0');
            });
            
            foreach (array_slice($messages, 0, 5) as $message) {
                if (!$message['is_read']) {
                    $activities[] = [
                        'type' => 'message',
                        'activity_type' => 'New Message',
                        'color' => 'blue',
                        'message_preview' => substr($message['message'] ?? '', 0, 50),
                        'timestamp' => $message['timestamp'],
                        'icon' => 'fa-envelope'
                    ];
                }
            }
        }
    } catch (Exception $e) {
        // Messages table might not exist, skip
    }
    
    // Sort all activities by timestamp (most recent first)
    usort($activities, function($a, $b) {
        return strtotime($b['timestamp'] ?? '0') - strtotime($a['timestamp'] ?? '0');
    });
    
    // Limit the results
    $activities = array_slice($activities, 0, $limit);
    
    echo json_encode([
        'success' => true,
        'activities' => $activities,
        'count' => count($activities)
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching recent activities: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch activities',
        'message' => $e->getMessage()
    ]);
}
