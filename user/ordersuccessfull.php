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
  </style>
</head>

<body class="bg-gray-50 text-gray-800">

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
        <a href="cart.php" class="text-gray-600"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="profile.php">
          <img id="headerProfilePic" src="../images/karl.png" alt="User" class="w-8 h-8 rounded-full cursor-pointer">
        </a>
      </div>
    </div>
  </header>


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
