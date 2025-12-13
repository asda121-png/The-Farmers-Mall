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
    <title>Vouchers & Coupons – The Farmer's Mall</title>
    <script src="https://cdn.tailwindcss.com">    
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });
</script>
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
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
                <i class="fas fa-ticket-alt text-lg mr-3"></i>
                Vouchers & Coupons
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-star text-lg mr-3"></i>
                Reviews & Customers
            </a>

            <div class="mt-auto pt-4 border-t border-gray-100">
                <a href="../auth/logout.php" class="w-full flex items-center justify-center p-2 rounded-xl text-red-600 bg-red-50 hover:bg-red-100 transition duration-150 font-medium">
                    <i class="fas fa-sign-out-alt text-sm mr-2"></i>
                    Logout
                </a>
            </div>
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
                            <div id="notificationPreview" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Notifications</h3></div>
                                <div id="notificationPreviewItems" class="max-h-96 overflow-y-auto"><div class="p-8 text-center text-gray-500"><i class="fas fa-bell text-4xl mb-2 text-gray-300"></i><p class="text-sm">No notifications</p></div></div>
                                <div class="p-4 border-t border-gray-100 bg-gray-50"><a href="retailernotifications.php" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition font-medium">View All Notifications</a></div>
                            </div>
                        </div>

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
                    </div>
                </div>
            </header>
        
            <main id="content" class="p-8 transition-all duration-300 flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Vouchers & Coupons Management</h2>
                
                <div class="grid grid-cols-1 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Active Vouchers & Coupons</h3>
                        <div class="p-4 bg-green-50 rounded-lg space-y-3">
                            <div class="flex justify-between items-center border-b border-green-100 pb-3 cursor-pointer hover:bg-green-100 p-3 rounded transition" onclick="showCouponPerformance('FRESH20', 75, 120, 4120.50, 45.80)">
                                <div>
                                    <p class="font-bold text-green-800">FRESH20</p>
                                    <p class="text-sm text-green-600">20% off all Fresh Produce (Min ₱30 spend)</p>
                                </div>
                                <span class="text-xs text-green-500 font-medium bg-green-100 px-2 py-1 rounded">Expires: 2024-11-30</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-green-100 pb-3 cursor-pointer hover:bg-green-100 p-3 rounded transition" onclick="showCouponPerformance('LOCALSHIP', 60, 89, 1850.00, 62.30)">
                                <div>
                                    <p class="font-bold text-green-800">LOCALSHIP</p>
                                    <p class="text-sm text-green-600">Free Shipping on orders over ₱50</p>
                                </div>
                                <span class="text-xs text-green-500 font-medium bg-green-100 px-2 py-1 rounded">Always Active</span>
                            </div>
                            <div class="flex justify-between items-center cursor-pointer hover:bg-green-100 p-3 rounded transition" onclick="showCouponPerformance('NEWBIE10', 45, 56, 560.00, 38.50)">
                                <div>
                                    <p class="font-bold text-green-800">NEWBIE10</p>
                                    <p class="text-sm text-green-600">Flat ₱10 off first order</p>
                                </div>
                                <span class="text-xs text-green-500 font-medium bg-green-100 px-2 py-1 rounded">Expires: 2025-01-01</span>
                            </div>
                        </div>
                        <button onclick="openCouponModal()" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 font-medium shadow-md">
                            <i class="fas fa-plus-circle text-lg mr-1"></i>
                            Create New Coupon
                        </button>
                    </div>
                    
                    <!-- Expired Vouchers & Coupons Section -->
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Expired Vouchers & Coupons</h3>
                        <div class="p-4 bg-red-50 rounded-lg space-y-3">
                            <div class="flex justify-between items-center border-b border-red-100 pb-3 opacity-60">
                                <div>
                                    <p class="font-bold text-red-800">HARVEST15</p>
                                    <p class="text-sm text-red-600">15% off all Harvest Products (Min ₱50 spend)</p>
                                </div>
                                <span class="text-xs text-red-500 font-medium bg-red-100 px-2 py-1 rounded">Expired: 2024-10-31</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-red-100 pb-3 opacity-60">
                                <div>
                                    <p class="font-bold text-red-800">SUMMER30</p>
                                    <p class="text-sm text-red-600">₱30 off orders over ₱100</p>
                                </div>
                                <span class="text-xs text-red-500 font-medium bg-red-100 px-2 py-1 rounded">Expired: 2024-09-15</span>
                            </div>
                            <div class="flex justify-between items-center opacity-60">
                                <div>
                                    <p class="font-bold text-red-800">WELCOME5</p>
                                    <p class="text-sm text-red-600">₱5 off first purchase</p>
                                </div>
                                <span class="text-xs text-red-500 font-medium bg-red-100 px-2 py-1 rounded">Expired: 2024-08-20</span>
                            </div>

                </div>
            </main>
        </div>
    </div>
    
</div>

<!-- Coupon Performance Modal -->
<div id="couponPerformanceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Coupon Performance</h3>
                <button onclick="closeCouponPerformanceModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="mb-6">
                <h4 class="text-base font-bold text-green-800 mb-2" id="performanceCouponCode">FRESH20</h4>
                <p class="text-xs text-gray-600">Performance metrics for the last 30 days</p>
            </div>
            
            <div class="space-y-6">
                <!-- Redemption Rate -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2 font-medium">Redemption Rate</p>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                        <div id="redemptionBar" class="bg-green-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                    <p class="text-sm text-gray-700">
                        <span id="redemptionRate" class="font-bold text-green-700">0%</span> redemption rate 
                        (<span id="usageCount" class="font-semibold">0</span> uses)
                    </p>
                </div>
                
                <!-- Total Discount Given -->
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-xs text-gray-600 mb-1">Total Discount Value Given (Last 30 Days)</p>
                    <p class="text-2xl font-bold text-red-600" id="totalDiscount">₱0.00</p>
                    <p class="text-xs text-gray-500 mt-1">Total amount saved by customers</p>
                </div>
                
                <!-- Average Basket Size -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-xs text-gray-600 mb-1">Average Basket Size with Coupon</p>
                    <p class="text-2xl font-bold text-green-700" id="avgBasket">₱0.00</p>
                    <p class="text-xs text-gray-500 mt-1">Average order value when this coupon is used</p>
                </div>
                
                <!-- Additional Stats -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <p class="text-xs text-gray-600 mb-1">Total Orders</p>
                        <p class="text-xl font-bold text-blue-700" id="totalOrders">0</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <p class="text-xs text-gray-600 mb-1">ROI Impact</p>
                        <p class="text-xl font-bold text-purple-700">+<span id="roiImpact">0</span>%</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-6 border-t bg-gray-50 flex justify-end">
            <button onclick="closeCouponPerformanceModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Coupon Creation Modal -->
<div id="couponModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl hover:shadow-3xl transition-shadow duration-300 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold text-gray-800">Create New Coupon</h3>
                <button onclick="closeCouponModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <form id="couponForm" onsubmit="saveCoupon(event)" class="p-6">
            <div class="space-y-4">
                <!-- Coupon Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code *</label>
                    <input type="text" id="couponCode" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent uppercase"
                        placeholder="e.g., SUMMER25" maxlength="20">
                    <p class="text-xs text-gray-500 mt-1">Letters and numbers only, no spaces</p>
                </div>
                
                <!-- Discount Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                    <select id="discountType" required onchange="updateDiscountLabel()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (₱)</option>
                    </select>
                </div>
                
                <!-- Discount Value -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span id="discountLabel">Discount Percentage</span> *
                    </label>
                    <input type="number" id="discountValue" required min="1" step="0.01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        placeholder="e.g., 20">
                </div>
                
                <!-- Minimum Purchase -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Purchase (₱)</label>
                    <input type="number" id="minPurchase" min="0" step="0.01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        placeholder="Optional - Leave empty for no minimum">
                </div>
                
                <!-- Maximum Discount -->
                <div id="maxDiscountDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount (₱)</label>
                    <input type="number" id="maxDiscount" min="0" step="0.01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        placeholder="Optional - For percentage discounts">
                </div>
                
                <!-- Expiry Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date *</label>
                    <input type="date" id="expiryDate" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                
                <!-- Usage Limit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Usage Limit</label>
                    <input type="number" id="usageLimit" min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        placeholder="Optional - Leave empty for unlimited">
                </div>
                
                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="couponDescription" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        placeholder="Internal notes about this coupon"></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeCouponModal()"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Create Coupon
                </button>
            </div>
        </form>
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
    
    // Coupon Performance Modal Functions
    function showCouponPerformance(code, redemptionRate, usageCount, totalDiscount, avgBasket) {
        // Update modal content
        document.getElementById('performanceCouponCode').textContent = code;
        document.getElementById('redemptionRate').textContent = redemptionRate + '%';
        document.getElementById('redemptionBar').style.width = redemptionRate + '%';
        document.getElementById('usageCount').textContent = usageCount;
        document.getElementById('totalDiscount').textContent = '₱' + totalDiscount.toFixed(2);
        document.getElementById('avgBasket').textContent = '₱' + avgBasket.toFixed(2);
        document.getElementById('totalOrders').textContent = usageCount;
        
        // Calculate ROI impact (simplified example)
        const roiImpact = Math.round((avgBasket * usageCount - totalDiscount) / totalDiscount * 10);
        document.getElementById('roiImpact').textContent = roiImpact;
        
        // Show modal
        document.getElementById('couponPerformanceModal').classList.remove('hidden');
    }
    
    function closeCouponPerformanceModal() {
        document.getElementById('couponPerformanceModal').classList.add('hidden');
    }
    
    // Coupon Modal Functions
    function openCouponModal() {
        document.getElementById('couponModal').classList.remove('hidden');
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('expiryDate').setAttribute('min', today);
    }
    
    function closeCouponModal() {
        document.getElementById('couponModal').classList.add('hidden');
        document.getElementById('couponForm').reset();
    }
    
    function updateDiscountLabel() {
        const discountType = document.getElementById('discountType').value;
        const label = document.getElementById('discountLabel');
        const maxDiscountDiv = document.getElementById('maxDiscountDiv');
        
        if (discountType === 'percentage') {
            label.textContent = 'Discount Percentage';
            maxDiscountDiv.style.display = 'block';
        } else {
            label.textContent = 'Discount Amount (₱)';
            maxDiscountDiv.style.display = 'none';
        }
    }
    
    function saveCoupon(event) {
        event.preventDefault();
        
        const couponData = {
            code: document.getElementById('couponCode').value.toUpperCase().trim(),
            discountType: document.getElementById('discountType').value,
            discountValue: parseFloat(document.getElementById('discountValue').value),
            minPurchase: document.getElementById('minPurchase').value ? parseFloat(document.getElementById('minPurchase').value) : null,
            maxDiscount: document.getElementById('maxDiscount').value ? parseFloat(document.getElementById('maxDiscount').value) : null,
            expiryDate: document.getElementById('expiryDate').value,
            usageLimit: document.getElementById('usageLimit').value ? parseInt(document.getElementById('usageLimit').value) : null,
            description: document.getElementById('couponDescription').value.trim()
        };
        
        // Validate coupon code format
        if (!/^[A-Z0-9]+$/.test(couponData.code)) {
            alert('Coupon code must contain only letters and numbers, no spaces or special characters.');
            return;
        }
        
        console.log('Creating new coupon:', couponData);
        
        // TODO: Make API call to create coupon
        // Example:
        // fetch('../api/create-coupon.php', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify(couponData)
        // }).then(response => response.json())
        //   .then(data => {
        //       if (data.success) {
        //           closeCouponModal();
        //           location.reload();
        //       } else {
        //           alert('Error: ' + data.message);
        //       }
        //   });
        
        alert(`Coupon "${couponData.code}" will be created with ${couponData.discountType === 'percentage' ? couponData.discountValue + '%' : '₱' + couponData.discountValue} discount.\n\nExpires: ${couponData.expiryDate}\nDatabase integration pending.`);
        
        // Uncomment when API is ready:
        // closeCouponModal();
        // location.reload();
    }
    
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
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
