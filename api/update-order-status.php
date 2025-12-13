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
require_once __DIR__ . '/../config/notifications-helper.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$order_id = $input['order_id'] ?? null;
$new_status = $input['status'] ?? null;

if (!$order_id || !$new_status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID and status are required']);
    exit();
}

// Validate status
$valid_statuses = ['pending', 'to_pay', 'confirmed', 'to_ship', 'processing', 'shipped', 'to_receive', 'delivered', 'completed', 'cancelled'];
if (!in_array($new_status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    $api = getSupabaseAPI();
    
    // Get order details to verify ownership
    $orders = $api->select('orders', ['id' => $order_id]);
    
    if (empty($orders)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }
    
    $order = $orders[0];
    
    // Verify the order belongs to the logged-in user
    if ($order['customer_id'] !== $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
    
    // Check if status transition is allowed
    $current_status = $order['status'];
    
    // Users can only cancel pending/to_pay orders or mark shipped/to_receive orders as delivered/completed
    $cancelable_statuses = ['pending', 'to_pay'];
    $deliverable_statuses = ['shipped', 'to_receive'];
    
    if ($new_status === 'cancelled' && !in_array($current_status, $cancelable_statuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Can only cancel pending or unpaid orders']);
        exit();
    }
    
    if (in_array($new_status, ['delivered', 'completed']) && !in_array($current_status, $deliverable_statuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Can only mark shipped orders as delivered']);
        exit();
    }
    
    // Update order status
    $update_data = [
        'status' => $new_status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // If marking as delivered/completed, also update payment status
    if (in_array($new_status, ['delivered', 'completed'])) {
        $update_data['payment_status'] = 'paid';
    }
    
    $result = $api->update('orders', $update_data, ['id' => $order_id]);
    
    if ($result) {
        // If order is cancelled, notify retailers
        if ($new_status === 'cancelled') {
            $orderItems = $api->select('order_items', ['order_id' => $order_id]);
            $notifiedRetailers = [];
            
            foreach ($orderItems as $item) {
                $product = $api->select('products', ['id' => $item['product_id']]);
                if (!empty($product)) {
                    $retailer_id = $product[0]['retailer_id'];
                    // Only notify each retailer once per order
                    if (!in_array($retailer_id, $notifiedRetailers)) {
                        $customer_name = $order['customer_name'] ?? 'Customer';
                        notifyRetailerOrderCancelled($retailer_id, $order_id, $customer_name, 'Customer cancelled the order');
                        $notifiedRetailers[] = $retailer_id;
                    }
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully',
            'new_status' => $new_status
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
