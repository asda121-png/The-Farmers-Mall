<?php
// admin-products.php
// Simulated Database Connection/Data

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
$admin_name = "Admin User";

// Mock Product Data
$products = [
    [
        "id" => "98432",
        "name" => "Fresh Carrots",
        "category" => "Vegetables",
        "price" => 120.00,
        "stock" => 450,
        "sold" => 1200,
        "status" => "In Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=VEG"
    ],
    [
        "id" => "76112",
        "name" => "Organic Apples",
        "category" => "Fruits",
        "price" => 85.50,
        "stock" => 12,
        "sold" => 850,
        "status" => "Low Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=FRU"
    ],
    [
        "id" => "23891",
        "name" => "Chicken Breast",
        "category" => "Meat",
        "price" => 240.00,
        "stock" => 0,
        "sold" => 2100,
        "status" => "Out of Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=MEA"
    ],
    [
        "id" => "11234",
        "name" => "Fresh Milk (1L)",
        "category" => "Dairy",
        "price" => 90.00,
        "stock" => 150,
        "sold" => 430,
        "status" => "In Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=DAI"
    ],
    [
        "id" => "55431",
        "name" => "Brown Rice (5kg)",
        "category" => "Grains",
        "price" => 350.00,
        "stock" => 80,
        "sold" => 150,
        "status" => "In Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=GRN"
    ],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Products</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
  <style>
    /* Global Styles (Consistent with Dashboard) */
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
            <i class="fa-solid fa-tachometer-alt w-5 "></i>
            <span>Dashboard</span>
        </a>

       <!-- UPDATED: Removed 'bg-green-700 text-white' to remove permanent highlight. Added hover effects. -->
        <a href="admin-riders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-motorcycle w-5"></i>
          <span>Riders</span>
        </a>
      </nav>

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
        <input type="text" id="search-input" placeholder="Search products by name or ID..."
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
            <h2 class="text-3xl font-bold text-gray-900">Products</h2>
            <p class="text-sm text-gray-500">Manage your product catalog and inventory</p>
        </div>
        <div class="flex gap-3">
             <button id="export-btn" class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-file-export"></i> Export
            </button>
            <button id="add-product-btn" class="flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors shadow-lg shadow-green-700/30">
                <i class="fa-solid fa-plus"></i> Add Product
            </button>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl card-shadow flex flex-wrap gap-4 items-center">
        <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 w-full md:w-auto md:min-w-[200px]">
            <i class="fa-solid fa-filter text-gray-400"></i>
            <select id="category-filter" class="w-full bg-transparent text-sm text-gray-700 outline-none cursor-pointer">
                <option value="">All Categories</option>
                <option value="Vegetables">Vegetables</option>
                <option value="Fruits">Fruits</option>
                <option value="Meat">Meat</option>
                <option value="Dairy">Dairy</option>
            </select>
        </div>
        
        <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 w-full md:w-auto md:min-w-[200px]">
             <i class="fa-solid fa-layer-group text-gray-400"></i>
            <select id="status-filter" class="w-full bg-transparent text-sm text-gray-700 outline-none cursor-pointer">
                <option value="">Status</option>
                <option value="In Stock">In Stock</option>
                <option value="Low Stock">Low Stock</option>
                <option value="Out of Stock">Out of Stock</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl card-shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            <input type="checkbox" id="select-all-checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Sold</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="bg-white divide-y divide-gray-200">
                    <?php foreach ($products as $product): ?>
                    <tr class="product-row hover:bg-gray-50 transition-colors" data-category="<?php echo $product['category']; ?>" data-status="<?php echo $product['status']; ?>">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="product-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500" data-id="<?php echo $product['id']; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-lg object-cover border border-gray-100" src="<?php echo $product['image']; ?>" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $product['name']; ?></div>
                                    <div class="text-xs text-gray-500">ID: #<?php echo $product['id']; ?></div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded-md"><?php echo $product['category']; ?></span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">₱<?php echo number_format($product['price'], 2); ?></div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <?php 
                                    $statusClass = 'bg-gray-100 text-gray-800';
                                    if($product['status'] === 'In Stock') $statusClass = 'bg-green-100 text-green-800';
                                    if($product['status'] === 'Low Stock') $statusClass = 'bg-yellow-100 text-yellow-800';
                                    if($product['status'] === 'Out of Stock') $statusClass = 'bg-red-100 text-red-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full w-fit <?php echo $statusClass; ?>">
                                    <?php echo $product['status']; ?>
                                </span>
                                <span class="text-xs text-gray-500"><?php echo $product['stock']; ?> items left</span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $product['sold']; ?>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="relative inline-block text-left">
                                <button type="button" class="action-dropdown-btn inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-3 py-1 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none" data-id="<?php echo $product['id']; ?>">
                                    Actions
                                    <i class="fa-solid fa-chevron-down -mr-1 ml-2 h-5 w-5"></i>
                                </button>
                                <div id="action-menu-<?php echo $product['id']; ?>" class="action-menu hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
                                    <div class="py-1" role="menu" aria-orientation="vertical">
                                        <a href="#" class="action-btn block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" data-action="edit" data-id="<?php echo $product['id']; ?>"><i class="fa-solid fa-pen-to-square w-5 mr-2"></i>Edit Product</a>
                                        <a href="#" class="action-btn block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" data-action="export-pdf" data-id="<?php echo $product['id']; ?>"><i class="fa-solid fa-file-pdf w-5 mr-2"></i>Export as PDF</a>
                                        <a href="#" class="action-btn block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" data-action="export-json" data-id="<?php echo $product['id']; ?>"><i class="fa-solid fa-file-code w-5 mr-2"></i>Export as JSON</a>
                                        <a href="#" class="action-btn block px-4 py-2 text-sm text-red-700 hover:bg-red-50" role="menuitem" data-action="delete" data-id="<?php echo $product['id']; ?>" data-name="<?php echo $product['name']; ?>"><i class="fa-solid fa-trash-can w-5 mr-2"></i>Delete Product</a>
                                    </div>
                                </div>
                            </div>
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
                        Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">97</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <i class="fa-solid fa-chevron-left h-4 w-4"></i>
                        </a>
                        <a href="#" aria-current="page" class="z-10 bg-green-50 border-green-500 text-green-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">2</a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">3</a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                             <i class="fa-solid fa-chevron-right h-4 w-4"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options Modal -->
    <div id="exportModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-md text-center">
        <div class="text-green-500 text-4xl mb-4">
          <i class="fa-solid fa-file-csv"></i>
        </div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Export Product Data</h3>
        <p class="text-gray-600 text-sm mb-6">Choose which set of data you would like to export to a CSV file.</p>
        <div class="flex flex-col justify-center gap-3">
          <button id="exportSelectedBtn" disabled class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed">
            Export Selected (0)
          </button>
          <button id="exportFilteredBtn" class="w-full px-6 py-3 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors">
            Export Filtered View
          </button>
          <button id="exportAllBtn" class="w-full px-6 py-3 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors">
            Export All Products
          </button>
        </div>
        <button id="cancelExport" class="mt-6 text-sm text-gray-500 hover:underline">Cancel</button>
      </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-6 w-full max-w-lg">
        <h3 id="modalTitle" class="font-bold text-xl mb-4 text-gray-900 border-b pb-2">Add New Product</h3>
        <form id="productForm" class="space-y-4">
            <input type="hidden" id="productId" name="productId">
            <div>
                <label for="productName" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                <input type="text" id="productName" name="productName" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="productCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="productCategory" name="productCategory" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        <option>Vegetables</option>
                        <option>Fruits</option>
                        <option>Meat</option>
                        <option>Dairy</option>
                        <option>Grains</option>
                    </select>
                </div>
                <div>
                    <label for="productPrice" class="block text-sm font-medium text-gray-700 mb-1">Price (₱)</label>
                    <input type="number" id="productPrice" name="productPrice" step="0.01" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div>
                <label for="productStock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                <input type="number" id="productStock" name="productStock" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" id="cancelProductModal" class="px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800">Save Product</button>
            </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
        <div class="text-red-500 text-4xl mb-4">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Deletion</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to delete "<span id="deleteProductName" class="font-bold"></span>"? This action cannot be undone.</p>
        <div class="flex justify-center gap-4">
          <button id="cancelDelete" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
          <button id="confirmDelete" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Delete</button>
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

  </div> <script src="admin-theme.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Logout Logic
      // Mock product data for JS
      const productsData = <?php echo json_encode($products); ?>;

      // --- Modal Elements ---
      const logoutButton = document.getElementById('logoutButton');
      const logoutModal = document.getElementById('logoutModal');
      const cancelLogout = document.getElementById('cancelLogout');
      const productModal = document.getElementById('productModal');
      const deleteModal = document.getElementById('deleteModal');
      const addProductBtn = document.getElementById('add-product-btn');
      const cancelProductModalBtn = document.getElementById('cancelProductModal');
      const productForm = document.getElementById('productForm');
      const modalTitle = document.getElementById('modalTitle');
      const cancelDeleteBtn = document.getElementById('cancelDelete');
      const confirmDeleteBtn = document.getElementById('confirmDelete');
      const tableBody = document.getElementById('products-table-body');
      const exportBtn = document.getElementById('export-btn');
      const exportModal = document.getElementById('exportModal');
      const cancelExportBtn = document.getElementById('cancelExport');
      const exportFilteredBtn = document.getElementById('exportFilteredBtn');
      const exportSelectedBtn = document.getElementById('exportSelectedBtn');
      const exportAllBtn = document.getElementById('exportAllBtn');
      const selectAllCheckbox = document.getElementById('select-all-checkbox');

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

      // --- Notification Dropdown Logic ---
      const notificationBtn = document.getElementById('notification-btn');
      const notificationDropdown = document.getElementById('notification-dropdown');
      const viewAllBtn = document.getElementById('view-all-notifications-btn');

      notificationBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notificationDropdown.classList.toggle('hidden');
      });

      window.addEventListener('click', (e) => {
        if (!notificationDropdown.classList.contains('hidden') && !notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
          notificationDropdown.classList.add('hidden');
        }
      });

      viewAllBtn.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('notification-list').classList.replace('max-h-80', 'max-h-[60vh]');
        viewAllBtn.style.display = 'none';
      });

      // --- Product Modal Logic ---
      const showProductModal = (product = null) => {
        productForm.reset();
        if (product) {
            modalTitle.textContent = 'Edit Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
        } else {
            modalTitle.textContent = 'Add New Product';
            document.getElementById('productId').value = '';
        }
        productModal.classList.remove('hidden');
        productModal.classList.add('flex');
      };

      const hideProductModal = () => {
        productModal.classList.add('hidden');
        productModal.classList.remove('flex');
      };

      addProductBtn.addEventListener('click', () => showProductModal());
      cancelProductModalBtn.addEventListener('click', hideProductModal);
      productModal.addEventListener('click', (e) => {
        if (e.target === productModal) hideProductModal();
      });

      productForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(productForm);
        const productData = Object.fromEntries(formData.entries());
        console.log('Saving product:', productData); // In a real app, send this to the server
        hideProductModal();
        // Here you would typically re-fetch data or dynamically update the table
      });

      // --- Delete Modal Logic ---
      let rowToDelete = null;

      const showDeleteModal = (productName, rowElement) => {
        document.getElementById('deleteProductName').textContent = productName;
        rowToDelete = rowElement;
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
      };

      const hideDeleteModal = () => {
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
        rowToDelete = null;
      };

      cancelDeleteBtn.addEventListener('click', hideDeleteModal);
      deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) hideDeleteModal();
      });

      confirmDeleteBtn.addEventListener('click', () => {
        if (rowToDelete) {
            console.log('Deleting product row:', rowToDelete);
            rowToDelete.remove(); // Remove the row from the DOM
        }
        hideDeleteModal();
      });

      // --- Table Actions (Edit/Delete) ---
        tableBody.addEventListener('click', (e) => {
            const dropdownBtn = e.target.closest('.action-dropdown-btn');
            const actionBtn = e.target.closest('.action-btn');

            // --- Handle Dropdown Toggle ---
            if (dropdownBtn) {
                e.preventDefault();
                const productId = dropdownBtn.dataset.id;
                const menu = document.getElementById(`action-menu-${productId}`);
                
                // Close all other open menus
                document.querySelectorAll('.action-menu').forEach(m => {
                    if (m !== menu) m.classList.add('hidden');
                });

                menu.classList.toggle('hidden');
            }

            // --- Handle Action Button Clicks ---
            if (actionBtn) {
                e.preventDefault();
                const action = actionBtn.dataset.action;
                const productId = actionBtn.dataset.id;
                const product = productsData.find(p => p.id === productId);

                if (!product) return;

                switch (action) {
                    case 'edit':
                        showProductModal(product);
                        break;
                    case 'delete':
                        const row = actionBtn.closest('tr');
                        showDeleteModal(product.name, row);
                        break;
                    case 'export-json':
                        const jsonString = JSON.stringify(product, null, 2);
                        const blob = new Blob([jsonString], { type: 'application/json' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `product-${product.id}.json`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        break;
                    case 'export-pdf':
                        alert(`Simulating PDF export for: ${product.name}.\nIn a real app, this would generate and download a PDF file.`);
                        break;
                }
            }
        });

      // --- Filtering Logic ---
      const categoryFilter = document.getElementById('category-filter');
      const statusFilter = document.getElementById('status-filter');
      const searchInput = document.getElementById('search-input');
      const productRows = document.querySelectorAll('.product-row');
      const allCheckboxes = document.querySelectorAll('.product-checkbox');

      function filterProducts() {
        const selectedCategory = categoryFilter.value;
        const selectedStatus = statusFilter.value;
        const searchTerm = searchInput.value.toLowerCase();

        productRows.forEach(row => {
            const categoryMatch = !selectedCategory || row.dataset.category === selectedCategory;
            const statusMatch = !selectedStatus || row.dataset.status === selectedStatus;
            const productText = row.querySelector('td').textContent.toLowerCase();
            const searchMatch = productText.includes(searchTerm);

            if (categoryMatch && statusMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        updateSelectionState();
      }

      categoryFilter.addEventListener('change', filterProducts);
      statusFilter.addEventListener('change', filterProducts);
      searchInput.addEventListener('input', filterProducts);

      // --- Checkbox Selection Logic ---
      function updateSelectionState() {
        const visibleCheckboxes = [...allCheckboxes].filter(cb => cb.closest('tr').style.display !== 'none');
        const checkedVisibleCheckboxes = visibleCheckboxes.filter(cb => cb.checked);

        // Update "Select All" checkbox state
        if (checkedVisibleCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedVisibleCheckboxes.length === visibleCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }

        // Update export button
        const totalSelectedCount = [...allCheckboxes].filter(cb => cb.checked).length;
        exportSelectedBtn.textContent = `Export Selected (${totalSelectedCount})`;
        exportSelectedBtn.disabled = totalSelectedCount === 0;
      }

      selectAllCheckbox.addEventListener('change', () => {
        const isChecked = selectAllCheckbox.checked;
        const visibleCheckboxes = [...allCheckboxes].filter(cb => cb.closest('tr').style.display !== 'none');
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        updateSelectionState();
      });

      tableBody.addEventListener('change', (e) => {
        if (e.target.classList.contains('product-checkbox')) {
            updateSelectionState();
        }
      });

      // --- Export to CSV Logic ---
      const downloadCSV = (dataToExport, filename) => {
        const headers = ["ID", "Name", "Category", "Price", "Stock", "Sold", "Status"];
        let csvContent = headers.join(",") + "\n";

        dataToExport.forEach(product => {
            const rowData = [
                product.id,
                `"${product.name}"`,
                product.category,
                product.price,
                product.stock,
                product.sold,
                product.status
            ].join(",");
            csvContent += rowData + "\n";
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        hideExportModal();
      };

      const showExportModal = () => {
        updateSelectionState(); // Ensure count is up-to-date when opening
        exportModal.classList.remove('hidden');
        exportModal.classList.add('flex');
      };

      const hideExportModal = () => {
        exportModal.classList.add('hidden');
        exportModal.classList.remove('flex');
      };

      exportBtn.addEventListener('click', showExportModal);
      cancelExportBtn.addEventListener('click', hideExportModal);
      exportModal.addEventListener('click', (e) => {
        if (e.target === exportModal) hideExportModal();
      });

      exportFilteredBtn.addEventListener('click', () => {
        const filteredData = [];
        const visibleRows = document.querySelectorAll('.product-row');
        visibleRows.forEach(row => {
            if (row.style.display !== 'none') {
                const productId = row.querySelector('.text-xs.text-gray-500').textContent.replace('ID: #', '').trim();
                const product = productsData.find(p => p.id === productId);
                if (product) filteredData.push(product);
            }
        });
        downloadCSV(filteredData, 'products-filtered-export.csv');
      });

      exportAllBtn.addEventListener('click', () => {
        downloadCSV(productsData, 'products-all-export.csv');
      });

      exportSelectedBtn.addEventListener('click', () => {
        const selectedData = [];
        const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
        selectedCheckboxes.forEach(checkbox => {
            const productId = checkbox.dataset.id;
            const product = productsData.find(p => p.id === productId);
            if (product) selectedData.push(product);
        });
        downloadCSV(selectedData, 'products-selected-export.csv');
      });

      // Close action dropdowns if clicking outside
      window.addEventListener('click', function(e) {
        if (!e.target.closest('.action-dropdown-btn')) {
            document.querySelectorAll('.action-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
      });
    });
  </script>
</body>
</html> 