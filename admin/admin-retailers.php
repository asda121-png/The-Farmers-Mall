<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Retailers</title>
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

    /* Ensure modals are correctly positioned and visible */
    #addRetailerModal, #logoutModal {
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
        <!-- Active Link: Dashboard -->
        <a href="admin-dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-tachometer-alt w-5 text-green-200"></i>
          <span>Dashboard</span>
        </a>
        <a href="admin-product.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
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
        <a href="admin-reviews.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-star w-5"></i>
          <span>Review</span>
          <span class="ml-auto text-xs bg-red-600 text-white px-2 py-0.5 rounded-full font-medium">02</span>
        </a>
        <a href="admin-orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
      </nav>

      <!-- Navigation: ACCOUNT -->
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
  </aside>

  <!-- Main Content Area -->
  <div class="flex-1 p-6 space-y-6">

    <!-- Top Header and Search Bar -->
    <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-6 z-10 w-full">
      <div class="relative w-full max-w-lg hidden md:block">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" placeholder="Search retailer names, location, contact..."
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
        <h2 class="text-3xl font-bold text-gray-900 mb-2 md:mb-0">Retailer Management</h2>
        <div class="flex gap-3">
            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-file-export mr-1"></i> Export Report
            </button>
            <button id="openAddRetailerModal" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors card-shadow">
                <i class="fa-solid fa-plus-circle mr-1"></i> Add New Retailer
            </button>
        </div>
    </div>

    <!-- Retailer Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Total Retailers -->
        <div class="bg-blue-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Total Retailers</p>
            <p id="statTotalRetailers" class="text-3xl font-extrabold text-gray-900">--</p>
          </div>
          <i class="fa-solid fa-users text-4xl text-blue-300"></i>
        </div>
        
        <!-- Card 2: Pending Applications -->
        <div class="bg-yellow-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Pending Applications</p>
            <p id="statPending" class="text-3xl font-extrabold text-yellow-600">--</p>
          </div>
          <i class="fa-solid fa-hourglass-half text-4xl text-yellow-300"></i>
        </div>
        
        <!-- Card 3: Average Rating -->
        <div class="bg-purple-50 p-5 rounded-xl card-shadow flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 mb-1">Avg. Retailer Rating</p>
            <p id="statAvgRating" class="text-3xl font-extrabold text-gray-900">--</p>
          </div>
          <i class="fa-solid fa-star text-4xl text-purple-300"></i>
        </div>

        <!-- Card 4: High Volume Retailers -->
        <div class="bg-green-50 p-5 rounded-xl card-shadow flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500 mb-1">High Volume Partners</p>
              <p id="statHighVolume" class="text-3xl font-extrabold text-gray-900">--</p>
            </div>
            <i class="fa-solid fa-medal text-4xl text-green-300"></i>
          </div>
    </div>
    
    <!-- Retailers Table -->
    <div class="bg-white rounded-xl card-shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
            <th class="py-4 px-6">ID</th>
            <th class="py-4 px-6">Retailer Name</th>
            <th class="py-4 px-6">Contact Email</th>
            <th class="py-4 px-6">Status</th>
            <th class="py-4 px-6">Products Listed</th>
            <th class="py-4 px-6">Joined Date</th>
            <th class="py-4 px-6 text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="retailersTableBody" class="bg-white divide-y divide-gray-100">
            <!-- Retailer rows will be injected here by JavaScript -->
        </tbody>
        </table>
    </div>

  </div> <!-- End Main Content Area -->

  <!-- Add New Retailer Modal -->
  <div id="addRetailerModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 transition-opacity duration-300 opacity-0 p-4">
    <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-lg transform scale-95 transition-transform duration-300">
      <h3 class="font-bold text-2xl mb-6 text-gray-900 border-b pb-3">Add New Retailer</h3>
      <form id="addRetailerForm">
        <div class="space-y-4">
          <div>
            <label for="retailerName" class="block text-sm font-medium text-gray-700 mb-1">Retailer Name</label>
            <input type="text" id="retailerName" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 outline-none transition-shadow">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="retailerEmail" class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
              <input type="email" id="retailerEmail" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 outline-none transition-shadow">
            </div>
            <div>
              <label for="retailerPhone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number (Optional)</label>
              <input type="tel" id="retailerPhone" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 outline-none transition-shadow">
            </div>
          </div>
          <div>
            <label for="retailerLocation" class="block text-sm font-medium text-gray-700 mb-1">Primary Location</label>
            <input type="text" id="retailerLocation" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 outline-none transition-shadow">
          </div>
        </div>
        
        <div class="mt-8 flex justify-end gap-4">
          <button type="button" id="closeAddRetailerModal" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors card-shadow">
            <i class="fa-solid fa-floppy-disk mr-1"></i> Save Retailer
          </button>
        </div>
      </form>
    </div>
  </div>


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
        const mockRetailers = [
            { id: 1001, name: "Green Valley Farms", email: "contact@gvfarms.com", status: "Active", products: 45, joined: "2023-01-15", rating: 4.8 },
            { id: 1002, name: "The Harvest Hub", email: "support@harvesthub.co", status: "Pending", products: 0, joined: "2024-05-10", rating: 0.0 },
            { id: 1003, name: "Rural Grocer Co.", email: "info@ruralgrocer.net", status: "Active", products: 120, joined: "2022-11-01", rating: 4.5 },
            { id: 1004, name: "Quality Produce Inc.", email: "sales@qproduce.com", status: "Inactive", products: 7, joined: "2024-02-28", rating: 3.2 },
            { id: 1005, name: "Sunnyside Organics", email: "hello@sunnyside.org", status: "Active", products: 88, joined: "2023-07-20", rating: 4.9 },
        ];
        
        // --- Data Rendering ---
        const retailersTableBody = document.getElementById('retailersTableBody');
        const statTotalRetailers = document.getElementById('statTotalRetailers');
        const statPending = document.getElementById('statPending');
        const statAvgRating = document.getElementById('statAvgRating');
        const statHighVolume = document.getElementById('statHighVolume');

        let totalProductsListed = 0;
        let pendingCount = 0;
        let activeRatingSum = 0;
        let activeRetailerCount = 0;
        let highVolumeCount = 0;
        let rowsHtml = '';
        
        // --- Helper Function to create Star Rating HTML ---
        const createRatingStars = (rating) => {
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating - fullStars >= 0.25 && rating - fullStars < 0.75; // Use 0.25-0.75 range for half star
            const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
            
            let stars = '';
            for (let i = 0; i < fullStars; i++) {
                stars += '<i class="fa-solid fa-star text-yellow-400"></i>';
            }
            if (hasHalfStar) {
                stars += '<i class="fa-solid fa-star-half-stroke text-yellow-400"></i>';
            }
            for (let i = 0; i < emptyStars; i++) {
                stars += '<i class="fa-regular fa-star text-gray-300"></i>';
            }
            return `<span class="flex items-center text-xs">${stars} <span class="ml-1 text-gray-600">(${rating.toFixed(1)})</span></span>`;
        };


        mockRetailers.forEach(retailer => {
            totalProductsListed += retailer.products;

            let statusClass, statusText;
            switch (retailer.status) {
                case 'Active':
                    statusClass = 'bg-green-100 text-green-700';
                    statusText = 'Active';
                    activeRatingSum += retailer.rating;
                    activeRetailerCount++;
                    if (retailer.products >= 80) highVolumeCount++;
                    break;
                case 'Pending':
                    statusClass = 'bg-yellow-100 text-yellow-700';
                    statusText = 'Pending Review';
                    pendingCount++;
                    break;
                case 'Inactive':
                    statusClass = 'bg-red-100 text-red-700';
                    statusText = 'Inactive';
                    break;
                default:
                    statusClass = 'bg-gray-100 text-gray-700';
                    statusText = 'Unknown';
            }
            
            const formattedJoinedDate = new Date(retailer.joined).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });

            rowsHtml += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6 text-sm font-semibold text-gray-900">${retailer.id}</td>
                    <td class="py-3 px-6 text-sm font-medium text-gray-700">${retailer.name}</td>
                    <td class="py-3 px-6 text-sm text-gray-500">${retailer.email}</td>
                    <td class="py-3 px-6 text-sm">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium ${statusClass}">
                           ${statusText}
                        </span>
                        ${retailer.status === 'Active' ? createRatingStars(retailer.rating) : ''}
                    </td>
                    <td class="py-3 px-6 text-sm text-gray-700">${retailer.products.toLocaleString()}</td>
                    <td class="py-3 px-6 text-sm text-gray-500">${formattedJoinedDate}</td>
                    <td class="py-3 px-6 text-right">
                        <div class="flex justify-end gap-2">
                            <button data-action="view" data-id="${retailer.id}" data-name="${retailer.name}" class="action-btn text-gray-500 hover:text-green-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="View Profile">
                                <i class="fa-solid fa-eye text-base"></i>
                            </button>
                            <button data-action="edit" data-id="${retailer.id}" data-name="${retailer.name}" class="action-btn text-gray-500 hover:text-blue-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="Edit Details">
                                <i class="fa-solid fa-pen-to-square text-base"></i>
                            </button>
                            <button data-action="delete" data-id="${retailer.id}" data-name="${retailer.name}" class="action-btn text-gray-500 hover:text-red-600 p-2 rounded-full hover:bg-gray-100 transition-colors" title="Delete Retailer">
                                <i class="fa-solid fa-trash-alt text-base"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        // Inject HTML and Update Stats
        retailersTableBody.innerHTML = rowsHtml;
        statTotalRetailers.textContent = mockRetailers.length;
        statPending.textContent = pendingCount;
        statHighVolume.textContent = highVolumeCount;
        
        const avgRating = activeRetailerCount > 0 ? (activeRatingSum / activeRetailerCount).toFixed(2) : 'N/A';
        statAvgRating.textContent = avgRating;

        // --- Modal & Action Logic ---

        // Get modal elements
        const addRetailerModal = document.getElementById('addRetailerModal');
        const openAddRetailerModalBtn = document.getElementById('openAddRetailerModal');
        const closeAddRetailerModalBtn = document.getElementById('closeAddRetailerModal');
        const addRetailerForm = document.getElementById('addRetailerForm');

        // Show Modal function
        const showModal = (modalElement) => {
            modalElement.classList.remove('hidden');
            modalElement.classList.add('flex');
            // Timeout to trigger opacity transition
            setTimeout(() => {
                modalElement.classList.remove('opacity-0');
                modalElement.classList.add('opacity-100');
            }, 50);
        };

        // Hide Modal function
        const hideModal = (modalElement) => {
            modalElement.classList.remove('opacity-100');
            modalElement.classList.add('opacity-0');
            // Timeout to hide fully after transition
            setTimeout(() => {
                modalElement.classList.add('hidden');
                modalElement.classList.remove('flex');
            }, 300);
        };

        // Open Add Retailer Modal
        openAddRetailerModalBtn.addEventListener('click', () => showModal(addRetailerModal));

        // Close Add Retailer Modal
        closeAddRetailerModalBtn.addEventListener('click', () => hideModal(addRetailerModal));

        // Close Add Retailer Modal if clicked outside
        addRetailerModal.addEventListener('click', (e) => {
            if (e.target === addRetailerModal) {
                hideModal(addRetailerModal);
            }
        });

        // Handle Add Retailer Form Submission (Mock)
        addRetailerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('retailerName').value;
            const email = document.getElementById('retailerEmail').value;
            // In a real app, this data would be sent to a server.
            console.log(`Submitting new retailer: Name=${name}, Email=${email}`);
            
            // Clear form and hide modal
            addRetailerForm.reset();
            hideModal(addRetailerModal);
        });
        
        // Handle Table Actions (Mock)
        retailersTableBody.addEventListener('click', (e) => {
            const button = e.target.closest('.action-btn');
            if (!button) return;

            const action = button.dataset.action;
            const retailerId = button.dataset.id;
            const retailerName = button.dataset.name;
            
            // For a production app, these would trigger new modals or redirects.
            switch (action) {
                case 'view':
                    console.log(`Action: Redirect to view profile for Retailer ${retailerId} (${retailerName})`);
                    // alert(`Viewing profile for: ${retailerName}`);
                    break;
                case 'edit':
                    console.log(`Action: Open edit form for Retailer ${retailerId} (${retailerName})`);
                    // alert(`Editing details for: ${retailerName}`);
                    break;
                case 'delete':
                    // **IMPORTANT:** Use a custom confirmation modal in a real app, not alert/confirm
                    console.log(`Action: Trigger deletion for Retailer ${retailerId} (${retailerName})`);
                    // if (confirm(`Are you sure you want to delete ${retailerName}?`)) {
                    //     console.log('Delete Confirmed');
                    // }
                    break;
            }
        });


        // --- Logout Modal Logic (Reused) ---
        const logoutButton = document.getElementById('logoutButton');
        const logoutModal = document.getElementById('logoutModal');
        const cancelLogout = document.getElementById('cancelLogout');

        logoutButton.addEventListener('click', function() {
            showModal(logoutModal);
        });

        cancelLogout.addEventListener('click', function() {
            hideModal(logoutModal);
        });

        // Close modal if clicked outside
        logoutModal.addEventListener('click', function(e) {
            if (e.target === logoutModal) {
                hideModal(logoutModal);
            }
        });
    });
  </script>
</body>

</html>