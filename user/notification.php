<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
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
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Notifications – Farmers Mall</title>

  <!-- Tailwind + Font Awesome -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Include Header -->
 <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <!-- Logo -->
        <a href="user-homepage.php" class="flex items-center gap-2">
            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                <i class="fas fa-leaf text-white text-lg"></i>
            </div>
            <span class="text-xl font-bold" style="color: #2E7D32;">Farmers Mall</span>
        </a>

        <!-- Search -->
        <div class="flex-1 mx-6">
            <form action="products.php" method="GET">
                <input 
                    type="text" 
                    name="search"
                    placeholder="Search for fresh produce, dairy, and more..."
                    class="w-full px-4 py-2 border rounded-full focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </form>
        </div>

        <!-- Icons & Profile Dropdown -->
        <div class="flex items-center space-x-6">
            <a href="../user/user-homepage.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
            <a href="message.php" class="text-gray-600 hover:text-green-600"><i class="fa-regular fa-comment"></i></a>
            <a href="notification.php" class="text-green-600 relative">
                <i class="fa-regular fa-bell"></i>
                <span id="notificationBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
            </a>
            <a href="cart.php" class="text-gray-600 relative">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>

            <!-- Profile Dropdown -->
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

                <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                    <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                    <a href="profile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                    <a href="../auth/login.php" id="logoutLink" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                </div>
            </div>
            <!-- End Profile Dropdown -->

        </div>
    </div>
</header>

<!-- Dropdown JS -->
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
                const logoutModal = document.getElementById('logoutModal');
                if (logoutModal) {
                    logoutModal.classList.remove('hidden');
                }
            });
        }

        document.addEventListener('click', (e) => {
            if (profileMenu && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });

        // --- Notification Badge Logic ---
        const notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
        const unreadCount = notifications.filter(n => !n.read).length;
        const badge = document.getElementById('notificationBadge');
        if (badge && unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.classList.remove('hidden');
        }
    });
</script>

  <!-- Main Notifications Section -->
  <main class="max-w-7xl mx-auto px-6 py-8 mb-[40rem]">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-4">
        <button onclick="window.history.back()" class="text-gray-500 hover:text-gray-800">
          <i class="fa-solid fa-arrow-left text-xl"></i>
        </button>
        <h2 class="text-2xl font-semibold">Notifications</h2>
      </div>
      <button id="clearAllBtn"
              class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700">
        Mark all as read
      </button>
    </div>

    <!-- Filter Tabs -->
    <div id="filterTabs" class="flex items-center gap-4 mb-4 border-b">
      <button data-filter="all" class="filter-tab py-2 px-1 border-b-2 border-green-600 text-green-600 font-semibold text-sm">All</button>
      <button data-filter="unread" class="filter-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-black text-sm">Unread</button>
    </div>

    <div id="notificationList" class="bg-white rounded-lg shadow-sm border border-gray-200 divide-y">
      <!-- Notifications will be dynamically inserted here -->
    </div>
  </main>

  <!-- Footer -->
  <footer class="text-white py-12 mt-10" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      <div>
        <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>
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
          <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-facebook"></i></a>
          <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
    <div class="border-t border-green-800 text-center text-gray-300 text-sm mt-10 pt-6">
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>

  <script src="../assets/js/customernotification.js"></script>
  <script>
    // Update cart icon with item count
    function updateCartIcon() {
      const cart = JSON.parse(localStorage.getItem('cart')) || [];
      const cartIcon = document.querySelector('a[href="cart.php"]');
      if (!cartIcon) return;
      // Create or update a badge for the count
      let badge = cartIcon.querySelector('.cart-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center';
        cartIcon.appendChild(badge);
      }
      const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
      badge.textContent = totalItems;
      badge.style.display = totalItems > 0 ? 'block' : 'none';
    }

    // Highlight the active notification icon in the header
    document.addEventListener('DOMContentLoaded', function() {
      const notificationIconLink = document.querySelector('a[href="notification.php"]');
      if (notificationIconLink) {
        // The bell icon is inside the link, so we target the link itself
        notificationIconLink.classList.remove('text-gray-600');
        notificationIconLink.classList.add('text-green-600');
      }

      // Load user profile data
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
      loadUserProfile();
      updateCartIcon(); // Update cart icon on page load

      // Listen for profile updates from other tabs
      window.addEventListener('storage', (e) => {
        if (e.key === 'userProfile') {
          loadUserProfile();
        }
        if (e.key === 'cart') {
          updateCartIcon();
        }
      });

      // Listen for profile updates in same tab
      window.addEventListener('profileUpdated', () => {
        loadUserProfile();
      });

      // Listen for cart updates in same tab
      window.addEventListener('cartUpdated', () => {
        updateCartIcon();
      });
    });

    // Logout Modal Close Logic
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');

    if (cancelLogout) {
      cancelLogout.addEventListener('click', () => {
        logoutModal.classList.add('hidden');
      });
    }
  </script>
  <script src="../assets/js/profile-sync.js"></script>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
      <div class="text-red-500 text-4xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-semibold text-lg mb-2">Confirm Logout</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelLogout" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <a href="../auth/logout.php" class="px-6 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Logout</a>
      </div>
    </div>
  </div>

</body>
</html>
