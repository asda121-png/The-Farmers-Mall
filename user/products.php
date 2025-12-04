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
                    <a href="/The-Farmers-Mall/The-Farmers-Mall/auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
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
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="vegetables">Vegetables (124)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="fruits">Fruits (89)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="dairy">Dairy (45)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="bakery">Bakery (32)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="meat">Meat (28)</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="seafood">Seafood (20)</label></li>
        </ul>
      </div>

      <div class="mb-6">
        <h3 class="font-medium mb-2 text-gray-800">Organic</h3>
        <label class="text-sm text-gray-700 inline-flex items-center">
          <input id="organicOnly" type="checkbox" class="mr-2">Organic Only
        </label>
      </div>

      <div class="mb-6">
        <h3 class="font-medium mb-2 text-gray-800">Price Range</h3>
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
        <!-- Initial 12 product cards (data-category, data-price, data-organic, data-name) -->
        <!-- 1 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="vegetables" data-price="24.99" data-organic="true" data-name="Fresh Vegetable Bundle" data-description="A fresh assortment of seasonal vegetables including carrots, spinach, and broccoli, perfect for healthy meals.">
          <img src="../images/products/Fresh Vegetable Box.png" alt="Fresh Vegetable Bundle" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Fresh Vegetable Bundle</h3>
            <p class="text-sm text-gray-500">Per kg</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱24.99</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 2 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="vegetables" data-price="5.99" data-organic="true" data-name="Organic Lettuce" data-description="Crisp and fresh organic lettuce, perfect for salads and sandwiches.">
          <img src="../images/products/Organic Lettuce.png" alt="Organic Lettuce" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Organic Lettuce</h3>
            <p class="text-sm text-gray-500">Per kg</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱200.00</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 3 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="fruits" data-price="8.99" data-organic="false" data-name="Fresh Strawberries" data-description="Juicy and sweet strawberries, handpicked at peak ripeness.">
          <img src="../images/products/strawberry.png" alt="Fresh Strawberries" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Fresh Strawberries</h3>
            <p class="text-sm text-gray-500">Per kg</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱89.99</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 4 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="dairy" data-price="65" data-organic="false" data-name="Farm Fresh Milk" data-description="Pure and fresh milk straight from local farms, rich in nutrients and perfect for daily consumption.">
          <img src="../images/products/Fresh Milk.png" alt="Farm Fresh Milk" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Farm Fresh Milk</h3>
            <p class="text-sm text-gray-500">Per liter</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱95.00</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 5 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="vegetables" data-price="32.75" data-organic="false" data-name="Baby Carrots" data-description="Sweet and crunchy baby carrots, perfect for snacking or adding to meals, grown locally for freshness.">
          <img src="../images/products/carrots.png" alt="Baby Carrots" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Baby Carrots</h3>
            <p class="text-sm text-gray-500">Per kg</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱32.75</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 6 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="bakery" data-price="45" data-organic="false" data-name="Artisan Bread" data-description="Freshly baked artisan bread with a crispy crust and soft interior, made with traditional methods and high-quality ingredients.">
          <img src="../images/products/bread.png" alt="Artisan Bread" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Artisan Bread</h3>
            <p class="text-sm text-gray-500">Per loaf</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱28.00</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 7 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="fruits" data-price="28.99" data-organic="false" data-name="Ripe Bananas" data-description="Perfectly ripe bananas, sweet and ready to eat, sourced from local plantations for optimal taste and nutrition.">
          <img src="../images/products/banana.png" alt="Ripe Bananas" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Ripe Bananas</h3>
            <p class="text-sm text-gray-500">Per kg</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱28.99</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 8 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="dairy" data-price="125.5" data-organic="false" data-name="Aged Cheddar" data-description="Rich and sharp aged cheddar cheese, matured to perfection for a bold flavor, ideal for cheese boards and cooking.">
          <img src="../images/products/cheese.png" alt="Aged Cheddar" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Aged Cheddar</h3>
            <p class="text-sm text-gray-500">Per 250g</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱120.00</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 9 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="vegetables" data-price="45" data-organic="true" data-name="Fresh Vegetable Box" data-description="A curated box of fresh vegetables, including a variety of greens and roots, sourced directly from local farms for maximum freshness.">
          <img src="../images/products/Fresh Vegetable Box.png" alt="Fresh Vegetable Box" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Fresh Vegetable Box</h3>
            <p class="text-sm text-gray-500">Bundle</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱38.00</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 10 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="fruits" data-price="15" data-organic="false" data-name="Banana" data-description="Fresh, ripe bananas, sweet and nutritious, ideal for a quick energy boost or baking, sourced from local plantations.">
          <img src="../images/products/banana.png" alt="Banana" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Banana</h3>
            <p class="text-sm text-gray-500">Per piece</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱150.00</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 11 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="vegetables" data-price="28" data-organic="false" data-name="Tomato" data-description="Juicy and ripe tomatoes, perfect for salads, sauces, or cooking, grown in local greenhouses for optimal flavor.">
          <img src="../images/products/Native tomato.jpg" alt="Tomato" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Tomato</h3>
            <p class="text-sm text-gray-500">Per kg</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱28.00</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

        <!-- 12 -->
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="vegetables" data-price="30.50" data-organic="false" data-name="Baby Carrots" data-description="Tender, crunchy baby carrots, perfect for dipping or adding to your favorite dishes.">
          <img src="../images/products/carrots.png" alt="Baby Carrots" class="w-full h-40 object-cover">
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Baby Carrots</h3>
            <p class="text-sm text-gray-500">Per kg</p>
            <div class="flex justify-between items-center mt-2">
              <p class="font-semibold text-green-700">₱32.75</p>
              <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        </div>

      </div>

      <div class="flex justify-center mt-10">
        <button id="loadMore" class="bg-green-700 text-white px-6 py-2 rounded-md hover:bg-green-800">Load More Products</button>
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

</body>
</html>
