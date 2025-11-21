<?php
// Start session to check if user is logged in
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username'] ?? 'User') : null;
$userRole = $isLoggedIn ? htmlspecialchars($_SESSION['role'] ?? 'user') : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>The Farmer's Mall - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        .hero-background {
            background-image: url('images/img.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <!-- Navbar -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold" style="color: #2E7D32;">The Farmer's Mall</h1>

            <div class="flex-1 mx-6">
                <form action="products.php" method="GET">
                    <input 
                        type="text" 
                        name="search"
                        placeholder="Search for fresh product..."
                        class="w-full px-4 py-2 border rounded-full focus:ring-2 focus:ring-green-500 focus:outline-none"
                    />
                </form>
            </div>

            <!-- Icons and Profile -->
            <div class="flex items-center space-x-6">
                <a href="message.html" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
                <a href="notification.html" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
                <a href="cart.html" class="text-gray-600"><i class="fa-solid fa-cart-shopping"></i></a>
                <?php if ($isLoggedIn): ?>
                    <!-- Logged In User Profile Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 cursor-pointer hover:opacity-80 transition-opacity">
                            <img src="images/karl.png" alt="User" class="w-8 h-8 rounded-full" onerror="this.onerror=null; this.src='https://placehold.co/32x32/333333/ffffff?text=U'">
                            <span class="text-sm font-medium text-gray-700"><?= $username ?></span>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <a href="profile.html" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-t-lg"><i class="fas fa-user mr-2"></i>My Profile</a>
                            <a href="orders.php" class="block px-4 py-2 text-gray-700 hover:bg-green-50"><i class="fas fa-shopping-bag mr-2"></i>My Orders</a>
                            <a href="account.html" class="block px-4 py-2 text-gray-700 hover:bg-green-50"><i class="fas fa-cog mr-2"></i>Account Settings</a>
                            <hr class="my-2">
                            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Not Logged In - Show Login Link -->
                    <a href="profile.html">
                        <img src="images/karl.png" alt="User" class="w-8 h-8 rounded-full cursor-pointer hover:opacity-80 transition-opacity" onerror="this.onerror=null; this.src='https://placehold.co/32x32/333333/ffffff?text=U'">
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <!-- Personalized Welcome Section for Logged-In Users -->
        <?php if ($isLoggedIn): ?>
        <section class="bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome back, <span style="color: #2E7D32;"><?= $username ?></span>! 👋</h2>
                        <p class="text-gray-600">Continue shopping fresh produce from your favorite farmers</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 mb-2">Account Status:</p>
                        <span class="inline-block bg-green-600 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>Active Member
                        </span>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Hero -->
        <section class="relative hero-background">
            <div class="bg-black bg-opacity-40">
                <div class="max-w-7xl mx-auto px-6 py-32 text-left text-white">
                    <h2 class="text-4xl md:text-5xl font-extrabold mb-4">Fresh Harvest Sale</h2>
                    <p class="text-lg md:text-xl mb-6">Up to 30% off on organic produce</p>
                <a href="products.php" class="inline-block bg-green-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition">
                        Shop Now
                    </a>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section class="max-w-7xl mx-auto px-6 py-10">
            <h2 class="text-xl font-bold text-center mb-6">Shop by Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                
                <a href="products.php?category=vegetables"
                    class="flex flex-col items-center bg-white p-4 rounded-xl shadow hover:shadow-md cursor-pointer">
                    <i class="fa-solid fa-carrot text-green-600 text-3xl mb-2"></i>
                    <p class="text-gray-700 font-medium">Vegetables</p>
                </a>
                
                <a href="products.php?category=fruits" class="flex flex-col items-center bg-white p-4 rounded-xl shadow hover:shadow-md cursor-pointer">
                    <i class="fa-solid fa-apple-whole text-green-600 text-2xl"></i>
                    <p class="mt-2">Fruits</p>
                </a>
                
                <a href="products.php?category=meat" class="flex flex-col items-center bg-white p-4 rounded-xl shadow hover:shadow-md cursor-pointer">
                    <i class="fa-solid fa-drumstick-bite text-green-600 text-2xl"></i>
                    <p class="mt-2">Meat</p>
                </a>
                
                <a href="products.php?category=seafood" class="flex flex-col items-center bg-white p-4 rounded-xl shadow hover:shadow-md cursor-pointer">
                    <i class="fa-solid fa-fish text-green-600 text-2xl"></i>
                    <p class="mt-2">Seafood</p>
                </a>
                
                <a href="products.php?category=dairy" class="flex flex-col items-center bg-white p-4 rounded-xl shadow hover:shadow-md cursor-pointer">
                    <i class="fa-solid fa-cheese text-green-600 text-2xl"></i>
                    <p class="mt-2">Dairy</p>
                </a>
                
                <a href="products.php?category=bakery" class="flex flex-col items-center bg-white p-4 rounded-xl shadow hover:shadow-md cursor-pointer">
                    <i class="fa-solid fa-bread-slice text-green-600 text-2xl"></i>
                    <p class="mt-2">Bakery</p>
                </a>
                
            </div>
        </section>


        <!-- Top Products (Dynamically rendered by PHP) -->
        <section class="max-w-7xl mx-auto px-6 pb-10">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Top Products</h2>
                <a href="products.php" class="text-green-600 hover:underline"><i class="fa-solid fa-arrow-right"></i> View All</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                <!-- Static Product Card 1 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img1.png" alt="Fresh Lettuce" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Fresh Lettuce</h3>
                  <p class="text-green-600 font-bold text-sm">₱50.00</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
                <!-- Static Product Card 2 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img2.png" alt="Organic Carrots" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Organic Carrots</h3>
                  <p class="text-green-600 font-bold text-sm">₱80.00 per kg</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
                <!-- Static Product Card 3 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img3.png" alt="Tomatoes" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Tomatoes</h3>
                  <p class="text-green-600 font-bold text-sm">₱60.00 per kg</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
                <!-- Static Product Card 4 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img4.png" alt="Broccoli" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Broccoli</h3>
                  <p class="text-green-600 font-bold text-sm">₱120.00</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
                <!-- Static Product Card 5 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img5.png" alt="Fresh Eggs" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Fresh Eggs</h3>
                  <p class="text-green-600 font-bold text-sm">₱90.00 per dozen</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
            </div>
        </section>

        <!-- Other Products (Dynamically rendered by PHP) -->
        <section class="max-w-7xl mx-auto px-6 pb-20">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Other Products</h2>
                <a href="products.php" class="text-green-600 hover:underline"><i class="fa-solid fa-arrow-right"></i> View All</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Static Product Card 1 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img6.png" alt="Pork Chop" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Pork Chop</h3>
                  <p class="text-green-600 font-bold text-sm">₱350.00 per kg</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
                <!-- Static Product Card 2 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img7.png" alt="Fresh Milk" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Fresh Milk</h3>
                  <p class="text-green-600 font-bold text-sm">₱85.00</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
                <!-- Static Product Card 3 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img8.png" alt="Apples" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Apples</h3>
                  <p class="text-green-600 font-bold text-sm">₱25.00 each</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
                <!-- Static Product Card 4 -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition relative product-card">
                  <img src="images/products/img9.png" alt="Bananas" class="w-full h-32 object-cover rounded">
                  <h3 class="mt-2 font-semibold text-sm">Bananas</h3>
                  <p class="text-green-600 font-bold text-sm">₱40.00 per kg</p>
                  <button aria-label="add" class="add-btn bg-green-600 text-white rounded-full p-2 hover:bg-green-700 absolute bottom-3 right-3 shadow transition" title="Add to cart"><i class="fa-solid fa-plus"></i></button>
                </div>
            </div>
        </section>

        <!-- Explore Other Shops (Static HTML) -->
        <section class="max-w-7xl mx-auto px-6 py-20">
            <h2 class="text-xl font-bold mb-6">Explore Other Shops</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
                    <img src="images/img1.png" alt="Mesa Farm" class="w-full h-40 object-cover" onerror="this.onerror=null; this.src='https://placehold.co/200x160/d1e7dd/1e7145?text=Mesa+Farm'">
                    <div class="p-4">
                        <h3 class="font-bold">Mesa Farm</h3>
                        <p class="text-sm text-gray-600">Organic vegetables & herbs</p>
                        <p class="text-green-600 mt-2">★ 4.8</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
                    <img src="images/img2.png" alt="Taco Bell" class="w-full h-40 object-cover" onerror="this.onerror=null; this.src='https://placehold.co/200x160/d1e7dd/1e7145?text=Taco+Bell'">
                    <div class="p-4">
                        <h3 class="font-bold">Taco Bell</h3>
                        <p class="text-sm text-gray-600">Fresh Mexican ingredients</p>
                        <p class="text-green-600 mt-2">★ 4.5</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
                    <img src="images/img3.png" alt="Jay's Artisan" class="w-full h-40 object-cover" onerror="this.onerror=null; this.src='https://placehold.co/200x160/d1e7dd/1e7145?text=Jay\'s+Artisan'">
                    <div class="p-4">
                        <h3 class="font-bold">Jay’s Artisan</h3>
                        <p class="text-sm text-gray-600">Coffees and bread</p>
                        <p class="text-green-600 mt-2">★ 4.9</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
                    <img src="images/img4.png" alt="Ocean Fresh" class="w-full h-40 object-cover" onerror="this.onerror=null; this.src='https://placehold.co/200x160/d1e7dd/1e7145?text=Ocean+Fresh'">
                    <div class="p-4">
                        <h3 class="font-bold">Ocean Fresh</h3>
                        <p class="text-sm text-gray-600">Daily catch seafood</p>
                        <p class="text-green-600 mt-2">★ 4.7</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="text-white py-12" style="background-color: #1B5E20;">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
            
            <!-- Logo/About -->
            <div>
                <h3 class="font-bold text-lg mb-3">The Farmer's Mall</h3>
                <p class="text-gray-300 text-sm">
                    Fresh, organic produce delivered straight to your home from local farmers.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="font-bold text-lg mb-3">Quick Links</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="#" class="hover:underline">About Us</a></li>
                    <li><a href="#" class="hover:underline">Contact</a></li>
                    <li><a href="#" class="hover:underline">FAQ</a></li>
                    <li><a href="#" class="hover:underline">Support</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div>
                <h3 class="font-bold text-lg mb-3">Categories</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="#" class="hover:underline">Vegetables</a></li>
                    <li><a href="#" class="hover:underline">Fruits</a></li>
                    <li><a href="#" class="hover:underline">Dairy</a></li>
                    <li><a href="#" class="hover:underline">Meat</a></li>
                </ul>
            </div>

            <!-- Social -->
            <div>
                <h3 class="font-bold text-lg mb-3">Follow Us</h3>
                <div class="flex space-x-4 text-xl">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
            © 2025 The Farmer's Mall. All rights reserved.
        </div>
    </footer>

    <script>
        // Add event listener to all '+' buttons to redirect to the products page
        document.querySelectorAll('.add-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Since this is a sample, we redirect. In a real app, this would be an AJAX call.
                window.location.href = 'products.php';
            });
        });
    </script>
</body>
</html>