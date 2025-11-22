<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<bo class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
  <!-- Header -->
  <header class="bg-white border-b shadow-sm sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">

      <!-- Left: Logo -->
      <div class="flex items-center space-x-3 cursor-pointer" onclick="window.location.href='retailerdashboard.php'">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <h1 class="text-xl font-bold text-green-700">Farmers Mall</h1>
      </div>

      <!-- Center: Search -->
      <div class="flex-1 mx-8 max-w-xl">
        <form action="retailersearchresults.php" method="GET" class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input id="searchInput" type="search" name="q" placeholder="Search orders, products, customers..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-green-600 focus:border-green-600 text-sm">
        </form>
      </div>

      <!-- Right: Icons & Profile -->
      <div class="flex items-center space-x-6">
        <a href="retailermessage.php" class="relative cursor-pointer">
          <i class="fa-regular fa-comment text-xl text-gray-600"></i>
        </a>
        <a href="retailernotifications.php" class="relative cursor-pointer">
          <i class="fa-regular fa-bell text-xl text-gray-600"></i>
        </a>
        <a href="retailerprofile.php" class="flex items-center space-x-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-8 h-8 rounded-full" alt="Seller Profile">
        </a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-grow">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <!-- Title -->
      <div class="flex items-center space-x-3 mb-6">
        <button onclick="window.history.back()" class="text-gray-600 hover:text-black">
          <i class="fa-solid fa-arrow-left text-lg"></i>
        </button>
        <div>
          <h2 id="resultsTitle" class="text-lg font-semibold">Search Results</h2>
          <p id="resultsCount" class="text-sm text-gray-500">Searching...</p>
        </div>
      </div>

      <!-- Results Container -->
      <div id="resultsContainer" class="space-y-4">
        <!-- JS will populate this area -->
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

  <script src="../js/retailersearchresults.js"></script>
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('searchInput');
      const resultsTitle = document.getElementById('resultsTitle');
      const resultsCount = document.getElementById('resultsCount');
      const resultsContainer = document.getElementById('resultsContainer');

      const params = new URLSearchParams(window.location.search);
      const query = params.get('q')?.toLowerCase() || '';

      // Pre-fill search bar and title
      searchInput.value = query;
      resultsTitle.textContent = `Search Results for "${query}"`;

      if (!query) {
        resultsCount.textContent = 'Please enter a search term.';
        return;
      }

      // Load data from localStorage
      const products = JSON.parse(localStorage.getItem('sellerProducts')) || [];
      const orders = JSON.parse(localStorage.getItem('sellerOrders')) || [];

      let resultsHtml = '';
      let count = 0;

      // Search products
      const matchingProducts = products.filter(p => p.name.toLowerCase().includes(query));
      matchingProducts.forEach(p => {
        count++;
        resultsHtml += `
          <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between hover:bg-gray-50">
            <div class="flex items-center gap-4">
              <i class="fa-solid fa-tag text-green-500 text-xl"></i>
              <div>
                <p class="font-medium">${p.name}</p>
                <p class="text-sm text-gray-500">Product</p>
              </div>
            </div>
            <a href="retailerproducts.php" class="text-green-600 text-sm font-medium">View Product</a>
          </div>
        `;
      });

      // Search orders
      const matchingOrders = orders.filter(o => o.id.toLowerCase().includes(query) || o.customerName.toLowerCase().includes(query));
      matchingOrders.forEach(o => {
        count++;
        resultsHtml += `
          <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between hover:bg-gray-50">
            <div class="flex items-center gap-4">
              <i class="fa-solid fa-receipt text-blue-500 text-xl"></i>
              <div>
                <p class="font-medium">Order ${o.id}</p>
                <p class="text-sm text-gray-500">Customer: ${o.customerName}</p>
              </div>
            </div>
            <a href="retailerorderdetails.php?orderId=${o.id}" onclick="localStorage.setItem('selectedSellerOrder', JSON.stringify(o));" class="text-green-600 text-sm font-medium">View Order</a>
          </div>
        `;
      });

      // Display results
      if (count > 0) {
        resultsCount.textContent = `Found ${count} result(s).`;
        resultsContainer.innerHTML = resultsHtml;
      } else {
        resultsCount.textContent = 'No results found.';
        resultsContainer.innerHTML = '<p class="text-center text-gray-500 py-10">Try searching for something else.</p>';
      }
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // --- Real-time Badge Update ---
      const count = localStorage.getItem('unreadNotifications');
      const badge = document.querySelector('a[href="retailernotifications.php"] .absolute');
      if (badge) {
        if (count && parseInt(count) > 0) {
          badge.textContent = count;
          badge.style.display = 'inline-flex';
        } else {
          badge.style.display = 'none';
        }
      }
    });
  </script>

</body>

</html>
