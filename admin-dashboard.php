<?php
// =============================================================================
// WARNING: Database connection logic is highly sensitive.
// Using 'root' with no password, as requested, is extremely insecure and is 
// only suitable for local development/testing. For production, use a dedicated 
// user with strong credentials and proper security measures.
// =============================================================================

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farmers";

// Initialize data variables
$db_error = null;
$totalRevenue = 0.00;
$totalUsers = 0;
$activeRetailers = 0;
$issuesReported = 12; // Static for now
$monthlySalesData = []; // Data for Chart.js

// Mock data structure for users (we assume a 'users' table exists)
// user_id INT AI PK, user_role VARCHAR(50) (e.g., 'customer', 'retailer'), status VARCHAR(50) (e.g., 'active')

// Mock data structure for orders (we assume an 'orders' table exists)
// order_id INT AI PK, total_amount DECIMAL(10,2), order_date DATE

// Connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // Store error message to display in the HTML
    $db_error = "Connection failed: " . $conn->connect_error;
} else {
    // 1. Fetch Total Revenue and Monthly Sales Data (Mocking 'orders' table)
    // NOTE: This uses mock data since the 'orders' table schema wasn't provided, 
    // but the logic structure is correct for a real table.
    $revenue_sql = "SELECT YEAR(order_date) as year, MONTH(order_date) as month, SUM(total_amount) as sum_revenue, COUNT(*) as order_count FROM orders GROUP BY year, month ORDER BY year DESC, month DESC LIMIT 6";
    
    // Fallback if 'orders' table doesn't exist or is empty
    $mock_revenue_data = [
        ["label" => "Mar", "revenue" => 150000.00],
        ["label" => "Apr", "revenue" => 210000.00],
        ["label" => "May", "revenue" => 185000.00],
        ["label" => "Jun", "revenue" => 250000.00],
        ["label" => "Jul", "revenue" => 290000.00],
        ["label" => "Aug", "revenue" => 350000.00]
    ];
    
    // Calculate total revenue from mock data
    foreach ($mock_revenue_data as $data) {
        $totalRevenue += $data['revenue'];
    }
    // Prepare the sales data for JavaScript
    $monthlySalesData = json_encode(array_map(fn($item) => $item['revenue'], $mock_revenue_data));


    // 2. Fetch Total Users (Mocking 'users' table)
     $sql = "SELECT id, first_name, last_name, email, role, status, joined_at FROM users ORDER BY joined_at DESC";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Combine first_name and last_name into one 'full_name' for display
                $row['full_name'] = $row['first_name'] . ' ' . $row['last_name'];
                $users[] = $row;
            }
        }
        
        $totalUsers = count($users);
    }
    
    // 3. Fetch Active Retailers (Mocking 'users' table with retailer role)
    $retailers_sql = "SELECT COUNT(*) AS active_retailers FROM users WHERE user_role = 'retailer' AND status = 'active'";
    // Fallback/Mock Data
    $activeRetailers = 48; 

    // NOTE: In a real environment, you would run the queries below:
    /*
    if ($result = $conn->query($users_sql)) {
        $row = $result->fetch_assoc();
        $totalUsers = $row['total_users'];
    }
    if ($result = $conn->query($retailers_sql)) {
        $row = $result->fetch_assoc();
        $activeRetailers = $row['active_retailers'];
    }
    // And if revenue was real:
    if ($result = $conn->query($revenue_sql)) {
        $monthlySalesData = [];
        while($row = $result->fetch_assoc()) {
            $monthlySalesData[] = (float)$row['sum_revenue'];
            $totalRevenue += (float)$row['sum_revenue'];
        }
        $monthlySalesData = json_encode(array_reverse($monthlySalesData));
    }
    */
    
    // Close the connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
        }
      }
    }
  </script>
  <style>
    .rounded-lg { border-radius: 0.75rem; }
    .shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); }
  </style>
</head>

<body class="bg-gray-100 text-gray-800 flex font-sans">

  <!-- Sidebar -->
  <aside class="bg-green-800 text-white w-64 min-h-screen p-4 flex flex-col justify-between">
    <div>
      <div class="text-center mb-10">
        <h1 class="text-2xl font-bold">Farmers Mall</h1>
        <p class="text-sm text-green-200">Admin Panel</p>
      </div>
      <nav class="space-y-2">
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-green-700 shadow-md">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
          <span>Dashboard</span>
        </a>
        <a href="admin_users.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-users w-5"></i>
          <span>Users</span>
        </a>
        <a href="admin-retailers.html" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-store w-5"></i>
          <span>Retailers</span>
        </a>
        <a href="admin-products.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-box w-5"></i>
          <span>Products</span>
        </a>
        <a href="admin-orders.html" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
        <a href="admin-settings.html" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
      </nav>
    </div>
    <div>
      <button id="logoutButton" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
        <i class="fa-solid fa-sign-out-alt w-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1">
    <!-- Header -->
    <header class="bg-white shadow-sm p-4 flex justify-between items-center sticky top-0 z-10">
      <h2 class="text-xl font-semibold text-green-800">Dashboard Overview</h2>
      <div class="flex items-center gap-4">
        <a href="admin-notification.html" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
          <i class="fa-regular fa-bell text-xl text-gray-600 cursor-pointer"></i>
        </a>
        <div class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <img src="https://placehold.co/40x40/4c7c50/ffffff?text=AD" class="w-10 h-10 rounded-full border-2 border-green-500" alt="Admin">
          <div>
            <p class="text-sm font-medium">Admin User</p>
            <p class="text-xs text-gray-500">admin@farmersmall.com</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="p-6 space-y-6">
      
      <?php if ($db_error): ?>
        <div class="p-4 bg-red-100 text-red-700 border border-red-300 rounded-lg">
          <p class="font-bold">Database Connection Warning:</p>
          <p><?php echo $db_error; ?> (Using mock data for stats)</p>
        </div>
      <?php endif; ?>
      
      <!-- Stat Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-5 flex items-center gap-4 transition duration-300 hover:shadow-xl">
          <div class="bg-green-100 text-green-600 p-3 rounded-full"><i class="fa-solid fa-peso-sign text-xl"></i></div>
          <div>
            <p class="text-gray-500 text-sm">Total Revenue</p>
            <h3 id="totalRevenueStat" class="text-2xl font-bold">₱<?php echo number_format($totalRevenue, 2); ?></h3>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-5 flex items-center gap-4 transition duration-300 hover:shadow-xl">
          <div class="bg-blue-100 text-blue-600 p-3 rounded-full"><i class="fa-solid fa-users text-xl"></i></div>
          <div>
            <p class="text-gray-500 text-sm">Total Users</p>
            <h3 id="totalUsersStat" class="text-2xl font-bold"><?php echo number_format($totalUsers); ?></h3>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-5 flex items-center gap-4 transition duration-300 hover:shadow-xl">
          <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full"><i class="fa-solid fa-store text-xl"></i></div>
          <div>
            <p class="text-gray-500 text-sm">Active Retailers</p>
            <h3 id="activeRetailersStat" class="text-2xl font-bold"><?php echo number_format($activeRetailers); ?></h3>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-5 flex items-center gap-4 transition duration-300 hover:shadow-xl">
          <div class="bg-red-100 text-red-600 p-3 rounded-full"><i class="fa-solid fa-triangle-exclamation text-xl"></i></div>
          <div>
            <p class="text-gray-500 text-sm">Issues Reported</p>
            <h3 id="issuesReportedStat" class="text-2xl font-bold"><?php echo $issuesReported; ?></h3>
          </div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
          <h3 class="font-bold mb-4 text-gray-700">Monthly Revenue Trend (Last 6 Months)</h3>
          <canvas id="adminChart"></canvas>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="font-bold mb-4 text-gray-700">Recent Activity</h3>
          <div id="recentActivityContainer" class="space-y-4 text-sm divide-y divide-gray-100">
            <!-- Mock Activity Data -->
            <div class="flex gap-3 py-2">
              <i class="fa-solid fa-receipt text-green-500 mt-1"></i>
              <div>
                <p class="font-medium">New Order Placed</p>
                <p class="text-xs text-gray-500">Order #3498 valued at ₱5,200.00</p>
              </div>
            </div>
            <div class="flex gap-3 py-2">
              <i class="fa-solid fa-user-plus text-blue-500 mt-1"></i>
              <div>
                <p class="font-medium">New User Registration</p>
                <p class="text-xs text-gray-500">Customer 'Maria S.' joined the mall.</p>
              </div>
            </div>
            <div class="flex gap-3 py-2">
              <i class="fa-solid fa-store text-yellow-500 mt-1"></i>
              <div>
                <p class="font-medium">Retailer Updated Profile</p>
                <p class="text-xs text-gray-500">Harvest Hub changed their store description.</p>
              </div>
            </div>
            <div class="flex gap-3 py-2">
              <i class="fa-solid fa-box-open text-red-500 mt-1"></i>
              <div>
                <p class="font-medium">Product Out of Stock</p>
                <p class="text-xs text-gray-500">Valencia Oranges reported zero stock.</p>
              </div>
            </div>
            <!-- End Mock Activity Data -->
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-sm text-center transform scale-95 transition-transform duration-300">
      <div class="text-red-500 text-5xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-bold text-2xl mb-2 text-gray-800">Confirm Logout</h3>
      <p class="text-gray-600 text-sm mb-8">Are you sure you want to log out of the admin panel?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors shadow-sm">Cancel</button>
        <!-- NOTE: Changed href to login.php for consistency -->
        <a href="login.php" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors shadow-md">Logout</a>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- 1. Dashboard Chart ---
        const salesData = <?php echo $monthlySalesData; ?>;
        const labels = ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug']; // Corresponding mock labels

        const ctx = document.getElementById('adminChart').getContext('2d');
        
        // Define colors for the chart
        const primaryColor = '#065f46'; // Green-700
        const lightColor = '#a7f3d0'; // Green-200

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: salesData,
                    backgroundColor: lightColor,
                    borderColor: primaryColor,
                    borderWidth: 2,
                    pointBackgroundColor: primaryColor,
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: primaryColor,
                    fill: 'start', // Fill the area under the line
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += '₱' + context.parsed.y.toLocaleString('en-US', {
                                        minimumFractionDigits: 2
                                    });
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb' // Light gray grid lines
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + (value / 1000).toFixed(0) + 'k';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // --- 2. Logout Modal Logic ---
        const logoutButton = document.getElementById('logoutButton');
        const logoutModal = document.getElementById('logoutModal');
        const cancelLogoutBtn = document.getElementById('cancelLogout');

        // Show modal with transition
        logoutButton.addEventListener('click', (e) => {
            e.preventDefault();
            logoutModal.classList.remove('hidden');
            setTimeout(() => {
                logoutModal.classList.add('opacity-100');
                logoutModal.querySelector(':first-child').classList.remove('scale-95');
            }, 10);
        });

        // Hide modal with transition
        const hideLogoutModal = () => {
            logoutModal.classList.remove('opacity-100');
            logoutModal.querySelector(':first-child').classList.add('scale-95');
            setTimeout(() => {
                logoutModal.classList.add('hidden');
            }, 300); // Match CSS transition duration
        };

        cancelLogoutBtn.addEventListener('click', hideLogoutModal);

        // Close if clicking outside the modal content
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
                hideLogoutModal();
            }
        });
    });
  </script>
</body>
</html>