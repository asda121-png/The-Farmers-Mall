<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Products</title>
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
    
    /* Custom Badge for consistency */
    .badge-top {
      background-color: #fef9c3; /* Light yellow background */
      color: #d97706; /* Darker amber text for visibility */
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
        <a href="admin-dashboard.html" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
          <span>Dashboard</span>
        </a>
        <!-- Active Link: Products -->
        <a href="admin-product.html" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-box w-5 text-green-200"></i>
          <span>Products</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-truck-ramp-box w-5"></i>
          <span>Inventory</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
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

    <!-- Top Header and Search Bar (Styled like Dashboard) -->
    <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-6 z-10 w-full">
      <div class="relative w-full max-w-lg hidden md:block">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" placeholder="Search products, retailers, ID..."
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
        <h2 class="text-3xl font-bold text-gray-900 mb-2 md:mb-0">Manage Products</h2>
        <div class="flex gap-3">
            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors card-shadow">
                <i class="fa-solid fa-plus mr-1"></i> Add New Product
            </button>
        </div>
    </div>

    <!-- Product Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Card 1: Total Products -->
        <div class="bg-green-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Total Products</p>
            <p id="statTotalProducts" class="text-3xl font-extrabold text-gray-900">--</p>
          </div>
          <i class="fa-solid fa-box-open text-4xl text-green-300"></i>
        </div>
        
        <!-- Card 2: Top Products -->
        <div class="bg-green-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Top Products</p>
            <p id="statTopProducts" class="text-3xl font-extrabold text-gray-900">--</p>
          </div>
          <i class="fa-solid fa-star text-4xl text-green-300"></i>
        </div>
        
        <!-- Card 3: Unique Categories -->
        <div class="bg-green-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Unique Categories</p>
            <p id="statUniqueCategories" class="text-3xl font-extrabold text-gray-900">--</p>
          </div>
          <i class="fa-solid fa-tags text-4xl text-green-300"></i>
        </div>
    </div>
    
    <!-- Product Table -->
    <div class="bg-white rounded-xl card-shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
            <th class="py-4 px-6">Product</th>
            <th class="py-4 px-6">Retailer</th>
            <th class="py-4 px-6">Category</th>
            <th class="py-4 px-6">Price</th>
            <th class="py-4 px-6">Stock</th>
            <th class="py-4 px-6 text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="productsTableBody" class="bg-white divide-y divide-gray-100">
            <!-- Product rows will be injected here by JavaScript -->
        </tbody>
        </table>
    </div>
      
    <!-- Product Action Modal -->
    <div id="actionModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 transition-opacity duration-300 opacity-0 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm transform scale-95 transition-transform duration-300">
        <div class="text-green-600 text-3xl mb-4">
            <i id="modalIcon" class="fa-solid fa-info-circle"></i>
        </div>
        <h3 id="modalTitle" class="font-bold text-xl mb-2 text-gray-900">Product Action</h3>
        <p id="modalMessage" class="text-gray-600 text-sm mb-6"></p>
        <div class="flex justify-end gap-4">
          <button id="cancelAction" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <button id="confirmAction" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors card-shadow">
            Confirm
          </button>
        </div>
      </div>
    </div>

  </div> <!-- End Main Content Area -->

  <!-- Logout Confirmation Modal (Matches Dashboard styling) -->
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
          <a href="#" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
            Logout
          </a>
        </div>
      </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Mock Data Setup ---
        const mockProducts = [
            { id: 101, name: "Organic Vine Tomatoes", price: 3.99, category: "Vegetables", retailer: "Green Farm Inc.", stock: 120, isTopSeller: true, image: "https://placehold.co/40x40/e0f2f1/064e3b?text=TOMATO" },
            { id: 102, name: "Free-Range Chicken Eggs (Dozen)", price: 5.50, category: "Dairy", retailer: "Harvest Hub", stock: 55, isTopSeller: false, image: "https://placehold.co/40x40/fff7ed/9a3412?text=EGG" },
            { id: 103, name: "Premium Basmati Rice (5kg)", price: 15.00, category: "Grains", retailer: "Rural Grocer", stock: 80, isTopSeller: true, image: "https://placehold.co/40x40/f3f4f6/1f2937?text=RICE" },
            { id: 104, name: "Freshly Baked Sourdough Loaf", price: 4.25, category: "Bakery", retailer: "Quality Produce", stock: 25, isTopSeller: false, image: "https://placehold.co/40x40/fef3c7/a16207?text=BREAD" },
            { id: 105, name: "Sweet Red Apples (Per kg)", price: 2.99, category: "Fruits", retailer: "Green Farm Inc.", stock: 210, isTopSeller: false, image: "https://placehold.co/40x40/fee2e2/991b1b?text=APPLE" },
            { id: 106, name: "Whole Milk (1 Gallon)", price: 3.75, category: "Dairy", retailer: "Harvest Hub", stock: 40, isTopSeller: false, image: "https://placehold.co/40x40/dbeafe/1e40af?text=MILK" },
            { id: 107, name: "Local Honey (Jar)", price: 8.99, category: "Pantry", retailer: "Rural Grocer", stock: 15, isTopSeller: true, image: "https://placehold.co/40x40/fcd34d/713f12?text=HONEY" },
        ];

        // --- Data Rendering ---
        const productsTableBody = document.getElementById('productsTableBody');
        const statTotalProducts = document.getElementById('statTotalProducts');
        const statTopProducts = document.getElementById('statTopProducts');
        const statUniqueCategories = document.getElementById('statUniqueCategories');

        let totalProducts = 0;
        let topProductsCount = 0;
        const uniqueCategories = new Set();
        let rowsHtml = '';

        mockProducts.forEach(product => {
            totalProducts++;
            if (product.isTopSeller) {
                topProductsCount++;
            }
            uniqueCategories.add(product.category);

            const stockClass = product.stock < 50 ? 'text-red-600' : 'text-green-600';
            const topSellerBadge = product.isTopSeller 
                ? '<span class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full badge-top mt-1 w-fit">TOP SELLER</span>' 
                : '';

            rowsHtml += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6 flex items-center gap-3">
                        <img src="${product.image}" 
                             class="w-10 h-10 object-cover rounded-md border border-gray-200" alt="${product.name}">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900">${product.name}</span>
                            ${topSellerBadge}
                        </div>
                    </td>
                    <td class="py-3 px-6 text-sm text-blue-600 font-medium cursor-pointer hover:underline">${product.retailer}</td>
                    <td class="py-3 px-6 text-sm text-gray-500">${product.category}</td>
                    <td class="py-3 px-6 text-sm font-semibold text-green-700">₱${product.price.toFixed(2)}</td>
                    <td class="py-3 px-6 text-sm text-gray-500">
                        <span class="${stockClass} font-medium">
                            ${product.stock} pcs
                        </span>
                    </td>
                    <td class="py-3 px-6 text-right">
                        <div class="flex justify-end gap-2">
                            <button data-action="view" data-id="${product.id}" data-name="${product.name}" class="action-btn text-gray-500 hover:text-green-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="View Details">
                                <i class="fa-solid fa-eye text-base"></i>
                            </button>
                            <button data-action="edit" data-id="${product.id}" data-name="${product.name}" class="action-btn text-gray-500 hover:text-yellow-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="Edit Product">
                                <i class="fa-solid fa-pen-to-square text-base"></i>
                            </button>
                            <button data-action="delete" data-id="${product.id}" data-name="${product.name}" class="action-btn text-gray-500 hover:text-red-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="Delete Product">
                                <i class="fa-solid fa-trash-can text-base"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        // Inject HTML and Update Stats
        productsTableBody.innerHTML = rowsHtml;
        statTotalProducts.textContent = totalProducts;
        statTopProducts.textContent = topProductsCount;
        statUniqueCategories.textContent = uniqueCategories.size;


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


        // --- Product Action Modal Logic ---
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

        productsTableBody.addEventListener('click', (e) => {
            const button = e.target.closest('.action-btn');
            if (!button) return;

            const action = button.dataset.action;
            const productId = button.dataset.id;
            const productName = button.dataset.name;
            
            showActionModal();

            // Reset classes
            confirmAction.classList.remove('bg-red-600', 'hover:bg-red-700', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-green-600', 'hover:bg-green-700', 'bg-gray-900', 'hover:bg-gray-800');
            modalIcon.classList.remove('text-red-600', 'text-yellow-600', 'text-green-600', 'text-gray-600', 'fa-eye', 'fa-pen-to-square', 'fa-trash-can');
            modalIcon.className = 'fa-solid mb-4 text-3xl';
            confirmAction.onclick = hideActionModal; // Default close action

            switch (action) {
                case 'view':
                    modalTitle.textContent = `View Product: ${productName}`;
                    modalMessage.innerHTML = `<p>Displaying mock details for Product ID <strong>${productId}</strong>.</p><p class="text-xs mt-3">This action should load a detailed view without leaving the page.</p>`;
                    confirmAction.textContent = 'Close';
                    confirmAction.classList.add('bg-gray-900', 'hover:bg-gray-800'); // Neutral confirm color
                    modalIcon.classList.add('fa-eye', 'text-gray-600');
                    break;
                case 'edit':
                    modalTitle.textContent = `Edit Product: ${productName}`;
                    modalMessage.innerHTML = `<p>Preparing to edit details for Product ID <strong>${productId}</strong>.</p><p class="text-xs mt-3">This action would redirect to a form or display an inline edit interface.</p>`;
                    confirmAction.textContent = 'Go to Edit Page (Mock)';
                    confirmAction.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                    modalIcon.classList.add('fa-pen-to-square', 'text-yellow-600');
                    confirmAction.onclick = () => {
                        console.log(`Mock: Redirecting to edit page for product ${productId}`);
                        hideActionModal();
                    };
                    break;
                case 'delete':
                    modalTitle.textContent = `Delete Product: ${productName}`;
                    modalMessage.innerHTML = `<p class="font-bold text-red-600">WARNING: Are you sure you want to permanently delete Product ID <strong>${productId}</strong>?</p><p class="text-xs mt-3">This action is irreversible and currently only a simulation.</p>`;
                    confirmAction.textContent = 'Delete Permanently (Mock)';
                    confirmAction.classList.add('bg-red-600', 'hover:bg-red-700');
                    modalIcon.classList.add('fa-trash-can', 'text-red-600');
                    confirmAction.onclick = () => {
                        console.log(`Mock: Deleting product ${productId}...`);
                        // In a real application, you would remove the row from the DOM here
                        hideActionModal();
                    };
                    break;
            }
        });
    });
  </script>
</body>

</html>