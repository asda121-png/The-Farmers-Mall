<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data from session
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Details – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/productdetails.css">
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

  <!-- Header -->
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
                    <a href="/The-Farmers-Mall/The-Farmers-Mall/auth/login.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
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


  <!-- Main Content -->
  <main class="max-w-7xl mx-auto px-6 py-8 flex-grow w-full">
    <!-- Breadcrumbs -->
    <div class="text-sm text-gray-500 mb-6 breadcrumb">
      <a href="user-homepage.php" class="hover:underline">Home</a>
      <span class="breadcrumb-gt">></span>
      <a href="products.php" class="hover:underline">Vegetables</a>
      <span class="breadcrumb-gt">></span>
      <span id="product-name-breadcrumb" class="font-semibold text-gray-700">Product Name</span>
    </div>

    <div class="grid md:grid-cols-2 gap-12">
      <!-- Image Gallery -->
      <div>
        <div class="mb-4">
          <img id="main-image" src="" alt="Product Image" class="w-full h-auto rounded-lg shadow-md object-contain">
        </div>
        <div class="flex space-x-4 thumbnail-gallery">
          <img src="../images/products/Native tomato.jpg" alt="Tomato" class="w-24 h-24 rounded-lg cursor-pointer border-2 border-green-500 object-contain">
          <img src="../images/products/Organic Lettuce.png" alt="Lettuce" class="w-24 h-24 rounded-lg cursor-pointer object-contain">
          <img src="../images/products/carrots.png" alt="Carrots" class="w-24 h-24 rounded-lg cursor-pointer object-contain">
          <img src="../images/products/bell pepper mix.png" alt="Mixed Vegetables" class="w-24 h-24 rounded-lg cursor-pointer object-contain">
        </div>
      </div>

      <!-- Product Details -->
      <div>
        <h1 id="product-name" class="text-4xl font-bold mb-3">Product Name</h1>
        <div class="flex items-center mb-4">
          <div class="text-yellow-400">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
          </div>
          <span class="ml-2 text-gray-600">4.9 (1.2k reviews)</span>
        </div>
        <div class="text-3xl font-bold mb-4">
          <span id="product-price" class="text-green-600">Price</span>
        </div>
        <div class="flex items-center mb-6">
          <i class="fas fa-check-circle text-green-500 mr-2"></i>
          <span class="text-gray-700 font-semibold">In Stock</span>
        </div>
        <div class="flex items-center mb-6">
          <label for="quantity" class="mr-4 font-semibold">Quantity:</label>
          <div class="flex items-center border rounded-md quantity-input">
            <button class="px-3 py-1.5 text-gray-600 hover:bg-gray-100">-</button>
            <input type="text" id="quantity" value="1" class="w-12 text-center border-l border-r">
            <button class="px-3 py-1.5 text-gray-600 hover:bg-gray-100">+</button>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <button class="flex-1 bg-green-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-700 transition flex items-center justify-center add-to-cart-btn" id="addToCartBtn">
            <i class="fas fa-shopping-cart mr-2"></i>
            Add to Cart
          </button>
          <button class="bg-gray-200 text-gray-700 p-3 rounded-lg hover:bg-gray-300 wishlist-btn">
            <i class="far fa-heart"></i>
          </button>
        </div>

        <!-- Delivery & Key Features -->
        <div class="mt-8">
          <div class="bg-green-50 p-6 rounded-lg mb-6">
            <h3 class="font-bold text-lg mb-3">Delivery Information</h3>
            <div class="flex items-center mb-2">
              <i class="fas fa-truck text-green-600 mr-3"></i>
              <span>Delivery within 2-4 business days</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-box-open text-green-600 mr-3"></i>
              <span>Free shipping on orders over ₱20</span>
            </div>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-3">Key Features</h3>
            <ul class="space-y-2 text-gray-600">
              <li class="flex items-center"><i class="fas fa-leaf text-green-500 mr-3"></i>100% Organic and pesticide-free</li>
              <li class="flex items-center"><i class="fas fa-tractor text-green-500 mr-3"></i>Locally sourced from certified farms</li>
              <li class="flex items-center"><i class="far fa-clock text-green-500 mr-3"></i>Harvested within 24 hours</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Description & You Might Like -->
    <div class="mt-16">
      <div class="border-b mb-8">
        <h2 class="text-2xl font-bold pb-4 border-b-2 border-green-600 inline-block">Product Description</h2>
      </div>
      <p class="text-gray-600 mb-12">
        Our Fresh Organic Vegetable Bundle is a curated selection of the season's best, sourced directly from local certified organic farms. This bundle is perfect for families and individuals looking to incorporate healthy, pesticide-free produce into their daily meals. Each box is packed with a variety of vibrant vegetables, ensuring you get a wide range of nutrients and flavors. Enjoy the farm-to-table experience with produce that is harvested within 24 hours of delivery, guaranteeing maximum freshness and taste.
      </p>

      <h2 class="text-3xl font-bold text-center mb-8">You Might Also Like</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Product Card -->
        <div class="product-card bg-white p-4 rounded-lg shadow-sm text-center cursor-pointer hover:shadow-xl transition-all" 
             data-name="Fresh Strawberries" data-price="89.99" data-img="images/products/strawberry.png" 
             data-description="Juicy and sweet fresh strawberries, hand-picked for maximum flavor and freshness.">
          <img src="../images/products/strawberry.png" alt="Fresh Strawberries" class="w-full h-48 object-cover rounded-md mb-4">
          <h3 class="font-semibold text-lg">Fresh Strawberries</h3>
          <p class="text-green-600 font-bold mt-2">₱89.99</p>
        </div>
        <!-- Product Card -->
        <div class="product-card bg-white p-4 rounded-lg shadow-sm text-center cursor-pointer hover:shadow-xl transition-all" 
             data-name="Organic Broccoli" data-price="45.50" data-img="images/products/fresh brocoli.png" 
             data-description="Fresh organic broccoli, rich in vitamins and minerals, perfect for healthy meals.">
          <img src="../images/products/fresh brocoli.png" alt="Organic Broccoli" class="w-full h-48 object-cover rounded-md mb-4">
          <h3 class="font-semibold text-lg">Organic Broccoli</h3>
          <p class="text-green-600 font-bold mt-2">₱45.50</p>
        </div>
        <!-- Product Card -->
        <div class="product-card bg-white p-4 rounded-lg shadow-sm text-center cursor-pointer hover:shadow-xl transition-all" 
             data-name="Free-Range Chicken" data-price="280.00" data-img="images/products/native chicken.jpg" 
             data-description="Premium free-range chicken, tender and flavorful, raised naturally without hormones.">
          <img src="../images/products/native chicken.jpg" alt="Free-Range Chicken" class="w-full h-48 object-cover rounded-md mb-4">
          <h3 class="font-semibold text-lg">Free-Range Chicken</h3>
          <p class="text-green-600 font-bold mt-2">₱280.00</p>
        </div>
        <!-- Product Card -->
        <div class="product-card bg-white p-4 rounded-lg shadow-sm text-center cursor-pointer hover:shadow-xl transition-all" 
             data-name="Farm Fresh Milk" data-price="95.00" data-img="images/products/Fresh Milk.png" 
             data-description="Pure and fresh milk straight from local farms, rich in nutrients and perfect for daily consumption.">
          <img src="../images/products/Fresh Milk.png" alt="Farm Fresh Milk" class="w-full h-48 object-cover rounded-md mb-4">
          <h3 class="font-semibold text-lg">Farm Fresh Milk</h3>
          <p class="text-green-600 font-bold mt-2">₱95.00</p>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="text-white py-12 mt-12" style="background-color: #1B5E20;">
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
        <div class="flex space-x-4 text-xl"><a href="#"><i class="fab fa-facebook"></i></a><a href="#"><i
              class="fab fa-twitter"></i></a><a href="#"><i class="fab fa-instagram"></i></a></div>
      </div>
    </div>
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">© 2025 Farmers Mall. All
      rights reserved.</div>
  </footer>

  <script src="../assets/js/productdetails.js"></script>
  <script>
    // Update cart icon with item count
    function updateCartIcon() {
      const cart = JSON.parse(localStorage.getItem('cart')) || [];
      const cartIcon = document.querySelector('a[href*="cart"]');
      if (!cartIcon) return;
      // Create or update a badge for the count
      let badge = cartIcon.querySelector('.cart-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5';
        cartIcon.classList.add('relative');
        cartIcon.appendChild(badge);
      }
      const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
      badge.textContent = totalItems;
      badge.style.display = totalItems > 0 ? 'block' : 'none';
    }

    // Update cart icon on page load
    document.addEventListener('DOMContentLoaded', function() {
      updateCartIcon();

      // Listen for cart updates from other tabs
      window.addEventListener('storage', (e) => {
        if (e.key === 'cart') {
          updateCartIcon();
        }
      });
    });
  </script>

</body>

</html>
