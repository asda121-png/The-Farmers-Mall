<?php
session_start();

// Check if user is logged in and is a retailer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'retailer' && $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Load database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase-api.php';

// Get user data from database
$api = getSupabaseAPI();
$userId = $_SESSION['user_id'];
$userData = null;
$profilePicture = '../images/default-avatar.svg';
$userFullName = $_SESSION['full_name'] ?? 'Retailer';
$userEmail = $_SESSION['email'] ?? '';
$shopName = 'My Shop';
$retailerId = null;

try {
    $users = $api->select('users', ['id' => $userId]);
    if (!empty($users)) {
        $userData = $users[0];
        $userFullName = $userData['full_name'] ?? $userFullName;
        $userEmail = $userData['email'] ?? $userEmail;
        
        if (!empty($userData['profile_picture'])) {
            $profilePath = '../' . ltrim($userData['profile_picture'], '/');
            if (file_exists($profilePath)) {
                $profilePicture = $profilePath;
            }
        }
        
        if ($userData['user_type'] === 'retailer') {
            $retailers = $api->select('retailers', ['user_id' => $userId]);
            if (!empty($retailers)) {
                $shopName = $retailers[0]['shop_name'] ?? $shopName;
                $retailerId = $retailers[0]['id'];
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}
    
// Fetch orders for this retailer
$orders = [];
if ($retailerId) {
    try {
        // Fetch orders for this retailer, newest first
        $orders = $api->select('orders', ['retailer_id' => $retailerId], ['order' => 'created_at:desc']);
        
        // Fallback: If no orders found directly, check order_items (handles mixed orders or missing retailer_id)
        if (empty($orders)) {
            $retailerItems = $api->select('order_items', ['retailer_id' => $retailerId]);
            $orderIds = [];
            foreach ($retailerItems as $item) {
                if (!empty($item['order_id'])) {
                    $orderIds[] = $item['order_id'];
                }
            }
            $orderIds = array_unique($orderIds);
            
            foreach ($orderIds as $oid) {
                $result = $api->select('orders', ['id' => $oid]);
                if (!empty($result)) {
                    $orders[] = $result[0];
                }
            }
            
            // Sort by date descending
            usort($orders, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }
    } catch (Exception $e) {
        error_log("Error fetching orders: " . $e->getMessage());
    }
}

// Helper function to map status to a color and progress
function getStatusInfo($status) {
    $statusMap = [
        'pending' => ['color' => '#F57C00', 'bg' => '#FFF9C4', 'text' => 'Pending', 'progress' => 0],
        'confirmed' => ['color' => '#1E88E5', 'bg' => '#E3F2FD', 'text' => 'Confirmed', 'progress' => 1],
        'processing' => ['color' => '#00BCD4', 'bg' => '#E1F5FE', 'text' => 'Processing', 'progress' => 2],
        'shipped' => ['color' => '#00897B', 'bg' => '#E0F2F1', 'text' => 'Shipped', 'progress' => 3],
        'delivered' => ['color' => '#388E3C', 'bg' => '#E8F5E9', 'text' => 'Delivered', 'progress' => 3],
        'cancelled' => ['color' => '#757575', 'bg' => '#EEEEEE', 'text' => 'Cancelled', 'progress' => 0],
    ];
    $status = strtolower($status);
    return $statusMap[$status] ?? ['color' => '#757575', 'bg' => '#EEEEEE', 'text' => ucfirst($status), 'progress' => 0];
}

// Prepare data for JavaScript modal
$jsOrderData = [];
foreach ($orders as $order) {
    $items = [];
    
    // Try to fetch actual order items
    try {
        $orderItems = $api->select('order_items', ['order_id' => $order['id'], 'retailer_id' => $retailerId]);
        foreach ($orderItems as $item) {
            $items[] = [
                'name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'price' => '₱' . number_format($item['price'], 2),
                'subtotal' => '₱' . number_format($item['subtotal'], 2)
            ];
        }
    } catch (Exception $e) { /* Ignore errors, fall back to simple display */ }

    if (empty($items) && !empty($order['product_name'])) {
        $items[] = [
            'name' => $order['product_name'],
            'quantity' => '1', // Placeholder, as this is not in the orders table
            'price' => '₱' . number_format($order['total_amount'], 2), // Placeholder
            'subtotal' => '₱' . number_format($order['total_amount'], 2)
        ];
    }

    $jsOrderData[$order['id']] = [
        'id' => '#' . substr($order['id'], 0, 8),
        'customer' => $order['customer_name'],
        'date' => date('m/d/Y', strtotime($order['created_at'])),
        'status' => ucfirst($order['status']),
        'progress' => getStatusInfo($order['status'])['progress'],
        'items' => $items,
        'address' => $order['delivery_address'],
        'phone' => '', // Not in orders table
        'notes' => $order['notes'],
        'subtotal' => '₱' . number_format($order['total_amount'], 2),
        'deliveryFee' => '₱0.00', // Not in orders table
        'total' => '₱' . number_format($order['total_amount'], 2)
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Fulfillment – The Farmer's Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fbf8;
        }
        #content {
            min-height: calc(100vh - 200px);
        }
        footer {
            margin-top: auto;
        }
        /* Sticky sidebar - fixed position */
        #sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            overflow-y: auto !important;
            display: flex !important;
            flex-direction: column !important;
            z-index: 40 !important;
        }
        /* Add left margin to main content to account for fixed sidebar */
        #app {
            margin-left: 16rem !important;
        }
        /* Mobile menu toggle */
        #mobileMenuBtn {
            display: none;
        }
        @media (max-width: 768px) {
            #mobileMenuBtn {
                display: flex;
            }
            #app {
                margin-left: 0 !important;
            }
            #sidebar {
                left: -100%;
                z-index: 50;
                transition: left 0.3s ease;
            }
            #sidebar.active {
                left: 0;
            }
            #overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            #overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="flex flex-col min-h-screen">
    <!-- Mobile Menu Overlay -->
    <div id="overlay" onclick="toggleMobileMenu()"></div>
    
    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="fixed top-4 left-4 z-50 bg-green-600 text-white p-3 rounded-lg shadow-lg md:hidden" onclick="toggleMobileMenu()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    
    <div id="app" class="flex flex-1">
        
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="w-64 md:w-64 bg-white shadow-xl flex flex-col p-4 space-y-2 flex-shrink-0">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-2">
                    <i class="fas fa-leaf text-white text-lg"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
            </div>
            <a href="retailer-dashboard2.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-tachometer-alt text-lg mr-3"></i>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-boxes text-lg mr-3"></i>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
                <i class="fas fa-clipboard-list text-lg mr-3"></i>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-chart-line text-lg mr-3"></i>
                Financial Reports
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-star text-lg mr-3"></i>
                Reviews & Customers
            </a>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600 transition" title="Home"><i class="fa-solid fa-house text-xl"></i></a>

                        <!-- Notifications Icon -->
                        <div class="relative" id="notificationPreviewContainer">
                            <a href="retailernotifications.php" class="text-gray-600 hover:text-green-600 transition relative" title="Notifications" id="notificationIcon">
                                <i class="fa-regular fa-bell text-xl"></i>
                                <span id="notificationBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
                            </a>
                            
                            <!-- Notification Preview Dropdown -->
                            <div id="notificationPreview" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-100">
                                    <h3 class="font-semibold text-gray-800">Notifications</h3>
                                </div>
                                <div id="notificationPreviewItems" class="max-h-96 overflow-y-auto">
                                    <div class="p-8 text-center text-gray-500">
                                        <i class="fas fa-bell text-4xl mb-2 text-gray-300"></i>
                                        <p class="text-sm">No notifications</p>
                                    </div>
                                </div>
                                <div class="p-4 border-t border-gray-100 bg-gray-50">
                                    <a href="retailernotifications.php" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition font-medium">
                                        View All Notifications
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" id="profileDropdownContainer">
                            <button id="profileDropdownBtn" class="flex items-center focus:outline-none" title="<?php echo htmlspecialchars($userFullName); ?>">
                                <?php if (!empty($profilePicture) && $profilePicture !== '../images/default-avatar.svg' && file_exists(__DIR__ . '/' . $profilePicture)): ?>
                                    <img id="headerProfilePic" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                <?php endif; ?>
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <!-- Profile Header -->
                                <div class="p-4 border-b border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($profilePicture) && $profilePicture !== '../images/default-avatar.svg' && file_exists(__DIR__ . '/' . $profilePicture)): ?>
                                            <img src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                                        <?php else: ?>
                                            <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center">
                                                <i class="fas fa-user text-white text-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($userFullName); ?></p>
                                            <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($userEmail); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Menu Items -->
                                <div class="py-2">
                                    <a href="retailerprofile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fas fa-user-circle text-gray-400 text-lg w-5"></i>
                                        <span class="ml-3 text-sm">Profile & Settings</span>
                                    </a>
                                    <a href="../auth/logout.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fas fa-sign-out-alt text-gray-400 text-lg w-5"></i>
                                        <span class="ml-3 text-sm">Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- End Profile Dropdown -->
                    </div>
                </div>
            </header>

        
            <main id="content" class="p-8 transition-all duration-300 flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Order Management & Fulfillment</h2>
                
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total New Orders -->
                    <div class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Total New Orders</p>
                            <i class="fas fa-file-alt text-2xl text-green-400"></i>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-800">594</p>
                    </div>

                    <!-- Total Order Pending -->
                    <div class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Total Order Pending</p>
                            <i class="fas fa-clock text-2xl text-green-400"></i>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-800">257,361</p>
                    </div>

                    <!-- Total Products Sales -->
                    <div class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Total Products Sales</p>
                            <i class="fas fa-chart-line text-2xl text-green-400"></i>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-800">8,594</p>
                    </div>
                </div>

                <!-- Bulk Actions Bar (Hidden by default, shown when items are selected) -->
                <div id="bulk-actions-bar" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 hidden">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-4">
                                <span id="selected-count">0</span> order(s) selected
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="bulkEditOrders()" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button onclick="bulkPrintOrders()" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition">
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                            <button onclick="clearSelection()" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-400 transition">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center mb-6">
                    <div class="relative min-w-[180px]">
                        <select id="order-status-filter" onchange="filterOrders()" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white appearance-none pr-10">
                            <option value="All">Filter by Status</option>
                            <option value="In Queue">In Queue</option>
                            <option value="Pending Fulfillment">Pending Fulfillment</option>
                            <option value="Picking in Progress">Picking in Progress</option>
                            <option value="Packing in Progress">Packing in Progress</option>
                            <option value="Picking Issue">Picking Issue</option>
                            <option value="Packing Issue">Packing Issue</option>
                            <option value="Ready for Delivery">Ready for Delivery</option>
                            <option value="Shipping / In Transit">Shipping / In Transit</option>
                            <option value="Late Delivery">Late Delivery</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 pointer-events-none text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                    <input type="checkbox" id="select-all-orders" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU/Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount & Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body" class="bg-white divide-y divide-gray-200">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">No orders found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <?php $statusInfo = getStatusInfo($order['status']); ?>
                                    <tr class="hover:bg-gray-50 cursor-pointer transition-colors" onclick="viewDetails('<?php echo htmlspecialchars($order['id']); ?>')">
                                        <td class="px-6 py-4" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="order-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500" data-order-id="<?php echo htmlspecialchars($order['id']); ?>">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo substr(htmlspecialchars($order['id']), 0, 8); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center">
                                                <img src="../images/default-avatar.svg" alt="<?php echo htmlspecialchars($order['customer_name']); ?>" class="w-8 h-8 rounded-full border-2 border-gray-200 mr-2 object-cover" onerror="this.src='../images/default-avatar.svg'">
                                                <span class="text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <div class="font-semibold text-gray-900">₱<?php echo number_format($order['total_amount'], 2); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars(ucfirst($order['payment_method'])); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-1">
                                                <div class="w-10 h-1 rounded <?php echo $statusInfo['progress'] >= 1 ? 'bg-blue-500' : 'bg-gray-200'; ?>"></div>
                                                <div class="w-10 h-1 rounded <?php echo $statusInfo['progress'] >= 2 ? 'bg-cyan-500' : 'bg-gray-200'; ?>"></div>
                                                <div class="w-10 h-1 rounded <?php echo $statusInfo['progress'] >= 3 ? 'bg-green-500' : 'bg-gray-200'; ?>"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" style="background-color: <?php echo $statusInfo['bg']; ?>; color: <?php echo $statusInfo['color']; ?>;">
                                                <?php echo htmlspecialchars($statusInfo['text']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div id="no-orders-message" class="p-6 text-center text-gray-500 hidden">No orders found.</div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h3 class="text-2xl font-bold text-gray-800">Order Details</h3>
                <button onclick="closeOrderDetailsModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6">
                <!-- Order Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600">Order ID</p>
                        <p id="modal-order-id" class="text-lg font-semibold text-gray-900">#A1B2C3D4</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <span id="modal-order-status" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">New</span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Customer</p>
                        <p id="modal-customer-name" class="text-lg font-medium text-gray-900">Alice Johnson</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date</p>
                        <p id="modal-order-date" class="text-lg font-medium text-gray-900">12/7/2025</p>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Order Items</h4>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="modal-order-items" class="bg-white divide-y divide-gray-200">
                                <!-- Sample items - will be populated dynamically -->
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">Fresh Apples</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">2 kg</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">₱150.00</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold">₱300.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Delivery Info -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Delivery Information</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p id="modal-delivery-address" class="text-sm text-gray-700 mb-2">
                            <strong>Address:</strong> 123 Main Street, Barangay Sample, Manila City
                        </p>
                        <p id="modal-delivery-phone" class="text-sm text-gray-700 mb-2">
                            <strong>Phone:</strong> +63 912 345 6789
                        </p>
                        <p id="modal-delivery-notes" class="text-sm text-gray-700">
                            <strong>Notes:</strong> Please deliver before 5 PM
                        </p>
                    </div>
                </div>
                
                <!-- Order Total -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Subtotal:</span>
                        <span id="modal-subtotal" class="font-medium">₱400.00</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Delivery Fee:</span>
                        <span id="modal-delivery-fee" class="font-medium">₱15.98</span>
                    </div>
                    <div class="flex justify-between items-center text-lg font-bold border-t pt-2">
                        <span>Total:</span>
                        <span id="modal-total" class="text-green-600">₱415.98</span>
                    </div>
                </div>
            </div>
            
            <div class="p-6 border-t bg-gray-50 flex justify-end space-x-3">
                <button onclick="closeOrderDetailsModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                    Close
                </button>
                <button id="modal-action-btn" onclick="openStatusUpdateModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Update Status
                </button>
            </div>
        </div>
    </div>
    
    <!-- Status Update Modal -->
    <div id="statusUpdateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-bold text-gray-800">Update Order Status</h3>
                    <button onclick="closeStatusUpdateModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <p class="text-gray-600 mb-4">Change status for order <span id="status-modal-order-id" class="font-semibold text-gray-900">#A1B2C3D4</span></p>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                    <select id="newStatusSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="In Queue">In Queue</option>
                        <option value="Pending Fulfillment">Pending Fulfillment</option>
                        <option value="Picking in Progress">Picking in Progress</option>
                        <option value="Packing in Progress">Packing in Progress</option>
                        <option value="Picking Issue">Picking Issue</option>
                        <option value="Packing Issue">Packing Issue</option>
                        <option value="Ready for Delivery">Ready for Delivery</option>
                        <option value="Shipping / In Transit">Shipping / In Transit</option>
                        <option value="Late Delivery">Late Delivery</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea id="statusUpdateNotes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Add any notes about this status change..."></textarea>
                </div>
            </div>
            
            <div class="p-6 border-t bg-gray-50 flex justify-end space-x-3">
                <button onclick="closeStatusUpdateModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button onclick="confirmStatusUpdate()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Update Status
                </button>
            </div>
        </div>
    </div>
    
</div>

<script>
    // Profile dropdown hover handlers (matching user header style)
    let profileDropdownTimeout = null;
    let notificationPreviewTimeout = null;
    const HOVER_DELAY = 200;
    
    const profileContainer = document.getElementById('profileDropdownContainer');
    const profileDropdown = document.getElementById('profileDropdown');
    const profileBtn = document.getElementById('profileDropdownBtn');
    
    if (profileContainer && profileDropdown && profileBtn) {
        profileContainer.addEventListener('mouseenter', function() {
            clearTimeout(profileDropdownTimeout);
            profileDropdown.classList.remove('hidden');
        });
        
        profileContainer.addEventListener('mouseleave', function() {
            profileDropdownTimeout = setTimeout(function() {
                profileDropdown.classList.add('hidden');
            }, HOVER_DELAY);
        });
        
        profileDropdown.addEventListener('mouseenter', function() {
            clearTimeout(profileDropdownTimeout);
        });
        
        profileDropdown.addEventListener('mouseleave', function() {
            profileDropdownTimeout = setTimeout(function() {
                profileDropdown.classList.add('hidden');
            }, HOVER_DELAY);
        });
        
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function() {
            if (!profileDropdown.classList.contains('hidden')) {
                profileDropdown.classList.add('hidden');
            }
        });
    }

    let lastProfilePicture = '<?php echo htmlspecialchars($profilePicture); ?>';
    
    async function checkProfileUpdates() {
        try {
            const response = await fetch('../api/get-profile.php');
            const result = await response.json();
            
            if (result.success && result.data) {
                const profilePicElement = document.getElementById('headerProfilePic');
                if (result.data.profile_picture && result.data.profile_picture !== lastProfilePicture) {
                    profilePicElement.src = result.data.profile_picture + '?t=' + new Date().getTime();
                    lastProfilePicture = result.data.profile_picture;
                }
                if (result.data.full_name) {
                    profilePicElement.parentElement.setAttribute('title', result.data.full_name);
                }
            }
        } catch (error) {
            console.error('Error checking profile updates:', error);
        }
    }
    
    setInterval(checkProfileUpdates, 5000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            checkProfileUpdates();
        }
    });
    window.addEventListener('load', () => {
        setTimeout(checkProfileUpdates, 1000);
    });

    // Notification preview hover handlers
    const notificationContainer = document.getElementById('notificationPreviewContainer');
    const notificationPreview = document.getElementById('notificationPreview');
    const notificationIcon = document.getElementById('notificationIcon');
    
    if (notificationContainer && notificationPreview && notificationIcon) {
        notificationContainer.addEventListener('mouseenter', function() {
            clearTimeout(notificationPreviewTimeout);
            loadRetailerNotificationPreview();
            notificationPreview.classList.remove('hidden');
        });
        
        notificationContainer.addEventListener('mouseleave', function() {
            notificationPreviewTimeout = setTimeout(function() {
                notificationPreview.classList.add('hidden');
            }, HOVER_DELAY);
        });
        
        notificationPreview.addEventListener('mouseenter', function() {
            clearTimeout(notificationPreviewTimeout);
        });
        
        notificationPreview.addEventListener('mouseleave', function() {
            notificationPreviewTimeout = setTimeout(function() {
                notificationPreview.classList.add('hidden');
            }, HOVER_DELAY);
        });
    }

    function loadRetailerNotificationBadge() {
        const badge = document.getElementById('notificationBadge');
        if (!badge) return;
        
        fetch('../api/get-retailer-notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications) {
                    const unreadCount = data.unreadCount || 0;
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                    localStorage.setItem('retailerNotifications', JSON.stringify(data.notifications));
                }
            })
            .catch(error => console.error('Error loading notification badge:', error));
    }
    
    function loadRetailerNotificationPreview() {
        const notificationPreviewItems = document.getElementById('notificationPreviewItems');
        if (!notificationPreviewItems) return;
        
        fetch('../api/get-retailer-notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications && data.notifications.length > 0) {
                    const recentNotifications = data.notifications.slice(0, 5);
                    
                    notificationPreviewItems.innerHTML = recentNotifications.map(notif => {
                        const isUnread = !notif.read;
                        const unreadClass = isUnread ? 'bg-green-50 border-l-4 border-green-500' : '';
                        const timeAgo = getTimeAgo(new Date(notif.timestamp));
                        
                        let iconClass = 'fa-info-circle', iconBgClass = 'bg-blue-100', iconTextClass = 'text-blue-700';
                        if (notif.type === 'order') { iconClass = 'fa-box'; iconBgClass = 'bg-green-100'; iconTextClass = 'text-green-700'; }
                        else if (notif.type === 'stock') { iconClass = 'fa-exclamation-triangle'; iconBgClass = 'bg-yellow-100'; iconTextClass = 'text-yellow-700'; }
                        else if (notif.type === 'review') { iconClass = 'fa-star'; iconBgClass = 'bg-yellow-100'; iconTextClass = 'text-yellow-700'; }
                        
                        const title = escapeHtml(notif.title || 'Notification');
                        const message = escapeHtml(notif.message || '');
                        const link = notif.link || 'retailernotifications.php';
                        
                        return `
                            <a href="${link}" class="block p-3 border-b border-gray-100 hover:bg-gray-50 transition ${unreadClass}" data-notification-id="${notif.id}" onclick="markNotificationAsRead(event, ${notif.id})">
                                <div class="flex items-start gap-3">
                                    <div class="${iconBgClass} ${iconTextClass} p-2 rounded-full flex-shrink-0">
                                        <i class="fas ${iconClass} text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-800 text-sm truncate">${title}</p>
                                        <p class="text-xs text-gray-500 mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${message}</p>
                                        <span class="text-xs text-gray-400 block mt-1">${timeAgo}</span>
                                    </div>
                                    ${isUnread ? '<div class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></div>' : ''}
                                </div>
                            </a>
                        `;
                    }).join('');
                } else {
                    notificationPreviewItems.innerHTML = `
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-bell text-4xl mb-2 text-gray-300"></i>
                            <p class="text-sm">No notifications</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading notification preview:', error);
            });
    }
    
    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
        return date.toLocaleDateString();
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function markNotificationAsRead(event, notificationId) {
        fetch('../api/mark-retailer-notification-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload badge count after marking as read
                setTimeout(() => {
                    loadRetailerNotificationBadge();
                }, 100);
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }
    
    loadRetailerNotificationBadge();
    loadRetailerNotificationPreview();
    setInterval(loadRetailerNotificationBadge, 5000);

    // Listen for notification updates from other pages (e.g., retailernotifications.php)
    window.addEventListener('storage', (e) => {
        if (e.key === 'notificationsUpdated') {
            loadRetailerNotificationBadge();
            loadRetailerNotificationPreview();
        }
    });

    // Listen for custom event from same page
    window.addEventListener('notificationsUpdated', () => {
        loadRetailerNotificationBadge();
        loadRetailerNotificationPreview();
    });

    // Select all orders functionality
    document.getElementById('select-all-orders').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Individual checkbox handling
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('order-checkbox')) {
            const allCheckboxes = document.querySelectorAll('.order-checkbox');
            const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            const selectAll = document.getElementById('select-all-orders');
            
            selectAll.checked = allCheckboxes.length === checkedBoxes.length;
            selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < allCheckboxes.length;
            
            // Update bulk actions bar
            updateBulkActionsBar();
        }
    });

    // Update bulk actions bar visibility and count
    function updateBulkActionsBar() {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        const bulkActionsBar = document.getElementById('bulk-actions-bar');
        const selectedCount = document.getElementById('selected-count');
        
        if (checkedBoxes.length > 0) {
            bulkActionsBar.classList.remove('hidden');
            selectedCount.textContent = checkedBoxes.length;
        } else {
            bulkActionsBar.classList.add('hidden');
            selectedCount.textContent = '0';
        }
    }

    // Bulk update status
    function bulkUpdateStatus(newStatus) {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Please select at least one order.');
            return;
        }
        
        const orderIds = Array.from(checkedBoxes).map(cb => cb.dataset.orderId);
        
        if (confirm(`Are you sure you want to update ${orderIds.length} order(s) to "${newStatus}"?`)) {
            console.log('Bulk updating orders:', orderIds);
            console.log('New status:', newStatus);
            
            // TODO: Make API call to bulk update order statuses
            // Example:
            // fetch('../api/bulk-update-order-status.php', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ orderIds, status: newStatus })
            // }).then(response => response.json())
            //   .then(data => {
            //       if (data.success) {
            //           location.reload();
            //       }
            //   });
            
            alert(`${orderIds.length} order(s) will be updated to "${newStatus}".\n\nOrders: ${orderIds.join(', ')}\n\nDatabase integration pending.`);
            
            // Uncomment when API is ready:
            // clearSelection();
            // location.reload();
        }
    }

    // Bulk edit orders
    function bulkEditOrders() {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Please select at least one order to edit.');
            return;
        }
        
        const orderIds = Array.from(checkedBoxes).map(cb => cb.dataset.orderId);
        
        console.log('Bulk editing orders:', orderIds);
        
        // TODO: Open bulk edit modal or redirect to bulk edit page
        alert(`Bulk editing ${orderIds.length} order(s):\n${orderIds.join(', ')}\n\nBulk edit functionality will be implemented.`);
    }

    // Bulk print orders
    function bulkPrintOrders() {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Please select at least one order to print.');
            return;
        }
        
        const orderIds = Array.from(checkedBoxes).map(cb => cb.dataset.orderId);
        
        console.log('Printing orders:', orderIds);
        
        // TODO: Implement print functionality
        alert(`Printing ${orderIds.length} order(s):\n${orderIds.join(', ')}\n\nPrint functionality will be implemented.`);
    }

    // Clear selection
    function clearSelection() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const selectAll = document.getElementById('select-all-orders');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAll.checked = false;
        selectAll.indeterminate = false;
        
        updateBulkActionsBar();
    }

    function filterOrders() {
        const filter = document.getElementById('order-status-filter').value;
        const tbody = document.getElementById('orders-table-body');
        const rows = tbody.getElementsByTagName('tr');
        const noOrdersMsg = document.getElementById('no-orders-message');
        let visibleCount = 0;
        
        for (let row of rows) {
            const statusSpan = row.querySelector('td:nth-child(7) span'); // Status is now 7th column
            if (!statusSpan) continue;
            
            const statusText = statusSpan.textContent.trim();
            
            if (filter === 'All' || statusText === filter) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }
        
        // Show/hide "no orders" message
        if (visibleCount === 0) {
            tbody.style.display = 'none';
            noOrdersMsg.classList.remove('hidden');
        } else {
            tbody.style.display = '';
            noOrdersMsg.classList.add('hidden');
        }
        
        console.log(`Filtered orders by: ${filter}, showing ${visibleCount} orders`);
    }

    // Store current order data for modals
    let currentOrderId = null;
    
    const orderData = <?php echo json_encode($jsOrderData); ?>;

    function viewDetails(orderId) {
        currentOrderId = orderId;
        const order = orderData[orderId];
        
        if (!order) {
            console.error('Order not found:', orderId);
            return;
        }
        
        // Populate modal with order data
        document.getElementById('modal-order-id').textContent = order.id;
        document.getElementById('modal-customer-name').textContent = order.customer;
        document.getElementById('modal-order-date').textContent = order.date;
        
        // Update status badge
        const statusBadge = document.getElementById('modal-order-status');
        statusBadge.textContent = order.status;
        statusBadge.className = 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full';
        
        // Map statuses to colors based on the color guide
        const statusColors = {
            'Pending': { bg: '#FFF9C4', color: '#F57C00' },
            'Confirmed': { bg: '#E3F2FD', color: '#1E88E5' },
            'Processing': { bg: '#E1F5FE', color: '#00BCD4' },
            'Shipped': { bg: '#E0F2F1', color: '#00897B' },
            'Delivered': { bg: '#E8F5E9', color: '#388E3C' },
            'Cancelled': { bg: '#EEEEEE', color: '#757575' }
        };
        
        const colors = statusColors[order.status] || { bg: '#EEEEEE', color: '#757575' };
        statusBadge.style.backgroundColor = colors.bg;
        statusBadge.style.color = colors.color;
        
        // Populate items table
        const itemsTableBody = document.getElementById('modal-order-items');
        itemsTableBody.innerHTML = order.items.map(item => `
            <tr>
                <td class="px-4 py-3 text-sm text-gray-900">${item.name}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${item.quantity}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${item.price}</td>
                <td class="px-4 py-3 text-sm text-gray-900 font-semibold">${item.subtotal}</td>
            </tr>
        `).join('');
        
        // Populate delivery info
        document.getElementById('modal-delivery-address').innerHTML = `<strong>Address:</strong> ${order.address}`;
        document.getElementById('modal-delivery-phone').innerHTML = `<strong>Phone:</strong> ${order.phone}`;
        document.getElementById('modal-delivery-notes').innerHTML = `<strong>Notes:</strong> ${order.notes}`;
        
        // Populate totals
        document.getElementById('modal-subtotal').textContent = order.subtotal;
        document.getElementById('modal-delivery-fee').textContent = order.deliveryFee;
        document.getElementById('modal-total').textContent = order.total;
        
        // Show/hide action button based on status
        const actionBtn = document.getElementById('modal-action-btn');
        if (order.status === 'Completed' || order.status === 'Cancelled') {
            actionBtn.style.display = 'none';
        } else {
            actionBtn.style.display = 'block';
        }
        
        // Show modal
        document.getElementById('orderDetailsModal').classList.remove('hidden');
        
        console.log('Viewing details for order:', orderId);
    }
    
    function closeOrderDetailsModal() {
        document.getElementById('orderDetailsModal').classList.add('hidden');
    }
    
    function openStatusUpdateModal() {
        if (!currentOrderId) return;
        
        const order = orderData[currentOrderId];
        document.getElementById('status-modal-order-id').textContent = order.id;
        document.getElementById('newStatusSelect').value = order.status;
        document.getElementById('statusUpdateNotes').value = '';
        
        document.getElementById('statusUpdateModal').classList.remove('hidden');
    }
    
    function closeStatusUpdateModal() {
        document.getElementById('statusUpdateModal').classList.add('hidden');
    }
    
    function confirmStatusUpdate() {
        if (!currentOrderId) return;
        
        const newStatus = document.getElementById('newStatusSelect').value;
        const notes = document.getElementById('statusUpdateNotes').value;
        
        console.log(`Updating order ${currentOrderId} to ${newStatus}`);
        console.log('Notes:', notes);
        
        // TODO: Make API call to update order status
        // Example:
        // fetch('../api/update-order-status.php', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify({ orderId: currentOrderId, status: newStatus, notes })
        // }).then(response => response.json())
        //   .then(data => {
        //       if (data.success) {
        //           closeStatusUpdateModal();
        //           closeOrderDetailsModal();
        //           location.reload();
        //       }
        //   });
        
        // For now, show success message
        alert(`Order ${currentOrderId} will be updated to ${newStatus}.\nNotes: ${notes || 'None'}\n\nDatabase integration pending.`);
        
        // Uncomment when API is ready:
        // closeStatusUpdateModal();
        // closeOrderDetailsModal();
        // location.reload();
    }

    function updateStatus(orderId, newStatus) {
        currentOrderId = orderId;
        const order = orderData[orderId];
        
        if (!order) {
            console.error('Order not found:', orderId);
            return;
        }
        
        // Open status update modal with pre-selected status
        document.getElementById('status-modal-order-id').textContent = order.id;
        document.getElementById('newStatusSelect').value = newStatus;
        document.getElementById('statusUpdateNotes').value = '';
        
        document.getElementById('statusUpdateModal').classList.remove('hidden');
    }
    
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    // Close mobile menu when clicking a nav link
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });

    // Check for URL parameters on page load
    window.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const filter = urlParams.get('filter');
        
        if (filter) {
            const statusFilter = document.getElementById('order-status-filter');
            if (statusFilter) {
                // Map filter parameter to actual status values
                if (filter === 'new') {
                    statusFilter.value = 'Pending';
                } else if (filter === 'processing') {
                    statusFilter.value = 'Processing';
                } else if (filter === 'completed') {
                    statusFilter.value = 'Delivered';
                }
                // Trigger filter
                filterOrders();
            }
        }
    });

</script>
</body>
</html>
