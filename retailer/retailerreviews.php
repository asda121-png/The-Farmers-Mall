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
    <title>Reviews & Customers – The Farmer's Mall</title>
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
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
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
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Customer Reviews & Ratings</h2>
                    
                    <!-- Filter Dropdown -->
                    <div class="relative min-w-[180px]">
                        <select id="rating-filter" onchange="filterReviews(this.value)" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white appearance-none pr-10">
                            <option value="all">Filter by Rating</option>
                            <option value="5">5 Stars (274)</option>
                            <option value="4">4 Stars (41)</option>
                            <option value="3">3 Stars (17)</option>
                            <option value="2">2 Stars (7)</option>
                            <option value="1">1 Star (3)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 h-fit">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Rating Summary</h3>
                        <div class="text-center">
                            <p class="text-5xl font-bold text-green-700">4.7</p>
                            <div class="flex justify-center items-center mt-2 text-yellow-500 text-xl">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            </div>
                            <p class="text-gray-600 mt-2 text-sm">Based on 342 reviews</p>
                        </div>
                        <div class="space-y-2 mt-4">
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 w-12">5★</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mx-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 80%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">274</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 w-12">4★</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mx-2">
                                    <div class="bg-yellow-400 h-2 rounded-full" style="width: 12%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">41</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 w-12">3★</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mx-2">
                                    <div class="bg-yellow-300 h-2 rounded-full" style="width: 5%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">17</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 w-12">2★</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mx-2">
                                    <div class="bg-orange-400 h-2 rounded-full" style="width: 2%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">7</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 w-12">1★</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mx-2">
                                    <div class="bg-red-500 h-2 rounded-full" style="width: 1%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">3</span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Recent Reviews</h3>
                        <div id="reviewsContainer" class="space-y-6">
                            <div class="border-b pb-4 review-item" data-rating="5">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="font-semibold text-gray-800">Maria Santos</p>
                                        <p class="text-xs text-gray-500">Product: <span class="text-green-600 font-medium">Organic Tomatoes</span></p>
                                        <div class="flex items-center text-yellow-500 text-sm mt-1">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">2 days ago</span>
                                </div>
                                <p class="text-gray-600 text-sm mb-3">"Fresh vegetables, quick delivery! Highly recommend the organic tomatoes."</p>
                                <div id="reply-box-1" class="hidden mb-2">
                                    <textarea id="reply-text-1" class="w-full p-2 border border-gray-300 rounded-lg text-sm mb-2" rows="2" placeholder="Write your response..."></textarea>
                                    <div class="flex space-x-2">
                                        <button onclick="submitReviewResponse(1)" class="px-4 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium">Send Reply</button>
                                        <button onclick="toggleReplyBox(1)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-xs font-medium">Cancel</button>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button onclick="toggleReplyBox(1)" class="text-xs text-blue-600 hover:underline">Reply</button>
                                    <button onclick="markHelpful(1)" class="text-xs text-gray-500 hover:underline">Mark as helpful</button>
                                </div>
                            </div>

                            <div class="border-b pb-4 review-item" data-rating="4">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="font-semibold text-gray-800">John Reyes</p>
                                        <p class="text-xs text-gray-500">Product: <span class="text-green-600 font-medium">Fresh Carrots</span></p>
                                        <div class="flex items-center text-yellow-500 text-sm mt-1">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">5 days ago</span>
                                </div>
                                <p class="text-gray-600 text-sm mb-3">"Good quality, slightly delayed delivery but worth the wait."</p>
                                <div id="reply-box-2" class="hidden mb-2">
                                    <textarea id="reply-text-2" class="w-full p-2 border border-gray-300 rounded-lg text-sm mb-2" rows="2" placeholder="Write your response..."></textarea>
                                    <div class="flex space-x-2">
                                        <button onclick="submitReviewResponse(2)" class="px-4 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium">Send Reply</button>
                                        <button onclick="toggleReplyBox(2)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-xs font-medium">Cancel</button>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button onclick="toggleReplyBox(2)" class="text-xs text-blue-600 hover:underline">Reply</button>
                                    <button onclick="markHelpful(2)" class="text-xs text-gray-500 hover:underline">Mark as helpful</button>
                                </div>
                            </div>

                            <div class="border-b pb-4 review-item" data-rating="5">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="font-semibold text-gray-800">Ana Cruz</p>
                                        <p class="text-xs text-gray-500">Product: <span class="text-green-600 font-medium">Fresh Mangoes</span></p>
                                        <div class="flex items-center text-yellow-500 text-sm mt-1">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">1 week ago</span>
                                </div>
                                <p class="text-gray-600 text-sm mb-3">"Amazing service! The mangoes were perfectly ripe. Will order again!"</p>
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded mb-2">
                                    <p class="text-xs text-gray-700"><strong>Your Reply:</strong> Thank you so much, Ana! We're glad you enjoyed our mangoes. 🥭</p>
                                    <span class="text-xs text-gray-500">Replied 1 week ago</span>
                                </div>
                                <div id="reply-box-5" class="hidden mb-2">
                                    <textarea id="reply-text-5" class="w-full p-2 border border-gray-300 rounded-lg text-sm mb-2" rows="2" placeholder="Write your response..."></textarea>
                                    <div class="flex space-x-2">
                                        <button onclick="submitReviewResponse(5)" class="px-4 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium">Send Reply</button>
                                        <button onclick="toggleReplyBox(5)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-xs font-medium">Cancel</button>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button onclick="toggleReplyBox(5)" class="text-xs text-blue-600 hover:underline">Reply</button>
                                    <button onclick="markHelpful(5)" class="text-xs text-gray-500 hover:underline">Mark as helpful</button>
                                </div>
                            </div>

                            <div class="border-b pb-4 review-item" data-rating="3">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="font-semibold text-gray-800">Pedro Garcia</p>
                                        <p class="text-xs text-gray-500">Product: <span class="text-green-600 font-medium">Mixed Vegetables</span></p>
                                        <div class="flex items-center text-yellow-500 text-sm mt-1">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">2 weeks ago</span>
                                </div>
                                <p class="text-gray-600 text-sm mb-3">"Average experience. Products were okay but packaging could be better."</p>
                                <div id="reply-box-3" class="hidden mb-2">
                                    <textarea id="reply-text-3" class="w-full p-2 border border-gray-300 rounded-lg text-sm mb-2" rows="2" placeholder="Write your response..."></textarea>
                                    <div class="flex space-x-2">
                                        <button onclick="submitReviewResponse(3)" class="px-4 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium">Send Reply</button>
                                        <button onclick="toggleReplyBox(3)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-xs font-medium">Cancel</button>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button onclick="toggleReplyBox(3)" class="text-xs text-blue-600 hover:underline">Reply</button>
                                    <button onclick="markHelpful(3)" class="text-xs text-gray-500 hover:underline">Mark as helpful</button>
                                </div>
                            </div>

                            <div class="border-b pb-4 review-item" data-rating="2">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="font-semibold text-gray-800">Linda Gomez</p>
                                        <div class="flex items-center text-yellow-500 text-sm mt-1">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">3 weeks ago</span>
                                </div>
                                <p class="text-gray-600 text-sm mb-3">"Delivery was very late and some items were damaged."</p>
                                <div id="reply-box-4" class="hidden mb-2">
                                    <textarea id="reply-text-4" class="w-full p-2 border border-gray-300 rounded-lg text-sm mb-2" rows="2" placeholder="Write your response..."></textarea>
                                    <div class="flex space-x-2">
                                        <button onclick="submitReviewResponse(4)" class="px-4 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium">Send Reply</button>
                                        <button onclick="toggleReplyBox(4)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-xs font-medium">Cancel</button>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button onclick="toggleReplyBox(4)" class="text-xs text-blue-600 hover:underline">Reply</button>
                                    <button onclick="markHelpful(4)" class="text-xs text-gray-500 hover:underline">Mark as helpful</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Confirmation Modal for Review Actions -->
    <div id="confirmationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl hover:shadow-3xl transition-shadow duration-300 max-w-md w-full">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800" id="confirmModalTitle">Confirm Action</h3>
            </div>
            
            <div class="p-6">
                <p class="text-gray-600" id="confirmModalMessage">Are you sure you want to proceed?</p>
            </div>
            
            <div class="p-6 border-t bg-gray-50 flex justify-end space-x-3">
                <button onclick="closeConfirmationModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button id="confirmActionBtn" onclick="executeConfirmedAction()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Confirm
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
    
    // Review Response Functions
    let pendingAction = null;
    let pendingData = null;
    
    function toggleReplyBox(reviewId) {
        const replyBox = document.getElementById(`reply-box-${reviewId}`);
        if (replyBox.classList.contains('hidden')) {
            replyBox.classList.remove('hidden');
        } else {
            replyBox.classList.add('hidden');
            // Clear textarea when canceling
            document.getElementById(`reply-text-${reviewId}`).value = '';
        }
    }
    
    function submitReviewResponse(reviewId) {
        const replyText = document.getElementById(`reply-text-${reviewId}`).value.trim();
        
        if (!replyText) {
            showConfirmationModal(
                'Missing Reply Text',
                'Please write a response before sending.',
                () => {},
                false
            );
            return;
        }
        
        // Show confirmation modal
        showConfirmationModal(
            'Send Reply',
            `Send this reply to the customer?\n\n"${replyText}"`,
            () => {
                console.log(`Submitting reply for review ${reviewId}:`, replyText);
                
                // TODO: Make API call to save review response
                // fetch('../api/submit-review-response.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ reviewId, response: replyText })
                // }).then(response => response.json())
                //   .then(data => {
                //       if (data.success) {
                //           location.reload();
                //       }
                //   });
                
                // Create and display the reply
                const replyBox = document.getElementById(`reply-box-${reviewId}`);
                const reviewItem = replyBox.closest('.review-item');
                
                // Check if there's already a reply display area
                let replyDisplay = reviewItem.querySelector('.reply-display');
                if (!replyDisplay) {
                    // Create new reply display area
                    replyDisplay = document.createElement('div');
                    replyDisplay.className = 'bg-blue-50 border-l-4 border-blue-500 p-3 rounded mb-2 reply-display';
                    replyDisplay.innerHTML = `
                        <p class="text-xs text-gray-700"><strong>Your Reply:</strong> ${replyText}</p>
                        <span class="text-xs text-gray-500">Replied just now</span>
                    `;
                    // Insert before reply box
                    reviewItem.insertBefore(replyDisplay, replyBox);
                } else {
                    // Update existing reply display
                    replyDisplay.innerHTML = `
                        <p class="text-xs text-gray-700"><strong>Your Reply:</strong> ${replyText}</p>
                        <span class="text-xs text-gray-500">Replied just now</span>
                    `;
                }
                
                // Clear textarea and hide reply box
                document.getElementById(`reply-text-${reviewId}`).value = '';
                toggleReplyBox(reviewId);
                
                showConfirmationModal(
                    'Success',
                    'Your reply has been posted successfully!\n\nDatabase integration pending.',
                    () => {},
                    false
                );
                
                // TODO: Make API call when ready
                // When API is ready, uncomment location.reload() to refresh from database
            }
        );
    }
    
    function markHelpful(reviewId) {
        console.log(`Marking review ${reviewId} as helpful`);
        
        showConfirmationModal(
            'Mark as Helpful',
            'Mark this review as helpful? This will help prioritize positive feedback.',
            () => {
                // TODO: Make API call to mark review as helpful
                // fetch('../api/mark-review-helpful.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ reviewId })
                // }).then(response => response.json())
                //   .then(data => {
                //       if (data.success) {
                //           showSuccessModal();
                //       }
                //   });
                
                showConfirmationModal(
                    'Success',
                    'Review marked as helpful!\n\nDatabase integration pending.',
                    () => {},
                    false
                );
            }
        );
    }
    
    // Confirmation Modal Functions
    function showConfirmationModal(title, message, onConfirm, showConfirmBtn = true) {
        document.getElementById('confirmModalTitle').textContent = title;
        document.getElementById('confirmModalMessage').textContent = message;
        
        const confirmBtn = document.getElementById('confirmActionBtn');
        if (showConfirmBtn) {
            confirmBtn.style.display = 'block';
            confirmBtn.textContent = 'Confirm';
            pendingAction = onConfirm;
        } else {
            confirmBtn.style.display = 'none';
            pendingAction = null;
        }
        
        document.getElementById('confirmationModal').classList.remove('hidden');
    }
    
    function closeConfirmationModal() {
        document.getElementById('confirmationModal').classList.add('hidden');
        pendingAction = null;
        pendingData = null;
    }
    
    function executeConfirmedAction() {
        if (pendingAction && typeof pendingAction === 'function') {
            pendingAction();
        }
        closeConfirmationModal();
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
    
    // Filter Dropdown and Reviews Filter Functions
    function toggleFilterDropdown() {
        const filterDropdown = document.getElementById('filterDropdown');
        filterDropdown.classList.toggle('hidden');
    }
    
    function filterReviews(rating) {
        const reviewsContainer = document.getElementById('reviewsContainer');
        const reviewItems = reviewsContainer.querySelectorAll('.review-item');
        
        // Close the filter dropdown
        document.getElementById('filterDropdown').classList.add('hidden');
        
        if (rating === 'all') {
            // Show all reviews
            reviewItems.forEach(item => {
                item.style.display = 'block';
            });
        } else {
            // Filter by rating
            reviewItems.forEach(item => {
                const itemRating = parseInt(item.getAttribute('data-rating'));
                if (itemRating === rating) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    }
    
    // Close filter dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const filterBtn = document.getElementById('filterBtn');
        const filterDropdown = document.getElementById('filterDropdown');
        
        if (!filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
            filterDropdown.classList.add('hidden');
        }
    });
</script>
</body>
</html>
