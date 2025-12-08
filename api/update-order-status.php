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
$valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
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
    
    // Users can only cancel pending orders or mark shipped orders as delivered
    if ($new_status === 'cancelled' && $current_status !== 'pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Can only cancel pending orders']);
        exit();
    }
    
    if ($new_status === 'delivered' && $current_status !== 'shipped') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Can only mark shipped orders as delivered']);
        exit();
    }
    
    // Update order status
    $update_data = [
        'status' => $new_status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // If marking as delivered, also update payment status
    if ($new_status === 'delivered') {
        $update_data['payment_status'] = 'paid';
    }
    
    $result = $api->update('orders', ['id' => $order_id], $update_data);
    
    if ($result) {
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
