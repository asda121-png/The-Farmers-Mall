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
        <input type="text" id="search-input" placeholder="Search orders, customers, or IDs..."
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
             <button id="export-btn" class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-file-invoice"></i> Export Report
            </button>
            <button id="create-order-btn" class="flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors shadow-lg shadow-green-700/30">
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
            <div id="status-filters" class="flex gap-2">
                <button data-filter="all" class="filter-btn px-3 py-1.5 text-xs font-medium text-white bg-green-700 rounded-lg shadow-sm">All Orders</button>
                <button data-filter="pending" class="filter-btn px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg">Pending</button>
                <button data-filter="unpaid" class="filter-btn px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg">Unpaid</button>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-calendar text-gray-400"></i>
                <select id="date-filter" class="text-sm border-none bg-transparent text-gray-600 font-medium outline-none cursor-pointer">
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
                <tbody id="orders-table-body" class="bg-white divide-y divide-gray-200">
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
                            <button class="action-btn text-gray-400 hover:text-green-600 mr-2" title="View Details" data-action="view" data-id="<?php echo $order['id']; ?>"><i class="fa-solid fa-eye"></i></button>
                            <button class="action-btn text-gray-400 hover:text-blue-600" title="Print Invoice" data-action="print" data-id="<?php echo $order['id']; ?>"><i class="fa-solid fa-print"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div id="pagination-info">
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">20</span> results
                    </p>
                </div>
                <div>
                    <nav id="pagination-controls" class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
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

    <!-- Order Details Modal -->
    <div id="order-details-modal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-6 w-full max-w-2xl">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="font-bold text-xl text-gray-900">Order Details</h3>
            <button class="modal-close-btn text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div id="modal-content" class="space-y-4">
            <!-- Dynamic content will be injected here -->
        </div>
        <div class="flex justify-end gap-3 pt-6">
            <button class="modal-close-btn px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Close</button>
        </div>
      </div>
    </div>

    <!-- Create Order Modal -->
    <div id="create-order-modal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-6 w-full max-w-lg">
        <h3 class="font-bold text-xl mb-4 text-gray-900">Create New Order</h3>
        <form id="create-order-form">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                    <input type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Items (comma-separated)</label>
                    <textarea required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount (₱)</label>
                        <input type="number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <option>Pending</option>
                            <option>Processing</option>
                            <option>Shipped</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-6">
                <button type="button" class="modal-close-btn px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800">Create Order</button>
            </div>
        </form>
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
      const ordersData = <?php echo json_encode($orders); ?>;

      // --- Elements ---
      const searchInput = document.getElementById('search-input');
      const exportBtn = document.getElementById('export-btn');
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

      // --- Filtering and Pagination State ---
      let state = {
        currentPage: 1,
        rowsPerPage: 5,
        searchTerm: '',
        statusFilter: 'all',
      };

      const tableBody = document.getElementById('orders-table-body');

      function displayOrders() {
        let filteredOrders = ordersData.filter(order => {
            const searchMatch = state.searchTerm === '' || 
                                order.id.toLowerCase().includes(state.searchTerm) ||
                                order.customer.toLowerCase().includes(state.searchTerm) ||
                                order.email.toLowerCase().includes(state.searchTerm);

            const statusMatch = state.statusFilter === 'all' ||
                                (state.statusFilter === 'pending' && order.status.toLowerCase() === 'pending') ||
                                (state.statusFilter === 'unpaid' && order.payment_status.toLowerCase() === 'pending');

            return searchMatch && statusMatch;
        });

        const totalResults = filteredOrders.length;
        const totalPages = Math.ceil(totalResults / state.rowsPerPage);
        state.currentPage = Math.min(state.currentPage, totalPages) || 1;

        const start = (state.currentPage - 1) * state.rowsPerPage;
        const end = start + state.rowsPerPage;
        const paginatedOrders = filteredOrders.slice(start, end);

        tableBody.innerHTML = paginatedOrders.map(order => {
            let payColor = 'text-gray-500';
            if(order.payment_status === 'Paid') payColor = 'text-green-600';
            if(order.payment_status === 'Pending') payColor = 'text-orange-500';
            if(order.payment_status === 'Cancelled') payColor = 'text-red-500';

            let statusClass = 'bg-gray-100 text-gray-800';
            if(order.status === 'Delivered') statusClass = 'bg-green-100 text-green-800 border border-green-200';
            if(order.status === 'Shipped') statusClass = 'bg-indigo-100 text-indigo-800 border border-indigo-200';
            if(order.status === 'Processing') statusClass = 'bg-blue-100 text-blue-800 border border-blue-200';
            if(order.status === 'Pending') statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
            if(order.status === 'Cancelled') statusClass = 'bg-red-100 text-red-800 border border-red-200';

            return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-bold text-green-700">${order.id}</div><div class="text-xs text-gray-400">${order.date}</div></td>
                    <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">${order.customer}</div><div class="text-xs text-gray-500">${order.email}</div></td>
                    <td class="px-6 py-4"><div class="text-xs text-gray-600 max-w-xs truncate" title="${order.items}">${order.items}</div></td>
                    <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-semibold text-gray-900">₱${order.total.toFixed(2)}</div></td>
                    <td class="px-6 py-4 whitespace-nowrap"><div class="text-xs"><span class="block font-medium text-gray-700">${order.payment_method}</span><span class="${payColor} font-bold text-[10px] uppercase tracking-wide">${order.payment_status}</span></div></td>
                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${order.status}</span></td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="action-btn text-gray-400 hover:text-green-600 mr-2" title="View Details" data-action="view" data-id="${order.id}"><i class="fa-solid fa-eye"></i></button>
                        <button class="action-btn text-gray-400 hover:text-blue-600" title="Print Invoice" data-action="print" data-id="${order.id}"><i class="fa-solid fa-print"></i></button>
                    </td>
                </tr>
            `;
        }).join('');

        updatePagination(totalResults, totalPages);
      }

      function updatePagination(totalResults, totalPages) {
        const paginationInfo = document.getElementById('pagination-info');
        const paginationControls = document.getElementById('pagination-controls');

        if (totalResults === 0) {
            paginationInfo.innerHTML = `<p class="text-sm text-gray-700">No results found</p>`;
            paginationControls.innerHTML = '';
            return;
        }

        const startItem = (state.currentPage - 1) * state.rowsPerPage + 1;
        const endItem = Math.min(startItem + state.rowsPerPage - 1, totalResults);
        paginationInfo.innerHTML = `<p class="text-sm text-gray-700">Showing <span class="font-medium">${startItem}</span> to <span class="font-medium">${endItem}</span> of <span class="font-medium">${totalResults}</span> results</p>`;

        let buttons = '';
        for (let i = 1; i <= totalPages; i++) {
            const activeClasses = 'z-10 bg-green-50 border-green-500 text-green-600';
            const defaultClasses = 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
            buttons += `<a href="#" data-page="${i}" class="page-btn ${i === state.currentPage ? activeClasses : defaultClasses} relative inline-flex items-center px-4 py-2 border text-sm font-medium">${i}</a>`;
        }
        paginationControls.innerHTML = `
            <a href="#" data-page="${state.currentPage - 1}" class="page-btn relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 ${state.currentPage === 1 ? 'hidden' : ''}"><i class="fa-solid fa-chevron-left h-4 w-4"></i></a>
            ${buttons}
            <a href="#" data-page="${state.currentPage + 1}" class="page-btn relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 ${state.currentPage === totalPages ? 'hidden' : ''}"><i class="fa-solid fa-chevron-right h-4 w-4"></i></a>
        `;
      }

      // --- Event Listeners ---
      searchInput.addEventListener('input', (e) => {
        state.searchTerm = e.target.value.toLowerCase();
        state.currentPage = 1;
        displayOrders();
      });

      document.getElementById('status-filters').addEventListener('click', (e) => {
        const btn = e.target.closest('.filter-btn');
        if (btn) {
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('bg-green-700', 'text-white');
                b.classList.add('text-gray-600', 'hover:bg-gray-100');
            });
            btn.classList.add('bg-green-700', 'text-white');
            btn.classList.remove('text-gray-600', 'hover:bg-gray-100');
            state.statusFilter = btn.dataset.filter;
            state.currentPage = 1;
            displayOrders();
        }
      });

      document.getElementById('pagination-controls').addEventListener('click', (e) => {
        e.preventDefault();
        const pageBtn = e.target.closest('.page-btn');
        if (pageBtn) {
            state.currentPage = parseInt(pageBtn.dataset.page);
            displayOrders();
        }
      });

      // --- Modal Logic ---
      const orderDetailsModal = document.getElementById('order-details-modal');
      const createOrderModal = document.getElementById('create-order-modal');

      const showModal = (modal) => modal.classList.replace('hidden', 'flex');
      const hideModal = (modal) => modal.classList.replace('flex', 'hidden');

      document.querySelectorAll('.modal-close-btn').forEach(btn => btn.addEventListener('click', () => {
        hideModal(btn.closest('.fixed'));
      }));

      document.getElementById('create-order-btn').addEventListener('click', () => showModal(createOrderModal));

      tableBody.addEventListener('click', (e) => {
        const btn = e.target.closest('.action-btn');
        if (!btn) return;
        const order = ordersData.find(o => o.id === btn.dataset.id);
        if (!order) return;

        if (btn.dataset.action === 'view') {
            document.getElementById('modal-content').innerHTML = `
                <p><strong>Order ID:</strong> ${order.id}</p>
                <p><strong>Customer:</strong> ${order.customer} (${order.email})</p>
                <p><strong>Items:</strong> ${order.items}</p>
                <p><strong>Total:</strong> ₱${order.total.toFixed(2)}</p>
                <p><strong>Status:</strong> ${order.status}</p>
            `;
            showModal(orderDetailsModal);
        } else if (btn.dataset.action === 'print') { 
            printInvoice(order);
        }
      });

      // --- Export Logic ---
      exportBtn.addEventListener('click', () => {
        let filteredOrders = ordersData.filter(order => {
            const searchMatch = state.searchTerm === '' || 
                                order.id.toLowerCase().includes(state.searchTerm) ||
                                order.customer.toLowerCase().includes(state.searchTerm);
            const statusMatch = state.statusFilter === 'all' ||
                                (state.statusFilter === 'pending' && order.status.toLowerCase() === 'pending') ||
                                (state.statusFilter === 'unpaid' && order.payment_status.toLowerCase() === 'pending');
            return searchMatch && statusMatch;
        });

        const headers = ["Order ID", "Date", "Customer", "Email", "Items", "Total", "Payment Method", "Payment Status", "Status"];
        let csvContent = headers.join(",") + "\n";

        filteredOrders.forEach(order => {
            const row = [
                order.id,
                order.date,
                `"${order.customer}"`,
                order.email,
                `"${order.items}"`,
                order.total,
                order.payment_method,
                order.payment_status,
                order.status
            ].join(",");
            csvContent += row + "\n";
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.setAttribute("download", "orders-report.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });

      // --- Print Invoice Logic ---
      function printInvoice(order) {
        const invoiceHTML = `
            <html><head><title>Invoice for ${order.id}</title>
            <style>body{font-family:sans-serif;padding:20px}h1{color:#184D34}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px}th{background-color:#f2f2f2}</style>
            </head><body>
            <h1>Invoice</h1><p><strong>Order ID:</strong> ${order.id}</p><p><strong>Customer:</strong> ${order.customer}</p>
            <hr><h3>Items</h3><p>${order.items.replace(/, /g, '<br>')}</p><hr>
            <h3>Total: ₱${order.total.toFixed(2)}</h3>
            </body></html>`;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(invoiceHTML);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
      }

      // Initial Load
      displayOrders();
    });
  </script>
</body>
</html>