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
    <title>Add New Product - Farmers Mall</title>
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
        /* Force sidebar to be full height with logout at bottom */
        #sidebar {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #sidebar > *:last-child {
            margin-top: auto !important;
            padding-top: 1rem !important;
            border-top: 1px solid #e5e7eb !important;
        }
        /* Mobile menu toggle */
        #mobileMenuBtn {
            display: none;
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
        #sidebar {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #sidebar > div:last-child {
            margin-top: auto !important;
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
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
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
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.193a2.003 2.003 0 013.902 0l1.018 2.062 2.277.33a2.003 2.003 0 011.11 3.407l-1.652 1.61.39 2.269a2.003 2.003 0 01-2.906 2.108L12 15.698l-2.035 1.071a2.003 2.003 0 01-2.906-2.108l.39-2.269-1.652-1.61a2.003 2.003 0 011.11-3.407l2.277-.33 1.018-2.062z"></path></svg>
                Reviews & Customers
            </a>
        </nav>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            
            <!-- Top Header Bar -->
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600 transition" title="Home"><i class="fa-solid fa-house text-xl"></i></a>

                        <!-- Notifications Icon -->
                        <div class="relative" id="notificationPreviewContainer">
                            <a href="retailernotifications.php" class="text-gray-600 hover:text-green-600 transition relative" title="Notifications" id="notificationIcon">
                                <i class="fa-regular fa-bell text-xl"></i>
                                <span id="notificationBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
                            </a>
                            <div id="notificationPreview" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Notifications</h3></div>
                                <div id="notificationPreviewItems" class="max-h-96 overflow-y-auto"><div class="p-8 text-center text-gray-500"><i class="fas fa-bell text-4xl mb-2 text-gray-300"></i><p class="text-sm">No notifications</p></div></div>
                                <div class="p-4 border-t border-gray-100 bg-gray-50"><a href="retailernotifications.php" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition font-medium">View All Notifications</a></div>
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
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="py-2">
                                    <a href="retailerprofile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                                        <i class="fas fa-user mr-2 text-gray-400"></i> My Account
                                    </a>
                                    <a href="retailerprofile.php#settings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                                        <i class="fas fa-cog mr-2 text-gray-400"></i> Settings
                                    </a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- End Profile Dropdown -->
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main id="content" class="flex-1 p-6">
                <div class="max-w-7xl mx-auto">
                    
                    <!-- Form Container with Two Column Layout -->
                    <form id="addProductForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Left Column - Main Content (2/3 width) -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Product Name Section -->
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 border border-gray-200 p-6">
                                <div class="mb-4">
                                    <input type="text" id="productName" name="productName" placeholder="Product name" required class="w-full text-2xl font-medium border-none outline-none focus:ring-0 placeholder-gray-400">
                                </div>
                                <div class="text-sm text-gray-500">
                                    <span>Permalink: </span>
                                    <a href="#" class="text-blue-600 hover:underline">https://solo.kg/shop/product/polo/</a>
                                    <button type="button" class="ml-2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Product Description Section -->
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 border border-gray-200 p-6">
                                <h3 class="text-base font-semibold text-gray-800 mb-4">Product Description</h3>
                                <div class="mb-4">
                                    <div class="border border-gray-300 rounded-lg">
                                        <div class="bg-gray-50 border-b border-gray-300 px-3 py-2 flex items-center space-x-2 text-sm">
                                            <button type="button" class="p-1 hover:bg-gray-200 rounded" title="Paragraph">
                                                <i class="fas fa-paragraph"></i>
                                            </button>
                                            <div class="w-px h-4 bg-gray-300"></div>
                                            <button type="button" class="p-1 hover:bg-gray-200 rounded font-bold" title="Bold">B</button>
                                            <button type="button" class="p-1 hover:bg-gray-200 rounded italic" title="Italic">I</button>
                                            <button type="button" class="p-1 hover:bg-gray-200 rounded" title="Link">
                                                <i class="fas fa-link"></i>
                                            </button>
                                            <div class="w-px h-4 bg-gray-300"></div>
                                            <button type="button" class="p-1 hover:bg-gray-200 rounded" title="Bulleted List">
                                                <i class="fas fa-list-ul"></i>
                                            </button>
                                            <button type="button" class="p-1 hover:bg-gray-200 rounded" title="Numbered List">
                                                <i class="fas fa-list-ol"></i>
                                            </button>
                                            <button type="button" class="p-1 hover:bg-gray-200 rounded" title="Quote">
                                                <i class="fas fa-quote-right"></i>
                                            </button>
                                        </div>
                                        <textarea id="productDescription" name="productDescription" rows="8" class="w-full px-4 py-3 border-none outline-none focus:ring-0 resize-none" placeholder="Describe your product in detail..."></textarea>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Word count: <span id="wordCount">0</span></p>
                                </div>
                                <div class="mt-4">
                                    <label for="shortDescription" class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                                    <textarea id="shortDescription" name="shortDescription" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="A brief summary of your product..."></textarea>
                                </div>
                            </div>

                            <!-- Product Data Section -->
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 border border-gray-200 p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-base font-semibold text-gray-800">Product Data</h3>
                                    <select class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                                        <option>Simple product</option>
                                        <option>Variable product</option>
                                        <option>Grouped product</option>
                                    </select>
                                </div>

                                <!-- Tabs -->
                                <div class="border-b border-gray-200 mb-4">
                                    <nav class="flex space-x-4" aria-label="Tabs">
                                        <button type="button" class="tab-btn px-3 py-2 text-sm font-medium border-b-2 border-green-600 text-green-600" data-tab="general">General</button>
                                        <button type="button" class="tab-btn px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="inventory">Inventory</button>
                                        <button type="button" class="tab-btn px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="shipping">Shipping</button>
                                        <button type="button" class="tab-btn px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="attributes">Attributes</button>
                                    </nav>
                                </div>

                                <!-- Tab Content -->
                                <div id="general" class="tab-content">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="regularPrice" class="block text-sm font-medium text-gray-700 mb-1">Regular price (₱)</label>
                                            <input type="number" id="regularPrice" name="regularPrice" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label for="salePrice" class="block text-sm font-medium text-gray-700 mb-1">Sale price (₱)</label>
                                            <input type="number" id="salePrice" name="salePrice" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="ml-2 text-sm text-gray-700">Schedule sale dates</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="inventory" class="tab-content hidden">
                                    <div class="space-y-4">
                                        <div>
                                            <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                            <input type="text" id="sku" name="sku" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="Product SKU">
                                        </div>
                                        <div>
                                            <label class="flex items-center mb-3">
                                                <input type="checkbox" id="manageStock" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                <span class="ml-2 text-sm font-medium text-gray-700">Manage stock?</span>
                                            </label>
                                            <div id="stockFields" class="hidden space-y-3">
                                                <div>
                                                    <label for="stockQuantity" class="block text-sm font-medium text-gray-700 mb-1">Stock quantity</label>
                                                    <input type="number" id="stockQuantity" name="stockQuantity" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="0">
                                                </div>
                                                <div>
                                                    <label for="lowStockThreshold" class="block text-sm font-medium text-gray-700 mb-1">Low stock threshold</label>
                                                    <input type="number" id="lowStockThreshold" name="lowStockThreshold" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="2">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="stockStatus" class="block text-sm font-medium text-gray-700 mb-1">Stock status</label>
                                            <select id="stockStatus" name="stockStatus" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                                                <option>In stock</option>
                                                <option>Out of stock</option>
                                                <option>On backorder</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div id="shipping" class="tab-content hidden">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                                            <input type="number" id="weight" name="weight" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label for="length" class="block text-sm font-medium text-gray-700 mb-1">Length (cm)</label>
                                            <input type="number" id="length" name="length" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label for="width" class="block text-sm font-medium text-gray-700 mb-1">Width (cm)</label>
                                            <input type="number" id="width" name="width" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label for="height" class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                                            <input type="number" id="height" name="height" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label for="shippingClass" class="block text-sm font-medium text-gray-700 mb-1">Shipping class</label>
                                        <select id="shippingClass" name="shippingClass" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                                            <option>Standard</option>
                                            <option>Express</option>
                                            <option>Same day</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="attributes" class="tab-content hidden">
                                    <div class="space-y-4">
                                        <div>
                                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                            <select id="unit" name="unit" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                                                <option>kg</option>
                                                <option>g</option>
                                                <option>lbs</option>
                                                <option>pcs</option>
                                                <option>pack</option>
                                                <option>bundle</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">Origin/Source</label>
                                            <input type="text" id="origin" name="origin" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" placeholder="e.g., Local Farm, Imported">
                                        </div>
                                        <div>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="organic" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                <span class="ml-2 text-sm text-gray-700">Organic product</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column - Sidebar (1/3 width) -->
                        <div class="lg:col-span-1 space-y-6">
                            
                            <!-- Product Image Section -->
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 border border-gray-200 p-4">
                                <h3 class="text-base font-semibold text-gray-800 mb-4">Product Image</h3>
                                <div id="productImagePreview" class="mb-3 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-8 hover:border-green-500 transition cursor-pointer">
                                    <div class="text-center">
                                        <i class="fas fa-image text-gray-400 text-4xl mb-2"></i>
                                        <p class="text-sm text-gray-500">Click to upload image</p>
                                    </div>
                                </div>
                                <input type="file" id="productImage" name="productImage" accept="image/*" class="hidden">
                                <button type="button" onclick="document.getElementById('productImage').click()" class="w-full px-4 py-2 text-sm text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50">
                                    Set product image
                                </button>
                            </div>

                            <!-- Product Gallery Section -->
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 border border-gray-200 p-4">
                                <h3 class="text-base font-semibold text-gray-800 mb-4">Product Gallery</h3>
                                <p class="text-xs text-gray-500 mb-3">Maximum 4 images</p>
                                <div id="galleryPreview" class="grid grid-cols-3 gap-2 mb-3">
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 flex items-center justify-center cursor-pointer hover:border-green-500">
                                        <i class="fas fa-plus text-gray-400"></i>
                                    </div>
                                </div>
                                <input type="file" id="productGallery" name="productGallery[]" accept="image/*" multiple class="hidden">
                                <button type="button" onclick="handleGalleryClick()" class="w-full px-4 py-2 text-sm text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50">
                                    Add gallery images
                                </button>
                            </div>

                            <!-- Publish Section -->
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 border border-gray-200 p-4">
                                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-paper-plane mr-2 text-gray-600"></i>
                                    Publish
                                </h3>
                                <div class="space-y-3 text-sm">
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600"><i class="fas fa-info-circle mr-2"></i>Status:</span>
                                        <span class="font-medium text-green-600">Draft</span>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600"><i class="fas fa-eye mr-2"></i>Visibility:</span>
                                        <a href="#" class="text-blue-600 hover:underline">Public</a>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600"><i class="fas fa-calendar mr-2"></i>Publish:</span>
                                        <a href="#" class="text-blue-600 hover:underline">immediately</a>
                                    </div>
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-gray-600"><i class="fas fa-globe mr-2"></i>Catalog visibility:</span>
                                        <a href="#" class="text-blue-600 hover:underline">Shop and search results</a>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <button type="button" class="w-full px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 mb-2">
                                        Save Draft
                                    </button>
                                    <button type="button" class="w-full px-4 py-2 text-sm text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50 mb-2">
                                        Preview
                                    </button>
                                    <button type="submit" class="w-full px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 font-medium">
                                        Publish Product
                                    </button>
                                </div>
                            </div>

                            <!-- Product Categories Section -->
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 border border-gray-200 p-4">
                                <h3 class="text-base font-semibold text-gray-800 mb-4">Product Categories</h3>
                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    <label class="flex items-center py-1">
                                        <input type="checkbox" name="categories[]" value="vegetables" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Vegetables</span>
                                    </label>
                                    <label class="flex items-center py-1">
                                        <input type="checkbox" name="categories[]" value="fruits" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Fruits</span>
                                    </label>
                                    <label class="flex items-center py-1">
                                        <input type="checkbox" name="categories[]" value="dairy" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Dairy Products</span>
                                    </label>
                                    <label class="flex items-center py-1">
                                        <input type="checkbox" name="categories[]" value="meat" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Meat & Poultry</span>
                                    </label>
                                    <label class="flex items-center py-1">
                                        <input type="checkbox" name="categories[]" value="seafood" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Seafood</span>
                                    </label>
                                    <label class="flex items-center py-1">
                                        <input type="checkbox" name="categories[]" value="bakery" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Bakery</span>
                                    </label>
                                    <label class="flex items-center py-1">
                                        <input type="checkbox" name="categories[]" value="grains" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Grains & Cereals</span>
                                    </label>
                                    <label class="flex items-center py-1">
                                        <input type="checkbox" name="categories[]" value="herbs" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Herbs & Spices</span>
                                    </label>
                                </div>
                                <button type="button" class="mt-3 text-sm text-blue-600 hover:underline">+ Add new category</button>
                            </div>

                            <!-- Product Tags Section -->
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 border border-gray-200 p-4">
                                <h3 class="text-base font-semibold text-gray-800 mb-4">Product Tags</h3>
                                <input type="text" id="tags" name="tags" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none text-sm" placeholder="Separate tags with commas">
                                <p class="text-xs text-gray-500 mt-2">Example: organic, fresh, local</p>
                            </div>

                        </div>

                    </form>

                </div>
            </main>

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

    const notificationContainer = document.getElementById('notificationPreviewContainer');
    const notificationPreview = document.getElementById('notificationPreview');
    if (notificationContainer && notificationPreview) {
        notificationContainer.addEventListener('mouseenter', function() { clearTimeout(notificationPreviewTimeout); loadRetailerNotificationPreview(); notificationPreview.classList.remove('hidden'); });
        notificationContainer.addEventListener('mouseleave', function() { notificationPreviewTimeout = setTimeout(() => notificationPreview.classList.add('hidden'), HOVER_DELAY); });
        notificationPreview.addEventListener('mouseenter', () => clearTimeout(notificationPreviewTimeout));
        notificationPreview.addEventListener('mouseleave', () => { notificationPreviewTimeout = setTimeout(() => notificationPreview.classList.add('hidden'), HOVER_DELAY); });
    }
    function loadRetailerNotificationBadge() { const badge = document.getElementById('notificationBadge'); if (!badge) return; fetch('../api/get-retailer-notifications.php').then(r => r.json()).then(d => { if (d.success && d.notifications) { const c = d.unreadCount || 0; if (c > 0) { badge.textContent = c; badge.classList.remove('hidden'); } else { badge.classList.add('hidden'); }}}).catch(e => console.error(e)); }
    function loadRetailerNotificationPreview() { const items = document.getElementById('notificationPreviewItems'); if (!items) return; fetch('../api/get-retailer-notifications.php').then(r => r.json()).then(d => { if (d.success && d.notifications && d.notifications.length > 0) { items.innerHTML = d.notifications.slice(0, 5).map(n => { const unread = !n.read ? 'bg-green-50 border-l-4 border-green-500' : ''; const time = getTimeAgo(new Date(n.timestamp)); let icon = 'fa-info-circle', bg = 'bg-blue-100', tc = 'text-blue-700'; if (n.type === 'order') { icon = 'fa-box'; bg = 'bg-green-100'; tc = 'text-green-700'; } else if (n.type === 'stock') { icon = 'fa-exclamation-triangle'; bg = 'bg-yellow-100'; tc = 'text-yellow-700'; } else if (n.type === 'review') { icon = 'fa-star'; bg = 'bg-yellow-100'; tc = 'text-yellow-700'; } const t = escapeHtml(n.title || 'Notification'); const m = escapeHtml(n.message || ''); const l = n.link || 'retailernotifications.php'; return `<a href="${l}" class="block p-3 border-b border-gray-100 hover:bg-gray-50 transition ${unread}" data-notification-id="${n.id}" onclick="markNotificationAsRead(event, ${n.id})"><div class="flex items-start gap-3"><div class="${bg} ${tc} p-2 rounded-full flex-shrink-0"><i class="fas ${icon} text-sm"></i></div><div class="flex-1 min-w-0"><p class="font-medium text-gray-800 text-sm truncate">${t}</p><p class="text-xs text-gray-500 mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${m}</p><span class="text-xs text-gray-400 block mt-1">${time}</span></div>${!n.read ? '<div class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></div>' : ''}</div></a>`; }).join(''); } else { items.innerHTML = '<div class="p-8 text-center text-gray-500"><i class="fas fa-bell text-4xl mb-2 text-gray-300"></i><p class="text-sm">No notifications</p></div>'; }}).catch(e => console.error(e)); }
    function getTimeAgo(date) { const s = Math.floor((new Date() - date) / 1000); if (s < 60) return 'Just now'; if (s < 3600) return `${Math.floor(s / 60)}m ago`; if (s < 86400) return `${Math.floor(s / 3600)}h ago`; if (s < 604800) return `${Math.floor(s / 86400)}d ago`; return date.toLocaleDateString(); }
    function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }
    function markNotificationAsRead(event, notificationId) { fetch('../api/mark-retailer-notification-read.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ notification_id: notificationId }) }).then(response => response.json()).then(data => { if (data.success) { setTimeout(() => { loadRetailerNotificationBadge(); }, 100); } }).catch(error => console.error('Error marking notification as read:', error)); }
    loadRetailerNotificationBadge(); setInterval(loadRetailerNotificationBadge, 5000);

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

    // Tab switching functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.getAttribute('data-tab');
            
            // Update button styles
            tabBtns.forEach(b => {
                b.classList.remove('border-green-600', 'text-green-600');
                b.classList.add('border-transparent', 'text-gray-500');
            });
            btn.classList.add('border-green-600', 'text-green-600');
            btn.classList.remove('border-transparent', 'text-gray-500');
            
            // Show/hide content
            tabContents.forEach(content => {
                if (content.id === targetTab) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });

    // Manage stock checkbox functionality
    const manageStockCheckbox = document.getElementById('manageStock');
    const stockFields = document.getElementById('stockFields');

    manageStockCheckbox.addEventListener('change', () => {
        if (manageStockCheckbox.checked) {
            stockFields.classList.remove('hidden');
        } else {
            stockFields.classList.add('hidden');
        }
    });

    // Word count for description
    const descriptionTextarea = document.getElementById('productDescription');
    const wordCountSpan = document.getElementById('wordCount');

    descriptionTextarea.addEventListener('input', () => {
        const text = descriptionTextarea.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        wordCountSpan.textContent = words;
    });

    // Product image preview
    const productImageInput = document.getElementById('productImage');
    const productImagePreview = document.getElementById('productImagePreview');

    productImageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                productImagePreview.innerHTML = `<img src="${e.target.result}" alt="Product Preview" class="max-h-48 rounded-lg object-cover">`;
            };
            reader.readAsDataURL(file);
        }
    });

    // Product gallery preview
    const productGalleryInput = document.getElementById('productGallery');
    const galleryPreview = document.getElementById('galleryPreview');
    let currentGalleryImages = [];
    const MAX_GALLERY_IMAGES = 4;

    // Handle gallery button click with validation
    function handleGalleryClick() {
        if (currentGalleryImages.length >= MAX_GALLERY_IMAGES) {
            alert(`Maximum ${MAX_GALLERY_IMAGES} images allowed in gallery`);
            return;
        }
        productGalleryInput.click();
    }

    productGalleryInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        
        // Calculate how many more images we can add
        const remainingSlots = MAX_GALLERY_IMAGES - currentGalleryImages.length;
        
        if (files.length > remainingSlots) {
            alert(`You can only add ${remainingSlots} more image(s). Maximum ${MAX_GALLERY_IMAGES} images allowed.`);
            // Take only the allowed number of files
            const allowedFiles = files.slice(0, remainingSlots);
            processGalleryFiles(allowedFiles);
        } else {
            processGalleryFiles(files);
        }
        
        // Reset input
        e.target.value = '';
    });

    function processGalleryFiles(files) {
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                currentGalleryImages.push({
                    file: file,
                    dataUrl: e.target.result
                });
                updateGalleryPreview();
            };
            reader.readAsDataURL(file);
        });
    }

    function updateGalleryPreview() {
        galleryPreview.innerHTML = '';
        
        // Display current images
        currentGalleryImages.forEach((img, index) => {
            const div = document.createElement('div');
            div.className = 'relative border border-gray-300 rounded-lg overflow-hidden';
            div.innerHTML = `
                <img src="${img.dataUrl}" alt="Gallery Image" class="w-full h-20 object-cover">
                <button type="button" onclick="removeGalleryImage(${index})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                    <i class="fas fa-times"></i>
                </button>
            `;
            galleryPreview.appendChild(div);
        });
        
        // Add "Add more" button if under limit
        if (currentGalleryImages.length < MAX_GALLERY_IMAGES) {
            const addMoreDiv = document.createElement('div');
            addMoreDiv.className = 'border-2 border-dashed border-gray-300 rounded-lg p-4 flex items-center justify-center cursor-pointer hover:border-green-500';
            addMoreDiv.innerHTML = '<i class="fas fa-plus text-gray-400"></i>';
            addMoreDiv.onclick = handleGalleryClick;
            galleryPreview.appendChild(addMoreDiv);
        }
    }

    function removeGalleryImage(index) {
        currentGalleryImages.splice(index, 1);
        updateGalleryPreview();
    }

    // Initialize gallery preview
    updateGalleryPreview();
    
    // Global variable for product ID (for editing)
    let editingProductId = null;
    
    // Load product data if editing
    window.addEventListener('DOMContentLoaded', async () => {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        
        if (productId) {
            editingProductId = productId;
            await loadProductData(productId);
        }
    });
    
    // Load product data for editing
    async function loadProductData(productId) {
        try {
            const response = await fetch(`../api/get-product.php?id=${productId}`);
            const result = await response.json();
            
            if (result.success && result.product) {
                const product = result.product;
                
                // Populate form fields
                document.getElementById('productName').value = product.name || '';
                document.getElementById('productDescription').value = product.description || '';
                document.getElementById('shortDescription').value = '';
                document.getElementById('regularPrice').value = product.price || '';
                document.getElementById('salePrice').value = '';
                document.getElementById('sku').value = '';
                document.getElementById('stockQuantity').value = product.stock_quantity || '';
                document.getElementById('unit').value = product.unit || 'kg';
                document.getElementById('tags').value = '';
                
                // Set categories
                if (product.category) {
                    const categories = product.category.split(',');
                    categories.forEach(cat => {
                        const checkbox = document.querySelector(`input[name="categories[]"][value="${cat.trim()}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
                
                // Set product image if exists
                if (product.image_url) {
                    const productImagePreview = document.getElementById('productImagePreview');
                    productImagePreview.innerHTML = `<img src="../${product.image_url}" alt="Product Preview" class="max-h-48 rounded-lg object-cover">`;
                }
                
                // Update page title
                document.title = 'Edit Product - Farmers Mall';
            }
        } catch (error) {
            console.error('Error loading product:', error);
            alert('Error loading product data');
        }
    }

    // Form submission
    document.getElementById('addProductForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        
        // Add product ID if editing
        if (editingProductId) {
            formData.append('product_id', editingProductId);
        }
        
        // Add form fields
        formData.append('product_name', document.getElementById('productName').value);
        formData.append('description', document.getElementById('productDescription').value);
        formData.append('short_description', document.getElementById('shortDescription').value);
        formData.append('regular_price', document.getElementById('regularPrice').value);
        formData.append('sale_price', document.getElementById('salePrice').value);
        formData.append('sku', document.getElementById('sku').value);
        formData.append('stock_quantity', document.getElementById('stockQuantity').value);
        formData.append('unit', document.getElementById('unit').value);
        formData.append('tags', document.getElementById('tags').value);
        
        // Add categories
        const selectedCategories = Array.from(document.querySelectorAll('input[name="categories[]"]:checked'))
            .map(cb => cb.value);
        formData.append('categories', selectedCategories.join(','));
        
        // Add product image if selected
        const productImageInput = document.getElementById('productImage');
        if (productImageInput.files.length > 0) {
            formData.append('product_image', productImageInput.files[0]);
        }
        
        try {
            const response = await fetch('../api/save-product.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(editingProductId ? 'Product updated successfully!' : 'Product created successfully!', 'success');
                setTimeout(() => {
                    window.location.href = 'retailerinventory.php';
                }, 1500);
            } else {
                showToast('Error: ' + (result.message || 'Failed to save product'), 'error');
            }
        } catch (error) {
            console.error('Error saving product:', error);
            showToast('Error saving product. Please try again.', 'error');
        }
    });
    
    // Toast Notification Function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icon = type === 'success' ? '✓' : '✕';
        const iconColor = type === 'success' ? 'text-green-600' : 'text-red-600';
        
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

    // Mobile menu toggle
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
