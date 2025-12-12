<?php
// admin-finance.php

require_once __DIR__ . '/../config/supabase-api.php';
$api = getSupabaseAPI();

$orders = [];
try {
    $rawOrders = $api->select('orders');
    if (is_array($rawOrders)) {
        $orders = $rawOrders;
    }
} catch (Exception $e) {
    $orders = [];
    error_log("Error fetching orders for finance page: " . $e->getMessage());
}

// --- Process Financial Data ---
$totalRevenue = 0;
$totalTransactions = count($orders);
$completedOrdersCount = 0;
$averageOrderValue = 0;
$customerIds = [];

// Initialize sales for the last 7 days
$salesByDay = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('M d', strtotime("-$i days"));
    $salesByDay[$date] = 0;
}

foreach ($orders as $order) {
    // Tally revenue from completed orders
    if (in_array($order['status'], ['completed', 'delivered'])) {
        $totalRevenue += floatval($order['total_amount']);
        $completedOrdersCount++;
    }

    // Tally unique customers
    if (!empty($order['customer_id']) && !in_array($order['customer_id'], $customerIds)) {
        $customerIds[] = $order['customer_id'];
    }

    // Aggregate sales for the chart (last 7 days)
    $orderDateStr = date('M d', strtotime($order['created_at']));
    if (array_key_exists($orderDateStr, $salesByDay) && in_array($order['status'], ['completed', 'delivered'])) {
        $salesByDay[$orderDateStr] += floatval($order['total_amount']);
    }
}

$totalCustomers = count($customerIds);
if ($completedOrdersCount > 0) {
    $averageOrderValue = $totalRevenue / $completedOrdersCount;
}

$chartLabels = json_encode(array_keys($salesByDay));
$chartData = json_encode(array_values($salesByDay));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Financial Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f7f9fc; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #4b5563; border-radius: 2px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .card-shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); }
    .bg-green-950 { background-color: #184D34; }    
    .chart-container { height: 320px; }

  </style>
</head>

<body class="flex h-screen overflow-hidden bg-gray-50 text-gray-800">

  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300 h-screen overflow-y-auto">
    <div>
      <div class="flex items-center gap-3 mb-8 px-2 py-2">
        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-white">
          <i class="fas fa-leaf text-green-700 text-lg"></i>
        </div>
        <h1 class="text-xl font-bold">Farmers Mall</h1>
      </div>

      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest mb-2 px-2">GENERAL</p>
      <nav class="space-y-1">
        <a href="admin-dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
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
        <a href="admin-finance.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-chart-pie w-5 text-green-200"></i>
          <span>Financial Reports</span>
        </a>
      </nav>

      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
      </nav>
    </div>

    <div class="mt-8 pt-4 border-t border-green-800">
      <button id="logoutButton" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200 text-gray-300">
        <i class="fa-solid fa-sign-out-alt w-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <div class="flex-1 custom-scrollbar overflow-y-auto relative">
    <div class="p-6">
      <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-0 z-10">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Financial Reports</h2>
            <p class="text-sm text-gray-500">View sales, revenue, and other financial data.</p>
        </div>
        <a href="admin-settings.php" class="flex items-center gap-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
        </a>
      </header>

      <div class="space-y-6 pt-6">
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-5 rounded-xl card-shadow">
                <p class="text-sm text-gray-500">Total Revenue</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">₱<?php echo number_format($totalRevenue, 2); ?></h3>
            </div>
            <div class="bg-white p-5 rounded-xl card-shadow">
                <p class="text-sm text-gray-500">Total Transactions</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($totalTransactions); ?></h3>
            </div>
            <div class="bg-white p-5 rounded-xl card-shadow">
                <p class="text-sm text-gray-500">Avg. Order Value</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">₱<?php echo number_format($averageOrderValue, 2); ?></h3>
            </div>
            <div class="bg-white p-5 rounded-xl card-shadow">
                <p class="text-sm text-gray-500">Total Customers</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($totalCustomers); ?></h3>
            </div>
        </div>

        <!-- Main Chart & Recent Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sales Chart -->
            <div class="lg:col-span-2 bg-white rounded-xl card-shadow p-6">
                <h3 class="font-semibold text-lg text-gray-900 mb-4">Revenue (Last 7 Days)</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl card-shadow p-6">
                <h3 class="font-semibold text-lg text-gray-900 mb-4">Recent Transactions</h3>
                <div class="space-y-4">
                    <?php 
                    $recentOrders = array_slice($orders, 0, 5);
                    foreach ($recentOrders as $order): 
                        $isCompleted = in_array($order['status'], ['completed', 'delivered']);
                    ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $isCompleted ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500'; ?>">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Order #<?php echo substr($order['id'], 0, 6); ?>...</p>
                                <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold <?php echo $isCompleted ? 'text-green-700' : 'text-gray-700'; ?>">
                                ₱<?php echo number_format($order['total_amount'], 2); ?>
                            </p>
                            <p class="text-xs text-gray-500"><?php echo ucfirst($order['status']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
      </div>

    </div>
  </div>

  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
        <div class="text-red-500 text-4xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Logout</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out?</p>
        <div class="flex justify-center gap-4">
          <button id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
          <a href="../public/index.php" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Logout</a>
        </div>
      </div>
  </div>

  <script src="admin-theme.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const logoutButton = document.getElementById('logoutButton');
      const logoutModal = document.getElementById('logoutModal');
      const cancelLogout = document.getElementById('cancelLogout');
      logoutButton.addEventListener('click', () => logoutModal.classList.replace('hidden', 'flex'));
      cancelLogout.addEventListener('click', () => logoutModal.classList.replace('flex', 'hidden'));      
      logoutModal.addEventListener('click', (e) => {
          if (e.target === logoutModal) {
              logoutModal.classList.replace('flex', 'hidden');
          }
      });

      // --- Chart.js Initialization ---
      const ctx = document.getElementById('revenueChart');
      if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $chartLabels; ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo $chartData; ?>,
                    borderColor: 'rgb(22, 163, 74)',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(22, 163, 74)',
                    pointBorderColor: 'white',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
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
                        ticks: { callback: (value) => `₱${value >= 1000 ? (value/1000) + 'k' : value}` }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
      }
    });
  </script>
</body>
</html>