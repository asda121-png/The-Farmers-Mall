<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data from session
$full_name = $_SESSION['full_name'] ?? 'Guest User';
$email = $_SESSION['email'] ?? 'user@email.com';
$user_id = $_SESSION['user_id'] ?? null;

// Fetch profile picture from database
$profile_picture = '';
if ($user_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    $api = getSupabaseAPI();
    $users = $api->select('users', ['id' => $user_id]);
    if (!empty($users)) {
        $profile_picture = $users[0]['profile_picture'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Farmers Mall - Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    .category-text {
        margin-top: 0.5rem; /* Adjusts spacing to the upper */
    }

    /* Smooth transitions for all elements */
    * {
        transition: all 0.3s ease;
    }

    /* Header icon hover effects */
    header a:hover i {
        transform: scale(1.2);
        color: #2E7D32;
    }

    /* Search input hover and focus effects */
    input[type="text"] {
        transition: all 0.3s ease;
    }

    input[type="text"]:hover {
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.1);
        border-color: #2E7D32;
    }

    /* Profile image hover effect */
    header img:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
    }

    /* Hero button hover effect */
    .hero-btn {
        position: relative;
        overflow: hidden;
    }

    .hero-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .hero-btn:hover::before {
        width: 300px;
        height: 300px;
    }

    /* Category hover effects - subtle */
    .category-item {
      transition: all 0.3s ease;
    }

    .category-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(46, 125, 50, 0.15);
    }

    .category-item:hover i {
      color: #1B5E20;
    }

    .category-item:hover p {
      color: #1B5E20;
    }

    /* Product card static (no hover effects) */
    .product-card {
        position: relative;
    }

    /* Make product cards vertical rectangles: taller image and card height */
    .product-card {
      display: flex;
      flex-direction: column;
      min-height: 20rem;
      border: 2px solid transparent;
      border-radius: 0.5rem;
      transition: all 0.3s ease;
      position: relative;
    }

    .product-card:hover {
      border-color: #2E7D32;
    }

    .product-card > img {
      height: 12rem;
      width: 100%;
      object-fit: cover;
      flex-shrink: 0;
    }

    .product-card > div {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 1rem;
    }

    .product-card > div > div {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.5rem;
    }

    .product-card h3,
    .product-card p {
        position: relative;
        z-index: 1;
    }

    /* Add to cart button enhanced effects */
    .add-btn {
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        z-index: 2;
    }

    .add-btn:hover {
        transform: rotate(90deg) scale(1.2);
        box-shadow: 0 4px 15px rgba(46, 125, 50, 0.5);
    }

    .add-btn:active {
        transform: rotate(90deg) scale(0.9);
    }

    /* Shop card hover effects */
    .shop-card {
        transition: all 0.4s ease;
        overflow: hidden;
    }

    .shop-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(46, 125, 50, 0.2);
    }

    .shop-card img {
        transition: all 0.5s ease;
    }

    .shop-card:hover img {
        transform: scale(1.2) rotate(2deg);
    }

    .shop-card:hover h3 {
        color: #2E7D32;
    }

    /* Footer links hover effect */
    footer a {
        position: relative;
        transition: all 0.3s ease;
    }

    footer a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background-color: #4CAF50;
        transition: width 0.3s ease;
    }

    footer a:hover::after {
        width: 100%;
    }

    footer a:hover {
        color: #4CAF50;
        transform: translateX(5px);
    }

    /* Social media icons hover effect */
    footer .fab {
        transition: all 0.3s ease;
    }

    footer .fab:hover {
        transform: translateY(-5px) scale(1.3);
        color: #4CAF50;
    }

    /* Arrow link hover effect */
    .arrow-link {
        transition: all 0.3s ease;
    }

    .arrow-link:hover {
        transform: translateX(5px);
    }

    /* Pulse animation for new badges */
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }

    /* Shimmer effect on hover */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }

    .shimmer-effect:hover::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.3),
            transparent
        );
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        pointer-events: none;
    }

    /* Section heading underline effect */
    .section-heading {
        position: relative;
        display: inline-block;
    }

    .section-heading::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #2E7D32, #4CAF50);
        transition: width 0.4s ease;
    }

    .section-heading:hover::after {
        width: 100%;
    }

    /* Hero slider styles */
    .hero-slider {
        position: relative;
        overflow: hidden;
    }

    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1.5s ease-in-out;
        background-size: cover;
        background-position: center;
    }

    .hero-slide.active {
        opacity: 1;
        position: relative;
    }

    .hero-content {
        position: relative;
        z-index: 10;
    }

    /* Slider navigation dots */
    .slider-dots {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 20;
    }

    .slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .slider-dot.active {
        background: #fff;
        transform: scale(1.2);
    }

    .slider-dot:hover {
        background: rgba(255, 255, 255, 0.8);
    }

    /* Notification dropdown styles */
    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        mt: 8px;
        width: 320px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 50;
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s ease;
    }

    .notification-item:hover {
        background-color: #f9f9f9;
    }

    .notification-item.unread {
        background-color: #f0f9f5;
        border-left: 4px solid #4CAF50;
    }

    .notification-item-title {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .notification-item-message {
        font-size: 12px;
        color: #666;
        margin-bottom: 4px;
    }

    .notification-item-time {
        font-size: 11px;
        color: #999;
    }

    .notification-empty {
        padding: 24px 16px;
        text-align: center;
        color: #999;
        font-size: 14px;
    }

    .notification-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e0e0e0;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-clear-btn {
        font-size: 12px;
        color: #2E7D32;
        cursor: pointer;
        hover: color #4CAF50;
    }
  
  </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

  <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                <i class="fas fa-leaf text-white text-lg"></i>
            </div>
            <h1 class="text-xl font-bold" style="color: #2E7D32;">Farmers Mall</h1>
        </div>

        <div class="flex-1 mx-6">
            <form action="products.php" method="GET">
                <input
                    type="text"
                    name="search"
                    placeholder="Search for fresh product..."
                    class="w-full px-4 py-2 border rounded-full focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </form>
        </div>

        <div class="flex items-center space-x-6">

            <a href="message.php" class="text-gray-600">
                <i class="fa-regular fa-comment"></i> 
            </a>

            <!-- ************ NOTIFICATION DROPDOWN START ************ -->
            <div class="relative inline-block text-left">
                <button id="notificationDropdownBtn" class="text-gray-600 hover:text-gray-800 relative">
                    <i class="fa-regular fa-bell"></i>
                    <span id="notificationBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
                </button>

                <div id="notificationDropdown" class="hidden notification-dropdown">
                    <div class="notification-header">
                        <span>Notifications</span>
                        <button id="clearNotifications" class="notification-clear-btn text-xs">Clear All</button>
                    </div>
                    <div id="notificationList" class="notification-empty">No notifications</div>
                </div>
            </div>
            <!-- ************ NOTIFICATION DROPDOWN END ************ -->

            <a href="cart.php" class="text-gray-600 relative inline-block">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="cartBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
            </a>

            <!-- ************ PROFILE DROPDOWN START ************ -->
            <div class="relative inline-block text-left">
                <button id="profileDropdownBtn" class="flex items-center">
                    <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars('../' . $profile_picture); ?>"
                             alt="Profile"
                             class="w-8 h-8 rounded-full cursor-pointer object-cover">
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    <?php endif; ?>
                </button>

                <div id="profileDropdown"
                     class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                    <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                    <a href="profile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a> 
                    <a href="..\auth\login.php" id="logoutLink" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                </div>
            </div>
            <!-- ************ PROFILE DROPDOWN END ************ -->

        </div>
    </div>
</header>

<!-- JS for dropdown -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdown');
        const logoutLink = document.getElementById('logoutLink');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
            });
        }

        if (logoutLink) {
            logoutLink.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = logoutLink.href;
                }
            });
        }

        document.addEventListener('click', (e) => {
            if (profileMenu && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });

        // --- Notification Dropdown Logic ---
        const notificationBtn = document.getElementById('notificationDropdownBtn');
        const notificationMenu = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const notificationBadge = document.getElementById('notificationBadge');
        const clearNotificationsBtn = document.getElementById('clearNotifications');

        if (notificationBtn && notificationMenu) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationMenu.classList.toggle('hidden');
                loadNotifications();
            });
        }

        document.addEventListener('click', (e) => {
            if (notificationMenu && !notificationMenu.contains(e.target) && !notificationBtn.contains(e.target)) {
                notificationMenu.classList.add('hidden');
            }
        });

        function loadNotifications() {
            const notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
            
            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                return;
            }

            notificationList.innerHTML = notifications.map((notif, idx) => {
                const time = new Date(notif.timestamp || Date.now());
                const timeAgo = getTimeAgo(time);
                const unreadClass = notif.read ? '' : 'unread';
                return `
                    <div class="notification-item ${unreadClass}" style="cursor: pointer;" onclick="goToNotificationPage(${idx})">
                        <div class="notification-item-title">${notif.title || 'Notification'}</div>
                        <div class="notification-item-message">${notif.message || ''}</div>
                        <div class="notification-item-time">${timeAgo}</div>
                    </div>
                `;
            }).join('');
        }

        function getTimeAgo(date) {
            const seconds = Math.floor((new Date() - date) / 1000);
            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
            if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
            return `${Math.floor(seconds / 86400)}d ago`;
        }

        window.markAsRead = function(idx) {
            const notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
            if (notifications[idx]) {
                notifications[idx].read = true;
                localStorage.setItem('userNotifications', JSON.stringify(notifications));
                updateNotificationBadge();
                loadNotifications();
            }
        };

        window.goToNotificationPage = function(idx) {
            const notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
            if (notifications[idx]) {
                notifications[idx].read = true;
                localStorage.setItem('userNotifications', JSON.stringify(notifications));
                updateNotificationBadge();
            }
            window.location.href = 'notification.php';
        };

        function updateNotificationBadge() {
            const notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
            const unreadCount = notifications.filter(n => !n.read).length;
            if (notificationBadge) {
                notificationBadge.textContent = unreadCount;
                notificationBadge.classList.toggle('hidden', unreadCount === 0);
            }
        }

        if (clearNotificationsBtn) {
            clearNotificationsBtn.addEventListener('click', (e) => {
                e.preventDefault();
                localStorage.removeItem('userNotifications');
                notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                updateNotificationBadge();
            });
        }

        // --- Notification Badge Logic (from storage) ---
        const notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
        const unreadCount = notifications.filter(n => !n.read).length;
        if (notificationBadge && unreadCount > 0) {
            notificationBadge.textContent = unreadCount;
            notificationBadge.classList.remove('hidden');
        }
    });
</script>


  <section class="hero-slider relative">
    <!-- Slide 1 -->
    <div class="hero-slide active" style="background-image: url('../images/img.png');">
      <div class="bg-black bg-opacity-40">
        <div class="hero-content max-w-7xl mx-auto px-6 py-32 text-left text-white">
          <h2 class="text-4xl md:text-5xl font-extrabold mb-4">Fresh Harvest Sale</h2>
          <p class="text-lg md:text-xl mb-6">Up to 30% off on organic produce</p>
          <a href="products.php" class="hero-btn inline-block bg-green-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition">
            Shop Now
          </a>
        </div>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="hero-slide" style="background-image: url('../images/img1.png');">
      <div class="bg-black bg-opacity-40">
        <div class="hero-content max-w-7xl mx-auto px-6 py-32 text-left text-white">
          <h2 class="text-4xl md:text-5xl font-extrabold mb-4">Farm Fresh Daily</h2>
          <p class="text-lg md:text-xl mb-6">Organic vegetables & herbs from local farms</p>
          <a href="../user/products.php" class="hero-btn inline-block bg-green-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition">
            Shop Now
          </a>
        </div>
      </div>
    </div>

    <!-- Slide 3 -->
    <div class="hero-slide" style="background-image: url('../images/img2.png');">
      <div class="bg-black bg-opacity-40">
        <div class="hero-content max-w-7xl mx-auto px-6 py-32 text-left text-white">
          <h2 class="text-4xl md:text-5xl font-extrabold mb-4">Premium Quality</h2>
          <p class="text-lg md:text-xl mb-6">Fresh ingredients delivered to your door</p>
          <a href="products.php" class="hero-btn inline-block bg-green-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition">
            Shop Now
          </a>
        </div>
      </div>
    </div>

    <!-- Navigation Dots -->
    <div class="slider-dots">
      <span class="slider-dot active" data-slide="0"></span>
      <span class="slider-dot" data-slide="1"></span>
      <span class="slider-dot" data-slide="2"></span>
    </div>
  </section>

  <section class="w-full mx-auto px-0 py-10 bg-[#FFFFFF] mb-10">
    <div class="flex justify-center mb-8">
      <h2 class="section-heading text-2xl font-bold">Shop by Category</h2>
    </div>
    <div class="flex flex-wrap justify-center gap-4 max-w-7xl mx-auto px-6">
      <a href="products.php?category=vegetables" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-carrot text-green-600 text-2xl"></i>
        <p class="text-gray-700 mt-2 text-xs category-text">Vegetables</p>
      </a>
      <a href="products.php?category=fruits" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-apple-whole text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Fruits</p>
      </a>
      <a href="products.php?category=meat" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-drumstick-bite text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Meat</p>
      </a>
      <a href="products.php?category=seafood" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-fish text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Seafood</p>
      </a>
      <a href="products.php?category=dairy" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-cheese text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Dairy</p>
      </a>
      <a href="products.php?category=bakery" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-bread-slice text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Bakery</p>
      </a>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-6 pt-2 pb-8">
    <div class="flex justify-between items-center mb-4">
      <h2 class="section-heading text-2xl font-bold">Top Products</h2>
    </div>
    <?php
    // Fetch products from Supabase
    require_once __DIR__ . '/../config/supabase-api.php';
    $api = getSupabaseAPI();
    $products = $api->select('products') ?: [];

    function resolveImagePath($img) {
        if (empty($img)) return '../images/products/placeholder.png';
        if (preg_match('#^https?://#i', $img)) return $img;
        if (strpos($img, '../') === 0) return $img;
        if (file_exists(__DIR__ . '/../' . $img)) return '../' . $img;
        if (file_exists(__DIR__ . '/../images/products/' . $img)) return '../images/products/' . $img;
        return $img;
    }

    function formatPriceValue($p) {
        if (is_null($p) || $p === '') return '0.00';
        if (is_numeric($p)) return number_format((float)$p, 2, '.', '');
        $clean = preg_replace('/[^0-9\.]/', '', $p);
        return $clean === '' ? '0.00' : number_format((float)$clean, 2, '.', '');
    }

    // Sort by units_sold (or times_ordered) to get top products
    usort($products, function($a, $b) {
        $aSold = (int)($a['units_sold'] ?? $a['times_ordered'] ?? $a['qty_sold'] ?? 0);
        $bSold = (int)($b['units_sold'] ?? $b['times_ordered'] ?? $b['qty_sold'] ?? 0);
        return $bSold <=> $aSold;
    });

    // Top 5 most sold products
    $topProducts = array_slice($products, 0, 5);
    // Remaining products (all others) - limit to 32 for homepage
    $otherProducts = array_slice($products, 5, 70);
    ?>

    <!-- Top 5 Most Sold Products -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
      <?php foreach ($topProducts as $prod):
        $name = htmlspecialchars($prod['name'] ?? $prod['product_name'] ?? 'Product');
        $priceVal = formatPriceValue($prod['price'] ?? $prod['amount'] ?? $prod['price_value'] ?? '0');
        $img = htmlspecialchars(resolveImagePath($prod['image'] ?? $prod['image_url'] ?? $prod['product_image'] ?? $prod['image_path'] ?? ''));
        $desc = htmlspecialchars($prod['description'] ?? '');
        $category = htmlspecialchars($prod['category'] ?? 'other');
        $id = htmlspecialchars($prod['id'] ?? '');
      ?>
      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg transition relative block overflow-hidden" data-name="<?php echo $name; ?>" data-price="<?php echo $priceVal; ?>" data-img="<?php echo $img; ?>" data-description="<?php echo $desc; ?>" data-category="<?php echo $category; ?>" data-id="<?php echo $id; ?>">
        <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" class="w-full h-32 object-cover" loading="lazy">
        <div>
          <div>
            <div>
              <h3 class="mt-2 font-semibold text-sm"><?php echo $name; ?></h3>
              <p class="text-green-600 font-bold text-sm">₱<?php echo number_format((float)$priceVal, 2); ?></p>
            </div>
            <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart">
              <i class="fa-solid fa-plus"></i>
            </button>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- All Products Section -->
  <section class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-4">
      <h2 class="section-heading text-2xl font-bold">All Products</h2>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
      <?php foreach ($otherProducts as $prod):
        $name = htmlspecialchars($prod['name'] ?? $prod['product_name'] ?? 'Product');
        $priceVal = formatPriceValue($prod['price'] ?? $prod['amount'] ?? $prod['price_value'] ?? '0');
        $img = htmlspecialchars(resolveImagePath($prod['image'] ?? $prod['image_url'] ?? $prod['product_image'] ?? $prod['image_path'] ?? ''));
        $desc = htmlspecialchars($prod['description'] ?? '');
        $category = htmlspecialchars($prod['category'] ?? 'other');
        $id = htmlspecialchars($prod['id'] ?? '');
      ?>
      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg transition relative block overflow-hidden" data-name="<?php echo $name; ?>" data-price="<?php echo $priceVal; ?>" data-img="<?php echo $img; ?>" data-description="<?php echo $desc; ?>" data-category="<?php echo $category; ?>" data-id="<?php echo $id; ?>">
        <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" class="w-full h-32 object-cover" loading="lazy">
        <div>
          <div>
            <div>
              <h3 class="mt-2 font-semibold text-sm"><?php echo $name; ?></h3>
              <p class="text-green-600 font-bold text-sm">₱<?php echo number_format((float)$priceVal, 2); ?></p>
            </div>
            <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart">
              <i class="fa-solid fa-plus"></i>
            </button>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="flex justify-center mt-6">
      <a href="products.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow">See More</a>
    </div>
  </section>
  

  <section class="max-w-7xl mx-auto px-6 pt-20 pb-8">
    <h2 class="section-heading text-2xl font-bold mb-6">Explore Other Shops</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <a href="shop-products.php?shop=Mesa Farm" class="shop-card bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
        <img src="../images/img1.png" alt="Mesa Farm" class="w-full h-40 object-cover">
        <div class="p-4">
          <h3 class="font-bold">Mesa Farm</h3>
          <p class="text-sm text-gray-600">Organic vegetables & herbs</p>
          <p class="text-green-600 mt-2">★ 4.8</p>
        </div>
      </a>

      <a href="shop-products.php?shop=Taco Bell" class="shop-card bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
        <img src="../images/img3.png" alt="Taco Bell" class="w-full h-40 object-cover">
        <div class="p-4">
          <h3 class="font-bold">Taco Bell</h3>
          <p class="text-sm text-gray-600">Fresh Mexican ingredients</p>
          <p class="text-green-600 mt-2">★ 4.5</p>
        </div>
      </a>

      <a href="shop-products.php?shop=Jay's Artisan" class="shop-card bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
        <img src="../images/img2.png" alt="Jay's Artisan" class="w-full h-40 object-cover">
        <div class="p-4">
          <h3 class="font-bold">Jay's Artisan</h3>
          <p class="text-sm text-gray-600">Coffees and bread</p>
          <p class="text-green-600 mt-2">★ 4.9</p>
        </div>
      </a>

      <a href="shop-products.php?shop=Ocean Fresh" class="shop-card bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
        <img src="../images/img4.png" alt="Ocean Fresh" class="w-full h-40 object-cover">
        <div class="p-4">
          <h3 class="font-bold">Ocean Fresh</h3>
          <p class="text-sm text-gray-600">Daily catch seafood</p>
          <p class="text-green-600 mt-2">★ 4.7</p>
        </div>
      </a>
    </div>
  </section>

  <footer class="text-white py-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      
      <div>
        <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>
        <p class="text-gray-300 text-sm">
          Fresh, organic produce delivered straight to your home from local farmers.
        </p>
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
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>

    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Hero Slider
      const slides = document.querySelectorAll('.hero-slide');
      const dots = document.querySelectorAll('.slider-dot');
      let currentSlide = 0;
      const slideInterval = 5000; // 5 seconds

      function showSlide(index) {
        slides.forEach((slide, i) => {
          slide.classList.remove('active');
          dots[i].classList.remove('active');
        });
        
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
      }

      function nextSlide() {
        showSlide(currentSlide + 1);
      }

      // Auto advance slides
      let sliderTimer = setInterval(nextSlide, slideInterval);

      // Dot click handlers
      dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          showSlide(index);
          clearInterval(sliderTimer);
          sliderTimer = setInterval(nextSlide, slideInterval);
        });
      });

      // Pause on hover
      const heroSlider = document.querySelector('.hero-slider');
      heroSlider.addEventListener('mouseenter', () => {
        clearInterval(sliderTimer);
      });

      heroSlider.addEventListener('mouseleave', () => {
        sliderTimer = setInterval(nextSlide, slideInterval);
      });

      // Product links
      document.querySelectorAll('.product-link').forEach(link => {
        link.addEventListener('click', (event) => {
          event.preventDefault();
          const name = link.dataset.name;
          const price = link.dataset.price;
          const img = link.dataset.img;
          window.location.href = `productdetails.php?name=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}&img=${encodeURIComponent(img)}`;
        });
      });

      // Add to cart functionality
      async function addToCart(product) {
        try {
          // Prepare the payload
          const payload = {
            quantity: 1
          };

          // If product has an ID, use it
          if (product.id) {
            payload.product_id = product.id;
          } else {
            // Otherwise send product details to create/find product
            payload.product_name = product.name;
            payload.price = parseFloat(product.price);
            payload.description = product.description || '';
            payload.image = product.image || '';
            payload.category = product.category || 'other';
          }

          const response = await fetch('../api/cart.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
          });
          
          const data = await response.json();
          if (data.success) {
            updateCartIcon();
            showNotification(`${product.name} added to cart!`);
          } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
          }
        } catch (error) {
          console.error('Error adding to cart:', error);
          showNotification('Error adding to cart', 'error');
        }
      }

      // Show toast notification
      function showNotification(message, type = 'success') {
        // Remove existing notification if any
        const existing = document.querySelector('.toast-notification');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.className = `toast-notification fixed top-20 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white`;
        toast.innerHTML = `
          <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium">${message}</span>
          </div>
        `;
        document.body.appendChild(toast);
        // Animate in
        setTimeout(() => toast.classList.remove('translate-x-full'), 10);
        // Animate out and remove
        setTimeout(() => {
          toast.classList.add('translate-x-full');
          setTimeout(() => toast.remove(), 300);
        }, 3000);
      }

      // Update cart icon with item count
      async function updateCartIcon() {
        const badge = document.getElementById('cartBadge');
        if (!badge) return;
        
        try {
          // Fetch from database
          const response = await fetch('../api/cart.php');
          const data = await response.json();
          
          if (data.success && data.items) {
            const totalItems = data.items.reduce((sum, item) => sum + (item.quantity || 1), 0);
            badge.textContent = totalItems;
            badge.classList.toggle('hidden', totalItems === 0);
            return;
          }
        } catch (error) {
          console.log('Error loading cart count:', error);
        }
        
        // Fallback to localStorage
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
        badge.textContent = totalItems;
        badge.classList.toggle('hidden', totalItems === 0);
      }

      document.querySelectorAll('.add-btn').forEach(button => {
        button.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          const link = button.closest('.product-link');
          if (link) {
            const product = {
              name: link.dataset.name,
              price: parseFloat(link.dataset.price.replace('₱', '')),
              image: link.dataset.img,
              description: link.dataset.description || '',
              category: link.dataset.category || 'other'
            };
            addToCart(product);
          }
        });
      });

      // --- Load User Profile Data ---
      function loadUserProfile() {
        const userProfile = JSON.parse(localStorage.getItem('userProfile'));
        const headerProfilePic = document.getElementById('headerProfilePic');
        
        if (headerProfilePic) {
          if (userProfile && userProfile.profilePic && userProfile.profilePic.startsWith('data:image')) {
            // Has uploaded image
            headerProfilePic.innerHTML = `<img src="${userProfile.profilePic}" alt="User" class="w-full h-full rounded-full object-cover">`;
            headerProfilePic.className = 'w-8 h-8 rounded-full cursor-pointer';
          } else {
            // Show icon
            headerProfilePic.innerHTML = '<i class="fas fa-user text-white text-sm"></i>';
            headerProfilePic.className = 'w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center';
          }
        }
      }

      // Load profile on page load
      loadUserProfile();

      // Update cart icon on page load
      updateCartIcon();

      // Listen for profile updates from other tabs
      window.addEventListener('storage', (e) => {
        if (e.key === 'userProfile') {
          loadUserProfile();
        }
      });

      // Listen for profile updates in same tab
      window.addEventListener('profileUpdated', () => {
        loadUserProfile();
      });
    });
  </script>
  <script src="../assets/js/profile-sync.js"></script>
</body>
</html>
