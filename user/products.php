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

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="../assets/css/productdetails.css">
  
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

  <style>
    /* Fresh Vegetable Bundle Font Style */
    .fresh-title-font {
        font-family: 'Playfair Display', serif;
        letter-spacing: 0.5px;
    }

    /* Product card styling matching User Homepage */
    .product-card {
      display: flex;
      flex-direction: column;
      min-height: 22rem; 
      border: 2px solid transparent;
      border-radius: 0.5rem;
      transition: all 0.3s ease;
      position: relative;
    }

    /* ONLY Green Highlight Border on Hover - No Scale, No Shadow Change */
    .product-card:hover {
      border-color: #2E7D32;
      transform: none !important; /* Ensure no scaling */
    }

    .product-card img {
      height: 12rem;
      width: 100%;
      object-fit: cover;
      flex-shrink: 0;
    }

    .product-card > div {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between; 
      padding: 1rem;
    }

    /* Container for the bottom elements (Price & Button) matching homepage */
    .product-card-bottom {
      display: flex;
      align-items: flex-end; /* Aligns content to the bottom baseline */
      justify-content: space-between;
      width: 100%;
      margin-top: auto;
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
  </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<?php include __DIR__ . '/../includes/user-header.php'; ?>

  <main class="max-w-7xl mx-auto px-6 py-8 grid md:grid-cols-4 gap-8 flex-grow w-full mb-28">

    <aside class="col-span-1 bg-white p-6 rounded-lg shadow-sm h-fit">
      <div class="flex justify-between items-center mb-4">
        <h2 class="font-bold text-base fresh-title-font">Filters</h2>
        <button id="clearFilters" class="text-green-600 text-sm font-medium hover:underline">Clear All</button>
      </div>

      <div class="mb-6">
        <h3 class="font-bold mb-2 text-gray-800 fresh-title-font text-sm">Categories</h3>
        <ul class="space-y-2 text-sm text-gray-700">
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="vegetables">Vegetables (124)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="fruits">Fruits (89)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="dairy">Dairy (45)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="bakery">Bakery (32)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="meat">Meat (28)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="seafood">Seafood (20)</label></li>
        </ul>
      </div>

      <div class="mb-6">
        <h3 class="font-bold mb-2 text-gray-800 fresh-title-font text-sm">Organic</h3>
        <label class="text-sm text-gray-700 inline-flex items-center">
          <input id="organicOnly" type="checkbox" class="mr-2">Organic Only
        </label>
      </div>

      <div class="mb-6">
        <h3 class="font-bold mb-2 text-gray-800 fresh-title-font text-sm">Price Range</h3>
        <div class="flex items-center space-x-2 mb-2">
          <input id="minPrice" type="number" placeholder="Min" class="w-1/2 border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-green-500 focus:outline-none">
          <span>-</span>
          <input id="maxPrice" type="number" placeholder="Max" class="w-1/2 border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-green-500 focus:outline-none">
        </div>
        <input id="priceRange" type="range" min="0" max="500" value="500" class="w-full accent-green-600">
      </div>

      <div>
        <button id="applyFilters" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Apply Filters</button>
      </div>
    </aside>

    <section class="col-span-3">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
          <a href="user-homepage.php" class="flex items-center text-green-600 hover:text-green-700 font-medium text-xl">
            <i class="fa-solid fa-arrow-left"></i>
          </a>
          <h2 class="text-lg font-bold fresh-title-font">All Products</h2>
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

      <?php 
      $search_query = $_GET['search'] ?? '';
      if (!empty($search_query)): 
      ?>
        <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
          <p class="text-gray-700">
            <span class="font-semibold">Search results for:</span> 
            <span class="text-green-700">"<?php echo htmlspecialchars($search_query); ?>"</span>
            <span id="searchResultCount" class="text-gray-500 text-sm ml-2"></span>
          </p>
        </div>
      <?php endif; ?>

      <div id="productsGrid" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php
        // Fetch all products from Supabase
        require_once __DIR__ . '/../config/supabase-api.php';
        $api = getSupabaseAPI();
        $products = $api->select('products') ?: [];
        
        // Filter by search query if provided
        $search_query = $_GET['search'] ?? '';
        if (!empty($search_query)) {
          $search_lower = strtolower($search_query);
          $products = array_filter($products, function($prod) use ($search_lower) {
            $name = strtolower($prod['name'] ?? '');
            $desc = strtolower($prod['description'] ?? '');
            $cat = strtolower($prod['category'] ?? '');
            return strpos($name, $search_lower) !== false || 
                   strpos($desc, $search_lower) !== false || 
                   strpos($cat, $search_lower) !== false;
          });
          $products = array_values($products); // Re-index array
        }

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
            
            // Calculate sold count logic similar to user-homepage.php
            $sold = $prod['units_sold'] ?? $prod['times_ordered'] ?? $prod['qty_sold'] ?? 0;
        ?>
        <div class="product-card bg-white rounded-lg shadow transition relative block overflow-hidden h-full" 
             data-category="<?php echo $category; ?>" 
             data-price="<?php echo $priceVal; ?>" 
             data-organic="<?php echo $organic; ?>" 
             data-name="<?php echo $name; ?>" 
             data-description="<?php echo $desc; ?>" 
             data-id="<?php echo $id; ?>">
             
          <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" class="w-full h-40 object-cover">
          <div>
            <div class="w-full">
                <h3 class="mt-2 font-bold text-sm leading-tight mb-1"><?php echo $name; ?></h3>
                <p class="text-xs text-gray-500 mb-2"><?php echo ucfirst($category); ?></p>
            </div>

            <div class="product-card-bottom">
                <p class="text-green-600 font-bold text-lg">₱<?php echo number_format((float)$priceVal, 2); ?></p>
                
                <div class="flex flex-col items-end">
                    <button class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition flex-shrink-0" title="Add to cart">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <p class="text-xs text-gray-400 mt-1 whitespace-nowrap"><?php echo $sold; ?> sold</p>
                </div>
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
  <script>
    // Update search result count and pre-fill search input
    document.addEventListener('DOMContentLoaded', function() {
      const searchQuery = '<?php echo htmlspecialchars($search_query ?? '', ENT_QUOTES, 'UTF-8'); ?>';
      if (searchQuery) {
        // Wait for products.js to filter, then count visible products
        setTimeout(() => {
          const productCards = document.querySelectorAll('.product-card');
          const visibleCount = Array.from(productCards).filter(card => {
            const style = window.getComputedStyle(card);
            return style.display !== 'none';
          }).length;
          const resultCountEl = document.getElementById('searchResultCount');
          if (resultCountEl) {
            resultCountEl.textContent = `(${visibleCount} product${visibleCount !== 1 ? 's' : ''} found)`;
          }
        }, 100);
        
        // Pre-fill search input with query
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
          searchInput.value = searchQuery;
        }
      }
    });
  </script>

</body>
</html>