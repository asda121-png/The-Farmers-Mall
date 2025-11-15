<?php
// Start session for user context
session_start();

// --- Configuration ---
$servername = "localhost";
$db_username = "root";
$db_password = ""; // No password
$dbname = "farmers";

// Function to establish database connection
function getDbConnection() {
    global $servername, $db_username, $db_password, $dbname;
    
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        error_log("Database Connection Failed: " . $conn->connect_error);
        return null;
    }
    return $conn;
}

// Fetch all products from database
$products = [];
$conn = getDbConnection();

if ($conn) {
    $sql = "SELECT product_id, name, price, image_path, category, is_top_product FROM Products ORDER BY product_id ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $conn->close();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username'] ?? 'User') : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>All Products – The Farmer's Mall</title>

  <!-- Tailwind + Font Awesome (CDN as in your project) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

  <!-- Navbar -->
   <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <h1 class="text-xl font-bold" style="color: #2E7D32;"><a href="homepage.php" > The Farmer's Mall</a></h1>

      <div class="flex-1 mx-6">
        <input 
  id="globalSearch"
  type="text" 
  placeholder="Search for fresh product..."
  class="w-full px-4 py-2 border rounded-full focus:ring-2 focus:ring-green-500 focus:outline-none"
/>
      </div>

      <div class="flex items-center space-x-6">
        <a href="message.html" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
        <a href="notification.html" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
        <a href="cart.html" class="text-gray-600 relative"><i class="fa-solid fa-cart-shopping"></i></a>
        <?php if ($isLoggedIn): ?>
            <!-- Logged In User Profile Dropdown -->
            <div class="relative group">
                <button class="flex items-center space-x-2 cursor-pointer hover:opacity-80 transition-opacity">
                    <img src="images/karl.png" alt="User" class="w-8 h-8 rounded-full" onerror="this.onerror=null; this.src='https://placehold.co/32x32/333333/ffffff?text=U'">
                    <span class="text-sm font-medium text-gray-700"><?= $username ?></span>
                </button>
                <!-- Dropdown Menu -->
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <a href="profile.html" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-t-lg"><i class="fas fa-user mr-2"></i>My Profile</a>
                    <a href="orders.php" class="block px-4 py-2 text-gray-700 hover:bg-green-50"><i class="fas fa-shopping-bag mr-2"></i>My Orders</a>
                    <a href="account.html" class="block px-4 py-2 text-gray-700 hover:bg-green-50"><i class="fas fa-cog mr-2"></i>Account Settings</a>
                    <hr class="my-2">
                    <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Not Logged In - Show Profile Icon -->
            <a href="login.php">
                <img src="images/karl.png" alt="User" class="w-8 h-8 rounded-full cursor-pointer hover:opacity-80 transition-opacity" onerror="this.onerror=null; this.src='https://placehold.co/32x32/333333/ffffff?text=U'">
            </a>
        <?php endif; ?>
      </div>
    </div>
  </header>

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
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="vegetables">Vegetables</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="fruits">Fruits</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="dairy">Dairy</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="bakery">Bakery</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="meat">Meat</label></li>
          <li><label class="inline-flex items-center"><input type="checkbox" class="category-checkbox mr-2" data-cat="seafood">Seafood</label></li>
        </ul>
      </div>

      <div class="mb-6">
        <h3 class="font-medium mb-2 text-gray-800">Organic</h3>
        <label class="text-sm text-gray-700 inline-flex items-center">
          <input id="organicOnly" type="checkbox" class="mr-2">Top Products Only
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
        <h2 class="text-xl font-semibold">All Products</h2>
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

      <!-- Grid of Products from Database -->
      <div id="productsGrid" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php
        if (count($products) > 0) {
            foreach ($products as $product) {
                $name = htmlspecialchars($product['name']);
                $price = number_format($product['price'], 2);
                $category = htmlspecialchars($product['category']);
                $image = htmlspecialchars($product['image_path'] ?? '');
                $isTopProduct = $product['is_top_product'] ? 'true' : 'false';
                $productId = htmlspecialchars($product['product_id']);
                
                // Determine unit suffix based on category
                $unitSuffix = ($category === 'meat' || $category === 'seafood') ? 'Per kg' : 'Per unit';
                
                // Use placeholder if image is missing
                $placeholder = 'https://placehold.co/200x160/eeeeee/333333?text=No+Image';
                
                echo '
                <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition" data-category="' . $category . '" data-price="' . $product['price'] . '" data-organic="' . $isTopProduct . '" data-name="' . $name . '" data-id="' . $productId . '">
                  <img src="' . $image . '" alt="' . $name . '" class="w-full h-40 object-cover" onerror="this.onerror=null; this.src=\'' . $placeholder . '\'">
                  <div class="p-4">
                    <h3 class="font-medium text-gray-800">' . $name . '</h3>
                    <p class="text-sm text-gray-500">' . $unitSuffix . '</p>
                    <div class="flex justify-between items-center mt-2">
                      <p class="font-semibold text-green-700">₱' . $price . '</p>
                      <button class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                    </div>
                  </div>
                </div>
                ';
            }
        } else {
            echo '<p class="col-span-full text-center text-gray-500">No products found in the database.</p>';
        }
        ?>
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
        <h3 class="font-bold text-lg mb-3">The Farmer's Mall</h3>
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
      © 2025 The Farmer's Mall. All rights reserved.
    </div>
  </footer>

  <!-- Script: filtering, sorting, search, load more -->
  <script>
  (function () {
    // helpers
    const $ = sel => document.querySelector(sel);
    const $$ = sel => Array.from(document.querySelectorAll(sel));

    // dynamic references (use functions to always get current nodes)
    const getCategoryCheckboxes = () => Array.from(document.querySelectorAll('.category-checkbox'));
    const getProductCards = () => Array.from(document.querySelectorAll('.product-card'));
    const getAddButtons = () => Array.from(document.querySelectorAll('.add-btn'));

    const organicCheckbox = $('#organicOnly');
    const minPriceInput = $('#minPrice');
    const maxPriceInput = $('#maxPrice');
    const priceRange = $('#priceRange');
    const applyBtn = $('#applyFilters');
    const clearBtn = $('#clearFilters');
    const productsGrid = $('#productsGrid');
    const sortSelect = $('#sortSelect');
    const globalSearch = $('#globalSearch');
    const loadMoreBtn = $('#loadMore');

    const params = new URLSearchParams(window.location.search);
    const initialCategory = params.get('category'); // may be comma separated
    const initialSearch = params.get('search');

    // Show/hide helpers
    function showCard(card) { card.style.display = ''; }
    function hideCard(card) { card.style.display = 'none'; }

    // Filter logic
    function filterProducts() {
      const categoryCheckboxes = getCategoryCheckboxes();
      const activeCats = categoryCheckboxes.filter(cb => cb.checked).map(cb => cb.dataset.cat.toLowerCase());
      const organicOnly = organicCheckbox.checked;
      const minP = parseFloat(minPriceInput.value) || 0;
      const maxP = parseFloat(maxPriceInput.value) || Number.POSITIVE_INFINITY;
      const searchText = (globalSearch.value || '').trim().toLowerCase();

      getProductCards().forEach(card => {
        const catStr = (card.dataset.category || '').toLowerCase();
        const itemCats = catStr.split(',').map(s => s.trim()).filter(Boolean);
        const price = parseFloat(card.dataset.price) || 0;
        const isOrganic = String(card.dataset.organic) === 'true';
        const name = (card.dataset.name || '').toLowerCase();

        const categoryMatch = activeCats.length === 0 || activeCats.some(c => itemCats.includes(c));
        const organicMatch = !organicOnly || isOrganic;
        const priceMatch = price >= minP && price <= maxP;
        const searchMatch = !searchText || name.includes(searchText);

        if (categoryMatch && organicMatch && priceMatch && searchMatch) {
          showCard(card);
        } else {
          hideCard(card);
        }
      });

      applySort(); // reorder visible cards
    }

    // Sorting
    function applySort() {
      const mode = sortSelect.value;
      const visible = getProductCards().filter(c => c.style.display !== 'none');
      let sorted = visible.slice();

      if (mode === 'price-asc') {
        sorted.sort((a,b) => (parseFloat(a.dataset.price)||0) - (parseFloat(b.dataset.price)||0));
      } else if (mode === 'price-desc') {
        sorted.sort((a,b) => (parseFloat(b.dataset.price)||0) - (parseFloat(a.dataset.price)||0));
      } else if (mode === 'newest') {
        // if you had data-date you could sort here; leave as-is for now
      } else {
        // featured - do nothing
      }

      sorted.forEach(card => productsGrid.appendChild(card));
    }

    // Initialize from URL (auto-check category and filter)
    function initFromURL() {
      if (initialCategory) {
        const catsFromUrl = initialCategory.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
        getCategoryCheckboxes().forEach(cb => {
          cb.checked = catsFromUrl.includes(cb.dataset.cat.toLowerCase());
        });
        filterProducts();
        setTimeout(() => {
          const top = productsGrid.getBoundingClientRect().top + window.scrollY - 80;
          window.scrollTo({ top, behavior: 'smooth' });
        }, 120);
      }
      if (initialSearch) {
        globalSearch.value = initialSearch;
      }
      // Always filter on load to apply any URL params from homepage or category links
      filterProducts();
    }

    // Wire up event listeners
    function bindEventListeners() {
      getCategoryCheckboxes().forEach(cb => cb.addEventListener('change', filterProducts));
      organicCheckbox.addEventListener('change', filterProducts);
      applyBtn.addEventListener('click', filterProducts);

      priceRange.addEventListener('input', (e) => {
        maxPriceInput.value = e.target.value;
      });
      minPriceInput.addEventListener('change', filterProducts);
      maxPriceInput.addEventListener('change', filterProducts);

      sortSelect.addEventListener('change', applySort);

      clearBtn.addEventListener('click', () => {
        getCategoryCheckboxes().forEach(cb => cb.checked = false);
        organicCheckbox.checked = false;
        minPriceInput.value = '';
        maxPriceInput.value = '';
        priceRange.value = priceRange.max || 500;
        globalSearch.value = '';
        filterProducts();
      });

      let searchTimeout = null;
      globalSearch.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterProducts, 250);
      });

      // Add to cart functionality (event delegation)
      productsGrid.addEventListener('click', (e) => {
        const btn = e.target.closest('.add-btn');
        if (btn) {
          e.preventDefault(); // Prevent any default link behavior
          const card = btn.closest('.product-card');
          const name = card?.dataset.name || 'Item';
          const price = parseFloat(card?.dataset.price || '0');
          const image = card?.querySelector('img')?.src || '';
          const productId = card?.dataset.id || '';

          let cart = JSON.parse(localStorage.getItem('cart')) || [];
          const existingItem = cart.find(item => item.name === name);

          if (existingItem) {
            existingItem.quantity = (existingItem.quantity || 1) + 1;
          } else {
            cart.push({ name, price, image, productId, quantity: 1 });
          }

          localStorage.setItem('cart', JSON.stringify(cart));
          updateCartIcon();
          // Show a success message
          showToast(name + ' added to cart!', 'success');
          return; // Stop further actions
        }

        // If the click was not on the add button, handle navigation to product details
        const card = e.target.closest('.product-card');
        if (card) {
          const name = card?.dataset.name || 'Item';
          const price = card?.dataset.price || '0';
          const img = card?.querySelector('img')?.src || '';
          const productId = card?.dataset.id || '';
          const selectedProduct = { name, price, img, productId };
          localStorage.setItem('selectedProduct', JSON.stringify(selectedProduct));
          window.location.href = 'productdetails.html';
        }
      });

      // Load more: append another batch of 12 products
      loadMoreBtn.addEventListener('click', () => {
        appendMoreProducts();
      });
    }

    // Show toast notification
    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = 'fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 shadow-lg';
      toast.style.backgroundColor = type === 'success' ? '#16a34a' : '#3b82f6';
      toast.textContent = message;
      document.body.appendChild(toast);

      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 2500);
    }

    // Utility to create a product card element
    function createProductCard(product) {
      const div = document.createElement('div');
      div.className = 'product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition';
      div.setAttribute('data-category', product.category);
      div.setAttribute('data-price', product.price);
      div.setAttribute('data-organic', product.organic ? 'true' : 'false');
      div.setAttribute('data-name', product.name);
      div.setAttribute('data-id', product.id || '');

      const placeholder = 'https://placehold.co/200x160/eeeeee/333333?text=No+Image';
      const unitSuffix = (product.category === 'meat' || product.category === 'seafood') ? 'Per kg' : 'Per unit';

      div.innerHTML = `
        <img src="${product.img}" alt="${escapeHtml(product.name)}" class="w-full h-40 object-cover" onerror="this.onerror=null; this.src='${placeholder}'">
        <div class="p-4">
          <h3 class="font-medium text-gray-800">${escapeHtml(product.name)}</h3>
          <p class="text-sm text-gray-500">${unitSuffix}</p>
          <div class="flex justify-between items-center mt-2">
            <p class="font-semibold text-green-700">₱${Number(product.price).toFixed(2)}</p>
            <button class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700" title="Add to cart">
              <i class="fa-solid fa-plus"></i>
            </button>
          </div>
        </div>
      `;
      return div;
    }

    // Escape HTML to prevent accidental injection from generated content
    function escapeHtml(s) {
      return String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#39;');
    }

    // Sample batch generator - returns 12 product objects (can be adjusted to fetch from API)
    let loadBatchCount = 0;
    function generateBatchProducts() {
      // simple rotating sample products to append — images reused from your set
      const sample = [
        { name: 'Kangkong Bunch', category: 'vegetables', price: 22.50, organic: false, img: 'images/products/img11.png', unit: 'Per bunch' },
        { name: 'Cucumber', category: 'vegetables', price: 30.00, organic: false, img: 'images/products/img12.png', unit: 'Per kg' },
        { name: 'Green Pepper', category: 'vegetables', price: 55.00, organic: false, img: 'images/products/img13.png', unit: 'Per kg' },
        { name: 'Mango (Carabao)', category: 'fruits', price: 120.00, organic: false, img: 'images/products/img14.png', unit: 'Per kg' },
        { name: 'Pineapple', category: 'fruits', price: 80.00, organic: false, img: 'images/products/img15.png', unit: 'Each' },
        { name: 'Goat Milk', category: 'dairy', price: 150.00, organic: false, img: 'images/products/img16.png', unit: 'Per liter' },
        { name: 'Sourdough Loaf', category: 'bakery', price: 65.00, organic: false, img: 'images/products/img5.png', unit: 'Per loaf' },
        { name: 'Pork Belly', category: 'meat', price: 320.00, organic: false, img: 'images/products/img17.png', unit: 'Per kg' },
        { name: 'Tilapia (Fresh)', category: 'seafood', price: 140.00, organic: false, img: 'images/products/img18.png', unit: 'Per kg' },
        { name: 'Local Cheese', category: 'dairy', price: 95.00, organic: false, img: 'images/products/img19.png', unit: 'Per 200g' },
        { name: 'Eggs (Free Range)', category: 'dairy', price: 70.00, organic: true, img: 'images/products/img5.png', unit: 'Dozen' },
        { name: 'Sweet Potato', category: 'vegetables', price: 40.00, organic: false, img: 'images/products/img20.png', unit: 'Per kg' }
      ];

      // shift starting index based on batch count to vary names if user clicks many times
      const start = (loadBatchCount * 12) % sample.length;
      const batch = [];
      for (let i=0; i<12; i++) {
        const s = sample[(start + i) % sample.length];
        // create a shallow copy and tweak name to remain unique-ish
        batch.push({
          name: `${s.name}${loadBatchCount ? ' — batch ' + (loadBatchCount+1) : ''}`,
          category: s.category,
          price: s.price,
          organic: s.organic,
          img: s.img,
          unit: s.unit
        });
      }
      loadBatchCount++;
      return batch;
    }

    // Append more products (12)
    function appendMoreProducts() {
      const more = generateBatchProducts();
      const fragment = document.createDocumentFragment();
      more.forEach(p => {
        const card = createProductCard(p);
        fragment.appendChild(card);
      });
      productsGrid.appendChild(fragment);

      // After appending, immediately apply current filters so newly added products respect active filters
      filterProducts();

      // smooth scroll to the first newly added card
      const all = getProductCards();
      const firstNew = all[all.length - more.length];
      if (firstNew) {
        firstNew.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }

    // Update cart icon with item count
    function updateCartIcon() {
      const cart = JSON.parse(localStorage.getItem('cart')) || [];
      const cartLink = document.querySelector('a[href="cart.html"]');
      if (!cartLink) return;

      // Create or update a badge for the count
      let badge = cartLink.querySelector('.cart-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5';
        cartLink.classList.add('relative');
        cartLink.appendChild(badge);
      }
      const totalQuantity = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
      badge.textContent = totalQuantity;
      if (totalQuantity === 0) badge.style.display = 'none';
      else badge.style.display = '';
    }

    // Initialization
    function init() {
      bindEventListeners();
      initFromURL();
      updateCartIcon(); // Update on page load
    }

    init();
  })();
  </script>

</body>
</html>
