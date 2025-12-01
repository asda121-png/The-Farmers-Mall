<?php
// admin-retailers.php
// Mock Retailer Data
$retailer_stats = [
    "total" => 45,
    "active" => 38,
    "pending" => 5,
    "suspended" => 2
];

$retailers = [
    [
        "id" => "R-1001",
        "name" => "Juan Dela Cruz",
        "store_name" => "Juan's Fresh Market",
        "email" => "juan.delacruz@email.com",
        "phone" => "+63 917 123 4567",
        "location" => "Quezon City, Metro Manila",
        "status" => "Verified",
        "joined_date" => "Oct 15, 2023",
        "avatar" => "https://randomuser.me/api/portraits/men/32.jpg"
    ],
    [
        "id" => "R-1002",
        "name" => "Maria Santos",
        "store_name" => "Green Earth Grocer",
        "email" => "maria.santos@email.com",
        "phone" => "+63 918 987 6543",
        "location" => "Cebu City, Cebu",
        "status" => "Pending",
        "joined_date" => "Nov 01, 2023",
        "avatar" => "https://randomuser.me/api/portraits/women/44.jpg"
    ],
    [
        "id" => "R-1003",
        "name" => "Antonio Reyes",
        "store_name" => "Reyes Organic Hub",
        "email" => "antonio.r@email.com",
        "phone" => "+63 920 555 1234",
        "location" => "Davao City, Davao del Sur",
        "status" => "Verified",
        "joined_date" => "Sep 20, 2023",
        "avatar" => "https://randomuser.me/api/portraits/men/85.jpg"
    ],
    [
        "id" => "R-1004",
        "name" => "Elena Gomez",
        "store_name" => "Elena's Pantry",
        "email" => "elena.gomez@email.com",
        "phone" => "+63 917 888 9999",
        "location" => "Baguio City, Benguet",
        "status" => "Suspended",
        "joined_date" => "Aug 10, 2023",
        "avatar" => "https://randomuser.me/api/portraits/women/65.jpg"
    ],
    [
        "id" => "R-1005",
        "name" => "Ricardo Dalisay",
        "store_name" => "Dalisay Farm Direct",
        "email" => "ricardo.d@email.com",
        "phone" => "+63 922 333 4444",
        "location" => "Iloilo City, Iloilo",
        "status" => "Verified",
        "joined_date" => "Oct 05, 2023",
        "avatar" => "https://randomuser.me/api/portraits/men/22.jpg"
    ],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Retailers</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
  <style>
    /* Global Styles (Consistent) */
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7f9fc;
    }

    .custom-scrollbar::-webkit-scrollbar {
      width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
      background-color: #4b5563;
      border-radius: 2px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
      background: transparent;
    }

    .card-shadow {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }
    
    .bg-green-950 {
        background-color: #184D34;
    }
  </style>
</head>

<body class="flex min-h-screen bg-gray-50 text-gray-800">

  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300">
    <div>
      <div class="flex items-center gap-3 mb-8 px-2 py-2">
        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-white">
          <i class="fas fa-leaf text-green-700 text-lg"></i>
        </div>
        <h1 class="text-xl font-bold">Farmers Mall</h1>
      </div>

      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest mb-2 px-2">GENERAL</p>
      <nav class="space-y-1">
        <a href="admin-dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
          <span>Dashboard</span>
        </a>
        <a href="admin-products.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-box w-5"></i>
          <span>Products</span>
        </a>
        <a href="admin-inventory.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-truck-ramp-box w-5"></i>
          <span>Inventory</span>
        </a>
        <a href="admin-retailers.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-store w-5 text-green-200"></i>
          <span>Retailers</span>
        </a>
        <a href="admin-reviews.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-star w-5"></i>
          <span>Review</span>
        </a>
        <a href="admin-orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
      </nav>

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
      </nav>
    </div>

    <div class="mt-8 pt-4 border-t border-green-800">
      <button id="logoutButton" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200 text-gray-300">
        <i class="fa-solid fa-sign-out-alt w-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <div class="flex-1 p-6 space-y-6 custom-scrollbar">

    <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-6 z-10 w-full">
      <div class="relative w-full max-w-lg hidden md:block">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" placeholder="Search retailer, store name, or location..."
          class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
      </div>

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

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Retailers</h2>
            <p class="text-sm text-gray-500">Manage registered retailers and verification requests</p>
        </div>
        <div class="flex gap-3">
             <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-file-arrow-down"></i> Export List
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors shadow-lg shadow-green-700/30">
                <i class="fa-solid fa-user-plus"></i> Invite Retailer
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Total Retailers</p>
                 <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $retailer_stats['total']; ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Active Partners</p>
                 <p class="text-2xl font-bold text-green-600 mt-1"><?php echo $retailer_stats['active']; ?></p>
            </div>
             <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <i class="fa-solid fa-store"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Pending Approval</p>
                 <p class="text-2xl font-bold text-yellow-600 mt-1"><?php echo $retailer_stats['pending']; ?></p>
            </div>
             <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Suspended</p>
                 <p class="text-2xl font-bold text-red-600 mt-1"><?php echo $retailer_stats['suspended']; ?></p>
            </div>
             <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                <i class="fa-solid fa-ban"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl card-shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                 <button class="px-3 py-1 text-sm font-medium text-green-700 bg-green-50 rounded-lg border border-green-200">All</button>
                 <button class="px-3 py-1 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg border border-transparent">Verified</button>
                 <button class="px-3 py-1 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg border border-transparent">Pending</button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Retailer Profile</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Store Info</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined Date</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($retailers as $retailer): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src="<?php echo $retailer['avatar']; ?>" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $retailer['name']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $retailer['email']; ?></div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium"><?php echo $retailer['store_name']; ?></div>
                            <div class="text-xs text-gray-500"><?php echo $retailer['phone']; ?></div>
                        </td>
                        
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <i class="fa-solid fa-location-dot text-gray-400 mr-1"></i> <?php echo $retailer['location']; ?>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                             <?php 
                                $statusClass = 'bg-gray-100 text-gray-800';
                                $icon = '';
                                if($retailer['status'] === 'Verified') {
                                    $statusClass = 'bg-green-100 text-green-800 border border-green-200';
                                    $icon = '<i class="fa-solid fa-check-circle mr-1 text-green-600"></i>';
                                }
                                if($retailer['status'] === 'Pending') {
                                    $statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                    $icon = '<i class="fa-solid fa-hourglass-half mr-1 text-yellow-600"></i>';
                                }
                                if($retailer['status'] === 'Suspended') {
                                    $statusClass = 'bg-red-100 text-red-800 border border-red-200';
                                    $icon = '<i class="fa-solid fa-ban mr-1 text-red-600"></i>';
                                }
                            ?>
                            <span class="px-2 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full <?php echo $statusClass; ?>">
                                <?php echo $icon . $retailer['status']; ?>
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $retailer['joined_date']; ?>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-500 hover:text-green-700 mr-3" title="View Details"><i class="fa-solid fa-eye"></i></button>
                            <button class="text-gray-500 hover:text-blue-700" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">45</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fa-solid fa-chevron-left h-4 w-4"></i>
                        </a>
                        <a href="#" aria-current="page" class="z-10 bg-green-50 border-green-500 text-green-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">2</a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                             <i class="fa-solid fa-chevron-right h-4 w-4"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

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
          <a href="../auth/login.php" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
            Logout
          </a>
        </div>
      </div>
    </div>

  </div> <script src="admin-theme.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Logout Modal Logic
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

      logoutModal.addEventListener('click', function(e) {
          if (e.target === logoutModal) {
              logoutModal.classList.add('hidden');
              logoutModal.classList.remove('flex');
          }
      });
    });
  </script>
</body>
</html>