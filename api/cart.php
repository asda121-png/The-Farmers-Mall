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

// Get customer's full name for cart operations
$customer = $api->select('users', ['id' => $user_id]);
$customer_name = 'Unknown Customer';
if (!empty($customer)) {
    $first_name = $customer[0]['first_name'] ?? '';
    $last_name = $customer[0]['last_name'] ?? '';
    $customer_name = trim($first_name . ' ' . $last_name);
    if (empty($customer_name)) {
        $customer_name = $customer[0]['email'] ?? 'Unknown Customer';
    }
}

try {
    switch ($method) {
        case 'GET':
            // Get all cart items for the user by customer_name
            $cartItems = $api->select('cart', ['customer_name' => $customer_name]);
            
            // Fetch product details for each cart item
            $items = [];
            foreach ($cartItems as $cartItem) {
                $product = $api->select('products', ['id' => $cartItem['product_id']]);
                if (!empty($product)) {
                    $items[] = [
                        'cart_id' => $cartItem['id'],
                        'product_id' => $cartItem['product_id'],
                        'customer_name' => $cartItem['customer_name'] ?? 'Unknown Customer',
                        'product_name' => $cartItem['product_name'] ?? $product[0]['name'],
                        'name' => $product[0]['name'],
                        'price' => floatval($product[0]['price']),
                        'description' => $product[0]['description'] ?? '',
                        'image' => $product[0]['image_url'] ?? '',
                        'quantity' => intval($cartItem['quantity']),
                        'stock_quantity' => intval($product[0]['stock_quantity']),
                        'category' => $product[0]['category'] ?? '',
                        'unit' => $product[0]['unit'] ?? 'kg'
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'items' => $items]);
            break;

        case 'POST':
            // Add item to cart
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['quantity'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required field: quantity']);
                exit();
            }

            $product_id = null;

            // Check if product_id is provided
            if (isset($data['product_id']) && !empty($data['product_id'])) {
                $product_id = $data['product_id'];
            } 
            // If no product_id, try to find or create product by name
            else if (isset($data['product_name']) && !empty($data['product_name'])) {
                // Try to find existing product by name
                $existingProduct = $api->select('products', ['name' => $data['product_name']]);
                
                if (!empty($existingProduct)) {
                    $product_id = $existingProduct[0]['id'];
                } else {
                    // Create new product entry
                    $newProduct = [
                        'name' => $data['product_name'],
                        'price' => floatval($data['price'] ?? 0),
                        'description' => $data['description'] ?? '',
                        'image_url' => $data['image'] ?? '',
                        'category' => $data['category'] ?? 'other',
                        'stock_quantity' => 1000, // Default high stock for manually added items
                        'unit' => 'kg',
                        'status' => 'active'
                    ];
                    
                    $created = $api->insert('products', $newProduct);
                    if (!empty($created) && isset($created[0]['id'])) {
                        $product_id = $created[0]['id'];
                    }
                }
            }

            if (!$product_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Product ID or product name is required']);
                exit();
            }

            // Check if item already exists in cart
            $existing = $api->select('cart', [
                'customer_name' => $customer_name,
                'product_id' => $product_id
            ]);

            if (!empty($existing)) {
                // Update quantity
                $newQuantity = intval($existing[0]['quantity']) + intval($data['quantity']);
                $result = $api->update('cart', ['quantity' => $newQuantity], ['id' => $existing[0]['id']]);
                
                if ($result !== false) {
                    echo json_encode(['success' => true, 'message' => 'Cart updated']);
                } else {
                    throw new Exception('Failed to update cart');
                }
            } else {
                // Get product name for the cart entry
                $product = $api->select('products', ['id' => $product_id]);
                $product_name = !empty($product) ? $product[0]['name'] : ($data['product_name'] ?? 'Unknown Product');
                
                // Add new item
                $result = $api->insert('cart', [
                    'customer_name' => $customer_name,
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'quantity' => intval($data['quantity'])
                ]);

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
                } else {
                    throw new Exception('Failed to add item to cart');
                }
            }
            break;

        case 'PUT':
            // Update cart item quantity
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['cart_id']) || !isset($data['quantity'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit();
            }

            if (intval($data['quantity']) <= 0) {
                // If quantity is 0 or less, delete the item
                $result = $api->delete('cart', ['id' => $data['cart_id']]);
            } else {
                $result = $api->update('cart', 
                    ['quantity' => intval($data['quantity'])], 
                    ['id' => $data['cart_id'], 'customer_name' => $customer_name]
                );
            }

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Cart updated']);
            } else {
                throw new Exception('Failed to update cart');
            }
            break;

        case 'DELETE':
            // Delete cart item or clear entire cart
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['cart_id'])) {
                // Delete specific item
                $result = $api->delete('cart', [
                    'id' => $data['cart_id'],
                    'customer_name' => $customer_name
                ]);
                $message = 'Item removed from cart';
            } else if (isset($data['clear_all']) && $data['clear_all'] === true) {
                // Clear entire cart
                $result = $api->delete('cart', ['customer_name' => $customer_name]);
                $message = 'Cart cleared';
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                exit();
            }

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                throw new Exception('Failed to delete from cart');
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
