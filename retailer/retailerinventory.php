<?php
session_start();

// Check if user is logged in and is a retailer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'retailer' && $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Load database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase-api.php';

// Get user data from database
$api = getSupabaseAPI();
$userId = $_SESSION['user_id'];
$userData = null;
$profilePicture = '../images/default-avatar.svg';
$userFullName = $_SESSION['full_name'] ?? 'Retailer';
$userEmail = $_SESSION['email'] ?? '';
$shopName = 'My Shop';

try {
    $users = $api->select('users', ['id' => $userId]);
    if (!empty($users)) {
        $userData = $users[0];
        $userFullName = $userData['full_name'] ?? $userFullName;
        $userEmail = $userData['email'] ?? $userEmail;
        
        if (!empty($userData['profile_picture'])) {
            $profilePath = '../' . ltrim($userData['profile_picture'], '/');
            if (file_exists($profilePath)) {
                $profilePicture = $profilePath;
            }
        }
        
        if ($userData['user_type'] === 'retailer') {
            $retailers = $api->select('retailers', ['user_id' => $userId]);
            if (!empty($retailers)) {
                $shopName = $retailers[0]['shop_name'] ?? $shopName;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products & Inventory – The Farmer's Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fbf8;
        }
        #content {
            min-height: calc(100vh - 200px);
        }
        footer {
            margin-top: auto;
        }
        /* Mobile menu toggle */
        #mobileMenuBtn {
            display: none;
        }
        @media (max-width: 768px) {
            #mobileMenuBtn {
                display: flex;
            }
            #sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
            #sidebar.active {
                left: 0;
            }
            #overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            #overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="flex flex-col min-h-screen">
    <!-- Mobile Menu Overlay -->
    <div id="overlay" onclick="toggleMobileMenu()"></div>
    
    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="fixed top-4 left-4 z-50 bg-green-600 text-white p-3 rounded-lg shadow-lg md:hidden" onclick="toggleMobileMenu()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    
    <div id="app" class="flex flex-1">
        
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="w-64 md:w-64 bg-white shadow-xl flex flex-col p-4 space-y-2 flex-shrink-0">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-2">
                    <i class="fas fa-leaf text-white text-lg"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
            </div>
            <a href="retailer-dashboard2.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0V9m0 2v2m-4-2h1m-1 0h-2m2 0v2m-2-2h-1m-1 0H5m-2 4h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5h6"></path></svg>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M9 14h6m-5 4h4m-4-8h4m-5-8h6a2 2 0 012 2v10a2 2 0 01-2 2h-6a2 2 0 01-2-2V6a2 2 0 012-2z"></path></svg>
                Financial Reports
            </a>
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l4-4-4-4m0 16l4-4-4-4m-1-5a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Vouchers & Coupons
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.193a2.003 2.003 0 013.902 0l1.018 2.062 2.277.33a2.003 2.003 0 011.11 3.407l-1.652 1.61.39 2.269a2.003 2.003 0 01-2.906 2.108L12 15.698l-2.035 1.071a2.003 2.003 0 01-2.906-2.108l.39-2.269-1.652-1.61a2.003 2.003 0 011.11-3.407l2.277-.33 1.018-2.062z"></path></svg>
                Reviews & Customers
            </a>

            <div class="mt-auto pt-4 border-t border-gray-100">
                <a href="../auth/logout.php" class="w-full flex items-center justify-center p-2 rounded-xl text-red-600 bg-red-50 hover:bg-red-100 transition duration-150 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
                        <a href="retailermessage.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
                        <a href="retailernotifications.php" class="text-gray-600 relative">
                        <i class="fa-regular fa-bell"></i>
                        </a>

                        <div class="relative inline-block text-left">
                            <button id="profileDropdownBtn" class="flex items-center" title="<?php echo htmlspecialchars($userFullName); ?>">
                                <img id="headerProfilePic" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                                <a href="retailerprofile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                                <a href="retailerprofile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                                <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        
            <main id="content" class="p-8 transition-all duration-300 flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Product & Inventory Management</h2>
                
                <div class="flex justify-between items-center mb-6">
                    <button onclick="openProductModal()" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 transition duration-150 font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add New Product
                    </button>
                    <button onclick="openBulkPriceModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 transition duration-150 text-sm">
                        Bulk Edit Prices
                    </button>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price / UOM</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-list" class="bg-white divide-y divide-gray-200">
                            <!-- Sample Product Rows -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-lg object-cover mr-4" src="https://placehold.co/40x40/22c55e/fff?text=A" alt="Organic Apples" />
                                        <span class="text-sm font-medium text-gray-900">Organic Apples</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Fruits</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱89.00 / kg</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">45</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button onclick="editProduct('apple1')" class="text-green-600 hover:text-green-900">Edit</button>
                                    <button onclick="deleteProduct('apple1')" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-lg object-cover mr-4" src="https://placehold.co/40x40/f59e0b/fff?text=T" alt="Organic Tomatoes" />
                                        <span class="text-sm font-medium text-gray-900">Organic Tomatoes</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Vegetables</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱65.00 / kg</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">8</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button onclick="editProduct('tomato1')" class="text-green-600 hover:text-green-900">Edit</button>
                                    <button onclick="deleteProduct('tomato1')" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-lg object-cover mr-4" src="https://placehold.co/40x40/3b82f6/fff?text=M" alt="Fresh Milk" />
                                        <span class="text-sm font-medium text-gray-900">Fresh Milk</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Dairy</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱120.00 / liter</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">22</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button onclick="editProduct('milk1')" class="text-green-600 hover:text-green-900">Edit</button>
                                    <button onclick="deleteProduct('milk1')" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div id="no-products-message" class="p-6 text-center text-gray-500 hidden">No products listed yet. Click "Add New Product" to start selling!</div>
                </div>
            </main>
        </div>
    </div>
    
    
    <footer id="support" class="text-white py-12 mt-auto" style="background-color: #1B5E20;">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
          <div>
            <h3 class="font-bold text-lg mb-3">The Farmer's Mall</h3>
            <p class="text-gray-300 text-sm">Fresh, organic produce delivered straight to your home from local farmers.</p>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-3">Quick Links</h3>
            <ul class="space-y-2 text-sm text-gray-300">
              <li><a href="#" class="hover:underline">About Us</a></li>
              <li><a href="#" class="hover:underline">Contact</a></li>
              <li><a href="#" class="hover:underline">FAQ</a></li>
              <li><a href="#" class="hover:underline">Support</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-3">Categories</h3>
            <ul class="space-y-2 text-sm text-gray-300">
              <li><a href="#" class="hover:underline">Vegetables</a></li>
              <li><a href="#" class="hover:underline">Fruits</a></li>
              <li><a href="#" class="hover:underline">Dairy</a></li>
              <li><a href="#" class="hover:underline">Meat</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-3">Follow Us</h3>
            <div class="flex space-x-4 text-xl">
              <a href="#" class="hover:text-green-300"><i class="fab fa-facebook"></i></a>
              <a href="#" class="hover:text-green-300"><i class="fab fa-twitter"></i></a>
              <a href="#" class="hover:text-green-300"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
        </div>
        <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
          © 2025 The Farmer's Mall. All rights reserved.
        </div>
    </footer>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Delete Product</h3>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <p class="text-gray-600 mb-2">Are you sure you want to delete this product?</p>
            <p class="text-sm text-gray-500">This action cannot be undone. The product will be permanently removed from your inventory.</p>
        </div>
        
        <div class="p-6 border-t bg-gray-50 flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
            </button>
            <button onclick="confirmDelete()" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete Product
            </button>
        </div>
    </div>
</div>

<script>
    const btn = document.getElementById('profileDropdownBtn');
    const menu = document.getElementById('profileDropdown');

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        if (!menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
        }
    });

    let lastProfilePicture = '<?php echo htmlspecialchars($profilePicture); ?>';
    
    async function checkProfileUpdates() {
        try {
            const response = await fetch('../api/get-profile.php');
            const result = await response.json();
            
            if (result.success && result.data) {
                const profilePicElement = document.getElementById('headerProfilePic');
                if (result.data.profile_picture && result.data.profile_picture !== lastProfilePicture) {
                    profilePicElement.src = result.data.profile_picture + '?t=' + new Date().getTime();
                    lastProfilePicture = result.data.profile_picture;
                }
                if (result.data.full_name) {
                    profilePicElement.parentElement.setAttribute('title', result.data.full_name);
                }
            }
        } catch (error) {
            console.error('Error checking profile updates:', error);
        }
    }
    
    setInterval(checkProfileUpdates, 5000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            checkProfileUpdates();
        }
    });
    window.addEventListener('load', () => {
        setTimeout(checkProfileUpdates, 1000);
    });

    // Product Modal Functions
    let editingProductId = null;

    function openProductModal(product = null) {
        const modal = document.getElementById('productModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('productForm');
        
        if (product) {
            modalTitle.textContent = 'Edit Product';
            editingProductId = product.id;
            document.getElementById('productName').value = product.name || '';
            document.getElementById('productCategory').value = product.category || '';
            document.getElementById('productPrice').value = product.price || '';
            document.getElementById('productUnit').value = product.unit || '';
            document.getElementById('productStock').value = product.stock || '';
        } else {
            modalTitle.textContent = 'Add New Product';
            editingProductId = null;
            form.reset();
        }
        
        modal.classList.remove('hidden');
    }

    function closeProductModal() {
        document.getElementById('productModal').classList.add('hidden');
        editingProductId = null;
    }

    function saveProduct(event) {
        event.preventDefault();
        const formData = {
            name: document.getElementById('productName').value,
            category: document.getElementById('productCategory').value,
            price: document.getElementById('productPrice').value,
            unit: document.getElementById('productUnit').value,
            stock: document.getElementById('productStock').value
        };
        
        console.log('Saving product:', formData);
        alert(editingProductId ? 'Product updated successfully!' : 'Product added successfully!');
        closeProductModal();
        // Integrate with your database here
    }

    // Bulk Price Modal Functions
    function openBulkPriceModal() {
        document.getElementById('bulkPriceModal').classList.remove('hidden');
    }

    function closeBulkPriceModal() {
        document.getElementById('bulkPriceModal').classList.add('hidden');
    }

    function applyBulkPrice(event) {
        event.preventDefault();
        const percentage = document.getElementById('pricePercentage').value;
        const action = document.getElementById('priceAction').value;
        
        console.log(`Applying ${action} of ${percentage}% to all products`);
        alert(`Bulk price ${action} of ${percentage}% applied successfully!`);
        closeBulkPriceModal();
        // Integrate with your database here
    }

    // Delete Product Function
    let productToDelete = null;
    
    function deleteProduct(productId) {
        productToDelete = productId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        productToDelete = null;
    }
    
    function confirmDelete() {
        if (productToDelete) {
            console.log('Deleting product:', productToDelete);
            // TODO: Make API call to delete product
            // fetch('../api/delete-product.php', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ productId: productToDelete })
            // }).then(response => response.json())
            //   .then(data => {
            //       if (data.success) {
            //           location.reload();
            //       }
            //   });
            
            alert('Product deleted successfully! Database integration pending.');
            closeDeleteModal();
            // Uncomment when API is ready:
            // location.reload();
        }
    }

    // Edit Product Function (sample data)
    function editProduct(productId) {
        const sampleProduct = {
            id: productId,
            name: 'Organic Apples',
            category: 'Fruits',
            price: 89.00,
            unit: 'kg',
            stock: 45
        };
        openProductModal(sampleProduct);
    }
</script>

<!-- Product Add/Edit Modal -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 id="modalTitle" class="font-semibold text-lg mb-4">Add New Product</h3>
        <form id="productForm" class="space-y-4" onsubmit="saveProduct(event)">
            <div>
                <label for="productName" class="block text-sm font-medium text-gray-700">Product Name</label>
                <input type="text" id="productName" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <div>
                <label for="productCategory" class="block text-sm font-medium text-gray-700">Category</label>
                <select id="productCategory" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none bg-white">
                    <option>Vegetables</option>
                    <option>Fruits</option>
                    <option>Dairy</option>
                    <option>Meat</option>
                    <option>Seafood</option>
                    <option>Bakery</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="productPrice" class="block text-sm font-medium text-gray-700">Price (per unit)</label>
                    <input type="number" id="productPrice" step="0.01" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div>
                    <label for="productUnit" class="block text-sm font-medium text-gray-700">Unit (e.g., kg)</label>
                    <input type="text" id="productUnit" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
            </div>
            <div>
                <label for="productStock" class="block text-sm font-medium text-gray-700">Stock</label>
                <input type="number" id="productStock" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeProductModal()" class="px-4 py-2 border rounded-md text-sm">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Price Modal -->
<div id="bulkPriceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 class="font-semibold text-lg mb-4">Bulk Edit Prices</h3>
        <form onsubmit="applyBulkPrice(event)" class="space-y-4">
            <div>
                <label for="priceAction" class="block text-sm font-medium text-gray-700">Action</label>
                <select id="priceAction" class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none bg-white">
                    <option value="increase">Increase prices by</option>
                    <option value="decrease">Decrease prices by</option>
                </select>
            </div>
            <div>
                <label for="pricePercentage" class="block text-sm font-medium text-gray-700">Percentage (%)</label>
                <input type="number" id="pricePercentage" min="0" max="100" step="0.1" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <div class="p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                <p class="text-sm text-yellow-700">This will apply to all active products in your inventory.</p>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeBulkPriceModal()" class="px-4 py-2 border rounded-md text-sm">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Apply Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

// Close mobile menu when clicking a nav link
document.querySelectorAll('#sidebar a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth < 768) {
            toggleMobileMenu();
        }
    });
});
</script>

</body>
</html>