<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Profile – The Farmer’s Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="bg-white border-b shadow-sm sticky top-0 z-50">
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
        <form action="retailersearchresults.php" method="GET" class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="search" name="q" placeholder="Search orders, products, customers..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-green-600 focus:border-green-600 text-sm">
        </form>
      </div>

      <!-- Right: Icons & Profile -->
      <div class="flex items-center space-x-6">
        <a href="retailermessage.php" class="relative cursor-pointer">
          <i class="fa-regular fa-comment text-xl text-gray-600"></i>
          <span class="absolute -top-2 -right-2 bg-green-700 text-white text-xs font-semibold rounded-full px-1.5">3</span>
        </a>
        <a href="retailernotifications.php" class="relative cursor-pointer">
          <i class="fa-regular fa-bell text-xl text-gray-600"></i>
          <span class="absolute -top-2 -right-2 bg-green-700 text-white text-xs font-semibold rounded-full px-1.5">5</span>
        </a>
        <a href="retailerprofile.php" class="flex items-center space-x-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-8 h-8 rounded-full ring-2 ring-green-600" alt="Seller Profile">
          <div class="profile-info">
            <p class="text-sm font-medium text-gray-800">Mesa Farm</p>
            <p class="text-xs text-gray-500">Seller</p>
          </div>
        </a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-grow">
    <div class="max-w-4xl mx-auto px-6 py-8 mb-80">
      <!-- Title -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
        <button onclick="window.location.href='retailerdashboard.php'" class="text-gray-600 hover:text-black">
          <i class="fa-solid fa-arrow-left text-lg"></i>
        </button>
        <h2 class="text-lg font-semibold">My Account</h2>
        </div>
        <button type="button" id="editProfileBtn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700">Edit Profile</button>
      </div>

      <!-- Profile Form -->
      <div class="bg-white rounded-lg shadow-sm p-8">
        <form class="space-y-6">
          <!-- Profile Picture -->
          <div class="flex items-center space-x-4">
            <img id="profileImage" src="https://randomuser.me/api/portraits/men/32.jpg" class="w-16 h-16 rounded-full object-cover" alt="Seller Profile">
            <div>
              <button type="button" id="changePictureBtn" class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-md hover:bg-green-700" disabled>Change Picture</button>
              <p class="text-xs text-gray-500 mt-1">JPG, GIF or PNG. 1MB max.</p>
              <!-- Hidden file input -->
              <input type="file" id="imageUpload" class="hidden" accept="image/png, image/jpeg, image/gif" disabled>
            </div>
          </div>

          <!-- Shop Name -->
          <div>
            <label for="shopName" class="block text-sm font-medium text-gray-700">Shop Name</label>
            <input type="text" id="shopName" value="Mesa Farm" class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" disabled>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" id="email" value="mesafarm@example.com" class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" disabled>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <button type="button" id="changePasswordBtn" class="text-sm text-green-600 hover:underline mt-1" disabled>Change Password</button>
          </div>

          <!-- Form Actions -->
          <div class="border-t pt-6 flex justify-between items-center">
            <button type="button" id="logoutBtn" class="text-sm text-red-600 hover:underline">Logout</button>
            <button type="submit" id="saveChangesBtn" class="bg-green-600 text-white px-5 py-2 rounded-md text-sm font-medium hover:bg-green-700 hidden">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="text-white py-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      <div><h3 class="font-bold text-lg mb-3">Farmers Mall</h3><p class="text-gray-300 text-sm">Fresh, organic produce delivered straight to your home from local farmers.</p></div>
      <div><h3 class="font-bold text-lg mb-3">Quick Links</h3><ul class="space-y-2 text-sm text-gray-300"><li><a href="#" class="hover:underline">About Us</a></li><li><a href="#" class="hover:underline">Contact</a></li><li><a href="#" class="hover:underline">FAQ</a></li><li><a href="#" class="hover:underline">Support</a></li></ul></div>
      <div><h3 class="font-bold text-lg mb-3">Categories</h3><ul class="space-y-2 text-sm text-gray-300"><li><a href="#" class="hover:underline">Vegetables</a></li><li><a href="#" class="hover:underline">Fruits</a></li><li><a href="#" class="hover:underline">Dairy</a></li><li><a href="#" class="hover:underline">Meat</a></li></ul></div>
      <div><h3 class="font-bold text-lg mb-3">Follow Us</h3><div class="flex space-x-4 text-xl"><a href="#"><i class="fab fa-facebook"></i></a><a href="#"><i class="fab fa-twitter"></i></a><a href="#"><i class="fab fa-instagram"></i></a></div></div>
    </div>
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">© 2025 Farmers Mall. All rights reserved.</div>
  </footer>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
      <div class="text-red-500 text-4xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-semibold text-lg mb-2">Confirm Logout</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelLogout" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <a href="../auth/login.php" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Logout</a>
      </div>
    </div>
  </div>

  <!-- Save Changes Confirmation Modal -->
  <div id="saveChangesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
      <div class="text-green-500 text-4xl mb-4"><i class="fa-solid fa-circle-question"></i></div>
      <h3 class="font-semibold text-lg mb-2">Confirm Changes</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure you want to save these changes?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelSaveChanges" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <button id="confirmSaveChanges" class="px-6 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Save</button>
      </div>
    </div>
  </div>

  <script src="../js/retailerprofile.js"></script>

</body>
</html>
