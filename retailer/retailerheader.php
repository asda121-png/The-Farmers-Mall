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
 <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="flex items-center space-x-3 cursor-pointer" onclick="window.location.href='retailerdashboard.php'"></div>
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
        <a href="retailerdashboard.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
        <a href="retailermessage.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
        <a href="retailernotifications.php" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
        <a href="retailerprofile.php">
          <img id="headerProfilePic" src="../images/karl.png" alt="User" class="w-8 h-8 rounded-full cursor-pointer">
        </a>
      </div>
    </div>
  </header>
</body>

</html>
