<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Inventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Global Styles */
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7f9fc; /* Light background for high-end feel */
    }

    /* Custom Shadow for High-End Cards */
    .card-shadow {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }
    
    /* Custom Green Color for Branding */
    .bg-green-950 {
        background-color: #184D34; /* Deep, rich green */
    }

    /* Ensure action modal is correctly positioned and visible */
    #actionModal, #logoutModal {
        z-index: 1000;
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
        <!-- Dashboard -->
        <a href="admin-dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
          <span>Dashboard</span>
        </a>
        <!-- Products -->
        <a href="admin-product.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-box w-5"></i>
          <span>Products</span>
        </a>
        <!-- Active Link: Inventory -->
        <a href="admin-inventory.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-truck-ramp-box w-5 text-green-200"></i>
          <span>Inventory</span>
        </a>
        <a href="admin-retailers.html" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-store w-5"></i>
          <span>Retailers</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-star w-5"></i>
          <span>Review</span>
          <span class="ml-auto text-xs bg-red-600 text-white px-2 py-0.5 rounded-full font-medium">02</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
      </nav>

      <!-- Navigation: ACCOUNT -->
      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-circle-info w-5"></i>
          <span>Help</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
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
  <div class="flex-1 p-6 space-y-6">

    <!-- Top Header and Search Bar -->
    <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-6 z-10 w-full">
      <div class="relative w-full max-w-lg hidden md:block">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" placeholder="Search inventory, warehouse, product ID..."
          class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
      </div>

      <!-- Right Header Icons -->
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
    
    <!-- Title and Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-2 md:mb-0">Warehouse Inventory</h2>
        <div class="flex gap-3">
            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-arrow-down-up-lock mr-1"></i> Stock Audit
            </button>
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors card-shadow">
                <i class="fa-solid fa-box-open mr-1"></i> New Shipment
            </button>
        </div>
    </div>

    <!-- Inventory Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Total SKUs -->
        <div class="bg-blue-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Total SKUs Tracked</p>
            <p id="statTotalSkus" class="text-3xl font-extrabold text-gray-900">--</p>
          </div>
          <i class="fa-solid fa-barcode text-4xl text-blue-300"></i>
        </div>
        
        <!-- Card 2: Low Stock Items -->
        <div class="bg-red-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Low Stock Items</p>
            <p id="statLowStock" class="text-3xl font-extrabold text-red-600">--</p>
          </div>
          <i class="fa-solid fa-triangle-exclamation text-4xl text-red-300"></i>
        </div>
        
        <!-- Card 3: Total Quantity -->
        <div class="bg-green-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Total Quantity In Hand</p>
            <p id="statTotalQuantity" class="text-3xl font-extrabold text-gray-900">--</p>
          </div>
          <i class="fa-solid fa-warehouse text-4xl text-green-300"></i>
        </div>

        <!-- Card 4: Recent Restock -->
        <div class="bg-yellow-50 p-5 rounded-xl card-shadow flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500 mb-1">Last Restock Date</p>
              <p id="statLastRestock" class="text-3xl font-extrabold text-gray-900">--</p>
            </div>
            <i class="fa-solid fa-calendar-alt text-4xl text-yellow-300"></i>
          </div>
    </div>
    
    <!-- Inventory Table -->
    <div class="bg-white rounded-xl card-shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
            <th class="py-4 px-6">Product SKU</th>
            <th class="py-4 px-6">Product Name</th>
            <th class="py-4 px-6">Stock Level</th>
            <th class="py-4 px-6">Location</th>
            <th class="py-4 px-6">Last Restock</th>
            <th class="py-4 px-6 text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="inventoryTableBody" class="bg-white divide-y divide-gray-100">
            <!-- Inventory rows will be injected here by JavaScript -->
        </tbody>
        </table>
    </div>
      
    <!-- Inventory Action Modal -->
    <div id="actionModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 transition-opacity duration-300 opacity-0 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm transform scale-95 transition-transform duration-300">
        <div class="text-green-600 text-3xl mb-4">
            <i id="modalIcon" class="fa-solid fa-info-circle"></i>
        </div>
        <h3 id="modalTitle" class="font-bold text-xl mb-2 text-gray-900">Inventory Action</h3>
        <p id="modalMessage" class="text-gray-600 text-sm mb-6"></p>
        <div class="flex justify-end gap-4">
          <button type="button" id="cancelAction" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <button type="button" id="confirmAction" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors card-shadow">
            Confirm
          </button>
        </div>
      </div>
    </div>

  </div> <!-- End Main Content Area -->

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
        <div class="text-red-500 text-4xl mb-4">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Logout</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out of the Farmers Mall Admin Panel?</p>
        <div class="flex justify-center gap-4">
          <button type="button" id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <a href="#" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
            Logout
          </a>
        </div>
      </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Mock Data Setup ---
        const mockInventory = [
            { id: 'SKU101A', name: "Organic Vine Tomatoes", stock: 120, location: "W-Aisle-05", lastRestock: "2024-05-15", lowThreshold: 60 },
            { id: 'SKU102B', name: "Free-Range Chicken Eggs (Dozen)", stock: 55, location: "C-Rack-02", lastRestock: "2024-05-10", lowThreshold: 70 }, // Low Stock
            { id: 'SKU103C', name: "Premium Basmati Rice (5kg)", stock: 80, location: "W-Shelf-12", lastRestock: "2024-04-28", lowThreshold: 50 },
            { id: 'SKU104D', name: "Freshly Baked Sourdough Loaf", stock: 25, location: "C-Rack-07", lastRestock: "2024-05-17", lowThreshold: 30 },
            { id: 'SKU105E', name: "Sweet Red Apples (Per kg)", stock: 210, location: "W-Aisle-03", lastRestock: "2024-05-16", lowThreshold: 100 },
            { id: 'SKU106F', name: "Whole Milk (1 Gallon)", stock: 40, location: "C-Rack-02", lastRestock: "2024-05-01", lowThreshold: 50 }, // Low Stock
            { id: 'SKU107G', name: "Local Honey (Jar)", stock: 15, location: "W-Shelf-01", lastRestock: "2024-03-20", lowThreshold: 20 },
        ];
        
        // Default low stock threshold if item-specific one is not provided
        const DEFAULT_LOW_STOCK_THRESHOLD = 50; 

        // --- Data Rendering ---
        const inventoryTableBody = document.getElementById('inventoryTableBody');
        const statTotalSkus = document.getElementById('statTotalSkus');
        const statLowStock = document.getElementById('statLowStock');
        const statTotalQuantity = document.getElementById('statTotalQuantity');
        const statLastRestock = document.getElementById('statLastRestock');

        let totalQuantity = 0;
        let lowStockCount = 0;
        let latestRestockDate = new Date(0);
        let rowsHtml = '';

        mockInventory.forEach(item => {
            totalQuantity += item.stock;

            // Determine stock status based on actual threshold, prioritizing item's specific threshold
            const threshold = item.lowThreshold || DEFAULT_LOW_STOCK_THRESHOLD;
            const isLowStock = item.stock <= threshold;
            if (isLowStock) {
                lowStockCount++;
            }

            // Update latest restock date
            const restockDate = new Date(item.lastRestock);
            if (restockDate > latestRestockDate) {
                latestRestockDate = restockDate;
            }

            // Stock badge logic
            let stockClass, stockText;
            if (item.stock === 0) {
                stockClass = 'bg-gray-200 text-gray-800';
                stockText = 'OUT OF STOCK';
            } else if (isLowStock) {
                stockClass = 'bg-red-100 text-red-700';
                stockText = 'LOW STOCK';
            } else if (item.stock > 150) { // arbitrary 'high stock' threshold for display purposes
                stockClass = 'bg-blue-100 text-blue-700';
                stockText = 'HIGH STOCK';
            } else {
                stockClass = 'bg-green-100 text-green-700';
                stockText = 'IN STOCK';
            }
            
            // Format Last Restock Date
            const formattedRestockDate = new Date(item.lastRestock).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });


            rowsHtml += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6 text-sm font-semibold text-gray-900">${item.id}</td>
                    <td class="py-3 px-6 text-sm text-gray-700">${item.name}</td>
                    <td class="py-3 px-6 text-sm">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium ${stockClass}">
                           ${stockText} (${item.stock})
                        </span>
                        <span class="text-xs text-gray-500 block mt-1">Min: ${threshold}</span>
                    </td>
                    <td class="py-3 px-6 text-sm text-gray-500 font-mono">${item.location}</td>
                    <td class="py-3 px-6 text-sm text-gray-500">${formattedRestockDate}</td>
                    <td class="py-3 px-6 text-right">
                        <div class="flex justify-end gap-2">
                            <button data-action="restock" data-id="${item.id}" data-name="${item.name}" class="action-btn text-gray-500 hover:text-green-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="Restock/Update">
                                <i class="fa-solid fa-dolly text-base"></i>
                            </button>
                            <button data-action="relocate" data-id="${item.id}" data-name="${item.name}" class="action-btn text-gray-500 hover:text-blue-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="Relocate Item">
                                <i class="fa-solid fa-map-pin text-base"></i>
                            </button>
                            <button data-action="audit" data-id="${item.id}" data-name="${item.name}" class="action-btn text-gray-500 hover:text-red-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="Trigger Audit">
                                <i class="fa-solid fa-clipboard-check text-base"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        // Inject HTML and Update Stats
        inventoryTableBody.innerHTML = rowsHtml;
        statTotalSkus.textContent = mockInventory.length;
        statLowStock.textContent = lowStockCount;
        statTotalQuantity.textContent = totalQuantity.toLocaleString();
        
        const formattedLatestRestock = latestRestockDate.toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric'
        });
        // Check if latestRestockDate is the initial date (1970)
        statLastRestock.textContent = latestRestockDate.getFullYear() === 1970 ? 'N/A' : formattedLatestRestock;


        // --- Logout Modal Logic (Reused) ---
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


        // --- Inventory Action Modal Logic ---
        const actionModal = document.getElementById('actionModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const confirmAction = document.getElementById('confirmAction');
        const cancelAction = document.getElementById('cancelAction');
        const modalIcon = document.getElementById('modalIcon');

        const hideActionModal = () => {
            // Use opacity for smoother transition effect
            actionModal.classList.remove('opacity-100');
            actionModal.classList.add('opacity-0');
            setTimeout(() => {
                actionModal.classList.add('hidden');
            }, 300); // Match transition duration
        };

        const showActionModal = () => {
             // Use opacity for smoother transition effect
            actionModal.classList.remove('hidden');
            actionModal.classList.add('flex');
            setTimeout(() => {
                actionModal.classList.remove('opacity-0');
                actionModal.classList.add('opacity-100');
            }, 50);
        };

        cancelAction.addEventListener('click', hideActionModal);
        actionModal.addEventListener('click', (e) => {
            if (e.target === actionModal) {
                hideActionModal();
            }
        });

        inventoryTableBody.addEventListener('click', (e) => {
            const button = e.target.closest('.action-btn');
            if (!button) return;

            const action = button.dataset.action;
            const itemId = button.dataset.id;
            const itemName = button.dataset.name;
            
            showActionModal();

            // Reset classes and event handler
            confirmAction.classList.remove('bg-red-600', 'hover:bg-red-700', 'bg-blue-600', 'hover:bg-blue-700', 'bg-green-600', 'hover:bg-green-700', 'bg-gray-900', 'hover:bg-gray-800');
            modalIcon.classList.remove('text-red-600', 'text-blue-600', 'text-green-600', 'text-gray-600', 'fa-dolly', 'fa-map-pin', 'fa-clipboard-check');
            modalIcon.className = 'fa-solid mb-4 text-3xl';
            confirmAction.onclick = hideActionModal; // Default close action

            switch (action) {
                case 'restock':
                    modalTitle.textContent = `Restock ${itemName}`;
                    modalMessage.innerHTML = `<p>Simulating the process of adding a new shipment to SKU <strong>${itemId}</strong>.</p><p class="text-xs mt-3">This would typically involve capturing the received quantity and updating the stock count and restock date in a real system.</p>`;
                    confirmAction.textContent = 'Process Restock (Mock)';
                    confirmAction.classList.add('bg-green-600', 'hover:bg-green-700'); // Green for successful addition
                    modalIcon.classList.add('fa-dolly', 'text-green-600');
                    confirmAction.onclick = () => {
                        console.log(`Mock: Restock recorded for SKU ${itemId}`);
                        hideActionModal();
                    };
                    break;
                case 'relocate':
                    modalTitle.textContent = `Relocate ${itemName}`;
                    modalMessage.innerHTML = `<p>Simulating the process of updating the warehouse location for SKU <strong>${itemId}</strong>.</p><p class="text-xs mt-3">In a real application, a form would appear here to accept the new location (e.g., Aisle 10, Shelf 3).</p>`;
                    confirmAction.textContent = 'Relocate (Mock)';
                    confirmAction.classList.add('bg-blue-600', 'hover:bg-blue-700'); // Blue for relocation
                    modalIcon.classList.add('fa-map-pin', 'text-blue-600');
                    confirmAction.onclick = () => {
                        console.log(`Mock: Location updated for SKU ${itemId}`);
                        hideActionModal();
                    };
                    break;
                case 'audit':
                    modalTitle.textContent = `Inventory Audit for ${itemName}`;
                    modalMessage.innerHTML = `<p class="font-bold">Are you sure you want to trigger a manual audit request for SKU <strong>${itemId}</strong>?</p><p class="text-xs mt-3">An audit confirms the physical stock count matches the system record, often resulting in a variance report.</p>`;
                    confirmAction.textContent = 'Start Audit (Mock)';
                    confirmAction.classList.add('bg-gray-900', 'hover:bg-gray-800'); // Neutral color for management task
                    modalIcon.classList.add('fa-clipboard-check', 'text-gray-600');
                    confirmAction.onclick = () => {
                        console.log(`Mock: Audit started for SKU ${itemId}`);
                        hideActionModal();
                    };
                    break;
            }
        });
    });
  </script>
</body>

</html>