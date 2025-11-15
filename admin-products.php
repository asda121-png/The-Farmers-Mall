<?php
// =============================================================================
// WARNING: Database connection logic is highly sensitive.
// Using 'root' with no password, as requested, is extremely insecure and is 
// only suitable for local development/testing. For production, use a dedicated 
// user with strong credentials and proper security measures.
// =============================================================================

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farmers";

// Attempt to connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

$products = [];
$db_error = null;
$totalProducts = 0;
$topProductsCount = 0;
$uniqueCategoryCount = 0;
// Simulated data for columns not present in the DB, or for display/mockup
$simulatedRetailers = ["Green Farm Inc.", "Harvest Hub", "Rural Grocer", "Quality Produce"]; 

if ($conn->connect_error) {
    // Store error message to display in the HTML
    $db_error = "Connection failed: " . $conn->connect_error;
} else {
    // Query all necessary fields from the products table
    $sql = "SELECT product_id, name, price, image_path, category, is_top_product FROM products ORDER BY product_id DESC";
    $result = $conn->query($sql);

    if ($result) {
        $uniqueCategories = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
                // Calculate stats while iterating
                if ($row['is_top_product'] == 1) {
                    $topProductsCount++;
                }
                $uniqueCategories[] = $row['category'];
            }
            $uniqueCategoryCount = count(array_unique($uniqueCategories));
        }
        $totalProducts = count($products);
    } else {
        $db_error = "Query failed: " . $conn->error;
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Products – The Farmer's Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Custom styles for consistency */
    .badge-top {
      background-color: #fef9c3;
      color: #eab308;
    }
    /* Ensure action modal is correctly positioned and visible */
    #actionModal, #logoutModal {
        z-index: 1000; /* High z-index to overlay all content */
    }
  </style>
</head>

<body class="bg-gray-100 text-gray-800 flex font-sans">

  <!-- Sidebar -->
  <aside class="bg-green-800 text-white w-64 min-h-screen p-4 flex flex-col justify-between">
    <div>
      <div class="text-center mb-10">
        <h1 class="text-2xl font-bold">The Farmer's Mall</h1>
        <p class="text-sm text-green-200">Admin Panel</p>
      </div>
      <nav class="space-y-2">
        <a href="admin-dashboard.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
          <span>Dashboard</span>
        </a>
        <a href="admin_users.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-users w-5"></i>
          <span>Users</span>
        </a>
        <a href="admin-retailers.html" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-store w-5"></i>
          <span>Retailers</span>
        </a>
        <a href="admin-products.php" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-green-700 shadow-md">
          <i class="fa-solid fa-box w-5"></i>
          <span>Products</span>
        </a>
        <a href="admin-orders.html" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
        <a href="admin-settings.html" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
      </nav>
    </div>
    <div>
      <button id="logoutButton" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
        <i class="fa-solid fa-sign-out-alt w-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1">
    <!-- Header -->
    <header class="bg-white shadow-md p-4 flex justify-between items-center sticky top-0 z-10">
      <h2 class="text-2xl font-bold text-green-800">Manage Products</h2>
      <div class="flex items-center gap-4">
        <a href="admin-notification.html" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
          <i class="fa-regular fa-bell text-xl text-gray-600 cursor-pointer"></i>
        </a>
        <div class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <img src="https://placehold.co/40x40/4c7c50/ffffff?text=AD" class="w-10 h-10 rounded-full border-2 border-green-500" alt="Admin">
          <div>
            <p class="text-sm font-medium">Admin User</p>
            <p class="text-xs text-gray-500">admin@farmersmall.com</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="p-6 space-y-8">
      
      <!-- Product Statistics -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-xl shadow-lg flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Products</p>
            <p class="text-3xl font-bold text-green-800"><?php echo $totalProducts; ?></p>
          </div>
          <i class="fa-solid fa-box-open text-4xl text-green-200"></i>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Top Products</p>
            <p class="text-3xl font-bold text-yellow-600"><?php echo $topProductsCount; ?></p>
          </div>
          <i class="fa-solid fa-star text-4xl text-green-200"></i>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Unique Categories</p>
            <p class="text-3xl font-bold text-blue-600"><?php echo $uniqueCategoryCount; ?></p>
          </div>
          <i class="fa-solid fa-tags text-4xl text-green-200"></i>
        </div>
      </div>
      
      <!-- Product Table -->
      <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
        <?php if ($db_error): ?>
            <div class="p-8 text-center text-red-600 bg-red-50 border-t-4 border-red-500 rounded-b shadow-md">
                <p class="font-bold">Database Error:</p>
                <p><?php echo $db_error; ?></p>
            </div>
        <?php elseif (empty($products)): ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fa-solid fa-info-circle text-2xl mb-2"></i>
                <p>No products found in the 'products' table.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left border-b border-gray-200">
                <tr>
                <th class="p-4 font-semibold text-gray-600">Product</th>
                <th class="p-4 font-semibold text-gray-600">Retailer (Mock)</th>
                <th class="p-4 font-semibold text-gray-600">Category</th>
                <th class="p-4 font-semibold text-gray-600">Price</th>
                <th class="p-4 font-semibold text-gray-600">Stock (Mock)</th>
                <th class="p-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody id="productsTableBody" class="divide-y divide-gray-100">
                <?php foreach ($products as $index => $product): 
                    // Use modulo to cycle through simulated retailers
                    $retailerName = $simulatedRetailers[$index % count($simulatedRetailers)];
                    // Mock Stock level (e.g., 50 + last digit of ID)
                    $mockStock = 50 + ($product['product_id'] % 10);
                    $productName = htmlspecialchars($product['name']);
                    $productID = $product['product_id'];
                    $isTopProduct = $product['is_top_product'] == 1;
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-4 flex items-center gap-3">
                        <img src="<?php echo htmlspecialchars($product['image_path'] ?? 'https://placehold.co/40x40/9ca3af/ffffff?text=NoImg'); ?>" 
                             onerror="this.onerror=null; this.src='https://placehold.co/40x40/9ca3af/ffffff?text=NoImg';"
                             class="w-10 h-10 object-cover rounded-md border" alt="<?php echo $productName; ?>">
                        <span class="font-medium"><?php echo $productName; ?></span>
                        <?php if ($isTopProduct): ?>
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full badge-top ml-2">Top</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-gray-600"><?php echo htmlspecialchars($retailerName); ?></td>
                    <td class="p-4 text-gray-600"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></td>
                    <td class="p-4 font-semibold text-green-700">₱<?php echo number_format($product['price'], 2); ?></td>
                    <td class="p-4 text-gray-600"><?php echo $mockStock; ?></td>
                    <td class="p-4">
                        <div class="flex gap-2">
                            <button data-action="view" data-id="<?php echo $productID; ?>" data-name="<?php echo $productName; ?>" class="action-btn text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-100 transition-colors" title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button data-action="edit" data-id="<?php echo $productID; ?>" data-name="<?php echo $productName; ?>" class="action-btn text-yellow-600 hover:text-yellow-800 p-1 rounded-full hover:bg-yellow-100 transition-colors" title="Edit Product">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button data-action="delete" data-id="<?php echo $productID; ?>" data-name="<?php echo $productName; ?>" class="action-btn text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition-colors" title="Delete Product">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        <?php endif; ?>
      </div>
      
      <!-- Product Action Modal -->
      <!-- Added 'flex' to ensure centering -->
      <div id="actionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md transform scale-95 transition-transform duration-300">
          <h3 id="modalTitle" class="font-bold text-xl mb-4 text-green-800">Product Action</h3>
          <p id="modalMessage" class="text-gray-600 mb-6"></p>
          <div class="flex justify-end gap-3">
            <button id="cancelAction" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
            <button id="confirmAction" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">Confirm</button>
          </div>
        </div>
      </div>
      
    </main>
  </div>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-sm text-center transform scale-95 transition-transform duration-300">
      <div class="text-red-500 text-5xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-bold text-2xl mb-2 text-gray-800">Confirm Logout</h3>
      <p class="text-gray-600 text-sm mb-8">Are you sure you want to log out of the admin panel?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors shadow-sm">Cancel</button>
        <a href="login.html" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors shadow-md">Logout</a>
      </div>
    </div>
  </div>

  <script>
    // --- MODAL LOGIC (Logout and Action) ---

    // Generic Modal Handler
    function setupModal(triggerId, modalId, cancelId) {
      const trigger = document.getElementById(triggerId);
      const modal = document.getElementById(modalId);
      const cancel = document.getElementById(cancelId);

      // Show modal
      trigger.addEventListener('click', () => {
        modal.classList.remove('hidden');
        // Add a slight delay for transition effect
        setTimeout(() => {
          modal.classList.add('opacity-100');
          modal.querySelector(':first-child').classList.remove('scale-95');
        }, 10);
      });

      // Hide modal
      const hideModal = () => {
        modal.classList.remove('opacity-100');
        modal.querySelector(':first-child').classList.add('scale-95');
        setTimeout(() => {
          modal.classList.add('hidden');
        }, 300); // Match CSS transition duration
      };

      cancel.addEventListener('click', hideModal);

      // Close if clicking outside the modal content
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          hideModal();
        }
      });

      return hideModal;
    }

    // Setup Logout Modal
    setupModal('logoutButton', 'logoutModal', 'cancelLogout');

    // Setup Product Action Modal (for View/Edit/Delete)
    const hideActionModal = (() => {
      const modal = document.getElementById('actionModal');
      const cancel = document.getElementById('cancelAction');
      
      const hide = () => {
        modal.classList.remove('opacity-100');
        modal.querySelector(':first-child').classList.add('scale-95');
        setTimeout(() => {
          modal.classList.add('hidden');
        }, 300);
      };

      cancel.addEventListener('click', hide);
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          hide();
        }
      });
      return hide;
    })();


    // Handle Action Buttons (View, Edit, Delete)
    document.getElementById('productsTableBody').addEventListener('click', (e) => {
      const button = e.target.closest('.action-btn');
      if (!button) return;

      const action = button.dataset.action;
      const productId = button.dataset.id;
      const productName = button.dataset.name;
      
      const modal = document.getElementById('actionModal');
      const modalTitle = document.getElementById('modalTitle');
      const modalMessage = document.getElementById('modalMessage');
      const confirmButton = document.getElementById('confirmAction');

      // Show modal
      modal.classList.remove('hidden');
      setTimeout(() => {
          modal.classList.add('opacity-100');
          modal.querySelector(':first-child').classList.remove('scale-95');
      }, 10);

      switch (action) {
        case 'view':
          modalTitle.textContent = `View Product: ${productName}`;
          modalMessage.innerHTML = `<p>Displaying full details for Product ID <strong>${productId}</strong>.</p><p class="text-xs mt-2">This is where product description, history, and image preview would load.</p>`;
          confirmButton.textContent = 'Close';
          confirmButton.classList.remove('bg-red-600', 'hover:bg-red-700', 'bg-yellow-600', 'hover:bg-yellow-700');
          confirmButton.classList.add('bg-green-600', 'hover:bg-green-700');
          confirmButton.onclick = hideActionModal;
          break;
        case 'edit':
          modalTitle.textContent = `Edit Product: ${productName}`;
          modalMessage.innerHTML = `<p>Preparing to edit details for Product ID <strong>${productId}</strong>.</p><p class="text-xs mt-2">This would redirect to an edit form or display an inline form.</p>`;
          confirmButton.textContent = 'Go to Edit Page';
          confirmButton.classList.remove('bg-red-600', 'hover:bg-red-700', 'bg-green-600', 'hover:bg-green-700');
          confirmButton.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
          confirmButton.onclick = () => {
             console.log(`Redirecting to edit page for product ${productId}`);
             hideActionModal();
          };
          break;
        case 'delete':
          modalTitle.textContent = `Delete Product: ${productName}`;
          modalMessage.innerHTML = `<p class="font-bold text-red-600">WARNING: Are you sure you want to permanently delete Product ID <strong>${productId}</strong>?</p><p class="text-sm mt-2">This action should trigger a PHP script to delete the record.</p>`;
          confirmButton.textContent = 'Delete Permanently';
          confirmButton.classList.remove('bg-green-600', 'hover:bg-green-700', 'bg-yellow-600', 'hover:bg-yellow-700');
          confirmButton.classList.add('bg-red-600', 'hover:bg-red-700');
          confirmButton.onclick = () => {
             // **REAL IMPLEMENTATION NOTE:** Send a fetch/AJAX request to a delete_product.php endpoint
             console.log(`Deleting product ${productId}...`);
             // Reload page to see changes (in a real app)
             hideActionModal();
          };
          break;
      }
    });

  </script>
</body>
</html>