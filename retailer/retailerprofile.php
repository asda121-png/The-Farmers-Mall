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
  <?php include 'retailerheader.php'; ?>

  <!-- Main Content -->
  <main class="max-w-7xl mx-auto px-6 py-10 flex gap-8 flex-grow mb-40">

    <!-- Sidebar -->
    <aside class="w-64 bg-white rounded-xl shadow p-6">
      <!-- Profile Info -->
      <div class="flex flex-col items-center text-center mb-6">
        <img id="sidebarProfilePic" src="https://randomuser.me/api/portraits/men/32.jpg" class="w-20 h-20 rounded-full mb-3 object-cover" alt="Profile">
        <h2 id="sidebarShopName" class="font-semibold">Shop Name</h2>
        <p id="sidebarEmail" class="text-gray-500 text-sm">email@example.com</p>
      </div>

      <!-- Navigation -->
      <nav class="space-y-2 text-sm font-medium">
        <a href="retailerprofile.php" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-green-50 text-green-700">
          <i class="fas fa-user"></i> My Profile
        </a>
        <a href="retailerorders.php" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">
          <i class="fas fa-box"></i> Orders
        </a>
        <a href="retailerproducts.php" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">
          <i class="fas fa-tag"></i> Products
        </a>
        <a href="retailerinventory.php" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">
          <i class="fas fa-warehouse"></i> Inventory
        </a>
      

        <!-- Logout Button -->
        <div class="border-t pt-2 mt-2">
          <button id="logoutBtn" class="w-full flex items-center gap-2 px-4 py-2 rounded-lg text-red-600 hover:bg-red-50">
            <i class="fas fa-sign-out-alt"></i> Logout
          </button>
        </div>
      </nav>
    </aside>

    <!-- Main Profile Form -->
    <div class="flex-1">
      <section id="my-profile" class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="font-semibold text-lg">My Profile</h2>
          <button id="editProfileBtn" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
            <i class="fas fa-pen"></i> Edit Profile
          </button>
        </div>

        <div>
          <form class="space-y-6">

            <!-- Profile Picture -->
            <div class="flex items-center gap-6">
              <div class="relative w-32 h-32 flex-shrink-0">
                <img id="profileImage" src="https://randomuser.me/api/portraits/men/32.jpg" class="w-32 h-32 rounded-full object-cover border-4 border-green-100" alt="Seller Profile" />
                <label for="imageUpload" id="changePictureBtn" class="absolute bottom-0 right-0 w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white border-4 border-white cursor-pointer hover:bg-green-700 transition">
                  <i class="fas fa-camera text-sm"></i>
                </label>
                <input type="file" id="imageUpload" class="hidden" accept="image/png, image/jpeg, image/gif" disabled/>
              </div>
              <div class="flex-1 space-y-2">
                <input type="text" id="shopName" placeholder="Example Farm" class="text-2xl font-bold bg-transparent border-none p-0 focus:ring-0 w-full placeholder-gray-400" disabled>
                <input type="text" id="shopAddress" placeholder="Mati, Davao Oriental" class="text-sm text-gray-500 bg-transparent border-none p-0 focus:ring-0 w-full placeholder-gray-400" disabled>
              </div>
            </div>

            <!-- Personal Info -->
            <div class="space-y-6 pt-6 border-t">
              <div class="grid md:grid-cols-2 gap-6">
                <!-- First Name -->
                <div class="info-block">
                  <div class="display-view bg-gray-50 p-4 rounded-lg"><label class="text-xs text-gray-500 uppercase tracking-wide">First Name</label><p id="displayFirstName" class="text-gray-800 font-medium mt-1">Juan</p></div>
                  <div class="edit-view hidden"><label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label><input type="text" id="firstName" placeholder="Juan" class="mt-1 w-full border rounded-md px-3 py-2"></div>
                </div>
                <!-- Last Name -->
                <div class="info-block">
                  <div class="display-view bg-gray-50 p-4 rounded-lg"><label class="text-xs text-gray-500 uppercase tracking-wide">Last Name</label><p id="displayLastName" class="text-gray-800 font-medium mt-1">Dela Cruz</p></div>
                  <div class="edit-view hidden"><label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label><input type="text" id="lastName" placeholder="Dela Cruz" class="mt-1 w-full border rounded-md px-3 py-2"></div>
                </div>
                <!-- Mobile Number -->
                <div class="info-block">
                  <div class="display-view bg-gray-50 p-4 rounded-lg"><label class="text-xs text-gray-500 uppercase tracking-wide">Mobile Number</label><p id="displayMobileNumber" class="text-gray-800 font-medium mt-1">09123456789</p></div>
                  <div class="edit-view hidden"><label for="mobileNumber" class="block text-sm font-medium text-gray-700">Mobile Number</label><input type="text" id="mobileNumber" placeholder="09123456789" class="mt-1 w-full border rounded-md px-3 py-2"></div>
                </div>
                <!-- Email Address -->
                <div class="info-block">
                  <div class="display-view bg-gray-50 p-4 rounded-lg"><label class="text-xs text-gray-500 uppercase tracking-wide">Email Address</label><p id="displayEmail" class="text-gray-800 font-medium mt-1">juan.delacruz@example.com</p></div>
                  <div class="edit-view hidden"><label for="email" class="block text-sm font-medium text-gray-700">Email Address</label><input type="email" id="email" placeholder="juan.delacruz@example.com" class="mt-1 w-full border rounded-md px-3 py-2"></div>
                </div>
              </div>
            </div>

            <!-- Business Permit Section -->
            <div class="space-y-6 pt-6 border-t">
              <h3 class="text-lg font-medium">Business Permit</h3>
              <div id="permitContainer">
                <button type="button" id="seePermitBtn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed">See Business Permit</button>
              </div>
            </div>


            <!-- Actions -->
            <div class="border-t pt-6 flex justify-end">
              <button type="submit" id="saveChangesBtn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 hidden">Save Changes</button>
            </div>

          </form>
        </div>

      </section>
    </div>
  </main>


  <!-- Logout Modal -->
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

  <!-- Save Changes Modal -->
  <div id="saveChangesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
      <div class="text-green-500 text-4xl mb-4"><i class="fa-solid fa-circle-question"></i></div>
      <h3 class="font-semibold text-lg mb-2">Confirm Changes</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelSaveChanges" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <button id="confirmSaveChanges" class="px-6 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Save</button>
      </div>
    </div>
  </div>

  <!-- Business Permit Modal -->
  <div id="permitModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg p-4 w-full max-w-2xl relative">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <h3 class="font-semibold text-lg">Business Permit</h3>
        <button id="closePermitModal" class="text-gray-500 hover:text-black text-2xl">&times;</button>
      </div>
      <div id="permitImageContainer" class="max-h-[70vh] overflow-auto">
        <!-- Image will be loaded here by JS -->
      </div>
    </div>
  </div>

  <script src="../assets/js/retailerprofile.js"></script>
  <!-- Footer -->
  <footer class="text-white py-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      
      <!-- Logo/About -->
      <div>
        <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>
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
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>
</body>
</html>
  