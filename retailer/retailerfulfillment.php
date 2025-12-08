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
        /* Mobile menu toggle */
        #mobileMenuBtn {
            display: none;
        }
        @media (max-width: 768px) {
            #mobileMenuBtn {
                display: flex;
            }
            #sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                height: 100vh;
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
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0V9m0 2v2m-4-2h1m-1 0h-2m2 0v2m-2-2h-1m-1 0H5m-2 4h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5h6"></path></svg>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M9 14h6m-5 4h4m-4-8h4m-5-8h6a2 2 0 012 2v10a2 2 0 01-2 2h-6a2 2 0 01-2-2V6a2 2 0 012-2z"></path></svg>
                Financial Reports
            </a>
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l4-4-4-4m0 16l4-4-4-4m-1-5a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Vouchers & Coupons
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.193a2.003 2.003 0 013.902 0l1.018 2.062 2.277.33a2.003 2.003 0 011.11 3.407l-1.652 1.61.39 2.269a2.003 2.003 0 01-2.906 2.108L12 15.698l-2.035 1.071a2.003 2.003 0 01-2.906-2.108l.39-2.269-1.652-1.61a2.003 2.003 0 011.11-3.407l2.277-.33 1.018-2.062z"></path></svg>
                Reviews & Customers
            </a>

            <div class="mt-auto pt-4 border-t border-gray-100">
                <a href="../auth/logout.php" class="w-full flex items-center justify-center p-2 rounded-xl text-red-600 bg-red-50 hover:bg-red-100 transition duration-150 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
                        <a href="retailermessage.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
                        <a href="retailernotifications.php" class="text-gray-600 relative">
                        <i class="fa-regular fa-bell"></i>
                        </a>

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
                    </div>
                </div>
            </header>
        
            <main id="content" class="p-8 transition-all duration-300 flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Order Management & Fulfillment</h2>
                
                <div class="flex justify-between items-center mb-6">
                    <p class="text-gray-500">Filter by Status: 
                        <select id="order-status-filter" onchange="filterOrders()" class="p-2 border border-gray-300 rounded-lg text-sm">
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
                            <!-- Sample Order Rows -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#A1B2C3D4</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Alice Johnson</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12/7/2025</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">₱415.98</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">New</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button onclick="updateStatus('A1B2C3D4', 'Processing')" class="text-yellow-600 hover:text-yellow-900">Process</button>
                                    <button onclick="viewDetails('A1B2C3D4')" class="text-blue-600 hover:text-blue-900">Details</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#E5F6G7H8</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Bob Williams</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12/6/2025</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">₱625.00</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Processing</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button onclick="updateStatus('E5F6G7H8', 'Completed')" class="text-green-600 hover:text-green-900">Complete</button>
                                    <button onclick="viewDetails('E5F6G7H8')" class="text-blue-600 hover:text-blue-900">Details</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#I9J0K1L2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Charlie Brown</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12/5/2025</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">₱310.50</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button onclick="viewDetails('I9J0K1L2')" class="text-blue-600 hover:text-blue-900">Details</button>
                                </td>
                            </tr>
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
                        <option value="New">New</option>
                        <option value="Processing">Processing</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
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
    
    <footer id="support" class="text-white py-12 mt-auto" style="background-color: #1B5E20;">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
          <div>
            <h3 class="font-bold text-lg mb-3">The Farmer's Mall</h3>
            <p class="text-gray-300 text-sm">Fresh, organic produce delivered straight to your home from local farmers.</p>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-3">Quick Links</h3>
            <ul class="space-y-2 text-sm text-gray-300">
              <li><a href="#" class="hover:underline">About Us</a></li>
              <li><a href="#" class="hover:underline">Contact</a></li>
              <li><a href="#" class="hover:underline">FAQ</a></li>
              <li><a href="#" class="hover:underline">Support</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-3">Categories</h3>
            <ul class="space-y-2 text-sm text-gray-300">
              <li><a href="#" class="hover:underline">Vegetables</a></li>
              <li><a href="#" class="hover:underline">Fruits</a></li>
              <li><a href="#" class="hover:underline">Dairy</a></li>
              <li><a href="#" class="hover:underline">Meat</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-3">Follow Us</h3>
            <div class="flex space-x-4 text-xl">
              <a href="#" class="hover:text-green-300"><i class="fab fa-facebook"></i></a>
              <a href="#" class="hover:text-green-300"><i class="fab fa-twitter"></i></a>
              <a href="#" class="hover:text-green-300"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
        </div>
        <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
          © 2025 The Farmer's Mall. All rights reserved.
        </div>
    </footer>
</div>

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

    function filterOrders() {
        const filter = document.getElementById('order-status-filter').value;
        const tbody = document.getElementById('orders-table-body');
        const rows = tbody.getElementsByTagName('tr');
        const noOrdersMsg = document.getElementById('no-orders-message');
        let visibleCount = 0;
        
        for (let row of rows) {
            const statusSpan = row.querySelector('td:nth-child(5) span');
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
    
    // Sample order data (in production, this would come from database)
    const orderData = {
        'A1B2C3D4': {
            id: '#A1B2C3D4',
            customer: 'Alice Johnson',
            date: '12/7/2025',
            status: 'New',
            items: [
                { name: 'Fresh Apples', quantity: '2 kg', price: '₱150.00', subtotal: '₱300.00' },
                { name: 'Organic Tomatoes', quantity: '1 kg', price: '₱65.00', subtotal: '₱65.00' }
            ],
            address: '123 Main Street, Barangay Sample, Manila City',
            phone: '+63 912 345 6789',
            notes: 'Please deliver before 5 PM',
            subtotal: '₱365.00',
            deliveryFee: '₱50.98',
            total: '₱415.98'
        },
        'E5F6G7H8': {
            id: '#E5F6G7H8',
            customer: 'Bob Williams',
            date: '12/6/2025',
            status: 'Processing',
            items: [
                { name: 'Fresh Milk', quantity: '2 liters', price: '₱120.00', subtotal: '₱240.00' },
                { name: 'Organic Eggs', quantity: '1 dozen', price: '₱180.00', subtotal: '₱180.00' }
            ],
            address: '456 Oak Avenue, Quezon City',
            phone: '+63 917 888 9999',
            notes: 'Leave at gate if nobody home',
            subtotal: '₱420.00',
            deliveryFee: '₱205.00',
            total: '₱625.00'
        },
        'I9J0K1L2': {
            id: '#I9J0K1L2',
            customer: 'Charlie Brown',
            date: '12/5/2025',
            status: 'Completed',
            items: [
                { name: 'Fresh Mangoes', quantity: '3 kg', price: '₱103.50', subtotal: '₱310.50' }
            ],
            address: '789 Pine Street, Makati City',
            phone: '+63 920 111 2222',
            notes: 'No special instructions',
            subtotal: '₱310.50',
            deliveryFee: '₱0.00',
            total: '₱310.50'
        }
    };

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
        if (order.status === 'New') {
            statusBadge.className += ' bg-blue-100 text-blue-800';
        } else if (order.status === 'Processing') {
            statusBadge.className += ' bg-yellow-100 text-yellow-800';
        } else if (order.status === 'Completed') {
            statusBadge.className += ' bg-green-100 text-green-800';
        } else if (order.status === 'Cancelled') {
            statusBadge.className += ' bg-red-100 text-red-800';
        }
        
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

</script>
</body>
</html>
