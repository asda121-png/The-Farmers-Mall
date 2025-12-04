<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data from session
$full_name = $_SESSION['full_name'] ?? 'Guest User';
$email = $_SESSION['email'] ?? 'user@email.com';
$user_id = $_SESSION['user_id'] ?? null;

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Farmers Mall - Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    .category-text {
        margin-top: 0.5rem; /* Adjusts spacing to the upper */
    }

    /* Smooth transitions for all elements */
    * {
        transition: all 0.3s ease;
    }

    /* Header icon hover effects */
    header a:hover i {
        transform: scale(1.2);
        color: #2E7D32;
    }

    /* Search input hover and focus effects */
    input[type="text"] {
        transition: all 0.3s ease;
    }

    input[type="text"]:hover {
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.1);
        border-color: #2E7D32;
    }

    /* Profile image hover effect */
    header img:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
    }

    /* Hero button hover effect */
    .hero-btn {
        position: relative;
        overflow: hidden;
    }

    .hero-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .hero-btn:hover::before {
        width: 300px;
        height: 300px;
    }

    /* Category hover effects - subtle */
    .category-item {
      transition: all 0.3s ease;
    }

    .category-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(46, 125, 50, 0.15);
    }

    .category-item:hover i {
      color: #1B5E20;
    }

    .category-item:hover p {
      color: #1B5E20;
    }

    /* Product card static (no hover effects) */
    .product-card {
        position: relative;
    }

    .product-card h3,
    .product-card p {
        position: relative;
        z-index: 1;
    }

    /* Add to cart button enhanced effects */
    .add-btn {
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        z-index: 2;
    }

    .add-btn:hover {
        transform: rotate(90deg) scale(1.2);
        box-shadow: 0 4px 15px rgba(46, 125, 50, 0.5);
    }

    .add-btn:active {
        transform: rotate(90deg) scale(0.9);
    }

    /* Shop card hover effects */
    .shop-card {
        transition: all 0.4s ease;
        overflow: hidden;
    }

    .shop-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(46, 125, 50, 0.2);
    }

    .shop-card img {
        transition: all 0.5s ease;
    }

    .shop-card:hover img {
        transform: scale(1.2) rotate(2deg);
    }

    .shop-card:hover h3 {
        color: #2E7D32;
    }

    /* Footer links hover effect */
    footer a {
        position: relative;
        transition: all 0.3s ease;
    }

    footer a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background-color: #4CAF50;
        transition: width 0.3s ease;
    }

    footer a:hover::after {
        width: 100%;
    }

    footer a:hover {
        color: #4CAF50;
        transform: translateX(5px);
    }

    /* Social media icons hover effect */
    footer .fab {
        transition: all 0.3s ease;
    }

    footer .fab:hover {
        transform: translateY(-5px) scale(1.3);
        color: #4CAF50;
    }

    /* Arrow link hover effect */
    .arrow-link {
        transition: all 0.3s ease;
    }

    .arrow-link:hover {
        transform: translateX(5px);
    }

    /* Pulse animation for new badges */
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }

    /* Shimmer effect on hover */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }

    .shimmer-effect:hover::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.3),
            transparent
        );
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        pointer-events: none;
    }

    /* Section heading underline effect */
    .section-heading {
        position: relative;
        display: inline-block;
    }

    .section-heading::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #2E7D32, #4CAF50);
        transition: width 0.4s ease;
    }

    .section-heading:hover::after {
        width: 100%;
    }

    /* Hero slider styles */
    .hero-slider {
        position: relative;
        overflow: hidden;
    }

    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1.5s ease-in-out;
        background-size: cover;
        background-position: center;
    }

    .hero-slide.active {
        opacity: 1;
        position: relative;
    }

    .hero-content {
        position: relative;
        z-index: 10;
    }

    /* Slider navigation dots */
    .slider-dots {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 20;
    }

    .slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .slider-dot.active {
        background: #fff;
        transform: scale(1.2);
    }

    .slider-dot:hover {
        background: rgba(255, 255, 255, 0.8);
    }
  </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

  <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <h1 class="text-xl font-bold" style="color: #2E7D32;">Farmers Mall</h1>
      </div>

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

      <div class="flex items-center space-x-6">
        <a href="message.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
        <a href="notification.php" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
        <a href="cart.php" class="text-gray-600 relative"><i class="fa-solid fa-cart-shopping"></i>
          <span id="cartBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
        </a>
        <a href="profile.php">
          <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>
            <img src="<?php echo htmlspecialchars('../' . $profile_picture); ?>" alt="Profile" class="w-8 h-8 rounded-full cursor-pointer object-cover">
          <?php else: ?>
            <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
              <i class="fas fa-user text-white text-sm"></i>
            </div>
          <?php endif; ?>
        </a>
      </div>
    </div>
  </header>

  <section class="hero-slider relative">
    <!-- Slide 1 -->
    <div class="hero-slide active" style="background-image: url('../images/img.png');">
      <div class="bg-black bg-opacity-40">
        <div class="hero-content max-w-7xl mx-auto px-6 py-32 text-left text-white">
          <h2 class="text-4xl md:text-5xl font-extrabold mb-4">Fresh Harvest Sale</h2>
          <p class="text-lg md:text-xl mb-6">Up to 30% off on organic produce</p>
          <a href="products.php" class="hero-btn inline-block bg-green-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition">
            Shop Now
          </a>
        </div>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="hero-slide" style="background-image: url('../images/img1.png');">
      <div class="bg-black bg-opacity-40">
        <div class="hero-content max-w-7xl mx-auto px-6 py-32 text-left text-white">
          <h2 class="text-4xl md:text-5xl font-extrabold mb-4">Farm Fresh Daily</h2>
          <p class="text-lg md:text-xl mb-6">Organic vegetables & herbs from local farms</p>
          <a href="../user/products.php" class="hero-btn inline-block bg-green-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition">
            Shop Now
          </a>
        </div>
      </div>
    </div>

    <!-- Slide 3 -->
    <div class="hero-slide" style="background-image: url('../images/img2.png');">
      <div class="bg-black bg-opacity-40">
        <div class="hero-content max-w-7xl mx-auto px-6 py-32 text-left text-white">
          <h2 class="text-4xl md:text-5xl font-extrabold mb-4">Premium Quality</h2>
          <p class="text-lg md:text-xl mb-6">Fresh ingredients delivered to your door</p>
          <a href="products.php" class="hero-btn inline-block bg-green-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition">
            Shop Now
          </a>
        </div>
      </div>
    </div>

    <!-- Navigation Dots -->
    <div class="slider-dots">
      <span class="slider-dot active" data-slide="0"></span>
      <span class="slider-dot" data-slide="1"></span>
      <span class="slider-dot" data-slide="2"></span>
    </div>
  </section>

  <section class="w-full mx-auto px-0 py-10 bg-[#FFFFFF] mb-10">
    <div class="flex justify-center mb-8">
      <h2 class="section-heading text-2xl font-bold">Shop by Category</h2>
    </div>
    <div class="flex flex-wrap justify-center gap-4 max-w-7xl mx-auto px-6">
      <a href="products.php?category=vegetables" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-carrot text-green-600 text-2xl"></i>
        <p class="text-gray-700 mt-2 text-xs category-text">Vegetables</p>
      </a>
      <a href="products.php?category=fruits" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-apple-whole text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Fruits</p>
      </a>
      <a href="products.php?category=meat" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-drumstick-bite text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Meat</p>
      </a>
      <a href="products.php?category=seafood" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-fish text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Seafood</p>
      </a>
      <a href="products.php?category=dairy" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-cheese text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Dairy</p>
      </a>
      <a href="products.php?category=bakery" class="category-item flex flex-col items-center justify-center bg-white w-24 h-24 rounded-full shadow-md hover:shadow-lg cursor-pointer transition">
        <i class="fa-solid fa-bread-slice text-green-600 text-2xl"></i>
        <p class="mt-2 text-xs category-text">Bakery</p>
      </a>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-6 pt-2 pb-8">
    <div class="flex justify-between items-center mb-4">
      <h2 class="section-heading text-2xl font-bold">Top Products</h2>
      <a href="products.php" class="arrow-link text-green-600 hover:underline"><i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg transition relative block overflow-hidden" data-name="Fresh Vegetable Box" data-price="45.00" data-img="../images/products/Fresh Vegetable Box.png">
        <img src="../images/products/Fresh Vegetable Box.png" alt="Fresh Vegetable Box" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Fresh Vegetable Box</h3>
          <p class="text-green-600 font-bold text-sm">₱45.00</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg transition relative block overflow-hidden" data-name="Organic Lettuce" data-price="30.00" data-img="../images/products/Organic Lettuce.png">
        <img src="../images/products/Organic Lettuce.png" alt="Organic Lettuce" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Organic Lettuce</h3>
          <p class="text-green-600 font-bold text-sm">₱30.00</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg transition relative block overflow-hidden" data-name="Fresh Milk" data-price="50.00" data-img="../images/products/Fresh Milk.png">
        <img src="../images/products/Fresh Milk.png" alt="Fresh Milk" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Fresh Milk</h3>
          <p class="text-green-600 font-bold text-sm">₱50.00</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg transition relative block overflow-hidden" data-name="Tilapia" data-price="80.00" data-img="../images/products/tilapia.jpg">
        <img src="../images/products/tilapia.jpg" alt="Aged Cheddar" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Tilapia</h3>
          <p class="text-green-600 font-bold text-sm">₱80.00</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg transition relative block overflow-hidden" data-name="Farm Eggs" data-price="60.00" data-img="../images/products/fresh eggs.jpeg">
        <img src="../images/products/fresh eggs.jpeg" alt="Farm Eggs" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Farm Eggs</h3>
          <p class="text-green-600 font-bold text-sm">₱60.00</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-4">
      <h2 class="section-heading text-2xl font-bold">Other Products</h2>
      <a href="products.php" class="arrow-link text-green-600 hover:underline"><i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Emsaymada" data-price="₱25.00" data-img="../images/products/Emsaymada.jpg">
        <img src="../images/products/Emsaymada.jpg" alt="Baby Carrots" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Emsaymada</h3>
          <p class="text-green-600 font-bold text-sm">₱25.00</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Butter Spread" data-price="₱70.00" data-img="../images/products/Butter Spread.jpg">
        <img src="../images/products/Butter Spread.jpg" alt="Artisan Bread" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Butter Spread</h3>
          <p class="text-green-600 font-bold text-sm">₱70.00</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Bangus" data-price="₱140.00" data-img="../images/products/Bangus.jpg">
        <img src="../images/products/Bangus.jpg" alt="Banana" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Bangus</h3>
          <p class="text-green-600 font-bold text-sm">₱140.00 per kg</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Fresh Pork Liempo" data-price="₱180.00" data-img="../images/products/fresh pork liempo.jpg">
        <img src="../images/products/fresh pork liempo.jpg" alt="Cheddar" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Fresh Pork Liempo</h3>
          <p class="text-green-600 font-bold text-sm">₱180.00 per kg</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Fresh Avocado" data-price="₱50.00" data-img="../images/products/fresh avocado.jpg">
        <img src="../images/products/fresh avocado.jpg" alt="Fresh Milk" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Fresh Avocado</h3>
          <p class="text-green-600 font-bold text-sm">₱50.00 per kg</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Native Tomatoes" data-price="₱30.00" data-img="../images/products/Native tomato.jpg">
        <img src="../images/products/Native tomato.jpg" alt="Organic Lettuce" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Native Tomatoes per kg</h3>
          <p class="text-green-600 font-bold text-sm">₱30.00</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Fresh Okra" data-price="₱25.00" data-img="../images/products/fresh okra.jpg">
        <img src="../images/products/fresh okra.jpg" alt="Baby Carrots" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Fresh Okra</h3>
          <p class="text-green-600 font-bold text-sm">₱25.00 per kg</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Native Chicken (Manok)" data-price="₱260.00" data-img="../images/products/native chicken.jpg">
        <img src="../images/products/native chicken.jpg" alt="Artisan Bread" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Native Chicken (Manok)</h3>
          <p class="text-green-600 font-bold text-sm">₱260.00 per kg</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Pork Ribs" data-price="₱310.00" data-img="../images/products/pork ribs.jpg">
        <img src="../images/products/pork ribs.jpg" alt="Banana" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Pork Ribs</h3>
          <p class="text-green-600 font-bold text-sm">₱310.00 per kg</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Shrimp (Hipon)" data-price="₱400.00" data-img="../images/products/shrimp.jpg">
        <img src="../images/products/shrimp.jpg" alt="Farm Eggs" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Shrimp (Hipon)</h3>
          <p class="text-green-600 font-bold text-sm">₱400.00 per kg</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Chocolate Milk" data-price="₱55.00" data-img="../images/products/chocolate milk.jpg">
        <img src="../images/products/chocolate milk.jpg" alt="Fresh Vegetable Box" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Chocolate Milk</h3>
          <p class="text-green-600 font-bold text-sm">₱55.00 per 250ml</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>

      <a href="#" class="product-card product-link bg-white rounded-lg shadow hover:shadow-lg relative block overflow-hidden" data-name="Ube Cheese Pandesal" data-price="₱50.00" data-img="../images/products/ube cheese pandesal.jpg">
        <img src="../images/products/ube cheese pandesal.jpg" alt="Tomato" class="w-full h-32 object-cover">
        <div class="p-4">
          <h3 class="mt-2 font-semibold text-sm">Ube Cheese Pandesal</h3>
          <p class="text-green-600 font-bold text-sm">₱50.00 per 5 pcs</p>
        </div>
        <button aria-label="add" class="add-btn bg-transparent border border-green-600 text-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white absolute bottom-3 right-3 shadow transition" title="Add to cart">
          <i class="fa-solid fa-plus"></i>
        </button>
      </a>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-6 pt-20 pb-8">
    <h2 class="section-heading text-2xl font-bold mb-6">Explore Other Shops</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <a href="shop-products.php?shop=Mesa Farm" class="shop-card bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
        <img src="../images/img1.png" alt="Mesa Farm" class="w-full h-40 object-cover">
        <div class="p-4">
          <h3 class="font-bold">Mesa Farm</h3>
          <p class="text-sm text-gray-600">Organic vegetables & herbs</p>
          <p class="text-green-600 mt-2">★ 4.8</p>
        </div>
      </a>

      <a href="shop-products.php?shop=Taco Bell" class="shop-card bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
        <img src="../images/img3.png" alt="Taco Bell" class="w-full h-40 object-cover">
        <div class="p-4">
          <h3 class="font-bold">Taco Bell</h3>
          <p class="text-sm text-gray-600">Fresh Mexican ingredients</p>
          <p class="text-green-600 mt-2">★ 4.5</p>
        </div>
      </a>

      <a href="shop-products.php?shop=Jay's Artisan" class="shop-card bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
        <img src="../images/img2.png" alt="Jay's Artisan" class="w-full h-40 object-cover">
        <div class="p-4">
          <h3 class="font-bold">Jay's Artisan</h3>
          <p class="text-sm text-gray-600">Coffees and bread</p>
          <p class="text-green-600 mt-2">★ 4.9</p>
        </div>
      </a>

      <a href="shop-products.php?shop=Ocean Fresh" class="shop-card bg-white rounded-lg shadow overflow-hidden hover:shadow-md cursor-pointer">
        <img src="../images/img4.png" alt="Ocean Fresh" class="w-full h-40 object-cover">
        <div class="p-4">
          <h3 class="font-bold">Ocean Fresh</h3>
          <p class="text-sm text-gray-600">Daily catch seafood</p>
          <p class="text-green-600 mt-2">★ 4.7</p>
        </div>
      </a>
    </div>
  </section>

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

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Hero Slider
      const slides = document.querySelectorAll('.hero-slide');
      const dots = document.querySelectorAll('.slider-dot');
      let currentSlide = 0;
      const slideInterval = 5000; // 5 seconds

      function showSlide(index) {
        slides.forEach((slide, i) => {
          slide.classList.remove('active');
          dots[i].classList.remove('active');
        });
        
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
      }

      function nextSlide() {
        showSlide(currentSlide + 1);
      }

      // Auto advance slides
      let sliderTimer = setInterval(nextSlide, slideInterval);

      // Dot click handlers
      dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          showSlide(index);
          clearInterval(sliderTimer);
          sliderTimer = setInterval(nextSlide, slideInterval);
        });
      });

      // Pause on hover
      const heroSlider = document.querySelector('.hero-slider');
      heroSlider.addEventListener('mouseenter', () => {
        clearInterval(sliderTimer);
      });

      heroSlider.addEventListener('mouseleave', () => {
        sliderTimer = setInterval(nextSlide, slideInterval);
      });

      // Product links
      document.querySelectorAll('.product-link').forEach(link => {
        link.addEventListener('click', (event) => {
          event.preventDefault();
          const name = link.dataset.name;
          const price = link.dataset.price;
          const img = link.dataset.img;
          window.location.href = `productdetails.php?name=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}&img=${encodeURIComponent(img)}`;
        });
      });

      // Add to cart functionality
      async function addToCart(product) {
        try {
          // Prepare the payload
          const payload = {
            quantity: 1
          };

          // If product has an ID, use it
          if (product.id) {
            payload.product_id = product.id;
          } else {
            // Otherwise send product details to create/find product
            payload.product_name = product.name;
            payload.price = parseFloat(product.price);
            payload.description = product.description || '';
            payload.image = product.image || '';
            payload.category = product.category || 'other';
          }

          const response = await fetch('../api/cart.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
          });
          
          const data = await response.json();
          if (data.success) {
            updateCartIcon();
            showNotification(`${product.name} added to cart!`);
          } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
          }
        } catch (error) {
          console.error('Error adding to cart:', error);
          showNotification('Error adding to cart', 'error');
        }
      }

      // Show toast notification
      function showNotification(message, type = 'success') {
        // Remove existing notification if any
        const existing = document.querySelector('.toast-notification');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.className = `toast-notification fixed top-20 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white`;
        toast.innerHTML = `
          <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium">${message}</span>
          </div>
        `;
        document.body.appendChild(toast);
        // Animate in
        setTimeout(() => toast.classList.remove('translate-x-full'), 10);
        // Animate out and remove
        setTimeout(() => {
          toast.classList.add('translate-x-full');
          setTimeout(() => toast.remove(), 300);
        }, 3000);
      }

      // Update cart icon with item count
      async function updateCartIcon() {
        const badge = document.getElementById('cartBadge');
        if (!badge) return;
        
        try {
          // Fetch from database
          const response = await fetch('../api/cart.php');
          const data = await response.json();
          
          if (data.success && data.items) {
            const totalItems = data.items.reduce((sum, item) => sum + (item.quantity || 1), 0);
            badge.textContent = totalItems;
            badge.classList.toggle('hidden', totalItems === 0);
            return;
          }
        } catch (error) {
          console.log('Error loading cart count:', error);
        }
        
        // Fallback to localStorage
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
        badge.textContent = totalItems;
        badge.classList.toggle('hidden', totalItems === 0);
      }

      document.querySelectorAll('.add-btn').forEach(button => {
        button.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          const link = button.closest('.product-link');
          if (link) {
            const product = {
              name: link.dataset.name,
              price: parseFloat(link.dataset.price.replace('₱', '')),
              image: link.dataset.img,
              description: link.dataset.description || '',
              category: link.dataset.category || 'other'
            };
            addToCart(product);
          }
        });
      });

      // --- Load User Profile Data ---
      function loadUserProfile() {
        const userProfile = JSON.parse(localStorage.getItem('userProfile'));
        const headerProfilePic = document.getElementById('headerProfilePic');
        
        if (headerProfilePic) {
          if (userProfile && userProfile.profilePic && userProfile.profilePic.startsWith('data:image')) {
            // Has uploaded image
            headerProfilePic.innerHTML = `<img src="${userProfile.profilePic}" alt="User" class="w-full h-full rounded-full object-cover">`;
            headerProfilePic.className = 'w-8 h-8 rounded-full cursor-pointer';
          } else {
            // Show icon
            headerProfilePic.innerHTML = '<i class="fas fa-user text-white text-sm"></i>';
            headerProfilePic.className = 'w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center';
          }
        }
      }

      // Load profile on page load
      loadUserProfile();

      // Update cart icon on page load
      updateCartIcon();

      // Listen for profile updates from other tabs
      window.addEventListener('storage', (e) => {
        if (e.key === 'userProfile') {
          loadUserProfile();
        }
      });

      // Listen for profile updates in same tab
      window.addEventListener('profileUpdated', () => {
        loadUserProfile();
      });
    });
  </script>
</body>
</html>
