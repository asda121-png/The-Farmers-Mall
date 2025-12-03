<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Method – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* 🎨 Brand Colors */
    .visa { color: #1A1F71; }          /* Visa Blue */
    .mastercard { color: #EB001B; }    /* Mastercard Red */
    .amex { color: #2E77BB; }          /* American Express Blue */
    .gpay { color: #4285F4; }          /* Google Pay Blue */
    .paypal { color: #003087; }        /* PayPal Deep Blue */

    /* Style to make an element invisible but still occupy space */
    .invisible-placeholder {
      visibility: hidden;
      opacity: 0;
      pointer-events: none;
    }
  </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">

  <!-- HEADER -->
   <!-- Navbar -->
   <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <h1 class="text-xl font-bold" style="color: #2E7D32;">Farmers Mall</h1>

      <div class="flex-1 mx-6">
        <form action="products.php" method="GET">
          <input 
            type="text" 
            name="search"
            placeholder="Search for fresh produce..."
            class="w-full px-4 py-2 border rounded-full focus:ring-2 focus:ring-green-500 focus:outline-none"
          />
        </form>
      </div>

      <div class="flex items-center space-x-6">
        <a href="../user/user-homepage.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
        <a href="message.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
        <a href="notification.php" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
        <a href="cart.php" class="text-gray-600"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="profile.php">
          <div id="headerProfilePic" class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
            <i class="fas fa-user text-white text-sm"></i>
          </div>
        </a>
      </div>
    </div>
  </header>

  <!-- PAYMENT SECTION -->
  <main class="max-w-7xl mx-auto p-6 flex flex-col lg:flex-row gap-8 mt-6 flex-grow w-full mb-20">
    
    <!-- LEFT SIDE: Payment Form -->
    <section class="flex-1 bg-white p-6 rounded-xl shadow-sm min-h-[400px]">
      <h2 class="text-2xl font-semibold mb-6">Payment Details</h2>

      <!-- Payment Method Options -->
      <div class="space-y-3 mb-6">
        <!-- Card -->
        <label class="flex items-center justify-between border rounded-lg p-4 cursor-pointer hover:border-green-500">
          <div class="flex items-center gap-3">
            <input type="radio" name="payment" value="card" checked class="accent-green-600">
            <span class="font-medium">Credit/Debit Card</span>
          </div>
          <div class="flex items-center gap-2">
            <i class="fa-brands fa-cc-visa text-2xl visa"></i>
            <i class="fa-brands fa-cc-mastercard text-2xl mastercard"></i>
            <i class="fa-brands fa-cc-amex text-2xl amex"></i>
          </div>
        </label>

        <!-- Digital Wallets -->
        <label class="flex items-center justify-between border rounded-lg p-4 cursor-pointer hover:border-green-500">
          <div class="flex items-center gap-3">
            <input type="radio" name="payment" value="wallet" class="accent-green-600">
            <span class="font-medium">Digital Wallets</span>
          </div>
          <div class="flex items-center gap-3">
            <i class="fa-brands fa-google-pay text-2xl gpay"></i>
            <i class="fa-brands fa-paypal text-2xl paypal"></i>
          </div>
        </label>

        <!-- COD -->
        <label class="flex items-center border rounded-lg p-4 cursor-pointer hover:border-green-500">
          <input type="radio" name="payment" value="cod" class="accent-green-600 mr-3">
          <div>
            <span class="font-medium">Cash on Delivery</span>
            <p class="text-sm text-gray-500">Pay when your order arrives</p>
          </div>
        </label>
      </div>

      <!-- BILLING ADDRESS -->
      <div class="mt-6">
        <label class="flex items-center space-x-2">
          <input type="checkbox" checked class="accent-green-600">
          <span class="text-sm text-gray-700">Same as shipping address</span>
        </label>
      </div>

      <!-- SECURE PAYMENT NOTICE -->
      <div class="mt-6 p-4 bg-green-50 text-green-800 text-sm rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-lock"></i>
        <span>Your payment is secured — protected by 256-bit SSL encryption</span>
      </div>

      <!-- CARD INFORMATION -->
      <div id="card-info" class="space-y-4 transition-opacity duration-300">
        <div>
          <label class="block text-sm text-gray-700 mb-1">Card Number</label>
          <input type="text" placeholder="1234 5678 9012 3456" 
                 class="w-full border px-3 py-2 rounded-md focus:ring-green-500 focus:ring-1 outline-none">
        </div>

        <div>
          <label class="block text-sm text-gray-700 mb-1">Cardholder Name</label>
          <input type="text" placeholder="John Doe" 
                 class="w-full border px-3 py-2 rounded-md focus:ring-green-500 focus:ring-1 outline-none">
        </div>

        <div class="flex gap-4">
          <div class="flex-1">
            <label class="block text-sm text-gray-700 mb-1">Expiry Date</label>
            <input type="text" placeholder="MM/YY"
                   class="w-full border px-3 py-2 rounded-md focus:ring-green-500 focus:ring-1 outline-none">
          </div>
          <div class="flex-1">
            <label class="block text-sm text-gray-700 mb-1 flex items-center gap-1">
              CVV <i class="fa-regular fa-circle-question text-gray-400"></i>
            </label>
            <input type="text" placeholder="123"
                   class="w-full border px-3 py-2 rounded-md focus:ring-green-500 focus:ring-1 outline-none">
          </div>
        </div>
      </div>

    </section>

    <!-- RIGHT SIDE: Order Summary -->
    <aside class="bg-white shadow-sm p-6 rounded-xl w-full lg:w-80 h-fit">
      <h3 class="font-semibold text-lg mb-4">Order Summary</h3>

      <div id="orderItems" class="space-y-3 mb-4"></div>

      <div class="text-sm text-gray-700 space-y-2">
        <div class="flex justify-between"><span>Subtotal</span><span id="subtotal">₱0.00</span></div>
        <div class="flex justify-between"><span>Shipping</span><span>Free</span></div>
      </div>

      <div class="border-t mt-3 pt-3 flex justify-between text-lg font-semibold text-gray-800">
        <span>Total</span><span id="total">₱0.00</span>
      </div>

      <button id="placeOrderBtn"
              class="bg-green-600 w-full text-white py-3 rounded-md mt-6 font-medium hover:bg-green-700 transition">
        Place Order
      </button>
    </aside>
  </main>

  <!-- FOOTER -->
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

  <script src="../assets/js/paymentmethod.js"></script>
  <script>
    // Load User Profile Data
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

    // Listen for profile updates
    window.addEventListener('storage', (e) => {
      if (e.key === 'userProfile') {
        loadUserProfile();
      }
    });

    // Listen for profile updates in same tab
    window.addEventListener('profileUpdated', () => {
      loadUserProfile();
    });
  </script>

</body>
</html>
