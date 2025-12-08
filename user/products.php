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
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>All Products – Farmers Mall</title>

  <!-- Tailwind + Font Awesome (CDN as in your project) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="../assets/css/productdetails.css">
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
                    id="globalSearch"
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
            <a href="cart.php" class="text-gray-600 hover:text-green-600 relative inline-block">
                <i class="fa-solid fa-cart-shopping"></i>
                <!-- Cart badge will be added by JavaScript -->
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


    

  <!-- Main content -->
  <main class="max-w-7xl mx-auto px-6 py-8 grid md:grid-cols-4 gap-8 flex-grow w-full mb-28">

    <!-- Sidebar filters -->
    <aside class="col-span-1 bg-white p-6 rounded-lg shadow-sm h-fit">
      <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-lg">Filters</h2>
        <button id="clearFilters" class="text-green-600 text-sm font-medium hover:underline">Clear All</button>
      </div>

      <div class="mb-6">
        <h3 class="font-medium mb-2 text-gray-800">Categories</h3>
        <ul class="space-y-2 text-sm text-gray-700">
          <?php
          // Fetch all products first to count categories
          require_once __DIR__ . '/../config/supabase-api.php';
          $api = getSupabaseAPI();
          $all_products = $api->select('products') ?: [];
          
          // Count products per category
          $category_counts = [];
          foreach ($all_products as $product) {
              $cat = strtolower(trim($product['category'] ?? ''));
              
              // Skip empty categories, "other", "organic", and "bakery"
              if (empty($cat) || $cat === 'other' || $cat === 'organic' || $cat === 'bakery') {
                  continue;
              }
              
              // Remove "organic" prefix if it exists (e.g., "organic vegetables" -> "vegetables")
              $cat = preg_replace('/^organic\s+/i', '', $cat);
              
              if (!isset($category_counts[$cat])) {
                  $category_counts[$cat] = 0;
              }
              $category_counts[$cat]++;
          }
          
          // Sort categories by count (descending)
          arsort($category_counts);
          
          // Display categories with actual counts (only if count > 0)
          foreach ($category_counts as $category => $count):
              if ($count > 0):
                  $category_display = ucfirst($category);
          ?>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category_display); ?> (<?php echo $count; ?>)</label></li>
          <?php 
              endif;
          endforeach; 
          ?>
        </ul>
      </div>
    </aside>

    <!-- Products -->
    <section class="col-span-3">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
          <a href="user-homepage.php" class="flex items-center text-green-600 hover:text-green-700 font-medium text-xl">
            <i class="fa-solid fa-arrow-left"></i>
          </a>
          <h2 class="text-xl font-semibold">All Products</h2>
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm text-gray-600">Sort by:</label>
          <select id="sortSelect" class="border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-green-500 focus:outline-none">
            <option value="featured">Featured</option>
            <option value="price-asc">Price: Low to High</option>
            <option value="price-desc">Price: High to Low</option>
            <option value="newest">Newest</option>
          </select>
        </div>
      </div>

      <!-- Grid -->
      <div id="productsGrid" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php
        // Fetch all products from Supabase
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

        foreach ($products as $prod):
            $name = htmlspecialchars($prod['name'] ?? $prod['product_name'] ?? 'Product');
            $priceVal = formatPriceValue($prod['price'] ?? $prod['amount'] ?? $prod['price_value'] ?? '0');
            $img = htmlspecialchars(resolveImagePath($prod['image'] ?? $prod['image_url'] ?? $prod['product_image'] ?? $prod['image_path'] ?? ''));
            $desc = htmlspecialchars($prod['description'] ?? '');
            $category = htmlspecialchars($prod['category'] ?? 'other');
            $id = htmlspecialchars($prod['id'] ?? '');
            $organic = isset($prod['is_organic']) && $prod['is_organic'] ? 'true' : 'false';
        ?>
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="<?php echo $category; ?>" data-price="<?php echo $priceVal; ?>" data-organic="<?php echo $organic; ?>" data-name="<?php echo $name; ?>" data-description="<?php echo $desc; ?>" data-id="<?php echo $id; ?>">
          <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800"><?php echo $name; ?></h3>
            <p class="text-sm text-gray-500">Per unit</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱<?php echo number_format((float)$priceVal, 2); ?></p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div id="loadMoreContainer" class="flex justify-center mt-10 hidden">
        <button id="loadMore" class="bg-green-700 text-white px-6 py-2 rounded-md hover:bg-green-800">Load More</button>
      </div>

    </section>
  </main>

  <!-- Footer -->
  <footer class="text-white py-12" style="background-color: #1B5E20;">
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

  <script src="../assets/js/products.js"></script>
  <script src="../assets/js/profile-sync.js"></script>

</body>
</html>
