

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<?php
// Include the header
include 'retailerheader.php';
?>

  <!-- Dashboard Content -->
  <main class="max-w-6xl mx-auto px-6 py-6 space-y-6 flex-grow w-full mb-20">

    <!-- Banner -->
    <div class="relative bg-cover bg-center h-64 rounded-lg overflow-hidden" style="background-image: url('../images/mesafarm.jpg');">
      <div class="absolute inset-0 bg-black bg-opacity-40"></div>
      <div class="absolute left-6 top-1/2 transform -translate-y-1/2 text-white">
        <h1 class="text-5xl font-bold mb-2">Mesa Farm</h1>
        <p class="text-lg">Fresh, organic produce straight from our farm to your table.</p>
      </div>
    </div>

    <!-- Title -->
    <div>
      <h2 class="text-2xl font-semibold">Dashboard Overview</h2>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
  <a href="retailerrevenue.php" class="block bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
    <div class="text-green-600 text-2xl"><i class="fa-solid fa-peso-sign"></i></div>
    <div>
      <p class="text-gray-500 text-sm">Revenue</p>
      <h3 id="revenueStat" class="text-xl font-semibold">₱0.00</h3>
    </div>
  </a>

  <a href="retailerorders.php" class="block bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
    <div class="text-green-600 text-2xl"><i class="fa-solid fa-cart-shopping"></i></div>
    <div>
      <p class="text-gray-500 text-sm">Orders</p>
      <h3 id="ordersStat" class="text-xl font-semibold">0</h3>
    </div>
  </a>

  <a href="retailercustomer.php" class="block bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
    <div class="text-green-600 text-2xl"><i class="fa-solid fa-users"></i></div>
    <div>
      <p class="text-gray-500 text-sm">Customers</p>
      <h3 id="customersStat" class="text-xl font-semibold">0</h3>
    </div>
  </a>

  <a href="retailerpending.php" class="block bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
    <div class="text-orange-500 text-2xl"><i class="fa-solid fa-clock"></i></div>
    <div>
      <p class="text-gray-500 text-sm">Pending</p>
      <h3 id="pendingStat" class="text-xl font-semibold">0</h3>
    </div>
  </a>
</div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left Side -->
      <div class="lg:col-span-7 flex flex-col gap-6">
        <!-- Sales Chart -->
        <div class="bg-white shadow-sm rounded-lg p-5 flex-1">
          <h3 class="font-semibold text-lg mb-4">Sales This Week</h3>
          <canvas id="salesChart"></canvas>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white shadow-sm rounded-lg p-5 flex-1">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-lg">Recent Orders</h3>
            <a href="retailerorders.php" class="text-green-600 text-sm font-medium">View All</a>
          </div>

          <div id="recentOrdersContainer" class="space-y-3"></div>
        </div>
      </div>

      <!-- Right Side -->
      <div class="lg:col-span-5 flex flex-col gap-6">
        <!-- Inventory Status -->
        <div class="bg-white shadow-sm rounded-lg p-5 flex-1">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-lg">Inventory Status</h3>
            <a href="retailerinventory.php" class="text-green-600 text-sm font-medium">Manage</a>
          </div>

          <div class="space-y-4 flex-1">
            <!-- Red Onions -->
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <img src="seller/red-onion.png" alt="Red Onions" class="w-6 h-6">
                <div>
                  <p class="font-medium">Red Onions</p>
                  <p class="text-sm text-gray-500">5 left in stock</p>
                </div>
              </div>
              <span class="text-sm text-red-600 bg-red-100 px-3 py-1 rounded-full">Low Stock</span>
            </div>

            <!-- Fresh Lettuce -->
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <img src="seller/lettuce.png" alt="Fresh Lettuce" class="w-6 h-6">
                <div>
                  <p class="font-medium">Fresh Lettuce</p>
                  <p class="text-sm text-gray-500">154 left in stock</p>
                </div>
              </div>
              <span class="text-sm text-green-600 bg-green-100 px-3 py-1 rounded-full">In Stock</span>
            </div>

            <!-- Carrots -->
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <img src="seller/carrots.png" alt="Carrots" class="w-6 h-6">
                <div>
                  <p class="font-medium">Carrots</p>
                  <p class="text-sm text-gray-500">Out of Stock</p>
                </div>
              </div>
              <span class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">Out of Stock</span>
            </div>
          </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white shadow-sm rounded-lg p-5 flex-1">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-lg">Top Products</h3>
            <a href="retailerproducts.php" class="text-green-600 text-sm font-medium">View All</a>
          </div>

          <div class="space-y-4 flex-1">
            <!-- Carrots -->
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <img src="seller/carrots.png" alt="Carrots" class="w-6 h-6">
                <div>
                  <p class="font-medium">Carrots</p>
                  <p class="text-sm text-gray-500">543 Sold</p>
                </div>
              </div>
              <span class="font-semibold text-green-700">₱2.99/kg</span>
            </div>

            <!-- Tomatoes -->
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <img src="seller/tomato.png" alt="Tomatoes" class="w-6 h-6">
                <div>
                  <p class="font-medium">Tomatoes</p>
                  <p class="text-sm text-gray-500">432 Sold</p>
                </div>
              </div>
              <span class="font-semibold text-green-700">₱4.50/kg</span>
            </div>

            <!-- Fresh Lettuce -->
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <img src="seller/lettuce.png" alt="Fresh Lettuce" class="w-6 h-6">
                <div>
                  <p class="font-medium">Fresh Lettuce</p>
                  <p class="text-sm text-gray-500">298 Sold</p>
                </div>
              </div>
              <span class="font-semibold text-green-700">₱3.25/kg</span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>

  <!-- Footer -->
  <footer class="text-white py-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      
      <!-- Logo/About -->
      <div>
        <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>
        <p class="text-gray-300 text-sm">
          Fresh, organic produce delivered straight to your home from local farmers.
        </p>
      </div>
      
      <!-- Quick Links -->
      <div>
        <h3 class="font-bold text-lg mb-3">Quick Links</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="#" class="hover:underline">About Us</a></li>
          <li><a href="#" class="hover:underline">Contact</a></li>
          <li><a href="#" class="hover:underline">FAQ</a></li>
          <li><a href="#" class="hover:underline">Support</a></li>
        </ul>
      </div>

      <!-- Categories -->
      <div>
        <h3 class="font-bold text-lg mb-3">Categories</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="#" class="hover:underline">Vegetables</a></li>
          <li><a href="#" class="hover:underline">Fruits</a></li>
          <li><a href="#" class="hover:underline">Dairy</a></li>
          <li><a href="#" class="hover:underline">Meat</a></li>
        </ul>
      </div>

      <!-- Social -->
      <div>
        <h3 class="font-bold text-lg mb-3">Follow Us</h3>
        <div class="flex space-x-4 text-xl">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>

  <!-- Chart Script -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const allOrders = JSON.parse(localStorage.getItem('sellerOrders')) || [];

      // --- Calculate Sales Data for the Current Week (Mon-Sun) ---
      const weeklySalesData = [0, 0, 0, 0, 0, 0, 0]; // Mon, Tue, Wed, Thu, Fri, Sat, Sun
      const today = new Date();
      const dayOfWeek = today.getDay(); // 0=Sun, 1=Mon, ..., 6=Sat

      // Adjust to make Monday the start of the week (day 0)
      const startOfWeek = new Date(today);
      const daysToSubtract = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
      startOfWeek.setDate(today.getDate() - daysToSubtract);
      startOfWeek.setHours(0, 0, 0, 0);

      const endOfWeek = new Date(startOfWeek);
      endOfWeek.setDate(startOfWeek.getDate() + 7);

      // Filter orders for the current week
      const thisWeeksOrders = allOrders.filter(order => {
        const orderDate = new Date(order.timestamp);
        return orderDate >= startOfWeek && orderDate < endOfWeek;
      });

      // Aggregate sales for each day
      thisWeeksOrders.forEach(order => {
        const orderDay = new Date(order.timestamp).getDay();
        const dayIndex = orderDay === 0 ? 6 : orderDay - 1; // Mon=0, ..., Sun=6
        weeklySalesData[dayIndex] += order.total;
      });

      // --- Initialize Chart with Dynamic Data ---
      const ctx = document.getElementById('salesChart');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Sales (₱)',
            data: weeklySalesData, // Use calculated data
            borderColor: '#2E7D32',
            backgroundColor: 'rgba(46,125,50,0.1)',
            tension: 0.3,
            fill: true
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
        }
      });
    });
  </script>
  
  <!-- Script to update dashboard stats -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Load data from localStorage
      const allOrders = JSON.parse(localStorage.getItem('sellerOrders')) || [];

      // Function to get unique customer count from orders
      const getCustomerCount = () => {
        if (allOrders.length === 0) return 0;
        const customerNames = allOrders.map(order => order.customerName);
        const uniqueCustomerNames = new Set(customerNames);
        return uniqueCustomerNames.size;
      };

      // Calculate stats
      const totalRevenue = allOrders.reduce((sum, order) => sum + order.total, 0);
      const totalOrders = allOrders.length;
      const pendingOrders = allOrders.filter(order => order.status === 'Pending').length;
      const totalCustomers = getCustomerCount();

      // Update the DOM
      document.getElementById('revenueStat').textContent = `₱${totalRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
      document.getElementById('ordersStat').textContent = totalOrders.toLocaleString();
      document.getElementById('customersStat').textContent = totalCustomers.toLocaleString();
      document.getElementById('pendingStat').textContent = pendingOrders.toLocaleString();
    });
  </script>

  <script>
    // Load recent orders dynamically
    document.addEventListener('DOMContentLoaded', () => {
      const recentOrdersContainer = document.getElementById('recentOrdersContainer');
      const sellerOrders = JSON.parse(localStorage.getItem('sellerOrders')) || [];

      recentOrdersContainer.innerHTML = ''; // Clear static content

      if (sellerOrders.length === 0) {
        recentOrdersContainer.innerHTML = '<p class="text-center text-gray-500 py-4">No recent orders found.</p>';
        return;
      }

      // Show only the 3 most recent orders
      const recentThree = sellerOrders.slice(0, 3);

      recentThree.forEach(order => {
        const orderDiv = document.createElement('div');
        orderDiv.className = 'flex justify-between items-center p-3 bg-gray-50 rounded-lg';

        let statusClass = 'bg-gray-100 text-gray-600';
        if (order.status === 'Delivered') {
          statusClass = 'bg-green-100 text-green-600';
        } else if (order.status === 'Shipped') {
          statusClass = 'bg-blue-100 text-blue-600';
        } else if (order.status === 'Pending') {
          statusClass = 'bg-orange-100 text-orange-600';
        }

        orderDiv.innerHTML = `
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fa-solid fa-user text-green-600"></i>
            </div>
            <div>
              <p class="font-medium">${order.customerName}</p>
              <p class="text-gray-500 text-sm">₱${order.total.toFixed(2)}</p>
            </div>
          </div>
          <span class="${statusClass} px-3 py-1 rounded-full text-sm">${order.status}</span>
        `;
        recentOrdersContainer.appendChild(orderDiv);
      });
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
