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
$profilePicture = '../images/default-avatar.svg'; // Default avatar
$userFullName = $_SESSION['full_name'] ?? 'Retailer';
$userEmail = $_SESSION['email'] ?? '';
$shopName = 'My Shop';

try {
    // Fetch user data from database
    $users = $api->select('users', ['id' => $userId]);
    if (!empty($users)) {
        $userData = $users[0];
        $userFullName = $userData['full_name'] ?? $userFullName;
        $userEmail = $userData['email'] ?? $userEmail;
        
        // Check if user has a profile picture
        if (!empty($userData['profile_picture'])) {
            $profilePath = '../' . ltrim($userData['profile_picture'], '/');
            if (file_exists($profilePath)) {
                $profilePicture = $profilePath;
            }
        }
        
        // Try to get retailer shop name
        if ($userData['user_type'] === 'retailer') {
            $retailers = $api->select('retailers', ['user_id' => $userId]);
            if (!empty($retailers)) {
                $shopName = $retailers[0]['shop_name'] ?? $shopName;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}

// Fetch sales data for the last 30 days
$salesData = [];
$salesLabels = [];
$salesValues = [];

try {
    // Get orders from the last 30 days for this retailer
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    
    // Fetch all orders for this retailer
    $orders = $api->select('orders', ['retailer_id' => $userId]);
    
    // Group by date and sum amounts
    $dailySales = [];
    
    foreach ($orders as $order) {
        if (!empty($order['created_at'])) {
            $orderDate = date('Y-m-d', strtotime($order['created_at']));
            
            // Only include orders from last 30 days
            if ($orderDate >= $thirtyDaysAgo) {
                if (!isset($dailySales[$orderDate])) {
                    $dailySales[$orderDate] = 0;
                }
                $dailySales[$orderDate] += floatval($order['total_amount'] ?? 0);
            }
        }
    }
    
    // Create data for last 30 days (fill missing days with 0)
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $label = date('M d', strtotime($date));
        
        $salesLabels[] = $label;
        $salesValues[] = isset($dailySales[$date]) ? $dailySales[$date] : 0;
    }
    
} catch (Exception $e) {
    error_log("Error fetching sales data: " . $e->getMessage());
    // Fill with sample data if error
    for ($i = 29; $i >= 0; $i--) {
        $date = date('M d', strtotime("-$i days"));
        $salesLabels[] = $date;
        $salesValues[] = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshHarvest Seller Dashboard</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Load Inter font -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fbf8;
        }
        /* Ensure main content area has minimum height */
        #content {
            min-height: calc(100vh - 200px);
        }
        /* Footer stays at bottom, visible only when scrolling */
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
    
    <!-- Main Application Container -->
    <div id="app" class="flex flex-1">
        
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="w-64 md:w-64 bg-white shadow-xl flex flex-col p-4 space-y-2 flex-shrink-0">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-2">
                    <i class="fas fa-leaf text-white text-lg"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
            </div>
            <a href="retailer-dashboard2.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
                <i class="fas fa-tachometer-alt text-lg mr-3"></i>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-boxes text-lg mr-3"></i>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
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
                                    <!-- Notifications will be loaded here -->
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

                        <div class="relative inline-block text-left">
                            <button id="profileDropdownBtn" class="flex items-center" title="<?php echo htmlspecialchars($userFullName); ?>">
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
                    </div>
                </div>
            </header>
        
            <!-- Main Content Area -->
            <main id="content" class="p-8 transition-all duration-300 flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Dashboard Overview</h2>
                
                <!-- KPI Cards -->
                <div id="dashboard-kpis" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div onclick="window.location.href='retailerfinance.php'" class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 cursor-pointer hover:scale-105 transform transition-transform">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Total Revenue</p>
                            <i class="fas fa-dollar-sign text-2xl text-green-400"></i>
                        </div>
                        <p id="kpi-revenue" class="text-2xl font-extrabold text-gray-800">₱0.00</p>
                    </div>
                    <div onclick="window.location.href='retailerfulfillment.php?filter=new'" class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 cursor-pointer hover:scale-105 transform transition-transform">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">New Orders</p>
                            <i class="fas fa-clipboard-list text-2xl text-green-400"></i>
                        </div>
                        <p id="kpi-orders" class="text-2xl font-extrabold text-gray-800">0</p>
                    </div>
                    <div onclick="window.location.href='retailerinventory.php?filter=outofstock'" class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 cursor-pointer hover:scale-105 transform transition-transform">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Out of Stock Items</p>
                            <i class="fas fa-exclamation-circle text-2xl text-green-400"></i>
                        </div>
                        <p id="kpi-outofstock" class="text-2xl font-extrabold text-gray-800">0</p>
                    </div>
                    <div onclick="window.location.href='retailerinventory.php'" class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 cursor-pointer hover:scale-105 transform transition-transform">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Total Products</p>
                            <i class="fas fa-star text-2xl text-green-400"></i>
                        </div>
                        <p id="kpi-products" class="text-2xl font-extrabold text-gray-800">0</p>
                    </div>
                </div>
                
                <!-- Sales Trend and Inventory Alerts -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Overall Sales</h3>
                                <div class="flex items-baseline gap-3">
                                    <p class="text-3xl font-bold text-gray-900" id="totalSalesAmount">₱0.00</p>
                                    <span class="flex items-center text-sm font-medium text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                        <span id="salesPercentChange">0%</span>
                                    </span>
                                </div>
                            </div>
                            <div class="relative">
                                <select id="salesPeriod" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                                    <option value="month">This Month</option>
                                    <option value="week">This Week</option>
                                    <option value="30days" selected>Last 30 Days</option>
                                </select>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                    </div>
                    <div id="dashboard-inventory-alerts" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 flex flex-col">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Out of Stock Alerts</h3>
                        <div id="low-stock-list" class="flex-1 overflow-y-auto" style="max-height: 300px;">
                            <!-- Content will be loaded dynamically -->
                        </div>
                        <button onclick="window.location.href='retailerinventory.php?filter=outofstock'" class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-2 mt-4">
                            <i class="fas fa-boxes"></i>
                            Manage Inventory
                        </button>
                    </div>
                </div>

                <!-- Products Sold & Top Products -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                    <!-- Products Sold -->
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Products Sold</h3>
                        <div id="products-sold-list" class="space-y-4">
                            <!-- Content will be loaded dynamically -->
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Top Performing Products</h3>
                        <div id="top-products-list" class="space-y-4">
                            <!-- Content will be loaded dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="mt-8 bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-green-100 p-2 rounded-lg">
                                    <i class="fas fa-stream text-green-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800">Recent Activity Feed</h3>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span id="activity-count" class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">0 activities</span>
                                <button onclick="loadRecentActivities()" class="text-gray-400 hover:text-green-600 transition p-2 rounded-lg hover:bg-green-50" title="Refresh">
                                    <i class="fas fa-sync-alt text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="recent-activity-feed" class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        <!-- Content will be loaded automatically -->
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Status Message Box (Replaces alert()) -->
    <div id="status-message" class="fixed top-5 right-5 z-50 transition-transform duration-500 transform translate-x-full">
        <div id="status-content" class="px-4 py-2 rounded-lg shadow-lg text-sm text-white font-medium flex items-center space-x-2">
            <!-- Message goes here -->
        </div>
    </div>

    <!-- Modal for Adding/Editing Products -->
    <div id="product-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-40 hidden flex items-center justify-center">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-2xl transform scale-95 transition-all">
            <h2 id="modal-title" class="text-2xl font-semibold text-green-700 mb-6">Add New Product</h2>
            <form id="product-form" onsubmit="handleProductSubmit(event)">
                <input type="hidden" id="product-id" name="id">

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" id="name" name="name" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category (e.g., Fruit, Dairy)</label>
                        <input type="text" id="category" name="category" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                        <input type="number" step="0.01" id="price" name="price" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="uom" class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure (e.g., kg, loaf, unit)</label>
                        <input type="text" id="uom" name="uom" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeProductModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-150">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 font-semibold">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Bulk Price Editing -->
    <div id="bulk-price-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-40 hidden flex items-center justify-center">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg transform scale-95 transition-all">
            <h2 class="text-2xl font-semibold text-green-700 mb-6">Bulk Edit Product Prices</h2>
            <form onsubmit="handleBulkPriceSubmit(event)">
                <div class="mb-6">
                    <label for="bulk-type" class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type</label>
                    <select id="bulk-type" name="type" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="percent_increase">Increase by Percentage (%)</option>
                        <option value="percent_decrease">Decrease by Percentage (%)</option>
                        <option value="fixed_increase">Increase by Fixed Amount ($)</option>
                        <option value="fixed_decrease">Decrease by Fixed Amount ($)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="bulk-value" class="block text-sm font-medium text-gray-700 mb-1">Adjustment Value</label>
                    <input type="number" step="0.01" id="bulk-value" name="value" required min="0.01" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" placeholder="e.g., 5.00 for 5% or $5.00">
                </div>

                <div class="mb-6">
                    <label for="bulk-category" class="block text-sm font-medium text-gray-700 mb-1">Apply to Category (Optional)</label>
                    <input type="text" id="bulk-category" name="category" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" placeholder="e.g., Fruit (leave blank for all products)">
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeBulkPriceModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-150">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-150 font-semibold">Apply Bulk Price Change</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- NEW: Modal for Creating Coupons -->
    <div id="coupon-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-40 hidden flex items-center justify-center">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-2xl transform scale-95 transition-all">
            <h2 class="text-2xl font-semibold text-blue-700 mb-6">Create New Coupon/Voucher</h2>
            <form id="coupon-form" onsubmit="handleCouponSubmit(event)">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="coupon-code" class="block text-sm font-medium text-gray-700 mb-1">Coupon Code (e.g., FRESH20)</label>
                        <input type="text" id="coupon-code" name="code" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 uppercase font-mono">
                    </div>
                    <div>
                        <label for="coupon-type" class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                        <select id="coupon-type" name="type" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="percent">Percentage Off (%)</option>
                            <option value="fixed">Fixed Amount Off ($)</option>
                            <option value="shipping">Free Shipping</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="coupon-value" class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                        <input type="number" step="0.01" id="coupon-value" name="value" required min="0" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., 20 or 5.00">
                    </div>
                    <div>
                        <label for="coupon-min-spend" class="block text-sm font-medium text-gray-700 mb-1">Minimum Spend ($)</label>
                        <input type="number" step="0.01" id="coupon-min-spend" name="min_spend" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" value="0.00">
                    </div>
                    <div>
                        <label for="coupon-expiry" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input type="date" id="coupon-expiry" name="expiry" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="coupon-product-filter" class="block text-sm font-medium text-gray-700 mb-1">Applies To (Specific Product/Category, Optional)</label>
                    <input type="text" id="coupon-product-filter" name="filter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., Fruit, Sourdough Loaf">
                    <p class="text-xs text-gray-500 mt-1">Leave blank to apply to all products.</p>
                </div>
                
                <div class="flex justify-end space-x-4 pt-4 border-t">
                    <button type="button" onclick="closeCouponModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-150">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 font-semibold">Create Coupon</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Application Logic -->
    <script type="module">
        let products = [];
        let orders = [];
        let reviews = [];

        // --- Utility Functions ---

        /** Shows a temporary status message */
        window.showMessage = (message, type = 'success') => {
            const statusBox = document.getElementById('status-message');
            const statusContent = document.getElementById('status-content');

            let bgColor = 'bg-green-500';
            if (type === 'error') bgColor = 'bg-red-500';
            if (type === 'info') bgColor = 'bg-blue-500';

            statusContent.className = `px-4 py-2 rounded-lg shadow-lg text-sm text-white font-medium flex items-center space-x-2 ${bgColor}`;
            statusContent.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : type === 'error' ? 'M6 18L18 6M6 6l12 12' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"></path></svg><span>${message}</span>`;
            
            // Show the message
            statusBox.classList.remove('translate-x-full');
            
            // Hide after 3 seconds
            setTimeout(() => {
                statusBox.classList.add('translate-x-full');
            }, 3000);
        };

        /** Signs out the current user */
        window.signOutUser = async () => {
            window.location.href = '../auth/login.php';
        };
        ;

            // 2. Orders Listener
            onSnapshot(collection(db, getCollectionPath('orders')), (snapshot) => {
                orders = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
                // Sort by creation date, newest first
                orders.sort((a, b) => (b.createdAt?.toMillis() || 0) - (a.createdAt?.toMillis() || 0));
                if (document.getElementById('orders-table-body')) {
                    renderOrderList(); // Update if on the orders page
                }
                if (document.getElementById('dashboard-kpis')) {
                    renderDashboard(); // Update if on the dashboard
                }
            }, (error) => {
                console.error("Error fetching orders:", error);
                showMessage("Failed to load orders. Check permissions.", 'error');
            });

            // 3. Mock Reviews Listener (Simplified for seller view)
             // Using mock data for reviews since customer input is complex to simulate here
             reviews = [
                { id: 'r1', productId: 'p1', productName: 'Organic Gala Apples', rating: 5, feedback: 'Absolutely fresh and delicious! Perfect crunch.', date: '2024-10-01', response: '' },
                { id: 'r2', productId: 'p2', productName: 'Artisan Sourdough Loaf', rating: 2, feedback: 'Loaf was stale and crust was too hard. Disappointed.', date: '2024-10-03', response: '' },
                { id: 'r3', productId: 'p1', productName: 'Organic Gala Apples', rating: 4, feedback: 'Great, but a little smaller than expected.', date: '2024-10-05', response: 'Thank you for your feedback! We are working on grading our produce more accurately. We hope to earn 5 stars next time!' }
             ];
             if (document.getElementById('reviews-list')) {
                renderReviewList();
             }
        

        // --- CRUD Operations for Products ---

        window.openProductModal = (product = null) => {
            const modal = document.getElementById('product-modal');
            const form = document.getElementById('product-form');
            const title = document.getElementById('modal-title');

            form.reset();
            
            if (product) {
                title.textContent = `Edit Product: ${product.name}`;
                document.getElementById('product-id').value = product.id;
                document.getElementById('name').value = product.name;
                document.getElementById('category').value = product.category;
                document.getElementById('description').value = product.description;
                document.getElementById('price').value = product.price;
                document.getElementById('stock').value = product.stock;
                document.getElementById('uom').value = product.uom;
            } else {
                title.textContent = 'Add New Product';
                document.getElementById('product-id').value = '';
            }
            
            modal.classList.remove('hidden');
        };

        window.closeProductModal = () => {
            document.getElementById('product-modal').classList.add('hidden');
        };

        window.handleProductSubmit = async (event) => {
            event.preventDefault();
            if (!userId) return showMessage("Cannot save product. Not authenticated.", 'error');

            const form = event.target;
            const formData = new FormData(form);
            const productData = {
                name: formData.get('name'),
                category: formData.get('category'),
                description: formData.get('description'),
                price: parseFloat(formData.get('price')),
                stock: parseInt(formData.get('stock')),
                uom: formData.get('uom'),
                status: 'Active', // Default status
                imageUrl: `https://placehold.co/150x150/065f46/ffffff?text=${formData.get('name').substring(0, 1)}`, // Placeholder
            };
            const productId = formData.get('id');

            try {
                if (productId) {
                    await updateDoc(doc(db, getCollectionPath('products'), productId), productData);
                    showMessage("Product updated successfully!", 'success');
                } else {
                    productData.createdAt = serverTimestamp();
                    await addDoc(collection(db, getCollectionPath('products')), productData);
                    showMessage("New product added successfully!", 'success');
                }
                closeProductModal();
            } catch (error) {
                console.error("Error saving product:", error);
                showMessage("Error saving product. See console.", 'error');
            }
        };

        window.deleteProduct = async (id) => {
            // Using a custom message box instead of confirm()
            if (!confirm("Are you sure you want to delete this product?")) return;
            if (!userId) return showMessage("Cannot delete product. Not authenticated.", 'error');
            
            try {
                await deleteDoc(doc(db, getCollectionPath('products'), id));
                showMessage("Product deleted.", 'success');
            } catch (error) {
                console.error("Error deleting product:", error);
                showMessage("Error deleting product. See console.", 'error');
            }
        };

        // --- Bulk Price Modal Functions ---

        window.openBulkPriceModal = () => {
            document.getElementById('bulk-price-modal').classList.remove('hidden');
        };

        window.closeBulkPriceModal = () => {
            document.getElementById('bulk-price-modal').classList.add('hidden');
        };

        window.handleBulkPriceSubmit = async (event) => {
            event.preventDefault();
            if (!userId) return showMessage("Cannot perform bulk update. Not authenticated.", 'error');

            const form = event.target;
            const formData = new FormData(form);
            const type = formData.get('type');
            const value = parseFloat(formData.get('value'));
            const categoryFilter = formData.get('category').trim().toLowerCase();

            if (isNaN(value) || value <= 0) return showMessage("Invalid adjustment value.", 'error');

            const updates = products
                .filter(p => !categoryFilter || (p.category && p.category.toLowerCase() === categoryFilter))
                .map(p => {
                    let newPrice = p.price;
                    if (type === 'percent_increase') {
                        newPrice = p.price * (1 + value / 100);
                    } else if (type === 'percent_decrease') {
                        newPrice = p.price * (1 - value / 100);
                    } else if (type === 'fixed_increase') {
                        newPrice = p.price + value;
                    } else if (type === 'fixed_decrease') {
                        newPrice = p.price - value;
                    }

                    // Prevent negative prices
                    newPrice = Math.max(0, newPrice);
                    
                    // Note: In a real app, this would use Firestore batch updates for efficiency.
                    return updateDoc(doc(db, getCollectionPath('products'), p.id), { price: parseFloat(newPrice.toFixed(2)), updatedAt: serverTimestamp() });
                });
            
            try {
                // Execute all updates (using Promise.allSettled to handle potential individual failures)
                await Promise.allSettled(updates);
                showMessage(`Bulk price update applied to ${updates.length} products! (Check console for update status)`, 'success');
                closeBulkPriceModal();
            } catch (error) {
                console.error("Bulk Price Update Error:", error);
                showMessage("Failed to apply bulk update. See console.", 'error');
            }
        };

        // --- NEW: Coupon Modal Functions ---
        window.openCouponModal = () => {
            const form = document.getElementById('coupon-form');
            form.reset();
            document.getElementById('coupon-modal').classList.remove('hidden');
        };

        window.closeCouponModal = () => {
            document.getElementById('coupon-modal').classList.add('hidden');
        };

        window.handleCouponSubmit = (event) => {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            const couponData = {
                code: formData.get('code').toUpperCase(),
                type: formData.get('type'),
                value: parseFloat(formData.get('value')),
                min_spend: parseFloat(formData.get('min_spend')),
                expiry: formData.get('expiry'),
                filter: formData.get('filter')
            };

            // Basic validation
            if (!couponData.code || couponData.code.length < 3) {
                return showMessage("Coupon code must be at least 3 characters.", 'error');
            }
            if (isNaN(couponData.value) || couponData.value <= 0 && couponData.type !== 'shipping') {
                 return showMessage("Discount value must be a positive number.", 'error');
            }

            // In a real application, you would save this to a 'coupons' Firestore collection here.
            console.log("New Coupon Created (MOCK SAVE):", couponData);
            
            showMessage(`Coupon ${couponData.code} created successfully!`, 'success');
            closeCouponModal();
        };


        // --- Order Management Functions ---

        window.updateOrderStatus = async (orderId, newStatus) => {
            if (!userId) return showMessage("Cannot update order status. Not authenticated.", 'error');

            try {
                await updateDoc(doc(db, getCollectionPath('orders'), orderId), {
                    status: newStatus,
                    updatedAt: serverTimestamp()
                });
                showMessage(`Order ${orderId.substring(0, 4)}... updated to ${newStatus}`, 'success');
            } catch (error) {
                console.error("Error updating order status:", error);
                showMessage("Error updating order status. See console.", 'error');
            }
        };

        // --- LLM Review Assistant Function ---

        async function getLLMResponse(reviewText) {
            const systemPrompt = `You are a polite, professional, and friendly customer service assistant for a small, local fresh produce seller called 'FreshHarvest'. Your goal is to draft a concise (max 3 sentences) and effective public reply to a customer review. The response should always thank the customer, address the specific feedback (positive or negative) in a constructive way, and maintain a warm tone.`;
            const userQuery = `Draft a public reply for the following customer review: "${reviewText}"`;

            try {
                const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${LLM_MODEL}:generateContent?key=${API_KEY}`;
                
                const payload = {
                    contents: [{ parts: [{ text: userQuery }] }],
                    systemInstruction: { parts: [{ text: systemPrompt }] },
                };

                // Use exponential backoff for retries
                for (let i = 0; i < 3; i++) {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.ok) {
                        const result = await response.json();
                        return result.candidates?.[0]?.content?.parts?.[0]?.text || "Could not generate a response.";
                    } else if (response.status === 429 && i < 2) {
                        // Too many requests - retry with backoff
                        const delay = Math.pow(2, i) * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    } else {
                        throw new Error(`API returned status ${response.status}: ${await response.text()}`);
                    }
                }
                return "Failed to get response after multiple retries.";

            } catch (error) {
                console.error("Gemini API Error:", error);
                return `Error: Failed to connect to assistant. (${error.message})`;
            }
        }

        window.draftReviewResponse = async (reviewId) => {
            const review = reviews.find(r => r.id === reviewId);
            if (!review) return;

            const button = document.getElementById(`draft-btn-${reviewId}`);
            const textarea = document.getElementById(`response-area-${reviewId}`);
            button.disabled = true;
            button.textContent = 'Drafting...';
            
            const responseText = await getLLMResponse(review.feedback);
            
            textarea.value = responseText;
            button.textContent = 'Re-draft Response';
            button.disabled = false;
        };

        window.submitReviewResponse = (reviewId) => {
            const textarea = document.getElementById(`response-area-${reviewId}`);
            const responseText = textarea.value.trim();

            if (!responseText) return showMessage('Response cannot be empty.', 'info');
            
            // In a real app, you would save this to Firestore. Here we update the local mock data.
            const reviewIndex = reviews.findIndex(r => r.id === reviewId);
            if (reviewIndex !== -1) {
                reviews[reviewIndex].response = responseText;
                renderReviewList(); // Re-render the list to show the update
                showMessage('Review response saved and posted.', 'success');
            }
        };

        // --- Rendering Functions ---

        /** Updates the navigation bar to show the active page */
        function updateNav(pageName) {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active-nav-item', 'bg-green-600', 'text-white');
                item.classList.add('text-gray-700', 'hover:bg-green-100');
                if (item.onclick.toString().includes(`'${pageName}'`)) {
                    item.classList.add('active-nav-item', 'bg-green-600', 'text-white');
                    item.classList.remove('text-gray-700', 'hover:bg-green-100');
                }
            });
        }

        window.renderPage = (pageName) => {
            updateNav(pageName);
            const contentDiv = document.getElementById('content');
            contentDiv.innerHTML = ''; // Clear content

            let title = '';
            let htmlContent = '';

            switch (pageName) {
                case 'dashboard':
                    title = 'Dashboard Overview';
                    htmlContent = renderDashboardTemplate();
                    break;
                case 'products':
                    title = 'Product & Inventory Management';
                    htmlContent = renderProductTemplate();
                    break;
                case 'orders':
                    title = 'Order Management & Fulfillment';
                    htmlContent = renderOrderTemplate();
                    break;
                case 'finance':
                    title = 'Payouts & Financial Reporting';
                    htmlContent = renderFinanceTemplate();
                    break;
                case 'coupons':
                    title = 'Vouchers & Coupons Management';
                    htmlContent = renderCouponsTemplate();
                    break;
                case 'reviews':
                    title = 'Customer & Review Management';
                    htmlContent = renderReviewsTemplate();
                    break;
                default:
                    title = 'Welcome';
                    htmlContent = `<h2 class="text-3xl font-bold text-gray-800">Welcome to FreshHarvest Dashboard</h2><p class="mt-4 text-gray-600">Select an option from the sidebar to get started.</p>`;
            }

            contentDiv.innerHTML = `<h2 class="text-3xl font-bold text-gray-800 mb-8">${title}</h2>${htmlContent}`;

            // Execute post-render functions
            if (pageName === 'dashboard') renderDashboard();
            if (pageName === 'products') renderProductList();
            if (pageName === 'orders') renderOrderList();
            if (pageName === 'reviews') renderReviewList();
        };

        // --- Dashboard Rendering ---

        function renderDashboardTemplate() {
            return `
                <div id="dashboard-kpis" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- KPI Cards will be injected here -->
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Sales Trend (Last 30 Days)</h3>
                        <div class="h-64 flex items-center justify-center text-gray-400 border border-dashed rounded-lg">
                            [Sales Trend Chart Placeholder]
                        </div>
                    </div>
                    <div id="dashboard-inventory-alerts" class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Inventory Alerts (Low Stock)</h3>
                        <!-- Alerts will be injected here -->
                    </div>
                </div>

                <div class="mt-8 bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Recent Activity Feed</h3>
                    <ul id="recent-activity-feed" class="space-y-3">
                        ${orders.slice(0, 5).map(o => `
                            <li class="flex items-center space-x-3 text-sm text-gray-600 border-b pb-2">
                                <span class="text-green-500 font-medium">New Order</span>
                                <span>#${o.id.substring(0, 6)}</span>
                                <span class="text-gray-500">•</span>
                                <span>$${(o.total || 0).toFixed(2)}</span>
                                <span class="text-gray-500">•</span>
                                <span>${o.customerName}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }

        function renderDashboard() {
            // Calculate KPIs
            const totalSales = orders.reduce((sum, o) => sum + (o.total || 0), 0);
            const newOrders = orders.filter(o => o.status === 'New').length;
            const lowStockProducts = products.filter(p => p.stock <= 10);
            const topProducts = products.slice(0, 3).map(p => p.name).join(', ');

            const kpiData = [
                { title: 'Total Revenue (All Time)', value: `$${totalSales.toFixed(2)}`, icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M9 14h6m-5 4h4' },
                { title: 'New Orders', value: newOrders, icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2' },
                { title: 'Low Inventory Items', value: lowStockProducts.length, icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
                { title: 'Top 3 Sellers', value: topProducts || 'N/A', icon: 'M19 11H5m14 0V9m0 2v2m-4-2h1m-1 0h-2m2 0v2m-2-2h-1m-1 0H5m-2 4h18' },
            ];

            const kpisHtml = kpiData.map(kpi => `
                <div class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-gray-500">${kpi.title}</p>
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${kpi.icon}"></path></svg>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-800 truncate">${kpi.value}</p>
                </div>
            `).join('');

            document.getElementById('dashboard-kpis').innerHTML = kpisHtml;

            // Inventory Alerts
            const alertsHtml = lowStockProducts.length > 0
                ? lowStockProducts.map(p => `
                    <div class="p-3 bg-red-50 border-l-4 border-red-500 rounded-lg mb-2 flex justify-between items-center">
                        <p class="text-sm text-red-700 font-medium">${p.name}</p>
                        <span class="text-xs text-red-600 font-bold">${p.stock} ${p.uom} left</span>
                    </div>
                `).join('')
                : '<p class="text-gray-500 text-sm">All inventory levels are looking healthy!</p>';
            
            document.getElementById('dashboard-inventory-alerts').innerHTML = `<h3 class="text-xl font-semibold text-gray-700 mb-4">Inventory Alerts (Low Stock)</h3>${alertsHtml}`;
        }


        // --- Products Rendering ---

        function renderProductTemplate() {
            return `
                <div class="flex justify-between items-center mb-6">
                    <button onclick="openProductModal()" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 transition duration-150 font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add New Product
                    </button>
                    <!-- Button to open the bulk price modal -->
                    <button onclick="openBulkPriceModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 transition duration-150 text-sm">
                        Bulk Edit Prices
                    </button>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price / UOM</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-list" class="bg-white divide-y divide-gray-200">
                            <!-- Product rows will be injected here -->
                        </tbody>
                    </table>
                    <div id="no-products-message" class="p-6 text-center text-gray-500 hidden">No products listed yet. Click "Add New Product" to start selling!</div>
                </div>
            `;
        }

        function renderProductList() {
            const listBody = document.getElementById('products-list');
            const noProductsMsg = document.getElementById('no-products-message');
            if (!listBody) return;

            if (products.length === 0) {
                listBody.innerHTML = '';
                noProductsMsg.classList.remove('hidden');
                return;
            } else {
                noProductsMsg.classList.add('hidden');
            }

            listBody.innerHTML = products.map(p => {
                const isLowStock = p.stock <= 10;
                const stockClasses = isLowStock ? 'text-red-600 font-semibold' : 'text-gray-700';
                const statusClasses = p.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                
                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-10 w-10 rounded-lg object-cover mr-4" src="${p.imageUrl}" alt="${p.name}" onerror="this.onerror=null;this.src='https://placehold.co/40x40/ccc/333?text=${p.name ? p.name.substring(0,1) : 'P'}';" />
                                <span class="text-sm font-medium text-gray-900">${p.name}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${p.category}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${p.price.toFixed(2)} / ${p.uom}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm ${stockClasses}">${p.stock}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClasses}">${p.status}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button onclick='openProductModal(${JSON.stringify(p)})' class="text-green-600 hover:text-green-900">Edit</button>
                            <button onclick="deleteProduct('${p.id}')" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // --- Orders Rendering ---

        function renderOrderTemplate() {
            return `
                <div class="flex justify-between items-center mb-6">
                    <p class="text-gray-500">Filter by Status: 
                        <select id="order-status-filter" onchange="renderOrderList()" class="p-2 border border-gray-300 rounded-lg text-sm">
                            <option value="All">All</option>
                            <option value="New">New</option>
                            <option value="Processing">Processing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body" class="bg-white divide-y divide-gray-200">
                            <!-- Order rows will be injected here -->
                        </tbody>
                    </table>
                    <div id="no-orders-message" class="p-6 text-center text-gray-500 hidden">No orders found.</div>
                </div>
            `;
        }

        function renderOrderList() {
            const listBody = document.getElementById('orders-table-body');
            const noOrdersMsg = document.getElementById('no-orders-message');
            const filter = document.getElementById('order-status-filter')?.value || 'All';
            if (!listBody) return;

            // Mock order data if none exists
            if (orders.length === 0 && firebaseConfig) {
                // Generate a few mock orders on first load if collection is empty
                const mockOrders = [
                    { id: 'o1', customerName: 'Alice Johnson', total: 15.98, status: 'New', createdAt: serverTimestamp(), deliveryAddress: '123 Main St, Anytown', items: [{ name: 'Organic Apples', quantity: 2, price: 5.99 }, { name: 'Sourdough Loaf', quantity: 1, price: 6.50 }] },
                    { id: 'o2', customerName: 'Bob Williams', total: 25.00, status: 'Processing', createdAt: serverTimestamp(), deliveryAddress: '456 Oak Ave, Cityville', items: [{ name: 'Local Sweet Potatoes', quantity: 5, price: 5.00 }] },
                    { id: 'o3', customerName: 'Charlie Brown', total: 10.50, status: 'Completed', createdAt: serverTimestamp(), deliveryAddress: '789 Pine Ln, Villagetown', items: [{ name: 'Almond Milk', quantity: 2, price: 5.25 }] },
                ];
                
                // Only write mock data if the list is truly empty and we are using Firebase
                if (orders.length === 0) {
                     mockOrders.forEach(o => {
                        addDoc(collection(db, getCollectionPath('orders')), o);
                    });
                }
            }


            const filteredOrders = filter === 'All' ? orders : orders.filter(o => o.status === filter);

            if (filteredOrders.length === 0) {
                listBody.innerHTML = '';
                noOrdersMsg.classList.remove('hidden');
                return;
            } else {
                noOrdersMsg.classList.add('hidden');
            }

            listBody.innerHTML = filteredOrders.map(o => {
                let statusClasses;
                switch (o.status) {
                    case 'New': statusClasses = 'bg-blue-100 text-blue-800'; break;
                    case 'Processing': statusClasses = 'bg-yellow-100 text-yellow-800'; break;
                    case 'Completed': statusClasses = 'bg-green-100 text-green-800'; break;
                    case 'Cancelled': statusClasses = 'bg-red-100 text-red-800'; break;
                    default: statusClasses = 'bg-gray-100 text-gray-800';
                }
                const dateString = o.createdAt ? new Date(o.createdAt.toMillis()).toLocaleDateString() : 'N/A';
                
                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${o.id.substring(0, 8)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${o.customerName}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${dateString}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">$${o.total.toFixed(2)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClasses}">${o.status}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            ${o.status === 'New' ? `<button onclick="updateOrderStatus('${o.id}', 'Processing')" class="text-yellow-600 hover:text-yellow-900">Process</button>` : ''}
                            ${o.status === 'Processing' ? `<button onclick="updateOrderStatus('${o.id}', 'Completed')" class="text-green-600 hover:text-green-900">Complete</button>` : ''}
                            <button onclick="console.log('Viewing details for:', '${o.id}'); showMessage('Showing details for order ${o.id.substring(0, 4)}...', 'info')" class="text-blue-600 hover:text-blue-900">Details</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // --- Finance Rendering ---

        function renderFinanceTemplate() {
            return `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Next Payout Schedule</h3>
                        <p class="text-3xl font-bold text-gray-800">$1,540.20</p>
                        <p class="text-gray-500 mt-2">Scheduled for: Wednesday, Oct 18, 2024</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Reports & Exports</h3>
                        <div class="space-y-3">
                            <button class="w-full text-left p-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-700 font-medium transition duration-150">
                                Download Total Sales Report (CSV)
                            </button>
                            <button class="w-full text-left p-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-700 font-medium transition duration-150">
                                Download Tax & Commission Report (CSV)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                    <h3 class="px-6 py-4 text-xl font-semibold text-gray-700 border-b">Transaction History</h3>
                    <div class="p-6 text-gray-500">
                        <ul class="space-y-3">
                            <li class="flex justify-between items-center border-b pb-2">
                                <span class="text-sm font-medium">Sale #8291 (Apples, Bread)</span>
                                <span class="text-sm text-green-600 font-semibold">+ $21.50</span>
                            </li>
                            <li class="flex justify-between items-center border-b pb-2">
                                <span class="text-sm font-medium">Payout ID P001-2024</span>
                                <span class="text-sm text-red-600 font-semibold">- $890.00</span>
                            </li>
                            <li class="flex justify-between items-center border-b pb-2">
                                <span class="text-sm font-medium">Sale #8290 (Potatoes)</span>
                                <span class="text-sm text-green-600 font-semibold">+ $12.00</span>
                            </li>
                        </ul>
                    </div>
                </div>
            `;
        }

        // --- Coupons Rendering (New Advanced Feature) ---

        function renderCouponsTemplate() {
            return `
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Active Vouchers & Coupons</h3>
                        <div class="p-4 bg-blue-50 border-l-4 border-blue-400 rounded-lg space-y-3">
                            <div class="flex justify-between items-center border-b border-blue-100 pb-3">
                                <div>
                                    <p class="font-bold text-blue-800">FRESH20</p>
                                    <p class="text-sm text-blue-600">20% off all Fresh Produce (Min $30 spend)</p>
                                </div>
                                <span class="text-xs text-blue-500 font-medium bg-blue-100 px-2 py-1 rounded">Expires: 2024-11-30</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-blue-100 pb-3">
                                <div>
                                    <p class="font-bold text-blue-800">LOCALSHIP</p>
                                    <p class="text-sm text-blue-600">Free Shipping on orders over $50</p>
                                </div>
                                <span class="text-xs text-blue-500 font-medium bg-blue-100 px-2 py-1 rounded">Always Active</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-blue-800">NEWBIE10</p>
                                    <p class="text-sm text-blue-600">Flat $10 off first order</p>
                                </div>
                                <span class="text-xs text-blue-500 font-medium bg-blue-100 px-2 py-1 rounded">Expires: 2025-01-01</span>
                            </div>
                        </div>
                        <!-- UPDATED: Button now opens the Create Coupon modal -->
                        <button onclick="openCouponModal()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 font-medium shadow-md">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Create New Coupon
                        </button>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Coupon Performance</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">FRESH20 Usage:</p>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: 75%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">75% redemption rate (120 uses)</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Discount Value Given (Last 30 Days):</p>
                                <p class="text-2xl font-bold text-red-600 mt-1">$4,120.50</p>
                            </div>
                            <div class="border-t pt-4">
                                <p class="text-sm text-gray-600">Avg. Basket Size with Coupon:</p>
                                <p class="text-xl font-bold text-green-700 mt-1">$45.80</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // --- Reviews Rendering (with LLM Integration) ---

        function renderReviewsTemplate() {
            return `
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Customer Ratings Summary</h3>
                        <div class="flex items-center space-x-6">
                            <div class="text-5xl font-extrabold text-green-600">4.7</div>
                            <div>
                                <div class="text-yellow-400 text-2xl">★★★★☆</div>
                                <p class="text-sm text-gray-500">Based on 154 ratings</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">All Customer Reviews</h3>
                        <div id="reviews-list" class="divide-y divide-gray-100">
                            <!-- Reviews will be injected here -->
                        </div>
                        <div id="no-reviews-message" class="p-6 text-center text-gray-500 hidden">No reviews available.</div>
                    </div>
                </div>
            `;
        }

        function renderReviewList() {
            const listDiv = document.getElementById('reviews-list');
            const noReviewsMsg = document.getElementById('no-reviews-message');
            if (!listDiv) return;

            if (reviews.length === 0) {
                listDiv.innerHTML = '';
                noReviewsMsg.classList.remove('hidden');
                return;
            } else {
                noReviewsMsg.classList.add('hidden');
            }

            listDiv.innerHTML = reviews.map(r => `
                <div class="py-5">
                    <div class="flex items-center mb-2 justify-between">
                        <div class="flex items-center">
                            <span class="text-lg font-bold text-gray-800 mr-2">${r.productName}</span>
                            <span class="text-yellow-400">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</span>
                        </div>
                        <span class="text-xs text-gray-400">${r.date}</span>
                    </div>
                    <p class="text-gray-700 italic mb-3 p-3 bg-gray-50 rounded-lg">"${r.feedback}"</p>
                    
                    ${r.response ? 
                        `<div class="mt-2 p-3 bg-green-50 border-l-4 border-green-500 rounded-lg">
                            <p class="font-semibold text-green-800 mb-1">Your Response:</p>
                            <p class="text-sm text-green-700">${r.response}</p>
                        </div>` : 
                        `
                        <div class="mt-4 border border-gray-200 rounded-lg p-3">
                            <label for="response-area-${r.id}" class="block text-sm font-medium text-gray-700 mb-2">Draft a Public Reply (Assisted by AI)</label>
                            <textarea id="response-area-${r.id}" rows="3" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-sm" placeholder="Type your response here or use the assistant..."></textarea>
                            <div class="flex justify-end space-x-2 mt-2">
                                <button id="draft-btn-${r.id}" onclick="draftReviewResponse('${r.id}')" class="px-3 py-1 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-150 text-sm font-medium">
                                    Draft Response with AI
                                </button>
                                <button onclick="submitReviewResponse('${r.id}')" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 text-sm font-medium">
                                    Post Reply
                                </button>
                            </div>
                        </div>
                        `
                    }
                </div>
            `).join('');
        }

        // --- Application Start ---
        window.onload = () => {
            // Initialize with mock data
            products = [
                { id: 'p1', name: 'Organic Gala Apples', category: 'Fruit', price: 2.99, stock: 45, uom: 'lb', status: 'Active', imageUrl: 'https://placehold.co/40x40/991b1b/ffffff?text=A' },
                { id: 'p2', name: 'Artisan Sourdough Loaf', category: 'Bread', price: 6.50, stock: 8, uom: 'loaf', status: 'Active', imageUrl: 'https://placehold.co/40x40/654321/ffffff?text=B' },
                { id: 'p3', name: 'Local Sweet Potatoes', category: 'Vegetable', price: 1.49, stock: 150, uom: 'kg', status: 'Active', imageUrl: 'https://placehold.co/40x40/f97316/ffffff?text=P' },
                { id: 'p4', name: 'Almond Milk (Unsweetened)', category: 'Dairy/Alt', price: 4.00, stock: 3, uom: 'unit', status: 'Active', imageUrl: 'https://placehold.co/40x40/047857/ffffff?text=M' },
            ];
            orders = [
                { id: 'o1', customerName: 'Alice Johnson', total: 15.98, status: 'New', createdAt: { toMillis: () => Date.now() } },
                { id: 'o2', customerName: 'Bob Williams', total: 25.00, status: 'Processing', createdAt: { toMillis: () => Date.now() - 1000000 } },
                { id: 'o3', customerName: 'Charlie Brown', total: 10.50, status: 'Completed', createdAt: { toMillis: () => Date.now() - 5000000 } },
            ];
            renderPage('dashboard');
        };

    </script>

      <!-- Dropdown JS -->
      <script>
        // Initialize Sales Trend Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesTrendChart');
            if (ctx) {
                const salesData = <?php echo json_encode($salesValues); ?>;
                const totalSales = salesData.reduce((sum, val) => sum + val, 0);
                
                // Calculate percentage change (mock calculation - compare first half vs second half)
                const firstHalf = salesData.slice(0, 15).reduce((sum, val) => sum + val, 0) / 15;
                const secondHalf = salesData.slice(15).reduce((sum, val) => sum + val, 0) / 15;
                const percentChange = firstHalf > 0 ? (((secondHalf - firstHalf) / firstHalf) * 100).toFixed(2) : 0;
                
                // Update total sales and percentage
                document.getElementById('totalSalesAmount').textContent = '₱' + totalSales.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('salesPercentChange').textContent = percentChange + '%';
                
                const salesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($salesLabels); ?>,
                        datasets: [{
                            label: 'Daily Sales',
                            data: salesData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.05)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverBackgroundColor: 'rgb(59, 130, 246)',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    family: 'Inter',
                                    size: 12,
                                    weight: '600'
                                },
                                bodyFont: {
                                    family: 'Inter',
                                    size: 13,
                                    weight: '500'
                                },
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return '₱' + context.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: {
                                    display: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + (value / 1000).toFixed(0) + 'K';
                                    },
                                    font: {
                                        family: 'Inter',
                                        size: 11
                                    },
                                    color: '#9CA3AF',
                                    padding: 8
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.06)',
                                    drawBorder: false
                                }
                            },
                            x: {
                                border: {
                                    display: false
                                },
                                ticks: {
                                    maxRotation: 0,
                                    minRotation: 0,
                                    font: {
                                        family: 'Inter',
                                        size: 10
                                    },
                                    color: '#9CA3AF',
                                    padding: 8,
                                    autoSkip: true,
                                    maxTicksLimit: 13
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                });
            }
        });

        // Profile and Notification dropdown hover handlers
        let profileDropdownTimeout = null;
        let notificationPreviewTimeout = null;
        const HOVER_DELAY = 200;
        
        // Profile dropdown hover functionality
        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdown');
        
        if (profileBtn && profileMenu) {
            profileBtn.parentElement.addEventListener('mouseenter', function() {
                clearTimeout(profileDropdownTimeout);
                profileMenu.classList.remove('hidden');
            });
            
            profileBtn.parentElement.addEventListener('mouseleave', function() {
                profileDropdownTimeout = setTimeout(function() {
                    profileMenu.classList.add('hidden');
                }, HOVER_DELAY);
            });
            
            profileMenu.addEventListener('mouseenter', function() {
                clearTimeout(profileDropdownTimeout);
            });
            
            profileMenu.addEventListener('mouseleave', function() {
                profileDropdownTimeout = setTimeout(function() {
                    profileMenu.classList.add('hidden');
                }, HOVER_DELAY);
            });
            
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function() {
                if (!profileMenu.classList.contains('hidden')) {
                    profileMenu.classList.add('hidden');
                }
            });
        }

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
            
            notificationIcon.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                notificationPreview.classList.toggle('hidden');
                if (!notificationPreview.classList.contains('hidden')) {
                    loadRetailerNotificationPreview();
                }
            });
        }

        // Real-time profile picture update - Check for changes every 5 seconds
        let lastProfilePicture = '<?php echo htmlspecialchars($profilePicture); ?>';
        
        async function checkProfileUpdates() {
            try {
                const response = await fetch('../api/get-profile.php');
                const result = await response.json();
                
                if (result.success && result.data) {
                    const profilePicElement = document.getElementById('headerProfilePic');
                    
                    // Update profile picture if changed
                    if (result.data.profile_picture && result.data.profile_picture !== lastProfilePicture) {
                        // Add cache buster to force reload
                        profilePicElement.src = result.data.profile_picture + '?t=' + new Date().getTime();
                        lastProfilePicture = result.data.profile_picture;
                        console.log('Profile picture updated in real-time');
                    }
                    
                    // Update title attribute with user's name
                    if (result.data.full_name) {
                        profilePicElement.parentElement.setAttribute('title', result.data.full_name);
                    }
                }
            } catch (error) {
                console.error('Error checking profile updates:', error);
            }
        }
        
        // Check for updates every 5 seconds
        setInterval(checkProfileUpdates, 5000);
        
        // Also check when user returns to the page
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                checkProfileUpdates();
            }
        });
        
        // Check when page loads
        window.addEventListener('load', () => {
            setTimeout(checkProfileUpdates, 1000);
        });
        
        // Mobile menu toggle function
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

        // Recent Activity Feed functionality
        function loadRecentActivities() {
            const activityFeed = document.getElementById('recent-activity-feed');
            const activityCount = document.getElementById('activity-count');
            
            if (!activityFeed) return;
            
            fetch('../api/get-recent-activities.php?limit=15')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.activities && data.activities.length > 0) {
                        // Update activity count
                        if (activityCount) {
                            activityCount.textContent = `${data.count} ${data.count === 1 ? 'activity' : 'activities'}`;
                        }
                        
                        // Render activities
                        activityFeed.innerHTML = data.activities.map(activity => {
                            return renderActivity(activity);
                        }).join('');
                    } else {
                        activityFeed.innerHTML = `
                            <div class="p-12 text-center text-gray-400">
                                <div class="inline-block p-4 bg-gray-100 rounded-full mb-4">
                                    <i class="fas fa-inbox text-4xl text-gray-300"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-600">No recent activities</p>
                                <p class="text-xs text-gray-400 mt-1">Your shop activity will appear here</p>
                            </div>
                        `;
                        if (activityCount) {
                            activityCount.textContent = '0 activities';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading activities:', error);
                    activityFeed.innerHTML = `
                        <div class="p-8 text-center text-red-400">
                            <i class="fas fa-exclamation-circle text-3xl mb-3"></i>
                            <p class="text-sm">Failed to load activities</p>
                            <button onclick="loadRecentActivities()" class="mt-3 text-xs text-green-600 hover:text-green-700 font-medium">
                                Try Again
                            </button>
                        </div>
                    `;
                });
        }
        
        function renderActivity(activity) {
            const colorClasses = {
                'green': { bg: 'bg-green-50', text: 'text-green-700', icon: 'text-green-600', border: 'border-green-200' },
                'blue': { bg: 'bg-blue-50', text: 'text-blue-700', icon: 'text-blue-600', border: 'border-blue-200' },
                'yellow': { bg: 'bg-yellow-50', text: 'text-yellow-700', icon: 'text-yellow-600', border: 'border-yellow-200' },
                'red': { bg: 'bg-red-50', text: 'text-red-700', icon: 'text-red-600', border: 'border-red-200' },
                'gray': { bg: 'bg-gray-50', text: 'text-gray-700', icon: 'text-gray-600', border: 'border-gray-200' }
            };
            
            const colors = colorClasses[activity.color] || colorClasses['gray'];
            const timeAgo = getTimeAgo(new Date(activity.timestamp));
            
            let content = '';
            
            if (activity.type === 'order') {
                content = `
                    <div class="flex items-start space-x-3 p-4 hover:bg-gray-50 transition group">
                        <div class="${colors.bg} ${colors.icon} p-2.5 rounded-lg flex-shrink-0 group-hover:scale-110 transition">
                            <i class="fas ${activity.icon}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="${colors.text} font-semibold text-sm">${escapeHtml(activity.activity_type)}</span>
                                <span class="text-xs text-gray-400">${timeAgo}</span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Order <span class="font-mono font-medium text-gray-800">#${escapeHtml(activity.order_number)}</span>
                                <span class="text-gray-400 mx-1">•</span>
                                <span class="font-semibold text-gray-800">₱${activity.amount.toFixed(2)}</span>
                                <span class="text-gray-400 mx-1">•</span>
                                ${escapeHtml(activity.customer_name)}
                            </p>
                        </div>
                    </div>
                `;
            } else if (activity.type === 'stock') {
                const stockText = activity.stock > 0 
                    ? `${activity.stock} ${activity.unit} remaining` 
                    : 'Out of stock';
                content = `
                    <div class="flex items-start space-x-3 p-4 hover:bg-gray-50 transition group">
                        <div class="${colors.bg} ${colors.icon} p-2.5 rounded-lg flex-shrink-0 group-hover:scale-110 transition">
                            <i class="fas ${activity.icon}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="${colors.text} font-semibold text-sm">${escapeHtml(activity.activity_type)}</span>
                                <span class="text-xs text-gray-400">${timeAgo}</span>
                            </div>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium text-gray-800">${escapeHtml(activity.product_name)}</span>
                                <span class="text-gray-400 mx-1">•</span>
                                <span class="${colors.text} font-medium">${stockText}</span>
                            </p>
                        </div>
                    </div>
                `;
            } else if (activity.type === 'product') {
                content = `
                    <div class="flex items-start space-x-3 p-4 hover:bg-gray-50 transition group">
                        <div class="${colors.bg} ${colors.icon} p-2.5 rounded-lg flex-shrink-0 group-hover:scale-110 transition">
                            <i class="fas ${activity.icon}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="${colors.text} font-semibold text-sm">${escapeHtml(activity.activity_type)}</span>
                                <span class="text-xs text-gray-400">${timeAgo}</span>
                            </div>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium text-gray-800">${escapeHtml(activity.product_name)}</span>
                                ${activity.activity_type === 'Product Added' ? ' added to inventory' : ' was updated'}
                            </p>
                        </div>
                    </div>
                `;
            } else if (activity.type === 'review') {
                const stars = '⭐'.repeat(activity.rating);
                content = `
                    <div class="flex items-start space-x-3 p-4 hover:bg-gray-50 transition group">
                        <div class="${colors.bg} ${colors.icon} p-2.5 rounded-lg flex-shrink-0 group-hover:scale-110 transition">
                            <i class="fas ${activity.icon}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="${colors.text} font-semibold text-sm">${escapeHtml(activity.activity_type)}</span>
                                <span class="text-xs text-gray-400">${timeAgo}</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">
                                ${stars} by <span class="font-medium text-gray-800">${escapeHtml(activity.customer_name)}</span>
                            </p>
                            ${activity.comment ? `<p class="text-xs text-gray-500 italic">"${escapeHtml(activity.comment.substring(0, 80))}${activity.comment.length > 80 ? '...' : ''}"</p>` : ''}
                        </div>
                    </div>
                `;
            } else if (activity.type === 'message') {
                content = `
                    <div class="flex items-start space-x-3 p-4 hover:bg-gray-50 transition group">
                        <div class="${colors.bg} ${colors.icon} p-2.5 rounded-lg flex-shrink-0 group-hover:scale-110 transition">
                            <i class="fas ${activity.icon}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="${colors.text} font-semibold text-sm">${escapeHtml(activity.activity_type)}</span>
                                <span class="text-xs text-gray-400">${timeAgo}</span>
                            </div>
                            <p class="text-sm text-gray-600 italic">
                                "${escapeHtml(activity.message_preview)}${activity.message_preview.length >= 50 ? '...' : ''}"
                            </p>
                        </div>
                    </div>
                `;
            }
            
            return content;
        }

        // Load activities on page load
        loadRecentActivities();
        
        // Reload when page becomes visible (user returns to tab)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                loadRecentActivities();
            }
        });

        // Notification functionality
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
        
        // Load notifications immediately on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadRetailerNotificationBadge();
            loadRetailerNotificationPreview();
        });
        // Also call immediately in case DOM is already loaded
        if (document.readyState === 'loading') {
            // DOM is still loading, wait for DOMContentLoaded
        } else {
            // DOM is already loaded, execute immediately
            loadRetailerNotificationBadge();
            loadRetailerNotificationPreview();
        }
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
        
        // Load recent activities on page load
        loadRecentActivities();
        
        // Load dashboard products data
        loadDashboardData();
        
        // Function to load all dashboard data
        function loadDashboardData() {
            fetch('../api/get-retailer-dashboard-stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update KPI cards
                        updateDashboardKPIs(data.dashboardStats);
                        
                        // Update inventory alerts
                        renderOutOfStockAlerts(data.outOfStockProducts);
                        
                        // Update product sections
                        if (data.topProducts) {
                            renderProductsSold(data.topProducts.slice(0, 4));
                            renderTopPerformingProducts(data.topProducts.slice(0, 4));
                        }
                    } else {
                        console.error('Failed to load dashboard data:', data.message);
                        showEmptyState();
                    }
                })
                .catch(error => {
                    console.error('Error loading dashboard data:', error);
                    showEmptyState();
                });
        }
        
        function updateDashboardKPIs(stats) {
            // Update Total Revenue
            const revenueEl = document.getElementById('kpi-revenue');
            if (revenueEl) {
                revenueEl.textContent = '₱' + (stats.totalRevenue || 0).toFixed(2);
            }
            
            // Update New Orders
            const ordersEl = document.getElementById('kpi-orders');
            if (ordersEl) {
                ordersEl.textContent = stats.newOrders || 0;
            }
            
            // Update Out of Stock Count
            const outOfStockEl = document.getElementById('kpi-outofstock');
            if (outOfStockEl) {
                outOfStockEl.textContent = stats.outOfStockCount || 0;
            }
            
            // Update Total Products
            const productsEl = document.getElementById('kpi-products');
            if (productsEl) {
                productsEl.textContent = stats.activeProducts || 0;
            }
        }
        
        function renderOutOfStockAlerts(products) {
            const container = document.getElementById('low-stock-list');
            if (!container) return;
            
            if (products.length === 0) {
                container.innerHTML = `
                    <div class=\"text-center py-6 text-gray-400\">
                        <i class=\"fas fa-check-circle text-4xl mb-2 text-green-400\"></i>
                        <p class=\"text-sm\">All products in stock!</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = products.slice(0, 5).map(product => {
                return `
                    <div class=\"p-3 bg-red-50 rounded-lg mb-2\">
                        <p class=\"text-sm text-red-700 font-medium\">${escapeHtml(product.name)}</p>
                        <span class=\"text-xs text-red-600 font-bold\">Out of Stock</span>
                    </div>
                `;
            }).join('') + (products.length > 5 ? `
                <p class=\"text-xs text-gray-500 text-center mt-2\">+${products.length - 5} more items</p>
            ` : '');
        }
        
        function renderProductsSold(products) {
            const container = document.getElementById('products-sold-list');
            if (!container) return;
            
            if (products.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-box-open text-4xl mb-2"></i>
                        <p class="text-sm">No products sold yet</p>
                    </div>
                `;
                return;
            }
            
            const colors = ['green', 'orange', 'yellow', 'purple', 'blue', 'red'];
            const icons = ['fa-apple-alt', 'fa-carrot', 'fa-lemon', 'fa-seedling', 'fa-leaf', 'fa-pepper-hot'];
            
            container.innerHTML = products.map((product, index) => {
                const color = colors[index % colors.length];
                const icon = icons[index % icons.length];
                
                return `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-${color}-100 rounded-lg flex items-center justify-center">
                                <i class="fas ${icon} text-${color}-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">${escapeHtml(product.product_name)}</p>
                                <p class="text-xs text-gray-500">${product.quantity} units sold</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800">₱${product.revenue.toFixed(2)}</p>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function renderTopPerformingProducts(products) {
            const container = document.getElementById('top-products-list');
            if (!container) return;
            
            if (products.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-chart-line text-4xl mb-2"></i>
                        <p class="text-sm">No sales data available</p>
                    </div>
                `;
                return;
            }
            
            const badgeColors = [
                'bg-yellow-400 text-white',
                'bg-gray-400 text-white',
                'bg-orange-400 text-white',
                'bg-gray-300 text-gray-700'
            ];
            
            container.innerHTML = products.map((product, index) => {
                const badgeClass = badgeColors[index] || 'bg-gray-200 text-gray-600';
                const percentage = product.percentage || 0;
                
                return `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 flex-1">
                            <div class="${badgeClass} font-bold w-8 h-8 rounded-full flex items-center justify-center text-sm">${index + 1}</div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">${escapeHtml(product.product_name)}</p>
                                <div class="flex items-center mt-1">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600">${percentage}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-lg font-bold text-green-600">₱${product.revenue.toFixed(2)}</p>
                            <p class="text-xs text-gray-500">${product.quantity} sold</p>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function showEmptyState() {
            const productsSoldContainer = document.getElementById('products-sold-list');
            const topProductsContainer = document.getElementById('top-products-list');
            
            const emptyMessage = `
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-box-open text-4xl mb-2"></i>
                    <p class="text-sm">No sales data available</p>
                </div>
            `;
            
            if (productsSoldContainer) productsSoldContainer.innerHTML = emptyMessage;
            if (topProductsContainer) topProductsContainer.innerHTML = emptyMessage;
        }
      </script>
</body>
</html>