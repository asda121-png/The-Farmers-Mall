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

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User ID not found']);
    exit();
}

$api = getSupabaseAPI();
$method = $_SERVER['REQUEST_METHOD'];

// Get customer info
$customer = $api->select('users', ['id' => $user_id]);
if (empty($customer)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$customer_data = $customer[0];

// Get customer name from full_name field or build from first/last name
$customer_name = '';
if (!empty($customer_data['full_name'])) {
    $customer_name = trim($customer_data['full_name']);
} else {
    // Fallback to first_name + last_name if full_name doesn't exist
    $first_name = $customer_data['first_name'] ?? '';
    $last_name = $customer_data['last_name'] ?? '';
    $customer_name = trim($first_name . ' ' . $last_name);
}

// If still empty, use username or email as fallback
if (empty($customer_name)) {
    $customer_name = $customer_data['username'] ?? $customer_data['email'] ?? 'Unknown Customer';
}

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';

        if ($action === 'place_order') {
            // Get cart items from database
            $cartItems = $api->select('cart', ['customer_name' => $customer_name]);
            
            if (empty($cartItems)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                exit();
            }
            
            // Filter by selected cart IDs if provided
            $selected_cart_ids = $data['cart_ids'] ?? [];
            if (!empty($selected_cart_ids)) {
                $cartItems = array_filter($cartItems, function($item) use ($selected_cart_ids) {
                    return in_array($item['id'], $selected_cart_ids);
                });
                
                if (empty($cartItems)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'No selected items found in cart']);
                    exit();
                }
            }

            // Calculate total and prepare order items
            $subtotal_amount = 0;
            $tax_rate = 0.12; // 12% tax rate
            $total_amount = 0;
            $order_items = [];
            
            foreach ($cartItems as $cartItem) {
                $product = $api->select('products', ['id' => $cartItem['product_id']]);
                if (!empty($product)) {
                    $product_data = $product[0];
                    $price = floatval($product_data['price']);
                    $quantity = intval($cartItem['quantity']);
                    $subtotal = $price * $quantity;
                    
                    $order_items[] = [
                        'product_id' => $cartItem['product_id'],
                        'product_name' => $product_data['name'], // Save product name
                        'product_image_url' => $product_data['image_url'] ?? '', // Save product image
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal
                    ];
                    
                    $subtotal_amount += $subtotal;
                }
            }

            // Calculate final total with tax
            $tax_amount = $subtotal_amount * $tax_rate;
            $total_amount = $subtotal_amount + $tax_amount;

            // Create the order
            $orderData = [
                'customer_id' => $user_id,
                'customer_name' => $customer_name, // Save customer name
                'customer_email' => $customer_data['email'] ?? '', // Save customer email
                'total_amount' => $total_amount,
                'status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'card',
                'payment_status' => 'pending',
                'delivery_address' => $data['delivery_address'] ?? $customer_data['address'] ?? '',
                'notes' => $data['notes'] ?? ''
            ];

            $newOrder = $api->insert('orders', $orderData);
            
            if (empty($newOrder)) {
                throw new Exception('Failed to create order');
            }

            $order_id = $newOrder[0]['id'];

            // Insert order items
            foreach ($order_items as $item) {
                $item['order_id'] = $order_id;
                $api->insert('order_items', $item);
            }

            // Clear the cart
            foreach ($cartItems as $cartItem) {
                $api->delete('cart', ['id' => $cartItem['id']]);
            }

            // Create notification
            $notificationData = [
                'user_id' => $user_id,
                'type' => 'order_placed',
                'title' => 'Order Placed Successfully',
                'message' => "Your order for {$total_amount} has been placed and is being processed.",
                'is_read' => false
            ];
            $api->insert('notifications', $notificationData);

            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order_id,
                'total' => $total_amount
            ]);

        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else if ($method === 'GET') {
        // Get user's orders
        $orders = $api->select('orders', ['customer_id' => $user_id]);
        
        // Fetch order items for each order
        $ordersList = [];
        foreach ($orders as $order) {
            $orderItems = $api->select('order_items', ['order_id' => $order['id']]);
            
            // Get product details for each item
            $items = [];
            foreach ($orderItems as $item) {
                $product = $api->select('products', ['id' => $item['product_id']]);
                if (!empty($product)) {
                    $items[] = [
                        'product_name' => $product[0]['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal']
                    ];
                }
            }
            
            $ordersList[] = [
                'id' => $order['id'],
                'total_amount' => $order['total_amount'],
                'status' => $order['status'],
                'payment_method' => $order['payment_method'],
                'payment_status' => $order['payment_status'],
                'created_at' => $order['created_at'],
                'items' => $items
            ];
        }
        
        echo json_encode(['success' => true, 'orders' => $ordersList]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
