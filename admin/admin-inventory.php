<?php
// admin-inventory.php
// Mock Inventory Data
$inventory_stats = [
    "total_products" => 97,
    "low_stock" => 12,
    "out_of_stock" => 5,
    "incoming" => 240
];

$inventory_items = [
    [
        "id" => "98432",
        "sku" => "VEG-CRT-001",
        "name" => "Fresh Carrots",
        "category" => "Vegetables",
        "stock" => 450,
        "max_stock" => 1000,
        "reorder_level" => 100,
        "last_updated" => "2023-10-25",
        "status" => "In Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=VEG"
    ],
    [
        "id" => "76112",
        "sku" => "FRU-APP-022",
        "name" => "Organic Apples",
        "category" => "Fruits",
        "stock" => 12,
        "max_stock" => 500,
        "reorder_level" => 50,
        "last_updated" => "2023-10-26",
        "status" => "Low Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=FRU"
    ],
    [
        "id" => "23891",
        "sku" => "MEA-CHK-103",
        "name" => "Chicken Breast",
        "category" => "Meat",
        "stock" => 0,
        "max_stock" => 200,
        "reorder_level" => 20,
        "last_updated" => "2023-10-24",
        "status" => "Out of Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=MEA"
    ],
    [
        "id" => "11234",
        "sku" => "DAI-MLK-005",
        "name" => "Fresh Milk (1L)",
        "category" => "Dairy",
        "stock" => 150,
        "max_stock" => 300,
        "reorder_level" => 30,
        "last_updated" => "2023-10-27",
        "status" => "In Stock",
        "image" => "https://placehold.co/40x40/f3f4f6/1f2937?text=DAI"
    ],
    [
        "id" => "55431",
        "sku" => "GRN-RIC-099",
        "name" => "Brown Rice (5kg)",
        "category" => "Grains",
        "stock" => 80,
        "max_stock" => 200,
        "reorder_level" => 40,
        "last_updated" => "2023-10-20",
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
  <title>Farmers Mall Admin Panel - Inventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Global Styles (Consistent) */
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

  <a href="admin-inventory.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
    <i class="fa-solid fa-truck-ramp-box w-5 text-green-200"></i>
    <span>Inventory</span>
  </a>

  <a href="admin-retailers.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
    <i class="fa-solid fa-store w-5"></i>
    <span>Retailers</span>
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
        <input type="text" placeholder="Search by SKU, Name or Category..."
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
            <h2 class="text-3xl font-bold text-gray-900">Inventory Management</h2>
            <p class="text-sm text-gray-500">Track stock levels and manage reorders</p>
        </div>
        <div class="flex gap-3">
             <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-download"></i> Report
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors shadow-lg shadow-green-700/30">
                <i class="fa-solid fa-plus"></i> Stock Adjustment
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl card-shadow border-l-4 border-blue-500">
            <div class="text-gray-500 text-sm font-medium">Total Products</div>
            <div class="text-2xl font-bold text-gray-900 mt-1"><?php echo $inventory_stats['total_products']; ?></div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow border-l-4 border-yellow-500">
             <div class="text-gray-500 text-sm font-medium">Low Stock Alerts</div>
            <div class="text-2xl font-bold text-gray-900 mt-1"><?php echo $inventory_stats['low_stock']; ?></div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow border-l-4 border-red-500">
             <div class="text-gray-500 text-sm font-medium">Out of Stock</div>
            <div class="text-2xl font-bold text-gray-900 mt-1"><?php echo $inventory_stats['out_of_stock']; ?></div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow border-l-4 border-green-500">
             <div class="text-gray-500 text-sm font-medium">Incoming (PO)</div>
            <div class="text-2xl font-bold text-gray-900 mt-1"><?php echo $inventory_stats['incoming']; ?></div>
        </div>
    </div>

    <div class="bg-white rounded-xl card-shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-4">
            <h3 class="font-semibold text-lg">Stock Overview</h3>
            <div class="flex items-center gap-2">
                <select class="border border-gray-300 rounded-lg text-sm px-3 py-2 outline-none focus:border-green-500">
                    <option>All Categories</option>
                    <option>Vegetables</option>
                    <option>Fruits</option>
                    <option>Meat</option>
                </select>
                <select class="border border-gray-300 rounded-lg text-sm px-3 py-2 outline-none focus:border-green-500">
                    <option>Stock Status</option>
                    <option>Low Stock</option>
                    <option>Out of Stock</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product / SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4">Stock Level</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Updated</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($inventory_items as $item): 
                        // Calculate percentage for progress bar
                        $percent = ($item['stock'] / $item['max_stock']) * 100;
                        if($percent > 100) $percent = 100;
                        
                        // Color logic for progress bar
                        $barColor = 'bg-green-500';
                        if($percent < 30) $barColor = 'bg-yellow-500';
                        if($item['stock'] == 0) $barColor = 'bg-red-500';
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-lg object-cover border border-gray-100" src="<?php echo $item['image']; ?>" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $item['name']; ?></div>
                                    <div class="text-xs text-gray-500 font-mono"><?php echo $item['sku']; ?></div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo $item['category']; ?>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap align-middle">
                            <div class="w-full">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700"><?php echo $item['stock']; ?></span>
                                    <span class="text-gray-400">/ <?php echo $item['max_stock']; ?></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="<?php echo $barColor; ?> h-2 rounded-full" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                             <?php 
                                $statusClass = 'bg-gray-100 text-gray-800';
                                if($item['status'] === 'In Stock') $statusClass = 'bg-green-100 text-green-800 border border-green-200';
                                if($item['status'] === 'Low Stock') $statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                if($item['status'] === 'Out of Stock') $statusClass = 'bg-red-100 text-red-800 border border-red-200';
                            ?>
                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $item['last_updated']; ?>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-green-600 hover:text-green-900 mr-3" title="Restock"><i class="fa-solid fa-rotate-right"></i></button>
                            <button class="text-indigo-600 hover:text-indigo-900" title="Edit"><i class="fa-solid fa-pen"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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