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
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l4-4-4-4m0 16l4-4-4-4m-1-5a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Vouchers & Coupons
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
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
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Customer Reviews & Ratings</h2>
                    
                    <!-- Filter Dropdown -->
                    <div class="relative">
                        <button id="filterBtn" onclick="toggleFilterDropdown()" class="flex items-center space-x-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <i class="fas fa-filter text-gray-600"></i>
                            <span class="text-gray-700 font-medium">Filter by Rating</span>
                            <i class="fas fa-chevron-down text-gray-500 text-sm"></i>
                        </button>
                        <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border z-50">
                            <div class="py-2">
                                <button onclick="filterReviews('all')" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition flex items-center justify-between">
                                    <span class="text-gray-700">All Reviews</span>
                                    <span class="text-xs text-gray-500">342</span>
                                </button>
                                <button onclick="filterReviews(5)" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition flex items-center justify-between">
                                    <span class="flex items-center">
                                        <span class="text-yellow-500 mr-2">★★★★★</span>
                                        <span class="text-gray-700">5 Stars</span>
                                    </span>
                                    <span class="text-xs text-gray-500">274</span>
                                </button>
                                <button onclick="filterReviews(4)" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition flex items-center justify-between">
                                    <span class="flex items-center">
                                        <span class="text-yellow-500 mr-2">★★★★</span>
                                        <span class="text-gray-700">4 Stars</span>
                                    </span>
                                    <span class="text-xs text-gray-500">41</span>
                                </button>
                                <button onclick="filterReviews(3)" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition flex items-center justify-between">
                                    <span class="flex items-center">
                                        <span class="text-yellow-500 mr-2">★★★</span>
                                        <span class="text-gray-700">3 Stars</span>
                                    </span>
                                    <span class="text-xs text-gray-500">17</span>
                                </button>
                                <button onclick="filterReviews(2)" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition flex items-center justify-between">
                                    <span class="flex items-center">
                                        <span class="text-yellow-500 mr-2">★★</span>
                                        <span class="text-gray-700">2 Stars</span>
                                    </span>
                                    <span class="text-xs text-gray-500">7</span>
                                </button>
                                <button onclick="filterReviews(1)" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition flex items-center justify-between">
                                    <span class="flex items-center">
                                        <span class="text-yellow-500 mr-2">★</span>
                                        <span class="text-gray-700">1 Star</span>
                                    </span>
                                    <span class="text-xs text-gray-500">3</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Rating Summary</h3>
                        <div class="text-center">
                            <p class="text-6xl font-bold text-green-700">4.7</p>
                            <div class="flex justify-center items-center mt-2 text-yellow-500 text-2xl">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            </div>
                            <p class="text-gray-600 mt-2 text-sm">Based on 342 reviews</p>
                        </div>
                        <div class="space-y-2 mt-6">
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
                                <div id="reply-box-2" class="mb-2">
                                    <textarea id="reply-text-2" class="w-full p-2 border border-gray-300 rounded-lg text-sm mb-2" rows="2" placeholder="Write your response..."></textarea>
                                    <div class="flex space-x-2">
                                        <button onclick="submitReviewResponse(2)" class="px-4 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium">Send Reply</button>
                                        <button onclick="toggleReplyBox(2)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-xs font-medium">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <div class="border-b pb-4 review-item" data-rating="5">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="font-semibold text-gray-800">Ana Cruz</p>
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
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                                    <p class="text-xs text-gray-700"><strong>Your Reply:</strong> Thank you so much, Ana! We're glad you enjoyed our mangoes. 🥭</p>
                                    <span class="text-xs text-gray-500">Replied 1 week ago</span>
                                </div>
                            </div>

                            <div class="border-b pb-4 review-item" data-rating="3">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="font-semibold text-gray-800">Pedro Garcia</p>
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
    
    // Review Response Functions
    let pendingAction = null;
    let pendingData = null;
    
    // Filter Functions
    function toggleFilterDropdown() {
        const dropdown = document.getElementById('filterDropdown');
        dropdown.classList.toggle('hidden');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const filterBtn = document.getElementById('filterBtn');
        const dropdown = document.getElementById('filterDropdown');
        if (!filterBtn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
    
    function filterReviews(rating) {
        const reviewItems = document.querySelectorAll('.review-item');
        
        reviewItems.forEach(item => {
            if (rating === 'all') {
                item.style.display = '';
            } else {
                const itemRating = parseInt(item.getAttribute('data-rating'));
                if (itemRating === rating) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            }
        });
        
        // Update button text
        const filterBtn = document.getElementById('filterBtn');
        if (rating === 'all') {
            filterBtn.innerHTML = '<i class="fas fa-filter text-gray-600"></i><span class="text-gray-700 font-medium">Filter by Rating</span><i class="fas fa-chevron-down text-gray-500 text-sm"></i>';
        } else {
            filterBtn.innerHTML = `<i class="fas fa-filter text-gray-600"></i><span class="text-gray-700 font-medium">${rating} Star${rating > 1 ? 's' : ''}</span><i class="fas fa-chevron-down text-gray-500 text-sm"></i>`;
        }
        
        // Close dropdown
        document.getElementById('filterDropdown').classList.add('hidden');
    }
    
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
                
                showConfirmationModal(
                    'Success',
                    'Your reply has been posted successfully!\n\nDatabase integration pending.',
                    () => {},
                    false
                );
                
                // Uncomment when API is ready:
                // document.getElementById(`reply-text-${reviewId}`).value = '';
                // toggleReplyBox(reviewId);
                // location.reload();
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
</script>
</body>
</html>
