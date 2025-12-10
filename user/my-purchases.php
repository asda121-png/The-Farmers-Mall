<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'] ?? null;

// Fetch orders from database
$orders = [];

if ($user_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    require_once __DIR__ . '/../config/uuid-helper.php';
    $api = getSupabaseAPI();
    
    // Get all orders for this user
    $orders = $api->select('orders', ['customer_id' => $user_id]) ?: [];
    
    // Sort orders by created_at descending (newest first)
    usort($orders, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}

// Function to get order items
function getOrderItems($order_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    $api = getSupabaseAPI();
    return $api->select('order_items', ['order_id' => $order_id]) ?: [];
}

// Function to format status for display
function formatStatus($status) {
    $status_map = [
        'pending' => 'To Pay',
        'confirmed' => 'To Ship',
        'processing' => 'To Ship',
        'shipped' => 'To Receive',
        'delivered' => 'Completed',
        'cancelled' => 'Cancelled'
    ];
    return $status_map[$status] ?? ucfirst($status);
}

// Function to get status color
function getStatusColor($status) {
    $colors = [
        'pending' => 'orange',
        'confirmed' => 'blue',
        'processing' => 'blue',
        'shipped' => 'purple',
        'delivered' => 'green',
        'cancelled' => 'red'
    ];
    return $colors[$status] ?? 'gray';
}

// Get filter from URL
$filter = $_GET['filter'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases - Farmers Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        .tab-btn {
            position: relative;
            padding: 12px 24px;
            color: #666;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        
        .tab-btn:hover {
            color: #2E7D32;
        }
        
        .tab-btn.active {
            color: #2E7D32;
            border-bottom-color: #2E7D32;
        }
        
        .order-card {
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-orange { background-color: #FFF3E0; color: #E65100; }
        .status-blue { background-color: #E3F2FD; color: #1565C0; }
        .status-purple { background-color: #F3E5F5; color: #6A1B9A; }
        .status-green { background-color: #E8F5E9; color: #2E7D32; }
        .status-red { background-color: #FFEBEE; color: #C62828; }
        .status-gray { background-color: #F5F5F5; color: #616161; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <?php include __DIR__ . '/../includes/user-header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-8">
        <!-- Page Title -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">My Purchases</h1>
            <p class="text-gray-600 mt-1">View and track all your orders</p>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="flex border-b overflow-x-auto">
                <button class="tab-btn <?php echo $filter === 'all' ? 'active' : ''; ?>" onclick="filterOrders('all')">
                    All Orders
                </button>
                <button class="tab-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>" onclick="filterOrders('pending')">
                    To Pay
                </button>
                <button class="tab-btn <?php echo $filter === 'to-ship' ? 'active' : ''; ?>" onclick="filterOrders('to-ship')">
                    To Ship
                </button>
                <button class="tab-btn <?php echo $filter === 'to-receive' ? 'active' : ''; ?>" onclick="filterOrders('to-receive')">
                    To Receive
                </button>
                <button class="tab-btn <?php echo $filter === 'completed' ? 'active' : ''; ?>" onclick="filterOrders('completed')">
                    Completed
                </button>
                <button class="tab-btn <?php echo $filter === 'cancelled' ? 'active' : ''; ?>" onclick="filterOrders('cancelled')">
                    Cancelled
                </button>
            </div>
        </div>

        <!-- Orders List -->
        <div id="ordersContainer" class="space-y-4">
            <?php if (empty($orders)): ?>
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <i class="fas fa-shopping-bag text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No Orders Yet</h3>
                    <p class="text-gray-500 mb-6">Start shopping and your orders will appear here!</p>
                    <a href="products.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): 
                    $order_items = getOrderItems($order['id']);
                    $status = $order['status'] ?? 'pending';
                    $status_color = getStatusColor($status);
                    $status_display = formatStatus($status);
                    
                    // Determine filter category
                    $filter_category = 'all';
                    if ($status === 'pending') {
                        $filter_category = 'pending';
                    } elseif (in_array($status, ['confirmed', 'processing'])) {
                        $filter_category = 'to-ship';
                    } elseif ($status === 'shipped') {
                        $filter_category = 'to-receive';
                    } elseif ($status === 'delivered') {
                        $filter_category = 'completed';
                    } elseif ($status === 'cancelled') {
                        $filter_category = 'cancelled';
                    }
                ?>
                    <div class="order-card bg-white rounded-lg shadow-sm p-6" data-filter="<?php echo $filter_category; ?>">
                        <!-- Order Header -->
                        <div class="flex items-center justify-between mb-4 pb-4 border-b">
                            <div class="flex items-center gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Order ID</p>
                                    <p class="font-semibold text-gray-800">#<?php echo substr($order['id'], 0, 8); ?></p>
                                </div>
                                <div class="h-8 w-px bg-gray-300"></div>
                                <div>
                                    <p class="text-sm text-gray-500">Order Date</p>
                                    <p class="font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $status_color; ?>">
                                <?php echo $status_display; ?>
                            </span>
                        </div>

                        <!-- Order Items -->
                        <div class="space-y-4 mb-4">
                            <?php foreach ($order_items as $item): 
                                // Resolve image path
                                $image_path = $item['product_image_url'] ?? $item['image'] ?? $item['image_url'] ?? '';
                                if (!empty($image_path)) {
                                    // If already has http/https, use as is
                                    if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
                                        $image_src = $image_path;
                                    }
                                    // If already has ../, use as is
                                    elseif (strpos($image_path, '../') === 0) {
                                        $image_src = $image_path;
                                    }
                                    // If starts with images/, add ../
                                    elseif (strpos($image_path, 'images/') === 0) {
                                        $image_src = '../' . $image_path;
                                    }
                                    else {
                                        $image_src = $image_path;
                                    }
                                } else {
                                    $image_src = '../images/products/placeholder.png';
                                }
                            ?>
                                <div class="flex gap-4">
                                    <img src="<?php echo htmlspecialchars($image_src); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         onerror="this.src='../images/products/placeholder.png'"
                                         class="w-20 h-20 object-cover rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                        <p class="text-sm text-gray-500">Quantity: <?php echo $item['quantity']; ?></p>
                                        <p class="text-green-700 font-semibold mt-1">₱<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Subtotal</p>
                                        <p class="font-semibold text-gray-800">₱<?php echo number_format($item['subtotal'], 2); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Footer -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <div>
                                <p class="text-sm text-gray-500">Payment Method</p>
                                <p class="font-medium text-gray-800"><?php echo ucfirst($order['payment_method'] ?? 'Cash on Delivery'); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Order Total</p>
                                <p class="text-xl font-bold text-green-700">₱<?php echo number_format($order['total_amount'], 2); ?></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 mt-4">
                            <button onclick="trackOrder('<?php echo $order['id']; ?>')" 
                                    class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 font-medium">
                                <i class="fas fa-map-marker-alt mr-2"></i>Track Order
                            </button>
                            
                            <?php if ($status === 'pending'): ?>
                                <button onclick="cancelOrder('<?php echo $order['id']; ?>')" 
                                        class="flex-1 bg-white border-2 border-red-600 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50 font-medium">
                                    Cancel Order
                                </button>
                            <?php elseif ($status === 'delivered'): ?>
                                <button onclick="reorderItems('<?php echo $order['id']; ?>')" 
                                        class="flex-1 bg-white border-2 border-green-600 text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 font-medium">
                                    Buy Again
                                </button>
                            <?php elseif ($status === 'shipped'): ?>
                                <button onclick="confirmDelivery('<?php echo $order['id']; ?>')" 
                                        class="flex-1 bg-white border-2 border-green-600 text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 font-medium">
                                    Order Received
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirm Logout</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to logout?</p>
                <div class="flex gap-3">
                    <button onclick="closeLogoutModal()" class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 font-medium">
                        Cancel
                    </button>
                    <button onclick="confirmLogout()" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-medium">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div id="cancelOrderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Cancel Order</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to cancel this order? This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button onclick="closeCancelOrderModal()" class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 font-medium">
                        No, Keep Order
                    </button>
                    <button onclick="confirmCancelOrder()" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-medium">
                        Yes, Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Delivery Modal -->
    <div id="confirmDeliveryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirm Order Received</h3>
                <p class="text-gray-600 mb-6">Have you received this order? Confirming will mark it as completed.</p>
                <div class="flex gap-3">
                    <button onclick="closeConfirmDeliveryModal()" class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 font-medium">
                        Not Yet
                    </button>
                    <button onclick="confirmDeliveryOrder()" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 font-medium">
                        Yes, Received
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-800">Order Details</h3>
                <button onclick="closeOrderDetails()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="orderDetailsContent" class="p-6">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <script>
        // Logout Modal functions (if needed for other logout buttons on the page)
        function closeLogoutModal() {
            const logoutModal = document.getElementById('logoutModal');
            if (logoutModal) {
                logoutModal.classList.add('hidden');
            }
        }

        function confirmLogout() {
            window.location.href = '../auth/logout.php';
        }

        // Filter Orders
        function filterOrders(filter) {
            const url = new URL(window.location.href);
            url.searchParams.set('filter', filter);
            window.location.href = url.toString();
        }

        // Track Order - Navigate to tracking page
        function trackOrder(orderId) {
            window.location.href = `track-order.php?order_id=${orderId}`;
        }

        // Apply filter on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const filter = urlParams.get('filter') || 'all';
            
            const cards = document.querySelectorAll('.order-card');
            cards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'block';
                } else {
                    if (card.dataset.filter === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });

            // Check if no orders are visible
            const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none');
            if (visibleCards.length === 0 && cards.length > 0) {
                const container = document.getElementById('ordersContainer');
                container.innerHTML = `
                    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Orders Found</h3>
                        <p class="text-gray-500">You don't have any orders in this category.</p>
                    </div>
                `;
            }
        });

        // View Order Details
        async function viewOrderDetails(orderId) {
            const modal = document.getElementById('orderDetailsModal');
            const content = document.getElementById('orderDetailsContent');
            
            content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-green-600"></i></div>';
            modal.classList.remove('hidden');
            
            try {
                const response = await fetch(`../api/get-order-details.php?order_id=${orderId}`);
                const data = await response.json();
                
                if (data.success) {
                    const order = data.order;
                    const items = data.items;
                    
                    content.innerHTML = `
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Order ID</p>
                                    <p class="font-semibold">#${order.id.substring(0, 8)}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Order Date</p>
                                    <p class="font-semibold">${new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    <p class="font-semibold text-green-700">${order.status}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Payment Status</p>
                                    <p class="font-semibold">${order.payment_status || 'Pending'}</p>
                                </div>
                            </div>
                            
                            <div class="border-t pt-4">
                                <h4 class="font-semibold mb-3">Delivery Address</h4>
                                <p class="text-gray-700">${order.delivery_address || 'N/A'}</p>
                            </div>
                            
                            <div class="border-t pt-4">
                                <h4 class="font-semibold mb-3">Order Items</h4>
                                <div class="space-y-3">
                                    ${items.map(item => {
                                        // Resolve image path in JavaScript
                                        let imageSrc = item.product_image_url || item.image || item.image_url || '';
                                        if (imageSrc) {
                                            if (imageSrc.startsWith('http://') || imageSrc.startsWith('https://')) {
                                                // Already absolute URL
                                            } else if (imageSrc.startsWith('../')) {
                                                // Already relative path
                                            } else if (imageSrc.startsWith('images/')) {
                                                imageSrc = '../' + imageSrc;
                                            }
                                        } else {
                                            imageSrc = '../images/products/placeholder.png';
                                        }
                                        
                                        return `
                                        <div class="flex gap-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="${imageSrc}" 
                                                 alt="${item.product_name}" 
                                                 onerror="this.src='../images/products/placeholder.png'"
                                                 class="w-16 h-16 object-cover rounded">
                                            <div class="flex-1">
                                                <p class="font-medium">${item.product_name}</p>
                                                <p class="text-sm text-gray-600">Qty: ${item.quantity} × ₱${parseFloat(item.price).toFixed(2)}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-green-700">₱${parseFloat(item.subtotal).toFixed(2)}</p>
                                            </div>
                                        </div>
                                        `;
                                    }).join('')}
                                </div>
                            </div>
                            
                            <div class="border-t pt-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium">₱${parseFloat(order.total_amount).toFixed(2)}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Shipping Fee</span>
                                    <span class="font-medium">₱0.00</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold border-t pt-2 mt-2">
                                    <span>Total</span>
                                    <span class="text-green-700">₱${parseFloat(order.total_amount).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<p class="text-center text-red-600">Failed to load order details</p>';
                }
            } catch (error) {
                content.innerHTML = '<p class="text-center text-red-600">Error loading order details</p>';
            }
        }

        function closeOrderDetails() {
            document.getElementById('orderDetailsModal').classList.add('hidden');
        }

        // Cancel Order Modal
        let currentOrderId = null;
        
        function cancelOrder(orderId) {
            currentOrderId = orderId;
            document.getElementById('cancelOrderModal').classList.remove('hidden');
        }
        
        function closeCancelOrderModal() {
            document.getElementById('cancelOrderModal').classList.add('hidden');
            currentOrderId = null;
        }
        
        async function confirmCancelOrder() {
            if (!currentOrderId) return;
            
            try {
                const response = await fetch('../api/update-order-status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: currentOrderId, status: 'cancelled' })
                });
                
                const data = await response.json();
                closeCancelOrderModal();
                
                if (data.success) {
                    // Show success message
                    showSuccessMessage('Order cancelled successfully');
                    // Reload after a brief delay to show the message
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorMessage('Failed to cancel order: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                closeCancelOrderModal();
                showErrorMessage('Error cancelling order');
            }
        }

        // Confirm Delivery Modal
        function confirmDelivery(orderId) {
            currentOrderId = orderId;
            document.getElementById('confirmDeliveryModal').classList.remove('hidden');
        }
        
        function closeConfirmDeliveryModal() {
            document.getElementById('confirmDeliveryModal').classList.add('hidden');
            currentOrderId = null;
        }
        
        async function confirmDeliveryOrder() {
            if (!currentOrderId) return;
            
            try {
                const response = await fetch('../api/update-order-status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: currentOrderId, status: 'completed' })
                });
                
                const data = await response.json();
                closeConfirmDeliveryModal();
                
                if (data.success) {
                    showSuccessMessage('Order marked as completed!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorMessage('Failed to update order: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                closeConfirmDeliveryModal();
                showErrorMessage('Error updating order');
            }
        }
        
        // Success/Error message helper functions
        function showSuccessMessage(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
            toast.innerHTML = `<i class="fas fa-check-circle"></i><span>${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        function showErrorMessage(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
            toast.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // Reorder Items
        async function reorderItems(orderId) {
            try {
                const response = await fetch(`../api/get-order-details.php?order_id=${orderId}`);
                const data = await response.json();
                
                if (data.success && data.items) {
                    // Add items to cart
                    for (const item of data.items) {
                        await fetch('../api/cart.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'add',
                                product_id: item.product_id,
                                quantity: item.quantity
                            })
                        });
                    }
                    alert('Items added to cart!');
                    window.location.href = 'cart.php';
                } else {
                    alert('Failed to load order items');
                }
            } catch (error) {
                alert('Error reordering items');
            }
        }
    </script>

    <script src="../assets/js/profile-sync.js"></script>
    <script src="../assets/js/cart-preview.js"></script>
    <script src="../assets/js/search-autocomplete.js"></script>
</body>
</html>
