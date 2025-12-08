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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Your Cart – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/productdetails.css">
  <style>
    /* Modal Animation */
    #deleteModal.show, #clearCartModal.show {
      display: flex !important;
      animation: fadeIn 0.2s ease-out;
    }
    
    #deleteModal.show .modal-content, #clearCartModal.show .modal-content {
      animation: slideUp 0.3s ease-out;
      transform: scale(1);
    }
    
    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }
    
    @keyframes slideUp {
      from {
        transform: translateY(20px) scale(0.95);
        opacity: 0;
      }
      to {
        transform: translateY(0) scale(1);
        opacity: 1;
      }
    }
  </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

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
            <a href="message.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
            <a href="notification.php" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
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
    const btn = document.getElementById('profileDropdownBtn');
    const menu = document.getElementById('profileDropdown');

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        menu.classList.add('hidden');
    });
</script>


     
  <!-- Main Cart Section -->
  <main class="max-w-7xl mx-auto p-4 md:p-6 mt-6 flex-grow w-full mb-20 md:mb-96">
    <div class="grid grid-cols-1 lg:grid-cols-3 items-start mb-6 gap-4">
      <div class="col-span-2 flex items-center space-x-4">
        <button onclick="window.history.back()" class="text-gray-500 hover:text-gray-800 transition">
          <i class="fa-solid fa-arrow-left text-xl"></i>
        </button>
        <h2 class="text-xl md:text-2xl font-semibold">Your Shopping Cart (<span id="cartCount">0</span>)</h2>
      </div>
      <div class="flex items-center gap-2 md:gap-4 justify-end">
        <a href="products.php" class="text-green-600 hover:text-green-700 font-medium text-sm flex items-center gap-2">
          <i class="fa-solid fa-plus"></i>
          <span class="hidden sm:inline">Continue Shopping</span>
        </a>
        <button id="clearCartBtn" class="text-red-500 hover:text-red-700 font-medium text-sm flex items-center gap-2 transition">
          <i class="fa-solid fa-trash"></i>
          <span class="hidden sm:inline">Clear Cart</span>
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- LEFT: Cart Items -->
      <div class="lg:col-span-2">
        <div class="flex items-center mb-4 pl-4">
          <input id="selectAllCheckbox" type="checkbox" class="w-5 h-5 rounded border-gray-300 cursor-pointer accent-green-600 mr-2" />
          <label for="selectAllCheckbox" class="text-green-600 font-medium">Select All</label>
        </div>

        <section id="cartItems" class="space-y-4">
          <!-- Cart items will be dynamically inserted here by cart.js -->
        </section>
      </div>

      <!-- RIGHT: Order Summary -->
      <aside class="bg-white shadow-sm p-4 md:p-6 rounded-xl h-fit sticky top-4">
        <h3 class="font-semibold text-lg mb-4">Order Summary</h3>
        
        <div class="text-sm text-gray-700 space-y-3">
          <div class="flex justify-between">
            <span>Items (<span id="itemCount">0</span>)</span>
            <span id="subtotal">₱0.00</span>
          </div>
          <div class="flex justify-between">
            <span>Shipping</span>
            <span class="text-green-600 font-medium">Free</span>
          </div>
          <div class="flex justify-between text-xs text-gray-500">
            <span>Tax (estimated 12%)</span>
            <span id="tax">₱0.00</span>
          </div>
        </div>

        <div class="border-t mt-4 pt-4 flex justify-between text-lg font-semibold text-gray-800">
          <span>Total</span>
          <span id="total" class="text-green-600">₱0.00</span>
        </div>

        <button id="checkoutBtn"
           class="block text-center bg-green-600 w-full text-white py-3 rounded-lg mt-6 font-medium hover:bg-green-700 transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-green-600">
          <i class="fa-solid fa-lock mr-2"></i>
          Select items to checkout
          <i class="fa-solid fa-arrow-right ml-2"></i>
        </button>
        
        <div class="mt-4 text-center space-y-2">
          <a href="products.php" class="text-sm text-gray-600 hover:text-green-600 transition block md:hidden">
            <i class="fa-solid fa-arrow-left mr-1"></i>
            Continue Shopping
          </a>
          <p class="text-xs text-gray-500 mt-4">
            <i class="fa-solid fa-shield-halved mr-1"></i>
            Secure checkout guaranteed
          </p>
        </div>
      </aside>
    </div>
  </main>

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

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all scale-95 modal-content">
      <div class="relative">
        <!-- Close button -->
        <button id="closeDeleteModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10">
          <i class="fa-solid fa-times text-xl"></i>
        </button>

        <div class="p-6">


          <!-- Title -->
          <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Remove Item?</h3>

          <!-- Enhanced Product Info -->
          <div id="modalProductInfo" class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
            <div class="flex items-center gap-3">
              <img id="modalProductImage" src="" alt="Product" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
              <div class="flex-1">
                <p id="modalProductName" class="font-semibold text-gray-800 text-lg"></p>
                <p id="modalProductPrice" class="text-green-600 font-medium text-base"></p>
                <p id="modalProductQuantity" class="text-gray-500 text-sm"></p>
              </div>
            </div>
          </div>

          <!-- Impact message -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6">
            <div class="flex items-start gap-2">
              <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
              <div>
                <p class="text-gray-700 text-sm font-medium">Impact on your order:</p>
                <p class="text-gray-600 text-sm mt-1">Removing this item will update your cart total and may affect any applied discounts.</p>
              </div>
            </div>
          </div>

          <!-- Enhanced buttons -->
          <div class="flex gap-3">
            <button id="cancelDeleteBtn" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
              <i class="fa-solid fa-arrow-left mr-2"></i>Keep Item
            </button>
            <button id="confirmDeleteBtn" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
              <i class="fa-solid fa-trash mr-2"></i>Remove Item
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Clear Cart Confirmation Modal -->
  <div id="clearCartModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all scale-95 modal-content">
      <div class="relative">
        <!-- Close button -->
        <button id="closeClearCartModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10">
          <i class="fa-solid fa-times text-xl"></i>
        </button>

        <div class="p-6">
          <!-- Icon with animation -->
          <div class="flex justify-center mb-4">
            <div class="bg-orange-100 rounded-full p-4 animate-bounce">
              <i class="fa-solid fa-exclamation-triangle text-orange-600 text-3xl"></i>
            </div>
          </div>

          <!-- Title -->
          <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Clear Entire Cart?</h3>

          <!-- Enhanced message with cart summary -->
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-center gap-2 mb-2">
              <i class="fa-solid fa-shopping-cart text-red-600"></i>
              <span class="font-semibold text-gray-800">Cart Summary</span>
            </div>
            <p class="text-gray-700 text-center mb-1">This will remove all <span id="clearCartItemCount" class="font-bold text-red-600">0</span> items from your cart.</p>
            <p class="text-gray-600 text-sm text-center">Total value: <span id="clearCartTotalValue" class="font-semibold text-red-600">₱0.00</span></p>
          </div>

          <!-- Warning message -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-6">
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-exclamation-triangle text-yellow-600"></i>
              <p class="text-gray-700 text-sm font-medium">This action cannot be undone</p>
            </div>
            <p class="text-gray-600 text-sm mt-1">All items will be permanently removed from your cart.</p>
          </div>

          <!-- Enhanced buttons -->
          <div class="flex gap-3">
            <button id="cancelClearBtn" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
              <i class="fa-solid fa-arrow-left mr-2"></i>Keep Items
            </button>
            <button id="confirmClearBtn" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
              <i class="fa-solid fa-trash mr-2"></i>Clear All
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script src="../assets/js/profile-sync.js"></script>
  <script src="../assets/js/cart.js"></script>
  <script>
    // Update cart icon with item count
    async function updateCartIcon() {
      const cartIcon = document.querySelector('a[href*="cart"]');
      if (!cartIcon) return;
      // Create or update a badge for the count
      let badge = cartIcon.querySelector('.cart-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center';
        cartIcon.classList.add('relative', 'inline-block');
        cartIcon.appendChild(badge);
      }
      
      try {
        // Fetch from database
        const response = await fetch('../api/cart.php');
        const data = await response.json();
        
        if (data.success && data.items) {
          const totalItems = data.items.reduce((sum, item) => sum + (item.quantity || 1), 0);
          badge.textContent = totalItems;
          badge.style.display = totalItems > 0 ? 'flex' : 'none';
          return;
        }
      } catch (error) {
        console.log('Error loading cart count:', error);
      }
      
      // Fallback
      badge.textContent = '0';
      badge.style.display = 'none';
    }

    // Dynamically load footer.php
    fetch('footer.php')
      .then(res => res.text())
      .then(data => document.getElementById('footer').innerHTML = data);

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
