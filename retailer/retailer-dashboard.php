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
    </style>
</head>
<body>

<div class="flex flex-col min-h-screen">
    <!-- Main Application Container -->
    <div id="app" class="flex flex-1">
        
        <!-- Sidebar Navigation -->
        <nav class="w-64 bg-white shadow-xl flex flex-col p-4 space-y-2 flex-shrink-0">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-2">
                    <i class="fas fa-leaf text-white text-lg"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
            </div>
            <a href="retailer-dashboard.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0V9m0 2v2m-4-2h1m-1 0h-2m2 0v2m-2-2h-1m-1 0H5m-2 4h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5h6"></path></svg>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M9 14h6m-5 4h4m-4-8h4m-5-8h6a2 2 0 012 2v10a2 2 0 01-2 2h-6a2 2 0 01-2-2V6a2 2 0 012-2z"></path></svg>
                Financial Reports
            </a>
            <!-- Vouchers & Coupons -->
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l4-4-4-4m0 16l4-4-4-4m-1-5a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Vouchers & Coupons
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.193a2.003 2.003 0 013.902 0l1.018 2.062 2.277.33a2.003 2.003 0 011.11 3.407l-1.652 1.61.39 2.269a2.003 2.003 0 01-2.906 2.108L12 15.698l-2.035 1.071a2.003 2.003 0 01-2.906-2.108l.39-2.269-1.652-1.61a2.003 2.003 0 011.11-3.407l2.277-.33 1.018-2.062z"></path></svg>
                Reviews & Customers
            </a>

            <!-- User Info at the bottom -->
            <div class="mt-auto pt-4 border-t border-gray-100">
                <!-- Logout Button -->
                <a href="../auth/login.php" class="w-full flex items-center justify-center p-2 rounded-xl text-red-600 bg-red-50 hover:bg-red-100 transition duration-150 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
                        <a href="retailermessage.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
                        <a href="retailernotifications.php" class="text-gray-600 relative">
                        <i class="fa-regular fa-bell"></i>
                        <!-- Notification badge can be added here if needed -->
                        </a>

                        <!-- Profile Dropdown -->
                        <div class="relative inline-block text-left">
                            <button id="profileDropdownBtn" class="flex items-center" title="<?php echo htmlspecialchars($userFullName); ?>">
                                <img id="headerProfilePic" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                                <a href="retailerprofile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                                <a href="retailerprofile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                                <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                        <!-- End Profile Dropdown -->
                    </div>
                </div>
            </header>
        
            <!-- Main Content Area -->
            <main id="content" class="p-8 transition-all duration-300 flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Dashboard Overview</h2>
                
                <!-- KPI Cards -->
                <div id="dashboard-kpis" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-4 rounded-xl shadow-md">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Total Revenue</p>
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M9 14h6m-5 4h4"></path></svg>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-800">₱12,450.00</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-md">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">New Orders</p>
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-800">8</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-md">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Low Inventory Items</p>
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-800">3</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-md">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500">Active Products</p>
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0V9m0 2v2"></path></svg>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-800">24</p>
                    </div>
                </div>
                
                <!-- Sales Trend and Inventory Alerts -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Sales Trend (Last 30 Days)</h3>
                        <div class="h-64 flex items-center justify-center text-gray-400 border border-dashed rounded-lg">
                            [Sales Trend Chart Placeholder]
                        </div>
                    </div>
                    <div id="dashboard-inventory-alerts" class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Inventory Alerts (Low Stock)</h3>
                        <div class="p-3 bg-red-50 border-l-4 border-red-500 rounded-lg mb-2">
                            <p class="text-sm text-red-700 font-medium">Organic Tomatoes</p>
                            <span class="text-xs text-red-600 font-bold">8 kg left</span>
                        </div>
                        <div class="p-3 bg-red-50 border-l-4 border-red-500 rounded-lg mb-2">
                            <p class="text-sm text-red-700 font-medium">Fresh Milk</p>
                            <span class="text-xs text-red-600 font-bold">5 liters left</span>
                        </div>
                        <div class="p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <p class="text-sm text-red-700 font-medium">Sourdough Bread</p>
                            <span class="text-xs text-red-600 font-bold">3 loaves left</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="mt-8 bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Recent Activity Feed</h3>
                    <ul id="recent-activity-feed" class="space-y-3">
                        <li class="flex items-center space-x-3 text-sm text-gray-600 border-b pb-2">
                            <span class="text-green-500 font-medium">New Order</span>
                            <span>#A1B2C3</span>
                            <span class="text-gray-500">•</span>
                            <span>₱415.98</span>
                            <span class="text-gray-500">•</span>
                            <span>Alice Johnson</span>
                        </li>
                        <li class="flex items-center space-x-3 text-sm text-gray-600 border-b pb-2">
                            <span class="text-green-500 font-medium">New Order</span>
                            <span>#D4E5F6</span>
                            <span class="text-gray-500">•</span>
                            <span>₱625.00</span>
                            <span class="text-gray-500">•</span>
                            <span>Bob Williams</span>
                        </li>
                        <li class="flex items-center space-x-3 text-sm text-gray-600 border-b pb-2">
                            <span class="text-blue-500 font-medium">Order Completed</span>
                            <span>#G7H8I9</span>
                            <span class="text-gray-500">•</span>
                            <span>₱310.50</span>
                            <span class="text-gray-500">•</span>
                            <span>Charlie Brown</span>
                        </li>
                        <li class="flex items-center space-x-3 text-sm text-gray-600 border-b pb-2">
                            <span class="text-yellow-500 font-medium">Low Stock Alert</span>
                            <span class="text-gray-500">•</span>
                            <span>Organic Tomatoes (8 kg remaining)</span>
                        </li>
                        <li class="flex items-center space-x-3 text-sm text-gray-600">
                            <span class="text-green-500 font-medium">Product Added</span>
                            <span class="text-gray-500">•</span>
                            <span>Fresh Strawberries added to inventory</span>
                        </li>
                    </ul>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Footer - Static at bottom, spans full width in front of sidebar -->
    <footer id="support" class="text-white py-12 mt-auto" style="background-color: #1B5E20;">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
          
          <!-- Logo/About -->
          <div>
            <h3 class="font-bold text-lg mb-3">The Farmer's Mall</h3>
            <p class="text-gray-300 text-sm">
              Fresh, organic produce delivered straight to your home from local farmers.
            </p>
          </div>
          
          <!-- Quick Links -->
          <div>
            <h3 class="font-bold text-lg mb-3">Quick Links</h3>
            <ul class="space-y-2 text-sm text-gray-300">
              <li><a href="#" class="hover:underline">About Us</a></li>
              <li><a href="#" class="hover:underline">Contact</a></li>
              <li><a href="#" class="hover:underline">FAQ</a></li>
              <li><a href="#" class="hover:underline">Support</a></li>
            </ul>
          </div>

          <!-- Categories -->
          <div>
            <h3 class="font-bold text-lg mb-3">Categories</h3>
            <ul class="space-y-2 text-sm text-gray-300">
              <li><a href="#" class="hover:underline">Vegetables</a></li>
              <li><a href="#" class="hover:underline">Fruits</a></li>
              <li><a href="#" class="hover:underline">Dairy</a></li>
              <li><a href="#" class="hover:underline">Meat</a></li>
            </ul>
          </div>

          <!-- Social -->
          <div>
            <h3 class="font-bold text-lg mb-3">Follow Us</h3>
            <div class="flex space-x-4 text-xl">
              <!-- Updated from footer.html -->
              <a href="#" class="hover:text-green-300"><i class="fab fa-facebook"></i></a>
              <a href="#" class="hover:text-green-300"><i class="fab fa-twitter"></i></a>
              <a href="#" class="hover:text-green-300"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
          © 2025 The Farmer's Mall. All rights reserved.
        </div>
    </footer>
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
            });

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
                <div class="bg-white p-4 rounded-xl shadow-md">
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
                
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
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
                
                <div class="bg-white rounded-xl shadow-lg overflow-auto">
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

                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
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
                    
                    <div class="bg-white rounded-xl shadow-lg p-6">
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
        const btn = document.getElementById('profileDropdownBtn');
        const menu = document.getElementById('profileDropdown');

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });

        document.addEventListener('click', () => {
            if (!menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });

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
      </script>
</body>
</html>