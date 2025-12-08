<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get order ID from URL
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header('Location: my-purchases.php');
    exit();
}

// Fetch order details
$order = null;
$order_items = [];

if ($order_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    require_once __DIR__ . '/../config/uuid-helper.php';
    $api = getSupabaseAPI();
    
    // Get order
    $orders = $api->select('orders', ['id' => $order_id]);
    if (!empty($orders)) {
        $order = $orders[0];
        
        // Verify order belongs to logged-in user
        if ($order['customer_id'] !== $_SESSION['user_id']) {
            header('Location: my-purchases.php');
            exit();
        }
        
        // Get order items
        $order_items = $api->select('order_items', ['order_id' => $order_id]) ?: [];
    }
}

if (!$order) {
    header('Location: my-purchases.php');
    exit();
}

// Status tracking steps
function getTrackingSteps($status) {
    $steps = [
        [
            'name' => 'Order Placed',
            'icon' => 'fa-file-alt',
            'description' => 'Your order has been received',
            'statuses' => ['pending', 'to_pay', 'confirmed', 'to_ship', 'processing', 'shipped', 'to_receive', 'delivered', 'completed']
        ],
        [
            'name' => 'Payment Confirmed',
            'icon' => 'fa-credit-card',
            'description' => 'Payment has been verified',
            'statuses' => ['confirmed', 'to_ship', 'processing', 'shipped', 'to_receive', 'delivered', 'completed']
        ],
        [
            'name' => 'Processing',
            'icon' => 'fa-box',
            'description' => 'Your order is being prepared',
            'statuses' => ['to_ship', 'processing', 'shipped', 'to_receive', 'delivered', 'completed']
        ],
        [
            'name' => 'Out for Delivery',
            'icon' => 'fa-truck',
            'description' => 'Order is on the way',
            'statuses' => ['shipped', 'to_receive', 'delivered', 'completed']
        ],
        [
            'name' => 'Delivered',
            'icon' => 'fa-check-circle',
            'description' => 'Order successfully delivered',
            'statuses' => ['delivered', 'completed']
        ]
    ];
    
    // If cancelled, show different steps
    if ($status === 'cancelled') {
        return [
            [
                'name' => 'Order Placed',
                'icon' => 'fa-file-alt',
                'description' => 'Your order was received',
                'statuses' => ['cancelled']
            ],
            [
                'name' => 'Order Cancelled',
                'icon' => 'fa-times-circle',
                'description' => 'Order has been cancelled',
                'statuses' => ['cancelled']
            ]
        ];
    }
    
    return $steps;
}

$current_status = $order['status'] ?? 'pending';
$tracking_steps = getTrackingSteps($current_status);

// Calculate current step
$current_step_index = 0;
foreach ($tracking_steps as $index => $step) {
    if (in_array($current_status, $step['statuses'])) {
        $current_step_index = $index;
    }
}

// Status display mapping
$status_display = [
    'pending' => 'To Pay',
    'to_pay' => 'To Pay',
    'confirmed' => 'To Ship',
    'to_ship' => 'To Ship',
    'processing' => 'To Ship',
    'shipped' => 'To Receive',
    'to_receive' => 'To Receive',
    'delivered' => 'Completed',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Farmers Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        .timeline-step {
            position: relative;
        }
        
        .timeline-line {
            position: absolute;
            left: 24px;
            top: 50px;
            width: 3px;
            height: calc(100% - 50px);
            background: #E5E7EB;
        }
        
        .timeline-line.active {
            background: #2E7D32;
        }
        
        .timeline-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            position: relative;
            z-index: 10;
            border: 3px solid #E5E7EB;
            background: white;
            color: #9CA3AF;
            transition: all 0.3s ease;
        }
        
        .timeline-icon.active {
            border-color: #2E7D32;
            background: #2E7D32;
            color: white;
            box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1);
        }
        
        .timeline-icon.completed {
            border-color: #2E7D32;
            background: #2E7D32;
            color: white;
        }
        
        .product-card {
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include __DIR__ . '/../includes/user-header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-6 py-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="my-purchases.php" class="inline-flex items-center text-green-700 hover:text-green-800">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to My Purchases
            </a>
        </div>

        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Order Tracking</h1>
                    <p class="text-gray-600">Order ID: <span class="font-semibold text-gray-800">#<?php echo substr($order['id'], 0, 8); ?></span></p>
                    <p class="text-sm text-gray-500">Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-500 mb-1">Order Status</p>
                    <span class="inline-block px-4 py-2 rounded-full font-semibold text-sm
                        <?php if ($current_status === 'cancelled'): ?>
                            bg-red-100 text-red-700
                        <?php elseif ($current_status === 'completed' || $current_status === 'delivered'): ?>
                            bg-green-100 text-green-700
                        <?php else: ?>
                            bg-blue-100 text-blue-700
                        <?php endif; ?>">
                        <?php echo $status_display[$current_status] ?? ucfirst($current_status); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Timeline Section -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Tracking Progress</h2>
                    
                    <div class="space-y-6">
                        <?php foreach ($tracking_steps as $index => $step): 
                            $is_completed = $index < $current_step_index;
                            $is_active = $index === $current_step_index;
                            $is_last = $index === count($tracking_steps) - 1;
                        ?>
                            <div class="timeline-step relative">
                                <?php if (!$is_last): ?>
                                    <div class="timeline-line <?php echo ($is_completed || $is_active) ? 'active' : ''; ?>"></div>
                                <?php endif; ?>
                                
                                <div class="flex gap-4">
                                    <div class="timeline-icon <?php echo $is_completed ? 'completed' : ($is_active ? 'active' : ''); ?>">
                                        <i class="fas <?php echo $step['icon']; ?>"></i>
                                    </div>
                                    
                                    <div class="flex-1 pb-8">
                                        <h3 class="font-semibold text-gray-800 mb-1
                                            <?php echo ($is_completed || $is_active) ? 'text-green-700' : ''; ?>">
                                            <?php echo $step['name']; ?>
                                        </h3>
                                        <p class="text-sm text-gray-600"><?php echo $step['description']; ?></p>
                                        <?php if ($is_active): ?>
                                            <p class="text-xs text-green-700 mt-2 font-medium">
                                                <i class="fas fa-circle text-xs mr-1"></i> Current Status
                                            </p>
                                        <?php elseif ($is_completed): ?>
                                            <p class="text-xs text-green-600 mt-2">
                                                <i class="fas fa-check text-xs mr-1"></i> Completed
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Order Items</h2>
                    
                    <div class="space-y-4">
                        <?php foreach ($order_items as $item): 
                            // Resolve image path
                            $image_path = $item['product_image_url'] ?? $item['image'] ?? $item['image_url'] ?? '';
                            if (!empty($image_path)) {
                                if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
                                    $image_src = $image_path;
                                } elseif (strpos($image_path, '../') === 0) {
                                    $image_src = $image_path;
                                } elseif (strpos($image_path, 'images/') === 0) {
                                    $image_src = '../' . $image_path;
                                } else {
                                    $image_src = $image_path;
                                }
                            } else {
                                $image_src = '../images/products/placeholder.png';
                            }
                        ?>
                            <div class="product-card flex gap-4 p-4 border border-gray-200 rounded-lg">
                                <img src="<?php echo htmlspecialchars($image_src); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                     onerror="this.src='../images/products/placeholder.png'"
                                     class="w-24 h-24 object-cover rounded-lg">
                                
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p class="text-sm text-gray-600 mb-2">Quantity: <?php echo $item['quantity']; ?></p>
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-700 font-semibold">₱<?php echo number_format($item['price'], 2); ?></span>
                                        <span class="text-gray-400">×</span>
                                        <span class="text-gray-600"><?php echo $item['quantity']; ?></span>
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-sm text-gray-500 mb-1">Subtotal</p>
                                    <p class="text-lg font-bold text-gray-800">₱<?php echo number_format($item['subtotal'], 2); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Order Summary</h3>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Delivery Fee</span>
                            <span>₱0.00</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between font-bold text-gray-800">
                            <span>Total</span>
                            <span class="text-green-700 text-xl">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>

                    <div class="border-t pt-4 space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Payment Method</p>
                            <p class="font-medium text-gray-800">
                                <i class="fas fa-wallet text-green-700 mr-2"></i>
                                <?php echo ucfirst($order['payment_method'] ?? 'Cash on Delivery'); ?>
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-gray-500 mb-1">Delivery Address</p>
                            <p class="font-medium text-gray-800">
                                <i class="fas fa-map-marker-alt text-green-700 mr-2"></i>
                                <?php echo htmlspecialchars($order['delivery_address'] ?? 'Not specified'); ?>
                            </p>
                        </div>
                        
                        <?php if (!empty($order['notes'])): ?>
                        <div>
                            <p class="text-gray-500 mb-1">Order Notes</p>
                            <p class="font-medium text-gray-800">
                                <i class="fas fa-sticky-note text-green-700 mr-2"></i>
                                <?php echo htmlspecialchars($order['notes']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-2">
                        <?php if ($current_status === 'pending' || $current_status === 'to_pay'): ?>
                            <button onclick="cancelOrder('<?php echo $order['id']; ?>')" 
                                    class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 font-medium transition">
                                <i class="fas fa-times mr-2"></i> Cancel Order
                            </button>
                        <?php elseif ($current_status === 'shipped' || $current_status === 'to_receive'): ?>
                            <button onclick="confirmDelivery('<?php echo $order['id']; ?>')" 
                                    class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 font-medium transition">
                                <i class="fas fa-check mr-2"></i> Order Received
                            </button>
                        <?php endif; ?>
                        
                        <a href="my-purchases.php" 
                           class="w-full block text-center bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 font-medium transition">
                            <i class="fas fa-list mr-2"></i> View All Orders
                        </a>
                    </div>

                    <!-- Help Section -->
                    <div class="mt-6 border-t pt-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Need Help?</p>
                        <p class="text-xs text-gray-600 mb-3">Contact our customer support for assistance</p>
                        <div class="space-y-2 text-xs">
                            <a href="tel:5551234567" class="flex items-center text-green-700 hover:text-green-800">
                                <i class="fas fa-phone w-4"></i>
                                <span class="ml-2">(555) 123-4567</span>
                            </a>
                            <a href="mailto:support@farmersmall.com" class="flex items-center text-green-700 hover:text-green-800">
                                <i class="fas fa-envelope w-4"></i>
                                <span class="ml-2">support@farmersmall.com</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                // Implement cancel order logic
                window.location.href = '../api/update-order-status.php?order_id=' + orderId + '&status=cancelled';
            }
        }

        function confirmDelivery(orderId) {
            if (confirm('Confirm that you have received this order?')) {
                // Implement confirm delivery logic
                window.location.href = '../api/update-order-status.php?order_id=' + orderId + '&status=completed';
            }
        }
    </script>
</body>
</html>
