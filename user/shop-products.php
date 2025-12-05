<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'] ?? null;
$shop_name = $_GET['shop'] ?? '';

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

// Fetch shop/retailer information
$shop_info = null;
$products = [];
if ($shop_name) {
    require_once __DIR__ . '/../config/supabase-api.php';
    $api = getSupabaseAPI();
    
    // Get retailer information by shop name
    $retailers = $api->select('retailers', ['shop_name' => $shop_name]);
    if (!empty($retailers)) {
        $shop_info = $retailers[0];
        $retailer_id = $shop_info['id'];
        
        // Fetch products for this retailer
        $products = $api->select('products', ['retailer_id' => $retailer_id, 'status' => 'active']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars($shop_name); ?> - Farmers Mall</title>

  <!-- Tailwind + Font Awesome (CDN as in your project) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    .product-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

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
            <a href="user-homepage.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
            <a href="message.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
            <a href="notification.php" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
            <a href="cart.php" class="text-gray-600 relative">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="cartBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
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
                    <a href="../auth/login.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
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


  <!-- Toast Notification Container -->
  <div id="toastContainer" class="fixed top-20 right-4 z-50 space-y-2"></div>

  <!-- Shop Header -->
  <?php if ($shop_info): ?>
  <section class="bg-white shadow-sm py-8">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center gap-6">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
          <i class="fas fa-store text-green-600 text-3xl"></i>
        </div>
        <div>
          <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($shop_info['shop_name']); ?></h1>
          <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($shop_info['shop_description'] ?? 'Quality products from local farmers'); ?></p>
          <div class="flex items-center gap-4 mt-3">
            <span class="text-yellow-500">
              <i class="fas fa-star"></i>
              <?php echo number_format($shop_info['rating'] ?? 0, 1); ?>
            </span>
            <span class="text-gray-500">|</span>
            <span class="text-gray-600">
              <i class="fas fa-box"></i>
              <?php echo count($products); ?> Products
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Products Section -->
  <main class="flex-1 max-w-7xl mx-auto px-6 py-8">
    <!-- Back Button -->
    <div class="mb-6">
      <a href="user-homepage.php" class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 font-medium transition-colors">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Homepage</span>
      </a>
    </div>

    <?php if (!$shop_info): ?>
      <div class="text-center py-20">
        <i class="fas fa-store-slash text-gray-300 text-6xl mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-600 mb-2">Shop Not Found</h2>
        <p class="text-gray-500 mb-6">The shop you're looking for doesn't exist.</p>
        <a href="user-homepage.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-full hover:bg-green-700">
          Back to Home
        </a>
      </div>
    <?php elseif (empty($products)): ?>
      <div class="text-center py-20">
        <i class="fas fa-box-open text-gray-300 text-6xl mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-600 mb-2">No Products Available</h2>
        <p class="text-gray-500 mb-6">This shop hasn't listed any products yet.</p>
        <a href="user-homepage.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-full hover:bg-green-700">
          Back to Home
        </a>
      </div>
    <?php else: ?>
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Available Products</h2>
        <p class="text-gray-600">Browse all products from <?php echo htmlspecialchars($shop_name); ?></p>
      </div>

      <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
          <div class="product-card bg-white rounded-lg shadow overflow-hidden">
            <a href="product-details.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="block">
              <div class="relative">
                <?php if (!empty($product['image_url'])): ?>
                  <img src="<?php echo htmlspecialchars('../' . $product['image_url']); ?>" 
                       alt="<?php echo htmlspecialchars($product['name']); ?>" 
                       class="w-full h-48 object-cover"
                       onerror="this.src='https://via.placeholder.com/300x200?text=Product+Image'"
                       loading="lazy">
                <?php else: ?>
                  <img src="https://via.placeholder.com/300x200?text=Product+Image" 
                       alt="<?php echo htmlspecialchars($product['name']); ?>" 
                       class="w-full h-48 object-cover">
                <?php endif; ?>
                
                <?php if ($product['stock_quantity'] <= 0): ?>
                  <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <span class="text-white font-bold text-lg">Out of Stock</span>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="p-4">
                <h3 class="font-bold text-gray-800 text-lg mb-1 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                
                <?php if (!empty($product['category'])): ?>
                  <p class="text-xs text-gray-500 mb-2"><?php echo htmlspecialchars($product['category']); ?></p>
                <?php endif; ?>
                
                <div class="flex items-center justify-between mt-3">
                  <span class="text-green-600 font-bold text-xl">₱<?php echo number_format($product['price'], 2); ?></span>
                  <?php if (!empty($product['unit'])): ?>
                    <span class="text-sm text-gray-500">per <?php echo htmlspecialchars($product['unit']); ?></span>
                  <?php endif; ?>
                </div>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                  <button onclick="addToCart(event, '<?php echo htmlspecialchars($product['id']); ?>')" 
                          class="w-full mt-4 bg-green-600 text-white py-2 rounded-full hover:bg-green-700 transition">
                    <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                  </button>
                <?php else: ?>
                  <button class="w-full mt-4 bg-gray-300 text-gray-600 py-2 rounded-full cursor-not-allowed" disabled>
                    Out of Stock
                  </button>
                <?php endif; ?>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- Footer -->
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
          <li><a href="../public/about.php" class="hover:underline">About Us</a></li>
          <li><a href="#" class="hover:underline">Contact</a></li>
          <li><a href="#" class="hover:underline">FAQ</a></li>
          <li><a href="../public/support.php" class="hover:underline">Support</a></li>
        </ul>
      </div>

      <div>
        <h3 class="font-bold text-lg mb-3">Categories</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="products.php?category=Vegetables" class="hover:underline">Vegetables</a></li>
          <li><a href="products.php?category=Fruits" class="hover:underline">Fruits</a></li>
          <li><a href="products.php?category=Dairy" class="hover:underline">Dairy</a></li>
          <li><a href="products.php?category=Meat" class="hover:underline">Meat</a></li>
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
    // Global search functionality
    document.getElementById('globalSearch').addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      const productCards = document.querySelectorAll('.product-card');
      
      productCards.forEach(card => {
        const productName = card.querySelector('h3').textContent.toLowerCase();
        const category = card.querySelector('.text-xs.text-gray-500')?.textContent.toLowerCase() || '';
        
        if (productName.includes(searchTerm) || category.includes(searchTerm)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });

    // Add to cart function with toast notification
    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
      } text-white`;
      toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} text-xl"></i>
        <span>${message}</span>
      `;
      
      document.getElementById('toastContainer').appendChild(toast);
      
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    function updateCartBadge() {
      fetch('../api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'count' })
      })
      .then(response => response.json())
      .then(data => {
        const badge = document.getElementById('cartBadge');
        if (data.count > 0) {
          badge.textContent = data.count;
          badge.classList.remove('hidden');
        } else {
          badge.classList.add('hidden');
        }
      })
      .catch(error => console.error('Error updating cart badge:', error));
    }

    function addToCart(event, productId) {
      event.preventDefault();
      event.stopPropagation();
      
      fetch('../api/cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'add',
          product_id: productId,
          quantity: 1
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Product added to cart successfully!', 'success');
          updateCartBadge();
        } else {
          showToast(data.message || 'Failed to add product to cart', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while adding to cart', 'error');
      });
    }

    // Initialize cart badge on page load
    updateCartBadge();
  </script>

</body>
</html>
