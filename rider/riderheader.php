<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rider Dashboard – The Farmer's Mall</title>

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

    /* Notification Badge Animation */
    .notification-badge {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
  </style>
</head>

<body>
  <!-- RIDER HEADER -->
 <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="flex items-center space-x-3 cursor-pointer" onclick="window.location.href='riderdashboard.php'"></div>
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <h1 class="text-xl font-bold" style="color: #2E7D32;">Farmers Mall</h1>
      </div>

      <div class="flex items-center space-x-6">
        <!-- Search Bar -->
        <div class="relative">
          <input type="text" placeholder="Search orders..." 
                 class="w-64 px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:border-green-600">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>

        <!-- Notifications -->
        <div class="relative">
          <button class="relative p-2 text-gray-600 hover:text-green-600 transition-colors">
            <i class="fas fa-bell text-xl"></i>
            <span class="notification-badge absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative profile-dropdown">
          <button class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors" 
                  onclick="toggleProfileDropdown()">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fas fa-motorcycle text-green-600 text-sm"></i>
            </div>
            <div class="profile-info text-left">
              <p class="text-sm font-medium text-gray-800">Rider</p>
              <p class="text-xs text-gray-500">Online</p>
            </div>
            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
          </button>

          <!-- Dropdown Menu -->
          <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden">
            <a href="riderprofile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="fas fa-user mr-2"></i> Profile
            </a>
            <a href="riderearnings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="fas fa-peso-sign mr-2"></i> Earnings
            </a>
            <a href="riderhelp.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="fas fa-question-circle mr-2"></i> Help
            </a>
            <hr class="my-1">
            <a href="../auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
              <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <?php 
  // Get the current page filename
  $current_page = basename($_SERVER['PHP_SELF']);
  ?>
  
  <!-- Navigation Tabs -->
  <nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex space-x-8">
        <a href="riderdashboard.php" 
           class="py-3 px-1 border-b-2 <?= $current_page === 'riderdashboard.php' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?> font-medium">
          <i class="fas fa-home mr-2"></i> Dashboard
        </a>
        <a href="riderorders.php" 
           class="py-3 px-1 border-b-2 <?= $current_page === 'riderorders.php' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?> font-medium">
          <i class="fas fa-box mr-2"></i> Orders
        </a>
        <a href="ridermap.php" 
           class="py-3 px-1 border-b-2 <?= $current_page === 'ridermap.php' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?> font-medium">
          <i class="fas fa-map mr-2"></i> Map
        </a>
        <a href="riderearnings.php" 
           class="py-3 px-1 border-b-2 <?= $current_page === 'riderearnings.php' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?> font-medium">
          <i class="fas fa-chart-line mr-2"></i> Earnings
        </a>
      </div>
    </div>
  </nav>

  <script>
    function toggleProfileDropdown() {
      const dropdown = document.getElementById('profileDropdown');
      dropdown.classList.toggle('hidden');
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function closeDropdown(e) {
        if (!e.target.closest('.profile-dropdown')) {
          dropdown.classList.add('hidden');
          document.removeEventListener('click', closeDropdown);
        }
      });
    }
  </script>
