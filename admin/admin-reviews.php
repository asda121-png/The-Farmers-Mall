<?php
// admin-reviews.php
// Mock Review Data
$review_stats = [
    "total_reviews" => 1240,
    "avg_rating" => 4.8,
    "pending" => 45,
    "reported" => 3
];

$reviews = [
    [
        "id" => "REV-001",
        "customer_name" => "Sophia Lee",
        "customer_img" => "https://randomuser.me/api/portraits/women/44.jpg",
        "product_name" => "Fresh Carrots (1kg)",
        "product_img" => "https://placehold.co/40x40/f3f4f6/1f2937?text=VEG",
        "rating" => 5,
        "date" => "2 hours ago",
        "comment" => "The carrots were incredibly fresh and crunchy! Delivery was fast too. Will definitely order again.",
        "status" => "Published",
        "reply" => ""
    ],
    [
        "id" => "REV-002",
        "customer_name" => "Mark Rivera",
        "customer_img" => "https://randomuser.me/api/portraits/men/32.jpg",
        "product_name" => "Organic Apples",
        "product_img" => "https://placehold.co/40x40/f3f4f6/1f2937?text=FRU",
        "rating" => 4,
        "date" => "1 day ago",
        "comment" => "Good quality apples, but one was slightly bruised. Overall happy with the purchase.",
        "status" => "Published",
        "reply" => "Admin: We apologize for that, Mark! We've sent you a voucher for your next purchase."
    ],
    [
        "id" => "REV-003",
        "customer_name" => "Jessica Tan",
        "customer_img" => "https://randomuser.me/api/portraits/women/65.jpg",
        "product_name" => "Chicken Breast (1kg)",
        "product_img" => "https://placehold.co/40x40/f3f4f6/1f2937?text=MEA",
        "rating" => 1,
        "date" => "2 days ago",
        "comment" => "The meat didn't smell fresh. I had to throw it away. Very disappointed.",
        "status" => "Pending",
        "reply" => ""
    ],
    [
        "id" => "REV-004",
        "customer_name" => "Ramon Bautista",
        "customer_img" => "https://randomuser.me/api/portraits/men/85.jpg",
        "product_name" => "Brown Rice (5kg)",
        "product_img" => "https://placehold.co/40x40/f3f4f6/1f2937?text=GRN",
        "rating" => 5,
        "date" => "3 days ago",
        "comment" => "Perfect staple for my diet. Packaging is secure and eco-friendly.",
        "status" => "Published",
        "reply" => ""
    ]
];

function renderStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fa-solid fa-star text-yellow-400 text-sm"></i>';
        } else {
            $stars .= '<i class="fa-regular fa-star text-gray-300 text-sm"></i>';
        }
    }
    return $stars;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Reviews</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Global Styles */
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
       
        <a href="admin-reviews.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-star w-5 text-green-200"></i>
          <span>Review</span>
          <span class="ml-auto text-xs bg-red-600 text-white px-2 py-0.5 rounded-full font-medium">02</span>
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
        <input type="text" placeholder="Search reviews..."
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
            <h2 class="text-3xl font-bold text-gray-900">Reviews & Ratings</h2>
            <p class="text-sm text-gray-500">Monitor customer feedback and product satisfaction</p>
        </div>
        <div class="flex gap-3">
             <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-download"></i> Export
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Total Reviews</p>
                 <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $review_stats['total_reviews']; ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                <i class="fa-solid fa-comments"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Avg. Rating</p>
                 <div class="flex items-center gap-2 mt-1">
                    <p class="text-2xl font-bold text-gray-800"><?php echo $review_stats['avg_rating']; ?></p>
                    <div class="flex text-yellow-400 text-xs">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                 </div>
            </div>
             <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                <i class="fa-solid fa-star"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Pending Response</p>
                 <p class="text-2xl font-bold text-orange-600 mt-1"><?php echo $review_stats['pending']; ?></p>
            </div>
             <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                <i class="fa-solid fa-reply"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                 <p class="text-sm text-gray-500 font-medium">Reported</p>
                 <p class="text-2xl font-bold text-red-600 mt-1"><?php echo $review_stats['reported']; ?></p>
            </div>
             <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                <i class="fa-solid fa-flag"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-5 rounded-xl card-shadow">
                <h3 class="font-bold text-gray-800 mb-4">Filters</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-semibold">Status</label>
                        <select class="w-full mt-1 border border-gray-300 rounded-lg p-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <option>All Statuses</option>
                            <option>Published</option>
                            <option>Pending</option>
                            <option>Reported</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-semibold">Rating</label>
                        <select class="w-full mt-1 border border-gray-300 rounded-lg p-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <option>All Ratings</option>
                            <option>5 Stars</option>
                            <option>4 Stars</option>
                            <option>3 Stars</option>
                            <option>2 Stars</option>
                            <option>1 Star</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-semibold">Date</label>
                        <select class="w-full mt-1 border border-gray-300 rounded-lg p-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <option>Newest First</option>
                            <option>Oldest First</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl card-shadow">
                <h3 class="font-bold text-gray-800 mb-4">Rating Breakdown</h3>
                <div class="space-y-3">
                    <?php 
                    $ratings = [5 => 80, 4 => 12, 3 => 5, 2 => 2, 1 => 1];
                    foreach($ratings as $star => $percent): ?>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-3 text-gray-600 font-medium"><?php echo $star; ?></span>
                        <i class="fa-solid fa-star text-yellow-400 text-xs"></i>
                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-400 rounded-full" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                        <span class="text-xs text-gray-400 w-8 text-right"><?php echo $percent; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($reviews as $review): ?>
            <div class="bg-white p-5 rounded-xl card-shadow transition hover:shadow-md">
                <div class="flex justify-between items-start">
                    <div class="flex gap-3">
                        <img src="<?php echo $review['customer_img']; ?>" class="w-10 h-10 rounded-full border border-gray-200" alt="Avatar">
                        <div>
                            <h4 class="font-bold text-sm text-gray-900"><?php echo $review['customer_name']; ?></h4>
                            <p class="text-xs text-gray-400"><?php echo $review['date']; ?></p>
                        </div>
                    </div>
                    <?php 
                        $statusColor = 'bg-green-100 text-green-700';
                        if($review['status'] == 'Pending') $statusColor = 'bg-yellow-100 text-yellow-700';
                        if($review['status'] == 'Reported') $statusColor = 'bg-red-100 text-red-700';
                    ?>
                    <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $statusColor; ?>">
                        <?php echo $review['status']; ?>
                    </span>
                </div>

                <div class="mt-3">
                     <div class="flex items-center gap-1 mb-2">
                        <?php echo renderStars($review['rating']); ?>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed">
                        <?php echo $review['comment']; ?>
                    </p>
                </div>

                <div class="mt-4 flex items-center gap-3 bg-gray-50 p-2 rounded-lg border border-gray-100">
                    <img src="<?php echo $review['product_img']; ?>" class="w-8 h-8 rounded object-cover" alt="Product">
                    <span class="text-xs font-medium text-gray-600">Purchased: <span class="text-gray-900"><?php echo $review['product_name']; ?></span></span>
                </div>

                <?php if(!empty($review['reply'])): ?>
                <div class="mt-4 ml-4 pl-4 border-l-2 border-green-200">
                    <p class="text-xs font-bold text-green-700 mb-1">Response from Store:</p>
                    <p class="text-sm text-gray-600 italic">"<?php echo str_replace('Admin: ', '', $review['reply']); ?>"</p>
                </div>
                <?php endif; ?>

                <div class="mt-4 pt-4 border-t border-gray-100 flex gap-3">
                    <button class="text-sm text-gray-500 hover:text-green-700 font-medium flex items-center gap-1">
                        <i class="fa-solid fa-reply"></i> Reply
                    </button>
                    <?php if($review['status'] == 'Pending'): ?>
                        <button class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Approve
                        </button>
                    <?php endif; ?>
                    <button class="text-sm text-gray-500 hover:text-red-600 font-medium flex items-center gap-1 ml-auto">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="flex justify-center mt-6">
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-l-lg text-gray-500 hover:bg-gray-50">Prev</button>
                <button class="px-4 py-2 bg-green-700 text-white font-bold border border-green-700">1</button>
                <button class="px-4 py-2 bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">2</button>
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-r-lg text-gray-500 hover:bg-gray-50">Next</button>
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

  </div> <script>
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