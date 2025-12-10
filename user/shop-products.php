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

// Helper function to resolve image path
function resolveImagePath($img) {
    if (empty($img)) return '../images/products/placeholder.png';
    if (preg_match('#^https?://#i', $img)) return $img;
    if (strpos($img, '../') === 0) return $img;
    if (file_exists(__DIR__ . '/../' . $img)) return '../' . $img;
    if (file_exists(__DIR__ . '/../images/products/' . $img)) return '../images/products/' . $img;
    return '../' . $img;
}

// Helper function to get product image
function getProductImage($product) {
    $img = $product['image'] ?? $product['image_url'] ?? $product['product_image'] ?? $product['image_path'] ?? '';
    return resolveImagePath($img);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars($shop_name); ?> - Farmers Mall</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* Product card transition - Matching User Homepage */
    .product-card {
      display: flex;
      flex-direction: column;
      min-height: 24rem; /* Accommodate wrapping text */
      border: 2px solid transparent;
      border-radius: 0.5rem;
      transition: all 0.3s ease;
      position: relative;
    }

    .product-card:hover {
      border-color: #2E7D32;
    }

    /* Add to cart button styling */
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
  </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<?php include __DIR__ . '/../includes/user-header.php'; ?>


  <div id="toastContainer" class="fixed top-20 right-4 z-50 space-y-2"></div>

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

  <main class="flex-1 max-w-7xl mx-auto px-6 py-8">
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
          <a href="product-details.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="product-card bg-white rounded-lg shadow hover:shadow-lg transition relative block overflow-hidden h-full">
            <div class="relative">
              <?php $productImg = getProductImage($product); ?>
              <img src="<?php echo htmlspecialchars($productImg); ?>" 
                   alt="<?php echo htmlspecialchars($product['name']); ?>" 
                   class="w-full h-48 object-cover"
                   onerror="this.src='https://via.placeholder.com/300x200?text=Product+Image'"
                   loading="lazy">
              
              <?php if ($product['stock_quantity'] <= 0): ?>
                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                  <span class="text-white font-bold text-lg">Out of Stock</span>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="p-4 flex flex-col flex-1">
              <div class="flex justify-between items-start mt-2">
                  <div class="flex-1 pr-2">
                      <h3 class="font-bold text-lg leading-tight mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                      <p class="text-xs text-gray-500 mb-2"><?php echo htmlspecialchars($product['category'] ?? 'Other'); ?></p>
                      <p class="text-green-600 font-bold text-lg">₱<?php echo number_format($product['price'], 2); ?></p>
                  </div>
                  
                  <div class="flex flex-col items-center">
                      <?php if ($product['stock_quantity'] > 0): ?>
                        <button onclick="addToCart(event, '<?php echo htmlspecialchars($product['id']); ?>')" 
                                class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition flex-shrink-0 mb-1" 
                                title="Add to cart">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                      <?php else: ?>
                        <button class="bg-gray-200 border border-gray-300 text-gray-400 rounded-full w-8 h-8 flex items-center justify-center cursor-not-allowed mb-1" disabled>
                            <i class="fa-solid fa-ban"></i>
                        </button>
                      <?php endif; ?>
                      
                      <p class="text-xs text-gray-400 whitespace-nowrap">
                          <?php echo ($product['units_sold'] ?? 0); ?> sold
                      </p>
                  </div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
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
        if (badge) {
            if (data.count > 0) {
              badge.textContent = data.count;
              badge.classList.remove('hidden');
            } else {
              badge.classList.add('hidden');
            }
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
    document.addEventListener('DOMContentLoaded', updateCartBadge);
  </script>

</body>
</html>