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
    <title>Products & Inventory – The Farmer's Mall</title>
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

        /* Product row hover effects */
        .product-row {
            transition: background-color 0.2s ease;
        }

        .product-row:hover {
            background-color: #f9fafb;
        }

        .product-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .product-row:hover .product-actions {
            opacity: 1;
        }

        /* Toast Notification Styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
        }

        .toast.success {
            border-left: 4px solid #22c55e;
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        .toast.warning {
            border-left: 4px solid #f59e0b;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .toast.hiding {
            animation: slideOut 0.3s ease-out forwards;
        }

        /* Responsive Table Styles */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Prevent body horizontal scroll */
        body {
            overflow-x: hidden;
        }

        /* Responsive main content */
        #content {
            width: 100%;
            overflow-x: hidden;
        }

        /* Table responsive adjustments */
        @media (max-width: 1280px) {
            table {
                font-size: 0.875rem;
            }

            th,
            td {
                padding: 0.75rem 0.5rem !important;
            }
        }

        @media (max-width: 1024px) {
            .filter-bar {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                font-size: 0.8125rem;
            }
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
            <i class="fas fa-bars text-xl"></i>
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
                <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
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

                            <div class="relative inline-block text-left" id="profileDropdownContainer">
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

                <main id="content" class="p-4 md:p-6 lg:p-8 transition-all duration-300 flex-1 w-full max-w-full">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 md:mb-8">Product & Inventory Management</h2>

                    <!-- Filter Bar -->
                    <div class="mb-6 filter-bar">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="relative min-w-[140px]">
                                <select id="bulkActions" onchange="applyBulkAction()" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white appearance-none pr-10">
                                    <option value="">Bulk actions</option>
                                    <option value="edit">Edit</option>
                                    <option value="delete">Move to Trash</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>

                            <div class="relative min-w-[160px]">
                                <select id="categoryFilter" onchange="applyFilters()" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white appearance-none pr-10">
                                    <option value="">Select a category</option>
                                    <option value="vegetables">Vegetables</option>
                                    <option value="fruits">Fruits</option>
                                    <option value="dairy">Dairy</option>
                                    <option value="meat">Meat</option>
                                    <option value="seafood">Seafood</option>
                                    <option value="bakery">Bakery</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>

                            <div class="relative min-w-[180px]">
                                <select id="productTypeFilter" onchange="applyFilters()" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white appearance-none pr-10">
                                    <option value="">Filter by product type</option>
                                    <option value="simple">Simple product</option>
                                    <option value="variable">Variable product</option>
                                    <option value="grouped">Grouped product</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>

                            <div class="relative min-w-[180px]">
                                <select id="stockStatusFilter" onchange="applyFilters()" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white appearance-none pr-10">
                                    <option value="">Filter by stock status</option>
                                    <option value="instock">In stock</option>
                                    <option value="outofstock">Out of stock</option>
                                    <option value="onbackorder">On backorder</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table class="w-full divide-y divide-gray-200" style="table-layout: auto;">
                                <thead class="bg-green-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 50px;">
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="w-4 h-4 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 250px;">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 100px;">SKU</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 100px;">Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 120px;">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 120px;">Categories</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 100px;">Tags</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 180px;">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="products-list" class="bg-white divide-y divide-gray-200">
                                    <!-- Products will be loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <div id="no-products-message" class="p-6 text-center text-gray-500 hidden">No products listed yet. Click "Add New Product" to start selling!</div>
                    </div>

                    <!-- Action Buttons Below Table -->
                    <div class="flex flex-col sm:flex-row justify-start items-stretch sm:items-center gap-3 mt-6">
                        <a href="retaileraddnewproduct.php" class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 transition duration-150 font-medium">
                            <i class="fas fa-plus text-lg mr-2"></i>
                            Add New Product
                        </a>
                        <button onclick="openBulkPriceModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 transition duration-150 text-sm">
                            Bulk Edit Prices
                        </button>
                    </div>
                </main>
            </div>
        </div>


    </div>

    <!-- View Product Modal -->
    <div id="viewProductModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h3 class="text-xl font-bold text-gray-900">Product Details</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="viewProductContent" class="p-6">
                <!-- Content will be populated dynamically -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Delete Product</h3>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <p class="text-gray-600 mb-2">Are you sure you want to delete this product?</p>
                <p class="text-sm text-gray-500">This action cannot be undone. The product will be permanently removed from your inventory.</p>
            </div>

            <div class="p-6 border-t bg-gray-50 flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button onclick="confirmDelete()" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete Product
                </button>
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

                            let iconClass = 'fa-info-circle',
                                iconBgClass = 'bg-blue-100',
                                iconTextClass = 'text-blue-700';
                            if (notif.type === 'order') {
                                iconClass = 'fa-box';
                                iconBgClass = 'bg-green-100';
                                iconTextClass = 'text-green-700';
                            } else if (notif.type === 'stock') {
                                iconClass = 'fa-exclamation-triangle';
                                iconBgClass = 'bg-yellow-100';
                                iconTextClass = 'text-yellow-700';
                            } else if (notif.type === 'review') {
                                iconClass = 'fa-star';
                                iconBgClass = 'bg-yellow-100';
                                iconTextClass = 'text-yellow-700';
                            }

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

        // Load products on page load with URL parameter support
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const filter = urlParams.get('filter');
            const updated = urlParams.get('updated');

            // Show success message if coming back from edit
            if (updated) {
                showToast('Product list refreshed with latest changes', 'success');
                // Clean URL
                window.history.replaceState({}, document.title, 'retailerinventory.php');
            }

            if (filter === 'lowstock') {
                // Set the stock status filter dropdown to show low stock or out of stock
                const stockStatusFilter = document.getElementById('stockStatusFilter');
                if (stockStatusFilter) {
                    stockStatusFilter.value = 'outofstock'; // This will also show low stock items
                }
                // Load products with low stock filter
                loadProducts({
                    stock_status: 'outofstock'
                });
            } else {
                loadProducts();
            }
        });

        // Load products function with filters
        async function loadProducts(filters = {}) {
            try {
                // Build query string from filters
                const params = new URLSearchParams();
                if (filters.category) params.append('category', filters.category);
                if (filters.stock_status) params.append('stock_status', filters.stock_status);
                if (filters.product_type) params.append('product_type', filters.product_type);

                const url = '../api/get-products.php' + (params.toString() ? '?' + params.toString() : '');
                const response = await fetch(url);
                const result = await response.json();

                if (result.success && result.products) {
                    const productsList = document.getElementById('products-list');
                    const noProductsMessage = document.getElementById('no-products-message');

                    if (result.products.length === 0) {
                        productsList.innerHTML = '';
                        noProductsMessage.classList.remove('hidden');
                        return;
                    }

                    noProductsMessage.classList.add('hidden');
                    productsList.innerHTML = result.products.map(product => {
                        // Debug: Log product image info
                        if (!product.image_url) {
                            console.warn('Product missing image_url:', {
                                id: product.id,
                                name: product.name,
                                image_url: product.image_url
                            });
                        }

                        // Add cache busting parameter to avoid stale images
                        const imageSrc = product.image_url ? `../${product.image_url}?v=${Date.now()}` : 'https://placehold.co/40x40/22c55e/fff?text=' + product.name.charAt(0);
                        const stockStatus = (product.stock_quantity && product.stock_quantity > 0) ? 'In stock' : 'Out of stock';
                        const stockClass = (product.stock_quantity && product.stock_quantity > 0) ? 'text-green-600' : 'text-red-600';
                        const salePrice = product.sale_price ? parseFloat(product.sale_price) : null;
                        const regularPrice = parseFloat(product.price);
                        const displayPrice = salePrice || regularPrice;
                        const formattedDate = product.created_at ? new Date(product.created_at).toLocaleString('en-US', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        }).replace(',', ' at') : 'N/A';

                        return `
                        <tr class="product-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="product-checkbox w-4 h-4 text-green-600 rounded focus:ring-2 focus:ring-green-500" data-product-id="${product.id}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded-lg object-cover mr-4" src="${imageSrc}" alt="${product.name}" onerror="this.src='https://placehold.co/40x40/22c55e/fff?text=${product.name.charAt(0)}'" />
                                    <div>
                                        <div class="text-sm font-medium text-blue-600">${product.name}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            ID: ${product.id}
                                        </div>
                                        <div class="product-actions text-xs text-gray-500 mt-1">
                                            <a href="#" onclick="editProduct('${product.id}'); return false;" class="text-blue-600 hover:underline">Edit</a> | 
                                            <a href="#" onclick="deleteProduct('${product.id}'); return false;" class="text-red-600 hover:underline">Trash</a> | 
                                            <a href="#" onclick="viewProduct('${product.id}'); return false;" class="text-blue-600 hover:underline">View</a> | 
                                            <a href="#" onclick="duplicateProduct('${product.id}'); return false;" class="text-blue-600 hover:underline">Duplicate</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-500">—</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium ${stockClass}">${stockStatus}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${salePrice ? `<div class="text-sm text-gray-500 line-through">₱${regularPrice.toFixed(2)}</div>` : ''}
                                <div class="text-sm font-medium text-gray-900">₱${displayPrice.toFixed(2)}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-blue-600">${product.category || 'Uncategorized'}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-blue-600">No tags</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">${product.status || 'Published'}</div>
                                <div class="text-sm text-gray-400">${formattedDate}</div>
                            </td>
                        </tr>
                    `;
                    }).join('');
                }
            } catch (error) {
                console.error('Error loading products:', error);
                showToast('Error loading products', 'error');
            }
        }

        // Product Modal Functions
        let editingProductId = null;

        function openProductModal(product = null) {
            const modal = document.getElementById('productModal');
            const modalTitle = document.getElementById('modalTitle');
            const form = document.getElementById('productForm');

            if (product) {
                modalTitle.textContent = 'Edit Product';
                editingProductId = product.id;
                document.getElementById('productName').value = product.name || '';
                document.getElementById('productCategory').value = product.category || '';
                document.getElementById('productPrice').value = product.price || '';
                document.getElementById('productUnit').value = product.unit || '';
                document.getElementById('productStock').value = product.stock || '';
            } else {
                modalTitle.textContent = 'Add New Product';
                editingProductId = null;
                form.reset();
            }

            modal.classList.remove('hidden');
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.add('hidden');
            editingProductId = null;
        }

        function saveProduct(event) {
            event.preventDefault();
            const formData = {
                name: document.getElementById('productName').value,
                category: document.getElementById('productCategory').value,
                price: document.getElementById('productPrice').value,
                unit: document.getElementById('productUnit').value,
                stock: document.getElementById('productStock').value
            };

            console.log('Saving product:', formData);
            showToast(editingProductId ? 'Product updated successfully!' : 'Product added successfully!', 'success');
            closeProductModal();
            // Integrate with your database here
        }

        // Bulk Price Modal Functions
        function openBulkPriceModal() {
            document.getElementById('bulkPriceModal').classList.remove('hidden');
        }

        function closeBulkPriceModal() {
            document.getElementById('bulkPriceModal').classList.add('hidden');
        }

        function applyBulkPrice(event) {
            event.preventDefault();
            const percentage = document.getElementById('pricePercentage').value;
            const action = document.getElementById('priceAction').value;

            console.log(`Applying ${action} of ${percentage}% to all products`);
            showToast(`Bulk price ${action} of ${percentage}% applied successfully!`, 'success');
            closeBulkPriceModal();
            // Integrate with your database here
        }

        // Delete Product Function
        let productToDelete = null;

        function deleteProduct(productId) {
            productToDelete = productId;
            showToast('Are you sure you want to delete this product?', 'warning');
            // Show confirmation in modal instead
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            // Reset modal text
            const modalContent = document.querySelector('#deleteModal .p-6 p');
            if (modalContent) {
                modalContent.textContent = 'Are you sure you want to delete this product?';
            }
            productToDelete = null;
        }

        async function confirmDelete() {
            if (productToDelete) {
                try {
                    // Check if it's bulk delete (array) or single delete (string)
                    const isBulkDelete = Array.isArray(productToDelete);
                    const productIds = isBulkDelete ? productToDelete : [productToDelete];

                    if (isBulkDelete) {
                        // Bulk delete
                        const response = await fetch('../api/bulk-action-products.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                product_ids: productIds,
                                action: 'delete'
                            })
                        });
                        const result = await response.json();

                        closeDeleteModal();

                        if (result.success) {
                            showToast(result.message || `${productIds.length} product(s) deleted successfully!`, 'success');
                            setTimeout(() => loadProducts(), 500);
                            // Uncheck all checkboxes
                            document.getElementById('selectAll').checked = false;
                            document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
                        } else {
                            showToast(result.message || 'Error deleting products', 'error');
                        }
                    } else {
                        // Single delete
                        const response = await fetch('../api/delete-product.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                product_id: productToDelete
                            })
                        });
                        const result = await response.json();

                        closeDeleteModal();

                        if (result.success) {
                            showToast('Product deleted successfully!', 'success');
                            setTimeout(() => loadProducts(), 500);
                        } else {
                            showToast(result.message || 'Error deleting product', 'error');
                        }
                    }
                } catch (error) {
                    console.error('Error deleting product:', error);
                    closeDeleteModal();
                    showToast('Error deleting product', 'error');
                }
            }
        }

        // Edit Product Function (sample data)
        function editProduct(productId) {
            // Redirect to edit page with product ID
            window.location.href = `retaileraddnewproduct.php?id=${productId}`;
        }

        // View Product Function
        async function viewProduct(productId) {
            try {
                const response = await fetch(`../api/get-product.php?id=${productId}`);
                const result = await response.json();

                if (result.success && result.product) {
                    const product = result.product;
                    const imageSrc = product.image_url ? `../${product.image_url}` : 'https://placehold.co/400x300/22c55e/fff?text=' + product.name.charAt(0);
                    const salePrice = null;
                    const regularPrice = parseFloat(product.price);

                    const content = `
                    <div class="space-y-4">
                        <div class="flex justify-center mb-4">
                            <img src="${imageSrc}" alt="${product.name}" class="max-w-full h-64 object-cover rounded-lg" onerror="this.src='https://placehold.co/400x300/22c55e/fff?text=${product.name.charAt(0)}'">
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">${product.name}</h4>
                            <p class="text-gray-600 text-sm">${product.description || 'No description available'}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                            <div>
                                <span class="text-sm text-gray-500">SKU:</span>
                                <p class="font-medium">N/A</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Stock:</span>
                                <p class="font-medium">${product.stock_quantity || 0} ${product.unit || 'units'}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Regular Price:</span>
                                <p class="font-medium">₱${regularPrice.toFixed(2)}</p>
                            </div>
                            ${salePrice ? `
                            <div>
                                <span class="text-sm text-gray-500">Sale Price:</span>
                                <p class="font-medium text-green-600">₱${salePrice.toFixed(2)}</p>
                            </div>
                            ` : ''}
                            <div>
                                <span class="text-sm text-gray-500">Category:</span>
                                <p class="font-medium">${product.category || 'Uncategorized'}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Tags:</span>
                                <p class="font-medium">No tags</p>
                            </div>
                        </div>
                    </div>
                `;

                    document.getElementById('viewProductContent').innerHTML = content;
                    document.getElementById('viewProductModal').classList.remove('hidden');
                } else {
                    showToast('Product not found', 'error');
                }
            } catch (error) {
                console.error('Error viewing product:', error);
                showToast('Error loading product details', 'error');
            }
        }

        function closeViewModal() {
            document.getElementById('viewProductModal').classList.add('hidden');
        }

        // Duplicate Product Function
        async function duplicateProduct(productId) {
            try {
                const response = await fetch('../api/duplicate-product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Product duplicated successfully!', 'success');
                    setTimeout(() => loadProducts(), 500);
                } else {
                    showToast(result.message || 'Error duplicating product', 'error');
                }
            } catch (error) {
                console.error('Error duplicating product:', error);
                showToast('Error duplicating product', 'error');
            }
        }

        // Filter Functions
        function applyFilters() {
            const category = document.getElementById('categoryFilter').value;
            const productType = document.getElementById('productTypeFilter').value;
            const stockStatus = document.getElementById('stockStatusFilter').value;

            const filters = {};
            if (category) filters.category = category;
            if (productType) filters.product_type = productType;
            if (stockStatus) filters.stock_status = stockStatus;

            loadProducts(filters);

            if (category || productType || stockStatus) {
                showToast('Filters applied successfully!', 'success');
            }
        }

        async function applyBulkAction() {
            const action = document.getElementById('bulkActions').value;
            if (!action) {
                return;
            }

            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            if (checkedBoxes.length === 0) {
                showToast('Please select at least one product.', 'warning');
                document.getElementById('bulkActions').value = '';
                return;
            }

            const productIds = Array.from(checkedBoxes).map(cb => cb.dataset.productId);

            if (action === 'delete') {
                // Show custom confirmation via modal instead of browser dialog
                productToDelete = productIds; // Store array for bulk delete
                document.getElementById('deleteModal').classList.remove('hidden');
                // Update modal text for bulk delete
                const modalContent = document.querySelector('#deleteModal .p-6 p');
                if (modalContent) {
                    modalContent.textContent = `Are you sure you want to delete ${productIds.length} product(s)?`;
                }
                document.getElementById('bulkActions').value = '';
                return;
            }

            if (action === 'edit' && productIds.length > 1) {
                showToast('Please select only one product to edit', 'warning');
                document.getElementById('bulkActions').value = '';
                return;
            }

            try {
                const response = await fetch('../api/bulk-action-products.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_ids: productIds,
                        action: action
                    })
                });
                const result = await response.json();

                if (result.success) {
                    if (result.redirect && action === 'edit' && productIds.length === 1) {
                        // Redirect to edit page for single product
                        window.location.href = `retaileraddnewproduct.php?id=${productIds[0]}`;
                    } else if (result.redirect && action === 'edit') {
                        showToast('Please select only one product to edit', 'warning');
                    } else {
                        showToast(result.message, 'success');
                        setTimeout(() => loadProducts(), 500);
                    }
                } else {
                    showToast(result.message || 'Error processing bulk action', 'error');
                }
            } catch (error) {
                console.error('Error applying bulk action:', error);
                showToast('Error processing bulk action', 'error');
            }

            document.getElementById('bulkActions').value = '';
            document.getElementById('selectAll').checked = false;
            checkedBoxes.forEach(cb => cb.checked = false);
        }

        // Checkbox functions
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        // Toast Notification Function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : '⚠';
            const iconColor = type === 'success' ? 'text-green-600' : type === 'error' ? 'text-red-600' : 'text-yellow-600';

            toast.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="text-2xl ${iconColor}">${icon}</span>
                <p class="text-gray-800 font-medium">${message}</p>
            </div>
        `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('hiding');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
    </script>

    <!-- Product Add/Edit Modal -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h3 id="modalTitle" class="font-semibold text-lg mb-4">Add New Product</h3>
            <form id="productForm" class="space-y-4" onsubmit="saveProduct(event)">
                <div>
                    <label for="productName" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" id="productName" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div>
                    <label for="productCategory" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="productCategory" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none bg-white">
                        <option>Vegetables</option>
                        <option>Fruits</option>
                        <option>Dairy</option>
                        <option>Meat</option>
                        <option>Seafood</option>
                        <option>Bakery</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="productPrice" class="block text-sm font-medium text-gray-700">Price (per unit)</label>
                        <input type="number" id="productPrice" step="0.01" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label for="productUnit" class="block text-sm font-medium text-gray-700">Unit (e.g., kg)</label>
                        <input type="text" id="productUnit" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>
                <div>
                    <label for="productStock" class="block text-sm font-medium text-gray-700">Stock</label>
                    <input type="number" id="productStock" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeProductModal()" class="px-4 py-2 border rounded-md text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Price Modal -->
    <div id="bulkPriceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h3 class="font-semibold text-lg mb-4">Bulk Edit Prices</h3>
            <form onsubmit="applyBulkPrice(event)" class="space-y-4">
                <div>
                    <label for="priceAction" class="block text-sm font-medium text-gray-700">Action</label>
                    <select id="priceAction" class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none bg-white">
                        <option value="increase">Increase prices by</option>
                        <option value="decrease">Decrease prices by</option>
                    </select>
                </div>
                <div>
                    <label for="pricePercentage" class="block text-sm font-medium text-gray-700">Percentage (%)</label>
                    <input type="number" id="pricePercentage" min="0" max="100" step="0.1" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div class="p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                    <p class="text-sm text-yellow-700">This will apply to all active products in your inventory.</p>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeBulkPriceModal()" class="px-4 py-2 border rounded-md text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Apply Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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