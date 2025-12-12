<?php
session_start();

require_once __DIR__ . '/../config/supabase-api.php';
$api = getSupabaseAPI();

$admin_name = $_SESSION['full_name'] ?? "Admin User";
$admin_email = $_SESSION['email'] ?? "admin@farmersmall.com";

// --- Data Fetching ---
$orders = $api->select('orders') ?: [];
$users = $api->select('users') ?: [];

// --- Data Processing ---
function processFinancialData($orders, $users, $days = null) {
    $stats = [
        'revenue' => 0,
        'new_customers' => 0,
        'cancelled_orders' => 0,
        'total_orders' => 0,
        'completed_orders_count' => 0,
        'chart_labels' => [],
        'chart_data' => []
    ];

    $now = new DateTime();
    $limitDate = $days ? (clone $now)->modify("-$days days") : null;

    // Initialize chart data for the period
    $salesByDay = [];
    if ($days) {
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('M d', strtotime("-$i days"));
            $salesByDay[$date] = 0;
        }
    } else { // All time - group by month
        foreach ($orders as $order) {
            $month = date('Y-m', strtotime($order['created_at']));
            if (!isset($salesByDay[$month])) $salesByDay[$month] = 0;
        }
        ksort($salesByDay);
    }

    foreach ($orders as $order) {
        $orderDate = new DateTime($order['created_at']);
        if ($limitDate && $orderDate < $limitDate) continue;

        $stats['total_orders']++;
        if (in_array($order['status'], ['completed', 'delivered'])) {
            $stats['revenue'] += floatval($order['total_amount']);
            $stats['completed_orders_count']++;

            // Populate chart data
            if ($days) {
                $dayKey = $orderDate->format('M d');
                if (array_key_exists($dayKey, $salesByDay)) {
                    $salesByDay[$dayKey] += floatval($order['total_amount']);
                }
            } else {
                $monthKey = $orderDate->format('Y-m');
                $salesByDay[$monthKey] += floatval($order['total_amount']);
            }
        }
        if ($order['status'] === 'cancelled') {
            $stats['cancelled_orders']++;
        }
    }

    foreach ($users as $user) {
        $joinDate = new DateTime($user['created_at']);
        if ($limitDate && $joinDate < $limitDate) continue;
        if ($user['user_type'] === 'customer') $stats['new_customers']++;
    }

    $stats['chart_labels'] = array_keys($salesByDay);
    $stats['chart_data'] = array_values($salesByDay);
    return $stats;
}

$data_all_time = processFinancialData($orders, $users);
$data_30_days = processFinancialData($orders, $users, 30);
$data_7_days = processFinancialData($orders, $users, 7);

$all_stats = [
    'all_time' => $data_all_time,
    '30_days' => $data_30_days,
    '7_days' => $data_7_days,
];
// Mock notifications for the dropdown
$notifications = [
    [
        "id" => "N001",
        "type" => "New User",
        "icon" => "fa-user-plus",
        "color" => "green",
        "title" => "New Customer Registered",
        "message" => "Alex Reyes has created an account.",
        "time" => "15m ago",
        "read" => false
    ],
    [
        "id" => "N002",
        "type" => "New Order",
        "icon" => "fa-receipt",
        "color" => "blue",
        "title" => "New Order #ORD-006",
        "message" => "An order amounting to ₱1,250 has been placed.",
        "time" => "1h ago",
        "read" => false
    ],
    [
        "id" => "N003",
        "type" => "Low Stock",
        "icon" => "fa-box-open",
        "color" => "yellow",
        "title" => "Low Stock Warning",
        "message" => "'Organic Apples' are running low.",
        "time" => "3h ago",
        "read" => true
    ],
    [
        "id" => "N004",
        "type" => "System Alert",
        "icon" => "fa-shield-halved",
        "color" => "red",
        "title" => "System Maintenance Scheduled",
        "message" => "A system-wide maintenance is scheduled for tonight.",
        "time" => "1d ago",
        "read" => true
    ],
    [
        "id" => "N005",
        "type" => "New Review",
        "icon" => "fa-star",
        "color" => "purple",
        "title" => "New Product Review",
        "message" => "A 5-star review was left for 'Fresh Carrots'.",
        "time" => "2d ago",
        "read" => true
    ],
    [
        "id" => "N006",
        "type" => "New Order",
        "icon" => "fa-receipt",
        "color" => "blue",
        "title" => "New Order #ORD-007",
        "message" => "An order amounting to ₱850 has been placed.",
        "time" => "2d ago",
        "read" => true
    ],
];

// --- [CHANGE START] Calculate unread notifications ---
$unread_notifications_count = 0;
foreach ($notifications as $notif) {
    if (!$notif['read']) $unread_notifications_count++;
}
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

<body class="flex h-screen overflow-hidden bg-gray-50 text-gray-800">

  <!-- Sidebar (Deep Green - Primary Brand Color) -->
  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300 overflow-y-auto">
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
  <a href="admin-orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
    <i class="fa-solid fa-receipt w-5"></i>
    <span>Orders</span>
  </a>
  <a href="admin-manage-users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-user-gear w-5"></i>
          <span>Manage Users</span>
        </a>
        <a href="admin-finance.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-chart-pie w-5"></i>
          <span>Financial Reports</span>
        </a>
</nav>

<!-- UPDATED: Removed 'bg-green-700 text-white' to remove permanent highlight. Added hover effects. -->
        
      </nav>



      <!-- Navigation: ACCOUNT -->
      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
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
  <div class="flex-1 custom-scrollbar overflow-y-auto relative">

    <div class="p-6">
      <!-- Top Header and Search Bar -->
      <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-0 z-10">
        <div class="relative w-full max-w-lg hidden md:block">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          <input type="text" placeholder="Search products, orders, customers..."
            class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
        </div>

        <!-- Right Header Icons -->
        <div class="flex items-center gap-4 ml-auto">
          <!-- Notification Dropdown -->
          <div class="relative flex items-center">
              <a href="admin-notification.php" class="relative" title="View Notifications">
                  <i class="fa-regular fa-bell text-xl text-gray-500 hover:text-green-600 cursor-pointer"></i>
                  <?php if ($unread_notifications_count > 0): ?>
                  <span id="notification-pulse" class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                  <?php endif; ?>
              </a>
              <button id="notification-btn" class="ml-2 text-gray-400 hover:text-gray-600" title="Toggle Notifications">
                  <i class="fa-solid fa-chevron-down text-xs"></i>
              </button>
              <div id="notification-dropdown" class="hidden absolute top-full right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 z-20">
                  <div class="p-4 border-b">
                      <h4 class="font-bold text-gray-800">Notifications</h4>
                  </div>
                  <div id="notification-list" class="max-h-80 overflow-y-auto custom-scrollbar transition-all duration-300">
                      <?php foreach($notifications as $notif): ?>
                      <a href="#" class="notification-item flex items-start gap-3 p-4 hover:bg-green-50 cursor-pointer <?php echo !$notif['read'] ? 'bg-green-50' : ''; ?>" data-read="<?php echo $notif['read'] ? 'true' : 'false'; ?>">
                          <div class="w-8 h-8 rounded-full bg-<?php echo $notif['color']; ?>-100 flex-shrink-0 flex items-center justify-center text-<?php echo $notif['color']; ?>-600">
                              <i class="fa-solid <?php echo $notif['icon']; ?> text-sm"></i>
                          </div>
                          <div class="flex-1">
                              <p class="text-sm font-semibold text-gray-800"><?php echo $notif['title']; ?></p>
                              <p class="text-xs text-gray-500"><?php echo $notif['message']; ?></p>
                          </div>
                          <span class="text-xs text-gray-400"><?php echo $notif['time']; ?></span>
                      </a>
                      <?php endforeach; ?>
                  </div>
                  <div class="p-2 border-t">
                      <a href="admin-notification.php" class="block w-full text-center text-sm font-medium text-green-600 hover:bg-gray-100 rounded-lg py-2">
                          View all notifications
                      </a>
                  </div>
              </div>
          </div>

          <div class="w-px h-6 bg-gray-200 mx-2 hidden sm:block"></div>
          <!-- User Dropdown -->
          <div class="relative">
              <button id="user-menu-btn" class="flex items-center gap-2 cursor-pointer">
                  <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
              </button>
              <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 z-20">
                  <div class="p-3 border-b">
                      <p class="text-sm font-semibold text-gray-800"><?php echo $admin_name; ?></p>
                      <p class="text-xs text-gray-500"><?php echo $admin_email; ?></p>
                  </div>
                  <nav class="p-2">
                      <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-gray-700">
                          <i class="fa-solid fa-user-cog w-5 text-gray-500"></i>
                          <span>Profile & Settings</span>
                      </a>
                      <button id="logoutButtonDropdown" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-gray-700">
                          <i class="fa-solid fa-sign-out-alt w-5 text-gray-500"></i>
                          <span>Logout</span>
                      </button>
                  </nav>
              </div>
          </div>
        </div>
      </header>

      <div class="space-y-6 pt-6"> <!-- This wrapper now controls the spacing for the rest of the content -->
      <!-- Welcome Banner & Controls -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
          <h2 class="text-3xl font-bold text-gray-900 mb-2 md:mb-0">Welcome Back, <?php echo $admin_name; ?>!</h2>
          <div class="flex gap-3">
              <select id="time-filter" class="p-2 border border-gray-300 rounded-lg text-sm bg-white hover:border-green-500 cursor-pointer transition-colors">
                  <option value="all_time">All Time</option>
                  <option value="30_days">Last 30 Days</option>
                  <option value="7_days" selected>Last 7 Days</option>
              </select>
          </div>
      </div>

      <!-- 1. Stat Cards (Updated: Removed colored top borders) -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">

          <!-- Card 1: Revenue -->
          <div class="bg-green-50 rounded-xl p-5 card-shadow">
              <p class="text-sm font-medium text-gray-500 mb-1">Ecommerce Revenue</p>
              <h3 id="stat-revenue" class="text-2xl font-extrabold text-gray-900">₱0.00</h3>
              <div class="flex items-center text-sm mt-2">
                  <i id="stat-revenue-icon" class="fa-solid fa-arrow-up text-green-600 mr-1"></i>
                  <span id="stat-revenue-change" class="text-green-600 font-semibold">--%</span>
              </div>
          </div>

          <!-- Card 2: New Customers -->
          <div class="bg-green-50 rounded-xl p-5 card-shadow">
              <p class="text-sm font-medium text-gray-500 mb-1">New Customers</p>
              <h3 id="stat-customers" class="text-2xl font-extrabold text-gray-900">0</h3>
              <div class="flex items-center text-sm mt-2">
                  <i id="stat-customers-icon" class="fa-solid fa-arrow-up text-green-600 mr-1"></i>
                  <span id="stat-customers-change" class="text-green-600 font-semibold">--%</span>
              </div>
          </div>

          <!-- Card 3: Cancelled Orders -->
          <div class="bg-green-50 rounded-xl p-5 card-shadow">
              <p class="text-sm font-medium text-gray-500 mb-1">Cancelled Orders</p>
              <h3 id="stat-cancelled" class="text-2xl font-extrabold text-gray-900">0</h3>
              <div class="flex items-center text-sm mt-2">
                  <i id="stat-cancelled-icon" class="fa-solid fa-arrow-down text-green-600 mr-1"></i>
                  <span id="stat-cancelled-change" class="text-red-600 font-semibold">--%</span>
              </div>
          </div>
          
          <!-- Card 4: Average Order Value -->
          <div class="bg-green-50 rounded-xl p-5 card-shadow">
              <p class="text-sm font-medium text-gray-500 mb-1">Average Order Value</p>
              <h3 id="stat-avg-order" class="text-2xl font-extrabold text-gray-900">₱0.00</h3>
              <div class="flex items-center text-sm mt-2">
                  <i id="stat-avg-order-icon" class="fa-solid fa-arrow-up text-green-600 mr-1"></i>
                  <span id="stat-avg-order-change" class="text-green-600 font-semibold">--%</span>
              </div>
          </div>
          
          <!-- Card 5: Total Orders -->
          <div class="bg-green-50 rounded-xl p-5 card-shadow">
              <p class="text-sm font-medium text-gray-500 mb-1">Total Orders</p>
              <h3 id="stat-total-orders" class="text-2xl font-extrabold text-gray-900">0</h3>
              <div class="flex items-center text-sm mt-2">
                  <i id="stat-total-orders-icon" class="fa-solid fa-arrow-up text-green-600 mr-1"></i>
                  <span id="stat-total-orders-change" class="text-green-600 font-semibold">--%</span>
              </div>
          </div>
      </div>


      <!-- 2. Main Grid: Chart & Side Panels -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- A. Monthly Revenue Chart (Summary) -->
          <div class="lg:col-span-2 bg-white rounded-xl card-shadow p-6 chart-container">
              <div class="flex justify-between items-center mb-4">
                  <h3 class="font-semibold text-lg text-gray-900">Summary</h3>
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
                  <a href="admin-orders.php" class="text-sm text-green-600 hover:text-green-800 font-medium">View All</a>
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
                      <tbody id="recent-orders-body" class="bg-white divide-y divide-gray-100">
                          <?php 
                            $recentOrders = array_slice($orders, 0, 3);
                            foreach ($recentOrders as $order): 
                                $status = strtolower($order['status']);
                                $statusColor = 'text-gray-600';
                                if ($status === 'pending') $statusColor = 'text-yellow-600';
                                if ($status === 'cancelled') $statusColor = 'text-red-600';
                                if (in_array($status, ['completed', 'delivered'])) $statusColor = 'text-green-600';
                          ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-2 flex items-center gap-3">
                                    <img src="https://placehold.co/32x32/f3f4f6/1f2937?text=PROD" class="w-8 h-8 rounded-md" alt="Product">
                                    <p class="text-sm font-medium"><?php echo htmlspecialchars($order['product_name']); ?></p>
                                </td>
                                <td class="py-3 px-2 text-sm text-blue-600 font-medium cursor-pointer hover:underline"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td class="py-3 px-2 text-sm text-gray-500">#<?php echo substr($order['id'], 0, 6); ?>...</td>
                                <td class="py-3 px-2 text-sm text-gray-500"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                <td class="py-3 px-2">
                                    <span class="flex items-center gap-1 <?php echo $statusColor; ?> text-xs font-semibold">
                                        <i class="fa-solid fa-circle text-[6px]"></i><?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-right"><a href="admin-orders.php" class="text-green-600 hover:text-green-800 text-sm">View</a></td>
                            </tr>
                          <?php endforeach; ?>
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
          <a href="../public/index.php" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
            Logout
          </a>
        </div>
      </div>
    </div>

  </div> <!-- End Main Content Area -->

  <script src="admin-theme.js"></script>
  <script>
    // --- Pass PHP data to JS ---
    const allStats = <?php echo json_encode($all_stats); ?>;
    let adminChartInstance = null;

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

      // --- Notification Dropdown Logic ---
      const notificationBtn = document.getElementById('notification-btn');
      const notificationDropdown = document.getElementById('notification-dropdown');
      const userMenuBtn = document.getElementById('user-menu-btn');
      const userMenuDropdown = document.getElementById('user-menu-dropdown');
      const viewAllBtn = document.getElementById('view-all-notifications-btn');
      const notificationPulse = document.getElementById('notification-pulse');
      const notificationList = document.getElementById('notification-list');

      notificationBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent the window click event from firing immediately
        notificationDropdown.classList.toggle('hidden');
        // Hide the pulse when the dropdown is opened
        if (notificationPulse) {
            notificationPulse.style.display = 'none';
        }
      });

      notificationList.addEventListener('click', (e) => {
          const item = e.target.closest('.notification-item');
          if (item && item.dataset.read === 'false') {
              item.classList.remove('bg-green-50');
              item.dataset.read = 'true';
          }
      });

      // Close dropdown if clicked outside
      window.addEventListener('click', (e) => {
        // Close notification dropdown
        if (!notificationDropdown.classList.contains('hidden') && !notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.add('hidden');
        }
        // Close user menu dropdown
        if (!userMenuDropdown.classList.contains('hidden') && !userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
            userMenuDropdown.classList.add('hidden');
        }
      });

      // --- User Menu Dropdown ---
      userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userMenuDropdown.classList.toggle('hidden');
      });
      document.getElementById('logoutButtonDropdown').addEventListener('click', function() {
        logoutModal.classList.remove('hidden');
        logoutModal.classList.add('flex');
      });

      // --- Chart.js Functions ---
      function createOrUpdateChart(labels, data) {
        const ctx = document.getElementById('adminChart');
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
            callbacks: {
                label: function(context) {
                    return ` Revenue: ₱${context.parsed.y.toFixed(2)}`;
                }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value, index, values) {
                if (value >= 1000) return '₱' + (value / 1000) + 'K';
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
        if (adminChartInstance) {
            adminChartInstance.destroy();
        }
        adminChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: data,
                    borderColor: 'rgb(22, 163, 74)',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(22, 163, 74)',
                    pointBorderColor: 'white',
                }]
            },
            options: chartOptions
        });
      }

      // --- Data Update Functions ---
      function updateDashboard(period) {
        const data = allStats[period];
        if (!data) return;

        // Update Stat Cards
        document.getElementById('stat-revenue').textContent = `₱${parseFloat(data.revenue).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('stat-customers').textContent = data.new_customers.toLocaleString();
        document.getElementById('stat-cancelled').textContent = data.cancelled_orders.toLocaleString();
        document.getElementById('stat-total-orders').textContent = data.total_orders.toLocaleString();
        
        const avgOrderValue = data.completed_orders_count > 0 ? data.revenue / data.completed_orders_count : 0;
        document.getElementById('stat-avg-order').textContent = `₱${parseFloat(avgOrderValue).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        // Update Chart
        createOrUpdateChart(data.chart_labels, data.chart_data);

        // Update trend icons (mocked for now, can be replaced with real comparison logic)
        updateTrendIcon('stat-revenue', true);
        updateTrendIcon('stat-customers', true);
        updateTrendIcon('stat-cancelled', false); // Lower is better
        updateTrendIcon('stat-avg-order', true);
        updateTrendIcon('stat-total-orders', true);
      }

      function updateTrendIcon(baseId, isPositive) {
          const icon = document.getElementById(`${baseId}-icon`);
          const change = document.getElementById(`${baseId}-change`);
          if (isPositive) {
              icon.className = 'fa-solid fa-arrow-up text-green-600 mr-1';
              change.className = 'text-green-600 font-semibold';
              change.textContent = `+${(Math.random() * 10 + 5).toFixed(1)}%`;
          } else {
              icon.className = 'fa-solid fa-arrow-down text-red-600 mr-1';
              change.className = 'text-red-600 font-semibold';
              change.textContent = `-${(Math.random() * 5 + 1).toFixed(1)}%`;
          }
      }

      // --- Event Listener for Filter ---
      const timeFilter = document.getElementById('time-filter');
      timeFilter.addEventListener('change', (e) => {
          updateDashboard(e.target.value);
      });

      // --- Initial Load ---
      updateDashboard('7_days'); // Default to 'Last 7 Days'

    });
  </script>
</body>

</html>