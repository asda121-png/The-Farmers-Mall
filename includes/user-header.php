<?php
/**
 * User Header Component - Fully Functional Header for Logged-in Users
 * 
 * Features:
 * - Logo redirects to user homepage
 * - Functional search with autocomplete
 * - Profile dropdown on hover
 * - Cart preview on hover
 * - Responsive design
 */

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user data
$user_id = $_SESSION['user_id'] ?? null;
$profile_picture = '';
$full_name = $_SESSION['full_name'] ?? 'User';

// Fetch profile picture from database
if ($user_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    require_once __DIR__ . '/../config/uuid-helper.php';
    $api = getSupabaseAPI();
    $user = safeGetUser($user_id, $api);
    if ($user) {
        $profile_picture = $user['profile_picture'] ?? '';
    }
}

// Determine base path
$base = '';
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$subdirectories = ['public', 'retailer', 'auth', 'admin', 'user', 'seller', 'rider'];
if (in_array($current_dir, $subdirectories)) {
    $base = '../';
}
?>
<header class="bg-white shadow-sm sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 md:h-20">
      
      <!-- Logo (Clickable - Redirects to User Homepage) -->
      <a href="<?php echo $base; ?>user/user-homepage.php" class="flex items-center gap-2 flex-shrink-0">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <span class="text-xl font-bold hidden sm:inline" style="color: #2E7D32;">Farmers Mall</span>
      </a>

      <!-- Search Bar with Autocomplete -->
      <div class="flex-1 max-w-2xl mx-4 relative">
        <form id="searchForm" action="<?php echo $base; ?>user/products.php" method="GET" class="relative">
          <div class="relative">
            <input 
              type="text" 
              id="searchInput"
              name="search"
              placeholder="Search for fresh produce, dairy, and more..."
              autocomplete="off"
              class="w-full px-4 py-2 pl-10 pr-10 border border-gray-300 rounded-full focus:ring-2 focus:ring-green-500 focus:outline-none focus:border-green-500 transition-all"
            />
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-green-600 text-white px-4 py-1 rounded-full hover:bg-green-700 transition text-sm hidden sm:block">
              Search
            </button>
          </div>
          
          <!-- Autocomplete Dropdown -->
          <div id="autocompleteDropdown" class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto z-50">
            <div id="autocompleteResults" class="py-2">
              <!-- Results will be populated here -->
            </div>
          </div>
        </form>
      </div>

      <!-- Right Side Icons -->
      <div class="flex items-center space-x-4 md:space-x-6 flex-shrink-0">
        
        <!-- Home Icon -->
        <a href="<?php echo $base; ?>user/user-homepage.php" class="text-gray-600 hover:text-green-600 transition" title="Home">
          <i class="fa-solid fa-house text-xl"></i>
        </a>

        <!-- Notifications Icon -->
        <a href="<?php echo $base; ?>user/notification.php" class="text-gray-600 hover:text-green-600 transition relative" title="Notifications">
          <i class="fa-regular fa-bell text-xl"></i>
          <span id="notificationBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
        </a>

        <!-- Cart Icon with Hover Preview -->
        <div class="relative" id="cartPreviewContainer">
          <a href="<?php echo $base; ?>user/cart.php" class="text-gray-600 hover:text-green-600 transition relative" title="Shopping Cart" id="cartIcon">
            <i class="fa-solid fa-cart-shopping text-xl"></i>
            <span id="cartBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
          </a>
          
          <!-- Cart Preview Dropdown -->
          <div id="cartPreview" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
            <div class="p-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-800">Shopping Cart</h3>
            </div>
            <div id="cartPreviewItems" class="max-h-96 overflow-y-auto">
              <!-- Cart items will be loaded here -->
              <div class="p-8 text-center text-gray-500">
                <i class="fas fa-shopping-cart text-4xl mb-2 text-gray-300"></i>
                <p class="text-sm">Your cart is empty</p>
              </div>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50">
              <a href="<?php echo $base; ?>user/cart.php" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition font-medium">
                View My Shopping Cart
              </a>
            </div>
          </div>
        </div>

        <!-- Profile Dropdown (Hover to Open) -->
        <div class="relative" id="profileDropdownContainer">
          <button class="flex items-center focus:outline-none" id="profileDropdownBtn">
            <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>
              <img src="<?php echo htmlspecialchars($base . $profile_picture); ?>" 
                   alt="Profile" 
                   class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200">
            <?php else: ?>
              <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
                <i class="fas fa-user text-white text-sm"></i>
              </div>
            <?php endif; ?>
          </button>

          <!-- Profile Dropdown Menu -->
          <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <div class="py-2">
              <a href="<?php echo $base; ?>user/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <i class="fas fa-user mr-2 text-gray-400"></i> My Account
              </a>
              <a href="<?php echo $base; ?>user/profile.php#order-history" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <i class="fas fa-box mr-2 text-gray-400"></i> My Purchase
              </a>
              <div class="border-t border-gray-100 my-1"></div>
              <a href="<?php echo $base; ?>public/index.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</header>

<!-- Header JavaScript -->
<script>
(function() {
  'use strict';
  
  // Search Autocomplete
  const searchInput = document.getElementById('searchInput');
  const autocompleteDropdown = document.getElementById('autocompleteDropdown');
  const autocompleteResults = document.getElementById('autocompleteResults');
  let searchTimeout = null;
  
  if (searchInput) {
    // Handle input for autocomplete
    searchInput.addEventListener('input', function() {
      const query = this.value.trim();
      
      clearTimeout(searchTimeout);
      
      if (query.length < 2) {
        autocompleteDropdown.classList.add('hidden');
        return;
      }
      
      searchTimeout = setTimeout(() => {
        fetchSearchSuggestions(query);
      }, 300);
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!searchInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
        autocompleteDropdown.classList.add('hidden');
      }
    });
    
    // Handle Enter key
    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('searchForm').submit();
      } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        const firstResult = autocompleteResults.querySelector('a');
        if (firstResult) firstResult.focus();
      }
    });
  }
  
  // Fetch search suggestions
  function fetchSearchSuggestions(query) {
    const basePath = '<?php echo $base; ?>';
    fetch(`${basePath}api/search.php?q=${encodeURIComponent(query)}&limit=5`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.results.length > 0) {
          displayAutocompleteResults(data.results);
          autocompleteDropdown.classList.remove('hidden');
        } else {
          autocompleteDropdown.classList.add('hidden');
        }
      })
      .catch(error => {
        console.error('Search error:', error);
        autocompleteDropdown.classList.add('hidden');
      });
  }
  
  // Display autocomplete results
  function displayAutocompleteResults(results) {
    autocompleteResults.innerHTML = results.map(product => `
      <a href="<?php echo $base; ?>user/products.php?search=${encodeURIComponent(product.name)}" 
         class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition cursor-pointer">
        <img src="${product.image_url}" 
             alt="${product.name}" 
             class="w-12 h-12 rounded object-cover">
        <div class="flex-1 min-w-0">
          <p class="font-medium text-gray-800 truncate">${escapeHtml(product.name)}</p>
        </div>
      </a>
    `).join('');
  }
  
  // Escape HTML
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  // Load Cart Preview
  function loadCartPreview() {
    const basePath = '<?php echo $base; ?>';
    const cartPreviewItems = document.getElementById('cartPreviewItems');
    const cartBadge = document.getElementById('cartBadge');
    
    fetch(`${basePath}api/cart.php`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.items && data.items.length > 0) {
          const totalItems = data.items.reduce((sum, item) => sum + (item.quantity || 1), 0);
          
          // Update badge
          if (cartBadge) {
            cartBadge.textContent = totalItems;
            cartBadge.classList.remove('hidden');
          }
          
          // Update preview
          if (cartPreviewItems) {
            cartPreviewItems.innerHTML = data.items.slice(0, 5).map(item => `
              <div class="flex items-center gap-3 p-3 border-b border-gray-100 hover:bg-gray-50">
                <img src="${item.product_image_url || '../images/products/placeholder.png'}" 
                     alt="${escapeHtml(item.product_name)}" 
                     class="w-16 h-16 rounded object-cover">
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-sm text-gray-800 truncate">${escapeHtml(item.product_name)}</p>
                  <p class="text-xs text-gray-500">Qty: ${item.quantity}</p>
                  <p class="text-green-600 font-semibold text-sm">₱${parseFloat(item.price || 0).toFixed(2)}</p>
                </div>
              </div>
            `).join('') + 
            (data.items.length > 5 ? `
              <div class="p-3 text-center text-sm text-gray-500 border-t border-gray-100">
                +${data.items.length - 5} more item(s)
              </div>
            ` : '');
          }
        } else {
          // Try localStorage as fallback
          const localCart = JSON.parse(localStorage.getItem('cart')) || [];
          if (localCart.length > 0) {
            const totalItems = localCart.reduce((sum, item) => sum + (item.quantity || 1), 0);
            if (cartBadge) {
              cartBadge.textContent = totalItems;
              cartBadge.classList.remove('hidden');
            }
            if (cartPreviewItems) {
              cartPreviewItems.innerHTML = localCart.slice(0, 5).map(item => `
                <div class="flex items-center gap-3 p-3 border-b border-gray-100 hover:bg-gray-50">
                  <img src="${item.image || '../images/products/placeholder.png'}" 
                       alt="${escapeHtml(item.name || 'Product')}" 
                       class="w-16 h-16 rounded object-cover">
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-gray-800 truncate">${escapeHtml(item.name || 'Product')}</p>
                    <p class="text-xs text-gray-500">Qty: ${item.quantity || 1}</p>
                    <p class="text-green-600 font-semibold text-sm">₱${parseFloat(item.price || 0).toFixed(2)}</p>
                  </div>
                </div>
              `).join('') + 
              (localCart.length > 5 ? `
                <div class="p-3 text-center text-sm text-gray-500 border-t border-gray-100">
                  +${localCart.length - 5} more item(s)
                </div>
              ` : '');
            }
          } else {
            // Empty cart
            if (cartBadge) {
              cartBadge.classList.add('hidden');
            }
            if (cartPreviewItems) {
              cartPreviewItems.innerHTML = `
                <div class="p-8 text-center text-gray-500">
                  <i class="fas fa-shopping-cart text-4xl mb-2 text-gray-300"></i>
                  <p class="text-sm">Your cart is empty</p>
                </div>
              `;
            }
          }
        }
      })
      .catch(error => {
        console.error('Cart load error:', error);
        // Fallback to localStorage
        const localCart = JSON.parse(localStorage.getItem('cart')) || [];
        if (localCart.length > 0) {
          const totalItems = localCart.reduce((sum, item) => sum + (item.quantity || 1), 0);
          if (cartBadge) {
            cartBadge.textContent = totalItems;
            cartBadge.classList.remove('hidden');
          }
        }
      });
  }
  
  // Load notification badge
  function loadNotificationBadge() {
    const notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
    const unreadCount = notifications.filter(n => !n.read).length;
    const badge = document.getElementById('notificationBadge');
    if (badge) {
      if (unreadCount > 0) {
        badge.textContent = unreadCount;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }
  }
  
  // Hover delay management for dropdowns
  let profileDropdownTimeout = null;
  let cartPreviewTimeout = null;
  const HOVER_DELAY = 200; // milliseconds delay before hiding
  
  // Initialize on page load
  document.addEventListener('DOMContentLoaded', function() {
    loadCartPreview();
    loadNotificationBadge();
    
    // Profile dropdown hover handlers
    const profileContainer = document.getElementById('profileDropdownContainer');
    const profileDropdown = document.getElementById('profileDropdown');
    const profileBtn = document.getElementById('profileDropdownBtn');
    
    if (profileContainer && profileDropdown && profileBtn) {
      // Show dropdown on hover
      profileContainer.addEventListener('mouseenter', function() {
        clearTimeout(profileDropdownTimeout);
        profileDropdown.classList.remove('hidden');
      });
      
      // Hide dropdown with delay on mouse leave
      profileContainer.addEventListener('mouseleave', function() {
        profileDropdownTimeout = setTimeout(function() {
          profileDropdown.classList.add('hidden');
        }, HOVER_DELAY);
      });
      
      // Keep dropdown visible when hovering over it
      profileDropdown.addEventListener('mouseenter', function() {
        clearTimeout(profileDropdownTimeout);
      });
      
      profileDropdown.addEventListener('mouseleave', function() {
        profileDropdownTimeout = setTimeout(function() {
          profileDropdown.classList.add('hidden');
        }, HOVER_DELAY);
      });
    }
    
    // Cart preview hover handlers
    const cartContainer = document.getElementById('cartPreviewContainer');
    const cartPreview = document.getElementById('cartPreview');
    const cartIcon = document.getElementById('cartIcon');
    
    if (cartContainer && cartPreview && cartIcon) {
      // Show preview on hover
      cartContainer.addEventListener('mouseenter', function() {
        clearTimeout(cartPreviewTimeout);
        cartPreview.classList.remove('hidden');
      });
      
      // Hide preview with delay on mouse leave
      cartContainer.addEventListener('mouseleave', function() {
        cartPreviewTimeout = setTimeout(function() {
          cartPreview.classList.add('hidden');
        }, HOVER_DELAY);
      });
      
      // Keep preview visible when hovering over it
      cartPreview.addEventListener('mouseenter', function() {
        clearTimeout(cartPreviewTimeout);
      });
      
      cartPreview.addEventListener('mouseleave', function() {
        cartPreviewTimeout = setTimeout(function() {
          cartPreview.classList.add('hidden');
        }, HOVER_DELAY);
      });
    }
    
    // Refresh cart every 10 seconds
    setInterval(loadCartPreview, 10000);
  });
  
  // Listen for cart updates
  window.addEventListener('cartUpdated', loadCartPreview);
  window.addEventListener('storage', function(e) {
    if (e.key === 'cart') {
      loadCartPreview();
    }
  });
})();
</script>

<style>

/* Smooth transitions */
#autocompleteDropdown,
#profileDropdown,
#cartPreview {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Mobile responsive */
@media (max-width: 640px) {
  #searchInput {
    font-size: 14px;
    padding-left: 2.5rem;
  }
  
  #autocompleteDropdown {
    max-height: 60vh;
  }
  
  #cartPreview {
    width: 90vw;
    right: -10px;
  }
}
</style>

