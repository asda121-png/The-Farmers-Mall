<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$profile_picture = '';
$full_name = $_SESSION['full_name'] ?? 'User';
if ($user_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    require_once __DIR__ . '/../config/uuid-helper.php';
    $api = getSupabaseAPI();
    $user = safeGetUser($user_id, $api);
    if ($user) {
        $profile_picture = $user['profile_picture'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Messages – Farmers Mall</title>

  <!-- Tailwind + Font Awesome -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    .notification-dropdown { position: absolute; top: 100%; right: 0; margin-top: 8px; width: 320px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); z-index: 50; max-height: 400px; overflow-y: auto; }
    .notification-item { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; transition: all 0.2s ease; cursor: pointer; }
    .notification-item:hover { background-color: #f9f9f9; }
    .notification-item.unread { background-color: #f0f9f5; border-left: 4px solid #4CAF50; }
    .notification-item-title { font-weight: 600; color: #333; font-size: 14px; margin-bottom: 4px; }
    .notification-item-message { font-size: 12px; color: #666; margin-bottom: 4px; }
    .notification-item-time { font-size: 11px; color: #999; }
    .notification-empty { padding: 24px 16px; text-align: center; color: #999; font-size: 14px; }
    .notification-header { padding: 12px 16px; border-bottom: 1px solid #e0e0e0; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .notification-clear-btn { font-size: 12px; color: #2E7D32; cursor: pointer; background: none; border: none; }
    .notification-clear-btn:hover { color: #1B5E20; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">

<?php include __DIR__ . '/../includes/user-header.php'; ?>


  <!-- Main Messaging Section -->
  <main class="max-w-7xl mx-auto px-6 py-8 mb-80">
    <div class="flex items-center space-x-4 mb-6">
      <button onclick="window.history.back()" class="text-gray-500 hover:text-gray-800">
        <i class="fa-solid fa-arrow-left text-xl"></i>
      </button>
      <h2 class="text-2xl font-semibold">Messages</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Conversations List -->
      <aside class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-4 border-b border-gray-100">
          <input type="text" placeholder="Search messages..."
                 class="w-full px-3 py-2 border rounded-md text-sm focus:ring-1 focus:ring-green-500 focus:outline-none">
        </div>

        <ul id="conversationList" class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
          <li class="p-4 flex items-center space-x-3 hover:bg-gray-50 cursor-pointer" data-user="Farmer Juan">
            <img src="images/farmer1.png" alt="Farmer" class="w-10 h-10 rounded-full">
            <div class="flex-1">
              <p class="font-medium text-gray-800">Farmer Juan</p>
              <p class="text-sm text-gray-500 truncate">Your order is ready for pickup today.</p>
            </div>
            <span class="text-xs text-gray-400">10:45 AM</span>
          </li>
          <li class="p-4 flex items-center space-x-3 hover:bg-gray-50 cursor-pointer" data-user="Farmer Maria">
            <img src="images/farmer2.png" alt="Farmer" class="w-10 h-10 rounded-full">
            <div class="flex-1">
              <p class="font-medium text-gray-800">Farmer Maria</p>
              <p class="text-sm text-gray-500 truncate">Thank you for your recent order!</p>
            </div>
            <span class="text-xs text-gray-400">Yesterday</span>
          </li>
        </ul>
      </aside>

      <!-- Chat Section -->
      <section class="md:col-span-2 bg-white rounded-lg shadow-sm flex flex-col border border-gray-200">
        <!-- Chat Header -->
        <div id="chatHeader" class="p-4 border-b border-gray-100 flex items-center space-x-3">
          <img src="images/farmer1.png" alt="Farmer" class="w-10 h-10 rounded-full">
          <div>
            <h3 id="chatName" class="font-semibold text-gray-800">Farmer Juan</h3>
            <p class="text-sm text-gray-500">Online</p>
          </div>
        </div>

        <!-- Chat Messages -->
        <div id="chatMessages" class="flex-1 p-4 overflow-y-auto space-y-4 bg-gray-50">
          <div class="flex items-end space-x-2">
            <img src="images/farmer1.png" class="w-8 h-8 rounded-full" alt="Farmer">
            <div class="bg-white p-3 rounded-lg shadow-sm max-w-xs">
              <p class="text-sm text-gray-700">Hello! Your vegetable box is ready.</p>
            </div>
          </div>

          <div class="flex justify-end">
            <div class="bg-green-600 text-white p-3 rounded-lg shadow-sm max-w-xs">
              <p class="text-sm">Great! I’ll pick it up this afternoon.</p>
            </div>
          </div>
        </div>

        <!-- Chat Input -->
        <div class="p-4 border-t border-gray-100 flex items-center space-x-3">
          <input id="messageInput" type="text" placeholder="Type your message..."
                 class="flex-1 px-4 py-2 border rounded-full text-sm focus:ring-1 focus:ring-green-500 focus:outline-none">
          <button id="sendBtn" class="bg-green-600 text-white px-4 py-2 rounded-full hover:bg-green-700">
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </div>
      </section>
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

  <script src="../assets/js/customermessage.js"></script>
  <script>
    // Update cart icon with item count from database
    async function updateCartIcon() {
      try {
        const response = await fetch('../api/cart.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'count' })
        });
        const data = await response.json();
        
        if (data.success) {
          const cartIcon = document.querySelector('a[href="cart.php"]');
          if (!cartIcon) return;
          
          let badge = cartIcon.querySelector('.cart-badge');
          if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center';
            cartIcon.appendChild(badge);
          }
          const totalItems = data.count || 0;
          badge.textContent = totalItems;
          badge.style.display = totalItems > 0 ? 'block' : 'none';
        }
      } catch (error) {
        console.error('Error updating cart icon:', error);
      }
    }

    // Highlight the active message icon in the header
    document.addEventListener('DOMContentLoaded', function() {
      const messageIconLink = document.querySelector('a[href="message.php"]');
      if (messageIconLink) {
        messageIconLink.classList.remove('text-gray-600');
        messageIconLink.classList.add('text-green-600');
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
    });

    // Logout Modal Logic
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');

    if (logoutLink) {
      logoutLink.addEventListener('click', (e) => {
        e.preventDefault();
        logoutModal.classList.remove('hidden');
      });
    }

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
