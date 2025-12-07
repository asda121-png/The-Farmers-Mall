<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data and product ID
$user_id = $_SESSION['user_id'] ?? null;
$product_id = $_GET['id'] ?? null;

// Fetch profile picture from database
$profile_picture = '';
if ($user_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    $api = getSupabaseAPI();
    $users = $api->select('users', ['id' => $user_id]);
    if (!empty($users)) {
        $profile_picture = $users[0]['profile_picture'] ?? '';
    }
}

// Fetch product details
$product = null;
$retailer = null;
if ($product_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    $api = getSupabaseAPI();
    
    $products = $api->select('products', ['id' => $product_id]);
    if (!empty($products)) {
        $product = $products[0];
        
        // Fetch retailer information
        if (!empty($product['retailer_id'])) {
            $retailers = $api->select('retailers', ['id' => $product['retailer_id']]);
            if (!empty($retailers)) {
                $retailer = $retailers[0];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $product ? htmlspecialchars($product['name']) : 'Product Details'; ?> - Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>

<!-- Navbar -->
<header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <!-- Logo -->
        <a href="user-homepage.php" class="flex items-center gap-2">
            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                <i class="fas fa-leaf text-white text-lg"></i>
            </div>
            <span class="text-xl font-bold" style="color: #2E7D32;">Farmers Mall</span>
        </a>

        <!-- Search -->
        <div class="flex-1 mx-6">
            <form action="products.php" method="GET">
                <input 
                    type="text" 
                    name="search"
                    placeholder="Search for fresh produce, dairy, and more..."
                    class="w-full px-4 py-2 border rounded-full focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </form>
        </div>

        <!-- Icons & Profile Dropdown -->
        <div class="flex items-center space-x-6">
            <a href="user-homepage.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
            <a href="message.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
            <a href="notification.php" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
            <a href="cart.php" class="text-gray-600 relative">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="cartBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
            </a>

            <!-- Profile Dropdown -->
            <div class="relative inline-block text-left">
                <button id="profileDropdownBtn" class="flex items-center">
                    <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars('../' . $profile_picture); ?>" 
                             alt="Profile" 
                             class="w-8 h-8 rounded-full cursor-pointer object-cover">
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    <?php endif; ?>
                </button>

                <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                    <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                    <a href="profile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                    <a href="../auth/login.php" id="logoutLink" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Dropdown JS -->
<script>
    const btn = document.getElementById('profileDropdownBtn');
    const menu = document.getElementById('profileDropdown');

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        menu.classList.add('hidden');
    });
</script>

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-6 py-8 mb-20">
    <?php if (!$product): ?>
        <!-- Product Not Found -->
        <div class="text-center py-20">
            <i class="fas fa-box-open text-gray-300 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-600 mb-2">Product Not Found</h2>
            <p class="text-gray-500 mb-6">The product you're looking for doesn't exist or has been removed.</p>
            <a href="user-homepage.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-full hover:bg-green-700">
                Back to Home
            </a>
        </div>
    <?php else: ?>
        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center text-sm text-gray-600">
            <a href="user-homepage.php" class="hover:text-green-600">Home</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <?php if ($retailer): ?>
                <a href="shop-products.php?shop=<?php echo urlencode($retailer['shop_name']); ?>" class="hover:text-green-600">
                    <?php echo htmlspecialchars($retailer['shop_name']); ?>
                </a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <?php endif; ?>
            <span class="text-gray-800"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <!-- Product Details -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- Left Column - Product Images -->
            <div>
                <!-- Main Product Image -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                    <div class="relative">
                        <?php if (!empty($product['image_url'])): ?>
                            <img id="mainImage" src="<?php echo htmlspecialchars('../' . $product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-96 object-contain"
                                 onerror="this.src='https://via.placeholder.com/500x500?text=Product+Image'">
                        <?php else: ?>
                            <img id="mainImage" src="https://via.placeholder.com/500x500?text=Product+Image" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-96 object-contain">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Thumbnail Images -->
                <div class="flex gap-2 overflow-x-auto">
                    <?php 
                    $image_url = !empty($product['image_url']) ? '../' . $product['image_url'] : 'https://via.placeholder.com/100x100?text=1';
                    for ($i = 0; $i < 4; $i++): 
                    ?>
                        <div class="thumbnail-item flex-shrink-0 cursor-pointer border-2 border-gray-200 hover:border-green-600 rounded-lg overflow-hidden transition <?php echo $i === 0 ? 'border-green-600' : ''; ?>" 
                             onclick="changeMainImage('<?php echo htmlspecialchars($image_url); ?>', this)">
                            <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                 alt="Thumbnail <?php echo $i + 1; ?>" 
                                 class="w-16 h-16 object-cover"
                                 onerror="this.src='https://via.placeholder.com/100x100?text=<?php echo $i + 1; ?>'">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Right Column - Product Info -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <!-- Rating -->
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="text-sm text-gray-600">4.9 (1.2k reviews)</span>
                </div>

                <!-- Price -->
                <div class="mb-6">
                    <span class="text-4xl font-bold text-green-600">₱<?php echo number_format($product['price'], 2); ?></span>
                </div>

                <!-- Stock Status -->
                <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="flex items-center gap-2 text-green-600 mb-6">
                        <i class="fas fa-check-circle"></i>
                        <span class="font-medium">In Stock</span>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-2 text-red-600 mb-6">
                        <i class="fas fa-times-circle"></i>
                        <span class="font-medium">Out of Stock</span>
                    </div>
                <?php endif; ?>

                <!-- Quantity Selector -->
                <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity:</label>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center border-2 border-gray-300 rounded-lg">
                                <button onclick="decreaseQuantity()" class="px-4 py-2 hover:bg-gray-100 text-gray-600">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" 
                                       class="w-16 text-center py-2 focus:outline-none font-medium">
                                <button onclick="increaseQuantity()" class="px-4 py-2 hover:bg-gray-100 text-gray-600">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mb-6">
                        <button onclick="addToCart('<?php echo htmlspecialchars($product['id']); ?>')" 
                                class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-medium flex items-center justify-center">
                            <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                        </button>
                        <button class="w-12 h-12 border-2 border-gray-300 rounded-lg hover:border-green-600 hover:text-green-600 transition flex items-center justify-center">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                <?php else: ?>
                    <button class="w-full bg-gray-300 text-gray-600 px-6 py-3 rounded-lg cursor-not-allowed font-medium mb-6" disabled>
                        Out of Stock
                    </button>
                <?php endif; ?>

                <!-- Delivery Information -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3">Delivery Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-truck text-green-600"></i>
                            <span>Delivery within 2-4 business days</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-shipping-fast text-green-600"></i>
                            <span>Free shipping on orders over ₱20</span>
                        </div>
                    </div>
                </div>

                <!-- Key Features -->
                <div class="border-t pt-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Key Features</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-leaf text-green-600"></i>
                            <span>100% Organic and pesticide-free</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                            <span>Locally sourced from certified farms</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-clock text-green-600"></i>
                            <span>Harvested within 24 hours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Description Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-green-600 inline-block">Product Description</h2>
            <div class="mt-4">
                <?php if (!empty($product['description'])): ?>
                    <p class="text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <?php else: ?>
                    <p class="text-gray-600 leading-relaxed">A fresh assortment of seasonal vegetables including carrots, spinach, and broccoli, perfect for healthy meals.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- You Might Also Like Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">You Might Also Like</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php
                // Fetch related products from the same category or retailer
                $related_products = [];
                if ($product) {
                    $filters = [];
                    if (!empty($product['category'])) {
                        $filters['category'] = $product['category'];
                    } elseif (!empty($product['retailer_id'])) {
                        $filters['retailer_id'] = $product['retailer_id'];
                    }
                    $filters['status'] = 'active';
                    
                    if (!empty($filters)) {
                        $all_related = $api->select('products', $filters);
                        // Remove current product and limit to 4
                        $related_products = array_filter($all_related, function($p) use ($product_id) {
                            return $p['id'] != $product_id;
                        });
                        $related_products = array_slice($related_products, 0, 4);
                    }
                }

                if (empty($related_products)):
                    // Show placeholder products if no related products found
                    $placeholder_products = [
                        ['name' => 'Fresh Strawberries', 'price' => 89.99, 'image' => 'https://via.placeholder.com/200x200?text=Strawberries'],
                        ['name' => 'Organic Broccoli', 'price' => 45.50, 'image' => 'https://via.placeholder.com/200x200?text=Broccoli'],
                        ['name' => 'Free-Range Chicken', 'price' => 280.00, 'image' => 'https://via.placeholder.com/200x200?text=Chicken'],
                        ['name' => 'Farm Fresh Milk', 'price' => 95.00, 'image' => 'https://via.placeholder.com/200x200?text=Milk']
                    ];
                    foreach ($placeholder_products as $item):
                ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="w-full h-40 object-cover">
                        <div class="p-4 text-center">
                            <h3 class="font-medium text-gray-800 mb-2"><?php echo $item['name']; ?></h3>
                            <p class="text-green-600 font-bold">₱<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else:
                    foreach ($related_products as $related):
                ?>
                    <a href="product-details.php?id=<?php echo htmlspecialchars($related['id']); ?>" class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition block">
                        <?php if (!empty($related['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars('../' . $related['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                 class="w-full h-40 object-cover"
                                 onerror="this.src='https://via.placeholder.com/200x200?text=Product'">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/200x200?text=Product" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                 class="w-full h-40 object-cover">
                        <?php endif; ?>
                        <div class="p-4 text-center">
                            <h3 class="font-medium text-gray-800 mb-2 truncate"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <p class="text-green-600 font-bold">₱<?php echo number_format($related['price'], 2); ?></p>
                        </div>
                    </a>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Footer -->
<footer class="text-white py-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
        <div>
            <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>
            <p class="text-gray-300 text-sm">
                Fresh, organic produce delivered straight to your home from local farmers.
            </p>
        </div>
        
        <div>
            <h3 class="font-bold text-lg mb-3">Quick Links</h3>
            <ul class="space-y-2 text-sm text-gray-300">
                <li><a href="../public/about.php" class="hover:underline">About Us</a></li>
                <li><a href="#" class="hover:underline">Contact</a></li>
                <li><a href="#" class="hover:underline">FAQ</a></li>
                <li><a href="../public/support.php" class="hover:underline">Support</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-bold text-lg mb-3">Categories</h3>
            <ul class="space-y-2 text-sm text-gray-300">
                <li><a href="products.php?category=Vegetables" class="hover:underline">Vegetables</a></li>
                <li><a href="products.php?category=Fruits" class="hover:underline">Fruits</a></li>
                <li><a href="products.php?category=Dairy" class="hover:underline">Dairy</a></li>
                <li><a href="products.php?category=Meat" class="hover:underline">Meat</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-bold text-lg mb-3">Follow Us</h3>
            <div class="flex space-x-4 text-xl">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
        © 2025 Farmers Mall. All rights reserved.
    </div>
</footer>

<!-- Logout Modal -->
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
        <div class="text-red-500 text-4xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 class="font-semibold text-lg mb-2">Confirm Logout</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out?</p>
        <div class="flex justify-center gap-4">
            <button id="cancelLogout" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
            <a href="../auth/logout.php" class="px-6 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Logout</a>
        </div>
    </div>
</div>

<script>
    // Logout Modal Logic
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');

    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            e.preventDefault();
            logoutModal.classList.remove('hidden');
        });
    }

    if (cancelLogout) {
        cancelLogout.addEventListener('click', () => {
            logoutModal.classList.add('hidden');
        });
    }

    // Change main image when clicking thumbnails
    function changeMainImage(imageSrc, element) {
        document.getElementById('mainImage').src = imageSrc;
        
        // Update active thumbnail border
        document.querySelectorAll('.thumbnail-item').forEach(thumb => {
            thumb.classList.remove('border-green-600');
            thumb.classList.add('border-gray-200');
        });
        element.classList.add('border-green-600');
        element.classList.remove('border-gray-200');
    }

    // Quantity controls
    const maxQuantity = <?php echo $product ? $product['stock_quantity'] : 1; ?>;

    function increaseQuantity() {
        const input = document.getElementById('quantity');
        const currentValue = parseInt(input.value);
        if (currentValue < maxQuantity) {
            input.value = currentValue + 1;
        }
    }

    function decreaseQuantity() {
        const input = document.getElementById('quantity');
        const currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 1;
        }
    }

    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        } text-white`;
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} text-xl"></i>
            <span>${message}</span>
        `;
        
        document.getElementById('toastContainer').appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Update cart badge
    function updateCartBadge() {
        fetch('../api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'count' })
        })
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('cartBadge');
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        })
        .catch(error => console.error('Error updating cart badge:', error));
    }

    // Add to cart function
    function addToCart(productId) {
        const quantityInput = document.getElementById('quantity');
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
        
        fetch('../api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`Added ${quantity} item(s) to cart successfully!`, 'success');
                updateCartBadge();
            } else {
                showToast(data.message || 'Failed to add product to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while adding to cart', 'error');
        });
    }

    // Initialize cart badge on page load
    updateCartBadge();
</script>

</body>
</html>
