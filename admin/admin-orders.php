<?php
// admin-orders.php
// Mock Order Data
$order_stats = [
    "total_revenue" => 45250.00,
    "pending_orders" => 12,
    "processing" => 8,
    "completed" => 156
];

$orders = [
    [
        "id" => "#ORD-2023-001",
        "customer" => "Juan Dela Cruz",
        "email" => "juan@gmail.com",
        "items" => "Fresh Carrots (2kg), Brown Rice (5kg)",
        "total" => 820.00,
        "date" => "Oct 27, 2023 - 10:30 AM",
        "payment_method" => "GCash",
        "payment_status" => "Paid",
        "status" => "Processing"
    ],
    [
        "id" => "#ORD-2023-002",
        "customer" => "Maria Santos",
        "email" => "maria.s@yahoo.com",
        "items" => "Organic Apples (12pcs)",
        "total" => 450.00,
        "date" => "Oct 27, 2023 - 09:15 AM",
        "payment_method" => "COD",
        "payment_status" => "Pending",
        "status" => "Pending"
    ],
    [
        "id" => "#ORD-2023-003",
        "customer" => "Antonio Reyes",
        "email" => "tony.reyes@email.com",
        "items" => "Chicken Breast (3kg)",
        "total" => 720.00,
        "date" => "Oct 26, 2023 - 04:45 PM",
        "payment_method" => "Credit Card",
        "payment_status" => "Paid",
        "status" => "Shipped"
    ],
    [
        "id" => "#ORD-2023-004",
        "customer" => "Elena Gomez",
        "email" => "elena.g@email.com",
        "items" => "Fresh Milk (2L), Eggs (1 tray)",
        "total" => 380.00,
        "date" => "Oct 26, 2023 - 02:00 PM",
        "payment_method" => "GCash",
        "payment_status" => "Paid",
        "status" => "Delivered"
    ],
    [
        "id" => "#ORD-2023-005",
        "customer" => "Ricardo Dalisay",
        "email" => "carding@email.com",
        "items" => "Broccoli (500g)",
        "total" => 150.00,
        "date" => "Oct 25, 2023 - 11:20 AM",
        "payment_method" => "COD",
        "payment_status" => "Cancelled",
        "status" => "Cancelled"
    ],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Orders</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Global Styles */
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7f9fc;
    }

    .custom-scrollbar::-webkit-scrollbar {
      width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
      background-color: #4b5563;
      border-radius: 2px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
      background: transparent;
    }

    .card-shadow {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }
    
    .bg-green-950 {
        background-color: #184D34;
    }
  </style>
</head>

<body class="flex min-h-screen bg-gray-50 text-gray-800">

  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300">
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
        <a href="admin-products.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-box w-5"></i>
          <span>Products</span>
        </a>
        <a href="admin-inventory.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-truck-ramp-box w-5"></i>
          <span>Inventory</span>
        </a>
     
        
        <a href="admin-orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-receipt w-5 text-green-200"></i>
          <span>Orders</span>
        </a>
      </nav>

      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
       
        <a href="admin-manage-users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-user-gear w-5"></i>
          <span>Manage Users</span>
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

  <div class="flex-1 p-6 space-y-6 custom-scrollbar">

    <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-6 z-10 w-full">
      <div class="relative w-full max-w-lg hidden md:block">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" placeholder="Search orders, customers, or IDs..."
          class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
      </div>

      <div class="flex items-center gap-4 ml-auto">
        <i class="fa-regular fa-bell text-xl text-gray-500 hover:text-green-600 cursor-pointer relative">
            <span class="absolute -top-1 -right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
        </i>
        <div class="w-px h-6 bg-gray-200 mx-2 hidden sm:block"></div>
        <a href="admin-settings.php" class="flex items-center gap-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
        </a>
      </div>
    </header>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Orders Management</h2>
            <p class="text-sm text-gray-500">Track and manage customer orders</p>
        </div>
        <div class="flex gap-3">
             <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-file-invoice"></i> Export Report
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors shadow-lg shadow-green-700/30">
                <i class="fa-solid fa-plus"></i> Create Order
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
                 <p class="text-2xl font-bold text-gray-800 mt-1">₱<?php echo number_format($order_stats['total_revenue']); ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <i class="fa-solid fa-peso-sign"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Pending Orders</p>
                 <p class="text-2xl font-bold text-yellow-600 mt-1"><?php echo $order_stats['pending_orders']; ?></p>
            </div>
             <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Processing</p>
                 <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo $order_stats['processing']; ?></p>
            </div>
             <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-box-open"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Completed</p>
                 <p class="text-2xl font-bold text-green-600 mt-1"><?php echo $order_stats['completed']; ?></p>
            </div>
             <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <i class="fa-solid fa-check-double"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl card-shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-wrap gap-3 items-center justify-between">
            <div class="flex gap-2">
                <button class="px-3 py-1.5 text-xs font-medium text-white bg-green-700 rounded-lg shadow-sm">All Orders</button>
                <button class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg">Pending</button>
                <button class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg">Unpaid</button>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-calendar text-gray-400"></i>
                <select class="text-sm border-none bg-transparent text-gray-600 font-medium outline-none cursor-pointer">
                    <option>Last 30 Days</option>
                    <option>This Week</option>
                    <option>Yesterday</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Items Summary</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-green-700"><?php echo $order['id']; ?></div>
                            <div class="text-xs text-gray-400"><?php echo $order['date']; ?></div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $order['customer']; ?></div>
                            <div class="text-xs text-gray-500"><?php echo $order['email']; ?></div>
                        </td>
                        
                         <td class="px-6 py-4">
                            <div class="text-xs text-gray-600 max-w-xs truncate" title="<?php echo $order['items']; ?>">
                                <?php echo $order['items']; ?>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">₱<?php echo number_format($order['total'], 2); ?></div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs">
                                <span class="block font-medium text-gray-700"><?php echo $order['payment_method']; ?></span>
                                <?php 
                                    $payColor = 'text-gray-500';
                                    if($order['payment_status'] == 'Paid') $payColor = 'text-green-600';
                                    if($order['payment_status'] == 'Pending') $payColor = 'text-orange-500';
                                    if($order['payment_status'] == 'Cancelled') $payColor = 'text-red-500';
                                ?>
                                <span class="<?php echo $payColor; ?> font-bold text-[10px] uppercase tracking-wide"><?php echo $order['payment_status']; ?></span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                             <?php 
                                $statusClass = 'bg-gray-100 text-gray-800';
                                if($order['status'] === 'Delivered') $statusClass = 'bg-green-100 text-green-800 border border-green-200';
                                if($order['status'] === 'Shipped') $statusClass = 'bg-indigo-100 text-indigo-800 border border-indigo-200';
                                if($order['status'] === 'Processing') $statusClass = 'bg-blue-100 text-blue-800 border border-blue-200';
                                if($order['status'] === 'Pending') $statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                if($order['status'] === 'Cancelled') $statusClass = 'bg-red-100 text-red-800 border border-red-200';
                            ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $order['status']; ?>
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-400 hover:text-green-600 mr-2" title="View Details"><i class="fa-solid fa-eye"></i></button>
                            <button class="text-gray-400 hover:text-blue-600" title="Print Invoice"><i class="fa-solid fa-print"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">20</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fa-solid fa-chevron-left h-4 w-4"></i>
                        </a>
                        <a href="#" aria-current="page" class="z-10 bg-green-50 border-green-500 text-green-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">2</a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                             <i class="fa-solid fa-chevron-right h-4 w-4"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

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

  </div> <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Logout Modal Logic
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

      logoutModal.addEventListener('click', function(e) {
          if (e.target === logoutModal) {
              logoutModal.classList.add('hidden');
              logoutModal.classList.remove('flex');
          }
      });
    });
  </script>
</body>
</html>