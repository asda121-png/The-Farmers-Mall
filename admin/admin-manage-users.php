<?php
// admin-manage-users.php
// Mock Data

$customers = [
    [
        "id" => "USR-001",
        "name" => "Sarah Johnson",
        "email" => "sarah.j@gmail.com",
        "phone" => "0917-111-2222",
        "location" => "Makati City",
        "total_orders" => 15,
        "status" => "Active",
        "joined" => "Nov 10, 2023",
        "avatar" => "https://randomuser.me/api/portraits/women/44.jpg"
    ],
    [
        "id" => "USR-002",
        "name" => "Mike Ross",
        "email" => "mike.ross@yahoo.com",
        "phone" => "0918-333-4444",
        "location" => "Taguig City",
        "total_orders" => 3,
        "status" => "Inactive",
        "joined" => "Oct 05, 2023",
        "avatar" => "https://randomuser.me/api/portraits/men/32.jpg"
    ],
    [
        "id" => "USR-003",
        "name" => "Emily Chen",
        "email" => "emily.c@gmail.com",
        "phone" => "0920-555-6666",
        "location" => "Pasig City",
        "total_orders" => 22,
        "status" => "Active",
        "joined" => "Sep 12, 2023",
        "avatar" => "https://randomuser.me/api/portraits/women/65.jpg"
    ]
];

$sellers = [
    [
        "id" => "SLR-101",
        "name" => "Juan Dela Cruz",
        "store_name" => "Juan's Fresh Farm",
        "email" => "juan.farm@email.com",
        "phone" => "0917-999-8888",
        "location" => "Batangas",
        "sales" => "₱45,200",
        "status" => "Verified",
        "joined" => "Aug 20, 2023",
        "avatar" => "https://randomuser.me/api/portraits/men/85.jpg"
    ],
    [
        "id" => "SLR-102",
        "name" => "Maria Clara",
        "store_name" => "Clara's Organic Goods",
        "email" => "maria.c@email.com",
        "phone" => "0919-777-6666",
        "location" => "Laguna",
        "sales" => "₱12,500",
        "status" => "Pending",
        "joined" => "Nov 15, 2023",
        "avatar" => "https://randomuser.me/api/portraits/women/22.jpg"
    ],
    [
        "id" => "SLR-103",
        "name" => "Pedro Penduko",
        "store_name" => "Pedro's Poultry",
        "email" => "pedro.p@email.com",
        "phone" => "0921-555-4321",
        "location" => "Bulacan",
        "sales" => "₱0",
        "status" => "Suspended",
        "joined" => "Jul 01, 2023",
        "avatar" => "https://randomuser.me/api/portraits/men/12.jpg"
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Manage Users</title>
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

    /* Tab Transitions */
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease-in-out;
    }
    .tab-content.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .tab-btn.active {
        background-color: #15803d; /* green-700 */
        color: white;
        border-color: #15803d;
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
        
        <a href="admin-orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
      </nav>

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
        <input type="text" placeholder="Search by name, email, or user ID..."
          class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
      </div>

      <div class="flex items-center gap-4 ml-auto">
        <i class="fa-regular fa-bell text-xl text-gray-500 hover:text-green-600 cursor-pointer relative">
            <span class="absolute -top-1 -right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
        </i>
        <div class="w-px h-6 bg-gray-200 mx-2 hidden sm:block"></div>
        <div class="flex items-center gap-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
        </div>
      </div>
    </header>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">User Management</h2>
            <p class="text-sm text-gray-500">Oversee customer accounts and seller profiles</p>
        </div>
        <div class="flex gap-3">
             <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-download"></i> Export Data
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors shadow-lg shadow-green-700/30">
                <i class="fa-solid fa-user-plus"></i> Add User
            </button>
        </div>
    </div>

    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="btn-customers" onclick="switchTab('customers')" class="tab-btn active whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm flex items-center gap-2 text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fa-solid fa-user"></i>
                Customers (Buyers)
                <span class="bg-gray-100 text-gray-600 py-0.5 px-2.5 rounded-full text-xs ml-2">1,204</span>
            </button>
            <button id="btn-sellers" onclick="switchTab('sellers')" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm flex items-center gap-2 text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fa-solid fa-store"></i>
                Sellers (Retailers)
                <span class="bg-gray-100 text-gray-600 py-0.5 px-2.5 rounded-full text-xs ml-2">48</span>
            </button>
        </nav>
    </div>

    <div id="tab-customers" class="tab-content active">
        <div class="bg-white rounded-xl card-shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <h3 class="font-bold text-gray-700">Customer List</h3>
                <div class="flex gap-2">
                    <select class="text-sm border-gray-300 border rounded-lg p-2 focus:ring-green-500 focus:border-green-500">
                        <option>All Statuses</option>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($customers as $c): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded-full object-cover" src="<?php echo $c['avatar']; ?>" alt="">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $c['name']; ?></div>
                                        <div class="text-xs text-gray-500">ID: <?php echo $c['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $c['email']; ?></div>
                                <div class="text-xs text-gray-500"><?php echo $c['phone']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo $c['total_orders']; ?> Orders
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                    $statusColor = $c['status'] == 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColor; ?>">
                                    <?php echo $c['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $c['joined']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-indigo-600 hover:text-indigo-900 mr-2"><i class="fa-solid fa-pen"></i></button>
                                <button class="text-red-600 hover:text-red-900"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-sellers" class="tab-content">
        <div class="bg-white rounded-xl card-shadow overflow-hidden">
             <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <h3 class="font-bold text-gray-700">Seller / Retailer List</h3>
                <div class="flex gap-2">
                    <select class="text-sm border-gray-300 border rounded-lg p-2 focus:ring-green-500 focus:border-green-500">
                        <option>All Statuses</option>
                        <option>Verified</option>
                        <option>Pending</option>
                        <option>Suspended</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Seller Profile</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Store Info</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Sales</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($sellers as $s): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded-full object-cover border-2 border-green-100" src="<?php echo $s['avatar']; ?>" alt="">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $s['name']; ?></div>
                                        <div class="text-xs text-gray-500"><?php echo $s['email']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-800"><?php echo $s['store_name']; ?></div>
                                <div class="text-xs text-gray-500"><i class="fa-solid fa-location-dot"></i> <?php echo $s['location']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900"><?php echo $s['sales']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                    $statusClass = 'bg-gray-100 text-gray-800';
                                    if($s['status'] === 'Verified') $statusClass = 'bg-green-100 text-green-800 border border-green-200';
                                    if($s['status'] === 'Pending') $statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                    if($s['status'] === 'Suspended') $statusClass = 'bg-red-100 text-red-800 border border-red-200';
                                ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo $s['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $s['joined']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-green-600 hover:text-green-900 mr-2" title="View Store"><i class="fa-solid fa-store"></i></button>
                                <button class="text-indigo-600 hover:text-indigo-900 mr-2" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                <button class="text-gray-400 hover:text-red-600" title="Block"><i class="fa-solid fa-ban"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
    // Tab Switching Logic
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('text-gray-900');
            btn.classList.add('text-gray-500');
            // Remove border color reset
            btn.classList.add('border-transparent'); 
        });

        // Show selected tab content
        document.getElementById('tab-' + tabName).classList.add('active');
        
        // Activate button styling
        const activeBtn = document.getElementById('btn-' + tabName);
        activeBtn.classList.add('active');
        activeBtn.classList.remove('text-gray-500');
        activeBtn.classList.remove('border-transparent');
    }

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