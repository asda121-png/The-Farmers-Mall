<?php
// admin-manage-users.php

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
];
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
  <link rel="stylesheet" href="admin-theme.css">
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
      
        <a href="admin-manage-users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-user-gear w-5 text-green-200"></i>
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
        <input type="text" id="search-input" placeholder="Search by name, email, or user ID..."
          class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
      </div>

      <div class="flex items-center gap-4 ml-auto">
        <!-- Notification Dropdown -->
        <div class="relative">
            <button id="notification-btn" class="relative" title="View Notifications">
                <i class="fa-regular fa-bell text-xl text-gray-500 hover:text-green-600 cursor-pointer"></i>
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
            </button>
            <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 z-20">
                <div class="p-4 border-b">
                    <h4 class="font-bold text-gray-800">Notifications</h4>
                </div>
                <div id="notification-list" class="max-h-80 overflow-y-auto custom-scrollbar transition-all duration-300">
                    <?php foreach($notifications as $notif): ?>
                    <a href="#" class="flex items-start gap-3 p-4 hover:bg-gray-50 <?php echo !$notif['read'] ? 'bg-green-50' : ''; ?>">
                        <div class="w-8 h-8 rounded-full bg-<?php echo $notif['color']; ?>-100 flex-shrink-0 flex items-center justify-center text-<?php echo $notif['color']; ?>-600">
                            <i class="fa-solid <?php echo $notif['icon']; ?> text-sm"></i>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-800"><?php echo $notif['title']; ?></p><p class="text-xs text-gray-500"><?php echo $notif['message']; ?></p></div>
                        <span class="text-xs text-gray-400"><?php echo $notif['time']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="p-2 border-t"><a href="#" id="view-all-notifications-btn" class="block w-full text-center text-sm font-medium text-green-600 hover:bg-gray-100 rounded-lg py-2">View all notifications</a></div>
            </div>
        </div>
        <div class="w-px h-6 bg-gray-200 mx-2 hidden sm:block"></div>
        <a href="admin-settings.php" class="flex items-center gap-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
        </a>
      </div>
    </header>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">User Management</h2>
            <p class="text-sm text-gray-500">Oversee customer accounts and seller profiles</p>
        </div>
        <div class="flex gap-3">
             <button id="export-btn" class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-download"></i> Export Data
            </button>
        </div>
    </div>

    <div class="border-b border-gray-200">
        <nav id="tabs" class="-mb-px flex space-x-6">
            <button data-tab="customers" class="tab-btn active whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm flex items-center gap-2">
                <i class="fa-solid fa-user"></i>
                Customers (Buyers)
                <span class="bg-green-100 text-green-800 py-0.5 px-2.5 rounded-full text-xs ml-2">1,204</span>
            </button>
            <button data-tab="sellers" class="tab-btn whitespace-nowrap py-3 px-1 border-b-2 border-transparent font-medium text-sm flex items-center gap-2 text-gray-500 hover:text-green-600 hover:border-green-300">
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
                    <select id="customer-status-filter" class="filter-dropdown text-sm border-gray-300 border rounded-lg p-2 focus:ring-green-500 focus:border-green-500">
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
                    <tbody id="customers-table-body" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($customers as $c): ?>
                        <tr class="customer-row hover:bg-gray-50" data-status="<?php echo $c['status']; ?>">
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
                                <button class="action-btn text-green-600 hover:text-green-800 mr-2" title="Edit" data-action="edit" data-id="<?php echo $c['id']; ?>"><i class="fa-solid fa-pen"></i></button>
                                <button class="action-btn text-red-600 hover:text-red-900" data-action="delete" data-id="<?php echo $c['id']; ?>"><i class="fa-solid fa-trash"></i></button>
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
                    <select id="seller-status-filter" class="filter-dropdown text-sm border-gray-300 border rounded-lg p-2 focus:ring-green-500 focus:border-green-500">
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
                    <tbody id="sellers-table-body" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($sellers as $s): ?>
                        <tr class="seller-row hover:bg-gray-50" data-status="<?php echo $s['status']; ?>">
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
                                <button class="action-btn text-green-600 hover:text-green-900 mr-2" title="<?php echo ($s['status'] === 'Pending') ? 'Review & Verify' : 'View Store'; ?>" data-action="view" data-id="<?php echo $s['id']; ?>"><i class="fa-solid fa-store"></i></button>
                                <button class="action-btn text-green-600 hover:text-green-800 mr-2" title="Edit" data-action="edit" data-id="<?php echo $s['id']; ?>"><i class="fa-solid fa-pen"></i></button>
                                <?php if ($s['status'] !== 'Suspended' && $s['status'] !== 'Pending'): ?>
                                    <button class="action-btn text-gray-400 hover:text-red-600" title="Suspend" data-action="suspend" data-id="<?php echo $s['id']; ?>"><i class="fa-solid fa-ban"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Add User Modal -->
    <div id="add-user-modal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-6 w-full max-w-lg">
        <h3 class="font-bold text-xl mb-4 text-gray-900">Add New User</h3>
        <form id="add-user-form">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                    <select required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="customer">Customer (Buyer)</option>
                        <option value="seller">Seller (Retailer)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-6">
                <button type="button" class="modal-close-btn px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800">Add User</button>
            </div>
        </form> <!-- This was missing the closing tag -->
      </div>
    </div>

        </div>
    </div>

    <!-- Edit User Modal (Generic for both Customers and Sellers) -->
    <div id="edit-user-modal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-6 w-full max-w-lg">
        <h3 class="font-bold text-xl mb-4 text-gray-900">Edit User Details</h3>
        <form id="edit-user-form">
            <input type="hidden" id="edit-user-id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="edit-user-name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="edit-user-email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="edit-user-phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-6">
                <button type="button" class="modal-close-btn px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800">Save Changes</button>
            </div>
        </form>
      </div>
    </div>

    <!-- Generic Confirmation Modal (Delete/Suspend) -->
    <div id="confirmation-modal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
            <div class="text-red-500 text-5xl mb-4 mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-red-100">
                <i class="fa-solid fa-question"></i>
            </div>
            <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Action</h3>
            <p class="text-gray-600 text-sm mb-6">Are you sure you want to proceed with this action?</p>
            <div class="flex justify-center gap-4">
                <button type="button" id="cancel-action-btn" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="button" id="confirm-action-btn" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                    Confirm
                </button>
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


  </div> 
  <script>
    // --- Main Application State ---
    const app = {
        activeTab: 'customers',
        currentAction: null,
        currentRowElement: null,
        currentUserId: null,
        elements: {},

        // --- Initialization ---
        init() {
            this.cacheDOMElements();
            this.bindEvents();
            this.applyFilters(); // Initial filter on page load
        },

        // --- Cache DOM Elements for performance ---
        cacheDOMElements() {
            this.elements = {
                searchInput: document.getElementById('search-input'),
                customerFilter: document.getElementById('customer-status-filter'),
                sellerFilter: document.getElementById('seller-status-filter'),
                customersTableBody: document.getElementById('customers-table-body'),
                sellersTableBody: document.getElementById('sellers-table-body'),                
                exportBtn: document.getElementById('export-btn'),
                editUserModal: document.getElementById('edit-user-modal'),
                editUserForm: document.getElementById('edit-user-form'),
                editUserNameInput: document.getElementById('edit-user-name'),
                editUserEmailInput: document.getElementById('edit-user-email'),
                editUserPhoneInput: document.getElementById('edit-user-phone'),
                confirmationModal: document.getElementById('confirmation-modal'),
                cancelActionBtn: document.getElementById('cancel-action-btn'),
                confirmActionBtn: document.getElementById('confirm-action-btn'),
                tabsContainer: document.getElementById('tabs'),
                allModals: document.querySelectorAll('.fixed[id$="-modal"], .fixed[id$="Modal"]'),
                allTBody: document.querySelectorAll('tbody'),
                logoutButton: document.getElementById('logoutButton'),
                logoutModal: document.getElementById('logoutModal'),
                notificationBtn: document.getElementById('notification-btn'),
                notificationDropdown: document.getElementById('notification-dropdown'),
                viewAllNotificationsBtn: document.getElementById('view-all-notifications-btn'),
            };
        },

        // --- Bind all event listeners ---
        bindEvents() {
            this.elements.tabsContainer.addEventListener('click', this.handleTabSwitch.bind(this));
            this.elements.searchInput.addEventListener('input', this.applyFilters.bind(this));
            this.elements.customerFilter.addEventListener('change', this.applyFilters.bind(this));
            this.elements.sellerFilter.addEventListener('change', this.applyFilters.bind(this));
            this.elements.exportBtn.addEventListener('click', this.handleExport.bind(this));
            this.elements.editUserForm.addEventListener('submit', this.handleFormSubmit.bind(this));
            this.elements.cancelActionBtn.addEventListener('click', () => this.hideModal(this.elements.confirmationModal));
            this.elements.confirmActionBtn.addEventListener('click', this.handleConfirmAction.bind(this));

            this.elements.allTBody.forEach(tbody => {
                tbody.addEventListener('click', this.handleTableAction.bind(this));
            });

            this.elements.allModals.forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal || e.target.closest('.modal-close-btn') || e.target.id === 'cancelLogout') {
                        this.hideModal(modal);
                    }
                });
            });
            
            this.elements.logoutButton.addEventListener('click', () => this.showModal(this.elements.logoutModal));
            this.elements.notificationBtn.addEventListener('click', this.toggleNotificationDropdown.bind(this));
            this.elements.viewAllNotificationsBtn.addEventListener('click', this.expandNotifications.bind(this));
            window.addEventListener('click', this.closeNotificationDropdown.bind(this));
        },

        // --- Event Handlers ---
        handleTabSwitch(e) {
            const tabButton = e.target.closest('.tab-btn');
            if (!tabButton) return;

            this.activeTab = tabButton.dataset.tab;

            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            tabButton.classList.add('active');

            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(`tab-${this.activeTab}`).classList.add('active');

            this.applyFilters();
        },

        applyFilters() {
            const searchTerm = this.elements.searchInput.value.toLowerCase();
            const isCustomers = this.activeTab === 'customers';
            const status = isCustomers ? this.elements.customerFilter.value : this.elements.sellerFilter.value;
            const tableBody = isCustomers ? this.elements.customersTableBody : this.elements.sellersTableBody;
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                const statusMatch = status === 'All Statuses' || row.dataset.status === status;
                const searchMatch = rowText.includes(searchTerm);
                row.style.display = (statusMatch && searchMatch) ? '' : 'none';
            });
        },

        handleTableAction(e) {
            const btn = e.target.closest('.action-btn');
            if (!btn) return;

            this.currentUserId = btn.dataset.id;
            this.currentAction = btn.dataset.action;
            this.currentRowElement = btn.closest('tr');

            if (this.currentAction === 'edit') {
                // Use more specific selectors to reliably get user data
                const name = this.currentRowElement.querySelector('.text-sm.font-medium.text-gray-900')?.textContent || '';
                const email = this.currentRowElement.querySelector('.text-xs.text-gray-500, .text-sm.text-gray-900')?.textContent || '';
                
                // Different phone selectors for customer vs seller
                let phone = this.currentRowElement.querySelector('td:nth-child(2) .text-xs.text-gray-500')?.textContent || ''; // Customer phone
                if (!phone) {
                    phone = this.currentRowElement.querySelector('td:nth-child(2) .text-xs.text-gray-500')?.textContent || ''; // Seller phone
                }
                
                this.elements.editUserNameInput.value = name;
                this.elements.editUserEmailInput.value = email;
                this.elements.editUserPhoneInput.value = phone.trim();
                document.getElementById('edit-user-id').value = this.currentUserId;
                this.showModal(this.elements.editUserModal);
            } else if (this.currentAction === 'delete' || this.currentAction === 'suspend') {
                this.showModal(this.elements.confirmationModal);
            } else if (this.currentAction === 'view') {
                window.location.href = `/admin/mock-store-view.php?sellerId=${this.currentUserId}`;
            }
        },

        handleFormSubmit(e) {
            e.preventDefault();
            const form = e.target;
            if (form.id === 'edit-user-form' && this.currentRowElement) {
                // Logic to update UI after edit
                this.currentRowElement.querySelector('.text-sm.font-medium.text-gray-900').textContent = this.elements.editUserNameInput.value;
                this.currentRowElement.querySelector('td:nth-child(2) .text-sm, td:nth-child(1) .text-xs.text-gray-500').textContent = this.elements.editUserEmailInput.value;
                if (this.currentRowElement.querySelector('td:nth-child(2) .text-xs.text-gray-500')) {
                    this.currentRowElement.querySelector('td:nth-child(2) .text-xs.text-gray-500').textContent = this.elements.editUserPhoneInput.value;
                }
                this.hideModal(this.elements.editUserModal);
            }
            form.reset();
        },

        handleConfirmAction() {
            if (this.currentAction === 'delete') {
                this.currentRowElement.remove();
            } else if (this.currentAction === 'suspend') {
                const statusCell = this.currentRowElement.querySelector('td:nth-child(4) span');
                statusCell.textContent = 'Suspended';
                statusCell.className = 'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200';
                this.currentRowElement.dataset.status = 'Suspended';
                
                // Remove the suspend button after action
                const suspendButton = this.currentRowElement.querySelector('button[data-action="suspend"]');
                if (suspendButton) suspendButton.remove();
            }
            this.hideModal(this.elements.confirmationModal);
        },

        handleExport() {
            // This function remains largely the same, just integrated here
            const isCustomers = this.activeTab === 'customers';
            const tableBody = isCustomers ? this.elements.customersTableBody : this.elements.sellersTableBody;
            const rows = [...tableBody.querySelectorAll('tr')].filter(row => row.style.display !== 'none');
            let data, headers, filename;

            if (isCustomers) {
                headers = ["ID", "Name", "Email", "Phone", "Total Orders", "Status", "Joined"];
                data = rows.map(row => {
                    const cells = row.querySelectorAll('td');
                    return [
                        cells[0].querySelector('.text-xs').textContent.replace('ID: ', ''),
                        cells[0].querySelector('.font-medium').textContent,
                        cells[1].querySelector('.text-sm').textContent,
                        cells[1].querySelectorAll('div')[1]?.textContent || 'N/A',
                        cells[2].textContent.trim(),
                        cells[3].textContent.trim(),
                        cells[4].textContent.trim()
                    ];
                });
                filename = 'customers-export.csv';
            } else {
                headers = ["ID", "Name", "Store Name", "Email", "Location", "Total Sales", "Status", "Joined"];
                data = rows.map(row => {
                    const cells = row.querySelectorAll('td');
                    return [
                        cells[0].querySelector('.text-xs').textContent.replace('ID: ', ''),
                        cells[0].querySelector('.font-medium').textContent,
                        cells[1].querySelector('.font-bold').textContent,
                        cells[0].querySelector('.text-xs.text-gray-500')?.textContent || 'N/A',
                        cells[1].querySelector('.text-xs').textContent.replace(' ', '').trim(),
                        cells[2].textContent.trim(),
                        cells[3].textContent.trim(),
                        cells[4].textContent.trim()
                    ];
                });
                filename = 'sellers-export.csv';
            }
            this.downloadCSV(headers, data, filename);
        },

        // --- Utility Functions ---
        showModal(modal) { modal?.classList.replace('hidden', 'flex'); },
        hideModal(modal) { modal?.classList.replace('flex', 'hidden'); },
        toggleNotificationDropdown(e) { e.stopPropagation(); this.elements.notificationDropdown.classList.toggle('hidden'); },
        closeNotificationDropdown(e) {
            if (!this.elements.notificationDropdown.classList.contains('hidden') && !this.elements.notificationBtn.contains(e.target) && !this.elements.notificationDropdown.contains(e.target)) {
                this.elements.notificationDropdown.classList.add('hidden');
            }
        },
        expandNotifications(e) { e.preventDefault(); this.elements.notificationDropdown.querySelector('#notification-list').classList.replace('max-h-80', 'max-h-[60vh]'); e.target.style.display = 'none'; },
        downloadCSV(headers, data, filename) {
            let csvContent = [headers.join(","), ...data.map(row => row.map(field => `"${(field || '').toString().replace(/"/g, '""')}"`).join(','))].join("\n");
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };

    // --- Entry Point ---
    document.addEventListener('DOMContentLoaded', () => app.init());
  </script>
  <script src="admin-theme.js"></script>
</body>
</html>