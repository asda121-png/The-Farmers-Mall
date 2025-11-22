<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Header – The Farmer’s Mall</title>

  <!-- Tailwind & Icons -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Optional Custom CSS -->
  <style>
    /* ===== HEADER STYLES ===== */
    header {
      position: sticky;
      top: 0;
      z-index: 50;
    }

    header img {
      object-fit: cover;
    }

    .profile-info p {
      line-height: 1.1;
    }

    /* Search Input Focus Glow */
    input:focus {
      box-shadow: 0 0 0 1px #15803d;
    }
  </style>
</head>

<body>
  <!-- SELLER HEADER -->
  <header class="bg-white border-b shadow-sm">
    <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">

      <!-- Left: Logo -->
      <div class="flex items-center space-x-3 cursor-pointer" onclick="window.location.href='retailerdashboard.php'">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <h1 class="text-xl font-bold text-green-700">Farmers Mall</h1>
      </div>

      <!-- Center: Search -->
      <div class="flex-1 mx-8 max-w-xl">
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
          <input type="text" placeholder="Search..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-green-600 focus:border-green-600 text-sm">
        </div>
        <form action="products.php" method="GET" class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="text" name="search" placeholder="Search products..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-green-600 focus:border-green-600 text-sm" />
        </form>
      </div>

      <!-- Right: Icons & Profile -->
      <div class="flex items-center space-x-6">
        <!-- Messages -->
        <div class="relative cursor-pointer">
          <i class="fa-regular fa-comment text-xl text-gray-600"></i>
          <span
            class="absolute -top-2 -right-2 bg-green-700 text-white text-xs font-semibold rounded-full px-1.5">3</span>
        </div>

        <!-- Notifications -->
        <div class="relative cursor-pointer">
          <i class="fa-regular fa-bell text-xl text-gray-600"></i>
          <span
            class="absolute -top-2 -right-2 bg-green-700 text-white text-xs font-semibold rounded-full px-1.5">5</span>
        </div>

        <!-- Profile -->
        <div class="flex items-center space-x-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-8 h-8 rounded-full" alt="Seller Profile">
          <div class="profile-info">
            <p class="text-sm font-medium text-gray-800">Mesa Farm</p>
            <p class="text-xs text-gray-500">Seller</p>
          </div>
        </div>
      </div>
    </div>
  </header>
</body>

</html>
