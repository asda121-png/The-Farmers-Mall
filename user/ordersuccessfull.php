<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'] ?? null;

// Fetch profile picture from database
$profile_picture = '';
$full_name = $_SESSION['full_name'] ?? 'User';
if ($user_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    require_once __DIR__ . '/../config/uuid-helper.php';
    $api = getSupabaseAPI();
    $user = safeGetUser($user_id, $api);
    if ($user) {
        $profile_picture = $user['profile_picture'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Successful – Farmers Mall</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Fonts (Inter + Poppins) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f9fafb;
    }
    h1, h2, h3, h4, h5 {
      font-family: 'Poppins', sans-serif;
    }

    .notification-dropdown { position: absolute; top: 100%; right: 0; margin-top: 8px; width: 320px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); z-index: 50; max-height: 400px; overflow-y: auto; }
    .notification-item { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; transition: all 0.2s ease; cursor: pointer; }
    .notification-item:hover { background-color: #f9f9f9; }
    .notification-item.unread { background-color: #f0f9f5; border-left: 4px solid #4CAF50; }
    .notification-item-title { font-weight: 600; color: #333; font-size: 14px; margin-bottom: 4px; }
    .notification-item-message { font-size: 12px; color: #666; margin-bottom: 4px; }
    .notification-item-time { font-size: 11px; color: #999; }
    .notification-empty { padding: 24px 16px; text-align: center; color: #999; font-size: 14px; }
    .notification-header { padding: 12px 16px; border-bottom: 1px solid #e0e0e0; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .notification-clear-btn { font-size: 12px; color: #2E7D32; cursor: pointer; background: none; border: none; }
    .notification-clear-btn:hover { color: #1B5E20; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800">

<?php include __DIR__ . '/../includes/user-header.php'; ?>




  <!-- Success Section -->
  <main class="flex flex-col items-center px-6 py-16">
    <div class="bg-white shadow-lg rounded-2xl p-8 max-w-2xl w-full text-center border border-gray-100">

      <!-- Success Icon -->
      <div class="flex justify-center mb-4">
        <div class="bg-green-100 text-green-600 w-16 h-16 flex items-center justify-center rounded-full text-3xl">
          <i class="fa-solid fa-check"></i>
        </div>
      </div>

      <!-- Title -->
      <h2 class="text-3xl font-semibold text-gray-800 mb-2">Order Placed Successfully!</h2>
      <p class="text-gray-500 mb-8 leading-relaxed">
        We’re preparing your fresh items now and will notify you when they’re on the way.
      </p>

      <!-- Order Summary -->
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 grid sm:grid-cols-3 gap-4 text-sm mb-6">
        <div>
          <p class="font-semibold text-gray-700">Order Number</p>
          <p class="text-green-700 font-semibold">#REG-0024-PDT</p>
        </div>
        <div>
          <p class="font-semibold text-gray-700">Total Paid</p>
          <p class="text-gray-800 font-semibold">₱226.94</p>
        </div>
        <div>
          <p class="font-semibold text-gray-700">Payment Method</p>
          <p class="flex items-center justify-center sm:justify-start space-x-1">
            <i class="fa-brands fa-cc-visa text-blue-700 text-lg"></i>
            <span>****1234</span>
          </p>
        </div>
      </div>

      <!-- Delivery Status -->
      <div class="bg-green-50 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3">
          <div class="bg-green-200 p-2 rounded-full text-green-700 text-lg">
            <i class="fa-solid fa-truck"></i>
          </div>
          <div class="text-left">
            <p class="font-semibold text-green-800">Estimated Delivery</p>
            <p class="text-sm text-gray-600">Your order is being processed</p>
          </div>
        </div>
        <div class="text-sm text-right">
          <p class="font-semibold text-gray-800">Expected Arrival</p>
          <p class="text-gray-600">Dec 2–4, 2024</p>
          <div class="bg-green-200 rounded-full h-2 mt-1 w-28">
            <div class="bg-green-600 h-2 rounded-full w-3/5"></div>
          </div>
        </div>
      </div>

      <!-- Buttons -->
      <div class="flex flex-col sm:flex-row justify-center gap-3 mt-8">
        <button class="bg-green-700 text-white px-6 py-2 rounded-lg hover:bg-green-800 transition flex items-center justify-center">
          <i class="fa-solid fa-box mr-2"></i>Track Order
        </button>
        <button onclick="window.location.href='../user/user-homepage.php'"
                class="border border-green-700 text-green-700 px-6 py-2 rounded-lg hover:bg-green-50 transition flex items-center justify-center">
          <i class="fa-solid fa-store mr-2"></i>Continue Shopping
        </button>
      </div>

      <!-- Help Section -->
      <div class="mt-10 border-t border-gray-200 pt-6 text-gray-600 text-sm">
        <p class="mb-2">Need help with your order?</p>
        <p>
          <i class="fa-solid fa-phone text-green-700"></i> Call Support: (555) 123-4567 &nbsp; | &nbsp;
          <i class="fa-solid fa-envelope text-green-700"></i>
          <a href="mailto:support@farmersmail.com" class="text-green-700 hover:underline">support@farmersmail.com</a>
        </p>
      </div>
    </div>
  </main>

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

  <!-- Script to load footer -->
  <script>
    fetch('footer.php')
      .then(res => res.text())
      .then(data => document.getElementById('footer').innerHTML = data);
  </script>

</body>
</html>
