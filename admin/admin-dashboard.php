<?php
// PHP file structure starts here. All static content is enclosed in HTML/JS/CSS below.
// In a real-world scenario, dynamic data fetching and authentication would happen here.
$admin_name = "Admin User";
$admin_email = "admin@farmersmall.com";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
  <style>
    /* Global Styles */
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7f9fc; /* Light background for high-end feel */
    }

    /* Custom Scrollbar for Sleek Look */
    .custom-scrollbar::-webkit-scrollbar {
      width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
      background-color: #4b5563; /* Darker scrollbar */
      border-radius: 2px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
      background: transparent;
    }

    /* Custom Shadow for High-End Cards */
    .card-shadow {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }
    
    /* Ensure Chart.js container is responsive */
    .chart-container {
      height: 300px; /* Fixed height for consistency */
    }
    
    /* Custom Green Color for Branding */
    .bg-green-950 {
        background-color: #184D34; /* Deep, rich green from image_f15fa1.png */
    }
    .border-green-950 {
        border-color: #184D34;
    }
  </style>
</head>

<body class="flex min-h-screen bg-gray-50 text-gray-800">

  <!-- Sidebar (Deep Green - Primary Brand Color) -->
  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300">
    <div>
      <!-- Logo and Title -->
      <div class="flex items-center gap-3 mb-8 px-2 py-2">
        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-white">
          <i class="fas fa-leaf text-green-700 text-lg"></i>
        </div>
        <h1 class="text-xl font-bold">Farmers Mall</h1>
      </div>

      <!-- Navigation: GENERAL -->
      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest mb-2 px-2">GENERAL</p>
      <nav class="space-y-1">
  <a href="admin-dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
    <i class="fa-solid fa-tachometer-alt w-5 text-green-200"></i>
    <span>Dashboard</span>
  </a>

  <a href="admin-products.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
    <i class="fa-solid fa-box w-5"></i>
    <span>Products</span>
  </a>

  <a href="admin-inventory.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
    <i class="fa-solid fa-truck-ramp-box w-5"></i>
    <span>Inventory</span>
  </a>

 
  <a href="admin-orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
    <i class="fa-solid fa-receipt w-5"></i>
    <span>Orders</span>
  </a>
</nav>

      <!-- Navigation: ACCOUNT -->
      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
        <a href="admin-help.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-circle-info w-5"></i>
          <span>Help</span>
        </a>
        <a href="admin-manage-users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-user-gear w-5"></i>
          <span>Manage Users</span>
        </a>
      </nav>
    </div>

    <!-- Logout Button -->
    <div class="mt-8 pt-4 border-t border-green-800">
      <button id="logoutButton" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200 text-gray-300">
        <i class="fa-solid fa-sign-out-alt w-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- Main Content Area -->
  <div class="flex-1 p-6 space-y-6 custom-scrollbar">

    <!-- Top Header and Search Bar -->
    <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-6 z-10 w-full">
      <div class="relative w-full max-w-lg hidden md:block">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" placeholder="Search products, orders, customers..."
          class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
      </div>

      <!-- Right Header Icons -->
      <div class="flex items-center gap-4 ml-auto">
        <i class="fa-solid fa-chart-column text-xl text-gray-500 hover:text-green-600 cursor-pointer hidden sm:block"></i>
        <i class="fa-regular fa-bell text-xl text-gray-500 hover:text-green-600 cursor-pointer relative">
            <span class="absolute -top-1 -right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
        </i>
        <i class="fa-regular fa-bookmark text-xl text-gray-500 hover:text-green-600 cursor-pointer hidden sm:block"></i>
        <div class="w-px h-6 bg-gray-200 mx-2 hidden sm:block"></div>
        <div class="flex items-center gap-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
        </div>
      </div>
    </header>
    
    <!-- Welcome Banner & Controls -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-2 md:mb-0">Welcome Back, <?php echo $admin_name; ?>!</h2>
        <div class="flex gap-3">
            <select class="p-2 border border-gray-300 rounded-lg text-sm bg-white hover:border-green-500 cursor-pointer transition-colors">
                <option>Previous Year</option>
                <option>Last 30 Days</option>
                <option>Last 7 Days</option>
            </select>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                View All Time
            </button>
        </div>
    </div>

    <!-- 1. Stat Cards (Updated: Removed colored top borders) -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">

        <!-- Card 1: Revenue (Positive) -->
        <div class="bg-green-50 rounded-xl p-5 card-shadow">
            <p class="text-sm font-medium text-gray-500 mb-1">Ecommerce Revenue</p>
            <h3 class="text-2xl font-extrabold text-gray-900">₱245,450</h3>
            <div class="flex items-center text-sm mt-2">
                <i class="fa-solid fa-arrow-up text-green-600 mr-1"></i>
                <span class="text-green-600 font-semibold">+34.9%</span>
                <span class="text-gray-500 ml-1">(+43.21 K)</span>
            </div>
        </div>

        <!-- Card 2: New Customers (Negative Trend - Neutral Color) -->
        <div class="bg-green-50 rounded-xl p-5 card-shadow">
            <p class="text-sm font-medium text-gray-500 mb-1">New Customers</p>
            <h3 class="text-2xl font-extrabold text-gray-900">684</h3>
            <div class="flex items-center text-sm mt-2">
                <i class="fa-solid fa-arrow-down text-red-600 mr-1"></i>
                <span class="text-red-600 font-semibold">-8.6%</span>
                <span class="text-gray-500 ml-1">(-64)</span>
            </div>
        </div>

        <!-- Card 3: Reject Purchase Rate (Warning/Negative) -->
        <div class="bg-green-50 rounded-xl p-5 card-shadow">
            <p class="text-sm font-medium text-gray-500 mb-1">Reject Purchase Rate</p>
            <h3 class="text-2xl font-extrabold text-gray-900">75.12 %</h3>
            <div class="flex items-center text-sm mt-2">
                <i class="fa-solid fa-arrow-up text-red-600 mr-1"></i>
                <span class="text-red-600 font-semibold">+25.4 %</span>
                <span class="text-gray-500 ml-1">(+20.11 K)</span>
            </div>
        </div>
        
        <!-- Card 4: Average Order Value (Positive) -->
        <div class="bg-green-50 rounded-xl p-5 card-shadow">
            <p class="text-sm font-medium text-gray-500 mb-1">Average Order Value</p>
            <h3 class="text-2xl font-extrabold text-gray-900">₱2,412.23</h3>
            <div class="flex items-center text-sm mt-2">
                <i class="fa-solid fa-arrow-up text-green-600 mr-1"></i>
                <span class="text-green-600 font-semibold">+35.2 %</span>
                <span class="text-gray-500 ml-1">(+₱744)</span>
            </div>
        </div>
        
        <!-- Card 5: Conversion Rate (Negative Trend - Warning Color) -->
        <div class="bg-green-50 rounded-xl p-5 card-shadow">
            <p class="text-sm font-medium text-gray-500 mb-1">Conversion Rate</p>
            <h3 class="text-2xl font-extrabold text-gray-900">32.65 %</h3>
            <div class="flex items-center text-sm mt-2">
                <i class="fa-solid fa-arrow-down text-red-600 mr-1"></i>
                <span class="text-red-600 font-semibold">-12.62 %</span>
                <span class="text-gray-500 ml-1">(-3.42 %)</span>
            </div>
        </div>
    </div>


    <!-- 2. Main Grid: Chart & Side Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- A. Monthly Revenue Chart (Summary) -->
        <div class="lg:col-span-2 bg-white rounded-xl card-shadow p-6 chart-container">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-lg text-gray-900">Summary</h3>
                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Order</div>
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-lime-400 mr-2"></span>Income Growth</div>
                    <select class="p-1 border border-gray-200 rounded-lg text-xs hover:border-green-500 cursor-pointer">
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                    </select>
                </div>
            </div>
            <canvas id="adminChart"></canvas>
        </div>

        <!-- B. Most Selling Products (Static Mockup) -->
        <div class="bg-white rounded-xl card-shadow p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-4">Most Selling Products</h3>
            <div class="space-y-4">
                <!-- Item 1 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <!-- Placeholder using neutral colors -->
                        <img src="https://placehold.co/40x40/f3f4f6/1f2937?text=VEG" class="w-10 h-10 rounded-lg" alt="Product">
                        <div>
                            <p class="text-sm font-medium">Fresh Carrots</p>
                            <p class="text-xs text-gray-500">ID: #98432</p>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-gray-600">421 Sold</p>
                </div>
                <!-- Item 2 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://placehold.co/40x40/f3f4f6/1f2937?text=FRU" class="w-10 h-10 rounded-lg" alt="Product">
                        <div>
                            <p class="text-sm font-medium">Organic Apples</p>
                            <p class="text-xs text-gray-500">ID: #76112</p>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-gray-600">355 Sold</p>
                </div>
                <!-- Item 3 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://placehold.co/40x40/f3f4f6/1f2937?text=MEA" class="w-10 h-10 rounded-lg" alt="Product">
                        <div>
                            <p class="text-sm font-medium">Chicken Breast</p>
                            <p class="text-xs text-gray-500">ID: #23891</p>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-gray-600">210 Sold</p>
                </div>
            </div>
        </div>
    </div>


    <!-- 3. Bottom Row: Recent Orders & Top Users -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- A. Recent Orders Table (Static Mockup) -->
        <div class="lg:col-span-2 bg-white rounded-xl card-shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-lg text-gray-900">Recent Orders</h3>
                <a href="#" class="text-sm text-green-600 hover:text-green-800 font-medium">View All</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-2">Product</th>
                            <th class="py-3 px-2">Customer</th>
                            <th class="py-3 px-2">Order ID</th>
                            <th class="py-3 px-2">Date</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 px-2"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- Order 1: Pending (Yellow/Orange is a standard color for Pending) -->
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-2 flex items-center gap-3">
                                <img src="https://placehold.co/32x32/f3f4f6/1f2937?text=VEG" class="w-8 h-8 rounded-md" alt="Product">
                                <p class="text-sm font-medium">Spinach Bundle</p>
                            </td>
                            <td class="py-3 px-2 text-sm text-blue-600 font-medium cursor-pointer hover:underline">Alvin Merto</td>
                            <td class="py-3 px-2 text-sm text-gray-500">#245789</td>
                            <td class="py-3 px-2 text-sm text-gray-500">27 Jun 2024</td>
                            <td class="py-3 px-2">
                                <span class="flex items-center gap-1 text-yellow-600 text-xs font-semibold">
                                    <i class="fa-solid fa-circle text-[6px]"></i>Pending
                                </span>
                            </td>
                            <td class="py-3 px-2 text-right"><button class="text-green-600 hover:text-green-800 text-sm">View</button></td>
                        </tr>
                        <!-- Order 2: Canceled (Red is a standard color for Cancelled/Warning) -->
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-2 flex items-center gap-3">
                                <img src="https://placehold.co/32x32/f3f4f6/1f2937?text=FRU" class="w-8 h-8 rounded-md" alt="Product">
                                <p class="text-sm font-medium">Banana Box</p>
                            </td>
                            <td class="py-3 px-2 text-sm text-blue-600 font-medium cursor-pointer hover:underline">Michelle Data</td>
                            <td class="py-3 px-2 text-sm text-gray-500">26 Jun 2024</td>
                            <td class="py-3 px-2">
                                <span class="flex items-center gap-1 text-red-600 text-xs font-semibold">
                                    <i class="fa-solid fa-circle text-[6px]"></i>Cancelled
                                </span>
                            </td>
                            <td class="py-3 px-2 text-right"><button class="text-green-600 hover:text-green-800 text-sm">View</button></td>
                        </tr>
                        <!-- Order 3: Shipped (Green is a standard color for Shipped/Complete) -->
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-2 flex items-center gap-3">
                                <img src="https://placehold.co/32x32/f3f4f6/1f2937?text=DAI" class="w-8 h-8 rounded-md" alt="Product">
                                <p class="text-sm font-medium">Fresh Milk</p>
                            </td>
                            <td class="py-3 px-2 text-sm text-blue-600 font-medium cursor-pointer hover:underline">Jessy Rose</td>
                            <td class="py-3 px-2 text-sm text-gray-500">#1024784</td>
                            <td class="py-3 px-2 text-sm text-gray-500">20 Jun 2024</td>
                            <td class="py-3 px-2">
                                <span class="flex items-center gap-1 text-green-600 text-xs font-semibold">
                                    <i class="fa-solid fa-circle text-[6px]"></i>Shipped
                                </span>
                            </td>
                            <td class="py-3 px-2 text-right"><button class="text-green-600 hover:text-green-800 text-sm">View</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- B. Weekly Top Customers (Static Mockup) -->
        <div class="bg-white rounded-xl card-shadow p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-4">Weekly Top Customers</h3>
            <div class="space-y-4">
                <!-- User 1 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://randomuser.me/api/portraits/women/61.jpg" class="w-10 h-10 rounded-full" alt="User">
                        <div>
                            <p class="text-sm font-medium">Marks Hoverson</p>
                            <p class="text-xs text-gray-500">25 Orders</p>
                        </div>
                    </div>
                    <button class="text-sm text-green-600 hover:text-green-800 font-medium">View</button>
                </div>
                <!-- User 2 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://randomuser.me/api/portraits/men/50.jpg" class="w-10 h-10 rounded-full" alt="User">
                        <div>
                            <p class="text-sm font-medium">Johny Peters</p>
                            <p class="text-xs text-gray-500">23 Orders</p>
                        </div>
                    </div>
                    <button class="text-sm text-green-600 hover:text-green-800 font-medium">View</button>
                </div>
                <!-- User 3 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-10 h-10 rounded-full" alt="User">
                        <div>
                            <p class="text-sm font-medium">Jane Doe</p>
                            <p class="text-xs text-gray-500">18 Orders</p>
                        </div>
                    </div>
                    <button class="text-sm text-green-600 hover:text-green-800 font-medium">View</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Logout Confirmation Modal (Hidden by default) -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
        <div class="text-red-500 text-4xl mb-4">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Logout</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out of the Farmers Mall Admin Panel?</p>
        <div class="flex justify-center gap-4">
          <button id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <a href="../auth/login.php" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
            Logout
          </a>
        </div>
      </div>
    </div>

  </div> <!-- End Main Content Area -->

  <script src="admin-theme.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // --- Logout Modal Logic ---
      const logoutButton = document.getElementById('logoutButton');
      const logoutModal = document.getElementById('logoutModal');
      const cancelLogout = document.getElementById('cancelLogout');

      logoutButton.addEventListener('click', function() {
        logoutModal.classList.remove('hidden');
        logoutModal.classList.add('flex');
      });

      cancelLogout.addEventListener('click', function() {
        logoutModal.classList.add('hidden');
        logoutModal.classList.remove('flex');
      });

      // Close modal if clicked outside
      logoutModal.addEventListener('click', function(e) {
          if (e.target === logoutModal) {
              logoutModal.classList.add('hidden');
              logoutModal.classList.remove('flex');
          }
      });


      // --- Chart.js Initialization ---
      const ctx = document.getElementById('adminChart');

      const data = {
        labels: ['Sep 07', 'Sep 08', 'Sep 09', 'Sep 10', 'Sep 11', 'Sep 12', 'Sep 13'],
        datasets: [{
          label: 'Order',
          data: [5500, 6800, 4200, 8000, 5000, 7500, 6000], // Primary Green Line (Order)
          borderColor: 'rgb(22, 163, 74)', // Tailwind green-600 for main line
          backgroundColor: 'rgba(22, 163, 74, 0.1)',
          tension: 0.4,
          fill: false,
          pointRadius: 4,
          pointBackgroundColor: 'rgb(22, 163, 74)', 
          pointBorderColor: 'white',
        }, {
          label: 'Income Growth',
          data: [4000, 5800, 3000, 6500, 4000, 6000, 4800], // Secondary Lighter Green/Lime Line (Income Growth)
          borderColor: 'rgb(163, 230, 53)', // Tailwind lime-400
          backgroundColor: 'rgba(163, 230, 53, 0.1)',
          tension: 0.4,
          fill: false,
          pointRadius: 4,
          pointBackgroundColor: 'white',
          pointBorderColor: 'rgb(163, 230, 53)',
        }]
      };

      const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false, // Custom legend is implemented in HTML
          },
          tooltip: {
            mode: 'index',
            intersect: false,
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value, index, values) {
                if (value >= 1000) return (value / 1000) + 'K';
                return value;
              },
              color: '#9ca3af', // gray-400
            },
            grid: {
                color: '#f3f4f6', // gray-100
                drawBorder: false,
            }
          },
          x: {
            grid: {
              display: false,
            },
            ticks: {
              color: '#9ca3af',
            }
          }
        }
      };

      new Chart(ctx, {
        type: 'line',
        data: data,
        options: chartOptions
      });
    });
  </script>
</body>

</html>