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
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
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
                        <a href="retailermessage.php" class="text-gray-600 hover:text-green-600"><i class="fa-regular fa-comment"></i></a>
                        <a href="retailernotifications.php" class="text-gray-600 hover:text-green-600 relative">
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
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Vouchers & Coupons Management</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Active Vouchers & Coupons</h3>
                        <div class="p-4 bg-blue-50 border-l-4 border-blue-400 rounded-lg space-y-3">
                            <div class="flex justify-between items-center border-b border-blue-100 pb-3">
                                <div>
                                    <p class="font-bold text-blue-800">FRESH20</p>
                                    <p class="text-sm text-blue-600">20% off all Fresh Produce (Min ₱30 spend)</p>
                                </div>
                                <span class="text-xs text-blue-500 font-medium bg-blue-100 px-2 py-1 rounded">Expires: 2024-11-30</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-blue-100 pb-3">
                                <div>
                                    <p class="font-bold text-blue-800">LOCALSHIP</p>
                                    <p class="text-sm text-blue-600">Free Shipping on orders over ₱50</p>
                                </div>
                                <span class="text-xs text-blue-500 font-medium bg-blue-100 px-2 py-1 rounded">Always Active</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-blue-800">NEWBIE10</p>
                                    <p class="text-sm text-blue-600">Flat ₱10 off first order</p>
                                </div>
                                <span class="text-xs text-blue-500 font-medium bg-blue-100 px-2 py-1 rounded">Expires: 2025-01-01</span>
                            </div>
                        </div>
                        <button onclick="openCouponModal()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 font-medium shadow-md">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Create New Coupon
                        </button>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
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
                                <p class="text-2xl font-bold text-red-600 mt-1">₱4,120.50</p>
                            </div>
                            <div class="border-t pt-4">
                                <p class="text-sm text-gray-600">Avg. Basket Size with Coupon:</p>
                                <p class="text-xl font-bold text-green-700 mt-1">₱45.80</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
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
