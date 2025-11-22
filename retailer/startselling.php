<?php
// Set error reporting to display errors during development (disable in production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Configuration for Database Connection
// NOTE: This setup uses common local development credentials (root/no password).
// For production, you must use a strong password, non-root user, and better error handling.
$host = 'localhost';
$db_name = 'farmers';
$username = 'root';
$password = ''; 

// Database Connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection and display a tailored error message if it fails
if ($conn->connect_error) {
    // In a real application, you would log this error and show a generic message.
    die("Database connection failed. Please ensure the 'farmers' database exists: " . $conn->connect_error);
}

$message = ''; // Message to be displayed to the user (e.g., success, error)

// --- Handle Sign Up Form Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup_submit'])) {
    // 1. Sanitize and retrieve form data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $user_input_username = trim($_POST['username'] ?? ''); // Renamed to avoid conflict with DB credential var
    $rawPassword = $_POST['password'] ?? '';

    // 2. Validate essential fields
    if (empty($firstName) || empty($email) || empty($rawPassword) || empty($phone) || empty($user_input_username)) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">Please fill out all required fields.</div>';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">Please enter a valid email address.</div>';
    } else {
        // 3. Hash the password for secure storage
        $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);

        // 4. Set default and fixed values
        $role = 'retailer'; // Always set role to 'retailer' as requested
        $status = 'active'; 
        $joinedAt = date('Y-m-d H:i:s');
        $address = 'N/A'; // Placeholder since address is not collected in this simple form

        // 5. Check if user already exists (email or username)
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt_check->bind_param("ss", $email, $user_input_username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4" role="alert">An account with that email or username already exists.</div>';
        } else {
            // 6. Insert new user using prepared statement
            // Assuming the 'users' table structure matches these columns
            $sql = "INSERT INTO users (first_name, last_name, email, address, status, role, username, phone, password_hash, joined_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            // "s" represents string types
            $stmt->bind_param("ssssssssss", $firstName, $lastName, $email, $address, $status, $role, $user_input_username, $phone, $passwordHash, $joinedAt);

                if ($stmt->execute()) {
                    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">Registration successful! You are now a registered Retailer.</div>';
                    // Use POST-Redirect-GET pattern for success to prevent resubmission
                  header("Location: ../auth/login.php");
                exit();
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">Error during registration: ' . $stmt->error . '</div>';
            }

            $stmt->close();
        }
        $stmt_check->close();
    }
} 
// Handle successful redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    // Clear POST data from previous successful submission
    $_POST = array(); 
    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">Registration successful! You are now a registered Retailer.</div>';
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farmer's Mall | Register as Retailer</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* Custom Green Color Palette (based on deep forest green) */
    .bg-green-700 { background-color: #1B5E20; }
    .hover\:bg-green-800:hover { background-color: #0A3D0B; }
    .text-green-700 { color: #1B5E20; }
    .border-green-700 { border-color: #1B5E20; }
    .text-green-300 { color: #81C784; }
  </style>
</head>
<body class="bg-gray-50 font-sans">

  <!-- Header -->
  <header class="sticky top-0 z-10 bg-white shadow-md">
    <div class="max-w-7xl mx-auto flex justify-between items-center py-4 px-6">
      <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
      <nav class="space-x-6 hidden md:flex">
        <a href="../index.php" class="text-gray-700 hover:text-green-700 transition duration-150">Home</a>
        <a href="#about" class="text-gray-700 hover:text-green-700 transition duration-150">About</a>
        <a href="#how" class="text-gray-700 hover:text-green-700 transition duration-150">How It Works</a>
        <a href="#support" class="text-gray-700 hover:text-green-700 transition duration-150">Support</a>
      </nav>
      <div class="space-x-2 hidden sm:block">
       
        <a href="../auth/login.php" class="bg-green-700 text-white px-4 py-2 rounded-full hover:bg-green-800 transition duration-150">Log In</a>
      </div>
    </div>
  </header>

  <!-- Hero / Sign Up Section -->
  <section id="home" class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between py-16 px-6">
    <div class="md:w-1/2 space-y-6">
      <h2 class="text-4xl lg:text-5xl font-extrabold text-gray-900 leading-tight">Farmer's Market</h2>
      <h3 class="text-2xl lg:text-3xl font-semibold text-green-700">Grow Your Harvest and Sell More</h3>
      <p class="text-lg text-gray-600">Join our community of local growers and producers. Reach more customers and cultivate your business with us.</p>
      <ul class="space-y-3">
        <li class="flex items-center text-gray-700"><span class="text-green-700 text-xl mr-2"><i class="fas fa-seedling"></i></span> Leading platform for local, fresh produce.</li>
        <li class="flex items-center text-gray-700"><span class="text-green-700 text-xl mr-2"><i class="fas fa-users"></i></span> Connecting you with a wider customer base.</li>
        <li class="flex items-center text-gray-700"><span class="text-green-700 text-xl mr-2"><i class="fas fa-mobile-alt"></i></span> Easy management via web and mobile app.</li>
      </ul>
      <a href="#register-form" class="mt-8 inline-block bg-green-700 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-green-800 transition duration-150 shadow-xl">Start Selling Today</a>
    </div>
    
    <!-- SIGN UP FORM (PHP INTEGRATION) -->
    <div id="register-form" class="md:w-2/5 bg-white p-6 md:p-8 rounded-xl shadow-2xl mt-10 md:mt-0 w-full transform transition duration-500 hover:scale-[1.01]">
      <h4 class="text-2xl font-bold text-center mb-6 text-gray-800">Sign Up as Retailer</h4>
      
      <!-- Display PHP Message -->
      <?php echo $message; ?>

      <!-- FIX: action="" submits the form back to this same PHP file (register.php) -->
      <form method="POST" action="">
        <div class="space-y-4">
          <div class="flex space-x-2">
            <!-- Ensure fields retain values on non-fatal error -->
            <input type="text" name="first_name" placeholder="First Name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-700 focus:border-green-700 transition duration-150" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
            <input type="text" name="last_name" placeholder="Last Name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-700 focus:border-green-700 transition duration-150" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
          </div>
          <input type="email" name="email" placeholder="Email Address" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-700 focus:border-green-700 transition duration-150" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
          <input type="text" name="username" placeholder="Choose Username" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-700 focus:border-green-700 transition duration-150" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
          <input type="tel" name="phone" placeholder="Mobile Number" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-700 focus:border-green-700 transition duration-150" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
          <input type="password" name="password" placeholder="Create Password" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-700 focus:border-green-700 transition duration-150" required>

          <button type="submit" name="signup_submit" class="w-full bg-green-700 text-white py-3 rounded-lg font-semibold hover:bg-green-800 transition duration-150 shadow-md">Sign Up Now</button>
        </div>
      </form>

      <div class="my-4 text-center text-sm text-gray-500">OR</div>

      <button class="w-full flex items-center justify-center gap-2 border border-gray-300 py-3 rounded-lg mb-2 hover:bg-gray-100 transition duration-150">
        <i class="fab fa-facebook text-blue-600"></i> Continue with Facebook
      </button>
      <button class="w-full flex items-center justify-center gap-2 border border-gray-300 py-3 rounded-lg hover:bg-gray-100 transition duration-150">
        <i class="fab fa-google text-red-500"></i> Continue with Google
      </button>
      <p class="text-xs text-gray-400 mt-4 text-center">By signing up, you agree to The Farmer's Mall <a href="#" class="text-green-700 hover:underline">Terms of Service & Privacy Policy</a>. Have an account? <a href="#" class="text-green-700 hover:underline">Log in</a>.</p>
    </div>
  </section>

  <!-- Why Sell With Us Section -->
  <section id="about" class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-6">
      <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">WHY SELL WITH US</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- Card 1: Reach More Customers -->
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border-t-4 border-indigo-500">
          <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-chart-line w-6 h-6 mr-2 text-indigo-500"></i>
            Expanded Market Reach
          </h2>
          <p class="text-gray-600">Instantly connect with thousands of local retailers and consumers searching for fresh, high-quality local produce.</p>
        </div>

        <!-- Card 2: Simple Management -->
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border-t-4 border-purple-500">
          <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-tablet-alt w-6 h-6 mr-2 text-purple-500"></i>
            Easy-to-Use Platform
          </h2>
          <p class="text-gray-600">Manage your inventory, process orders, and track payments all from a simple, intuitive dashboard optimized for farmers.</p>
        </div>

        <!-- Card 3: Fair Pricing -->
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border-t-4 border-blue-500">
          <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-hand-holding-usd w-6 h-6 mr-2 text-blue-500"></i>
            Maximize Your Profit
          </h2>
          <p class="text-gray-600">Sell directly to customers, cutting out the middlemen and ensuring you receive fair prices for your hard work and harvest.</p>
        </div>

        <!-- Card 4: Community Support -->
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border-t-4 border-green-500">
          <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-tractor w-6 h-6 mr-2 text-green-500"></i>
            Dedicated Support
          </h2>
          <p class="text-gray-600">Access resources, tutorials, and a friendly support team focused on helping agricultural businesses thrive in the digital age.</p>
        </div>

        <!-- Card 5: Sustainable Focus -->
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border-t-4 border-yellow-500">
          <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-leaf w-6 h-6 mr-2 text-yellow-500"></i>
            Emphasize Sustainability
          </h2>
          <p class="text-gray-600">Showcase your sustainable farming practices and local produce to a rapidly growing environmentally conscious consumer base.</p>
        </div>

        <!-- Card 6: Flexibility -->
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border-t-4 border-red-500">
          <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-calendar-alt w-6 h-6 mr-2 text-red-500"></i>
            Flexible Scheduling
          </h2>
          <p class="text-gray-600">Set your own availability for pickup or delivery, ensuring the platform works around your harvest and schedule.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- How to Start Selling Section -->
  <section id="how" class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-6">
      <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">HOW TO START SELLING</h2>
    <!-- Corrected Grid Definition: Used grid-cols-1 for mobile and md:grid-cols-4 for desktop -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-y-12 md:gap-x-12 text-center">
          
          <!-- Step 1 -->
          <div class="relative pb-10">
            <div class="w-16 h-16 bg-green-700 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold shadow-xl transition transform hover:scale-105 hover:bg-green-600">1</div>
            <h4 class="font-semibold text-lg mb-2 text-gray-800">Register Your Farm/Shop</h4>
            <p class="text-gray-600 text-sm">Create your seller account and verify your farm or business details.</p>
            
            <!-- Arrow Connector -->
            <div class="absolute right-[-25%] top-8 hidden md:block z-10 pointer-events-none">
              <i class="fas fa-arrow-right text-green-300 text-4xl"></i>
            </div>
            <!-- Vertical divider for mobile layout -->
            <div class="absolute left-1/2 -ml-0.5 bottom-0 h-1/4 w-0.5 bg-green-300 md:hidden"></div>
          </div>
          
          <!-- Step 2 -->
          <div class="relative pb-10">
            <div class="w-16 h-16 bg-green-700 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold shadow-xl transition transform hover:scale-105 hover:bg-green-600">2</div>
            <h4 class="font-semibold text-lg mb-2 text-gray-800">List Your Products</h4>
            <p class="text-gray-600 text-sm">Upload high-quality photos and descriptions of your fresh produce or artisan goods.</p>
            
            <!-- Arrow Connector -->
            <div class="absolute right-[-25%] top-8 hidden md:block z-10 pointer-events-none">
              <i class="fas fa-arrow-right text-green-300 text-4xl"></i>
            </div>
            <!-- Vertical divider for mobile layout -->
            <div class="absolute left-1/2 -ml-0.5 bottom-0 h-1/4 w-0.5 bg-green-300 md:hidden"></div>
          </div>
          
          <!-- Step 3 -->
          <div class="relative pb-10">
            <div class="w-16 h-16 bg-green-700 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold shadow-xl transition transform hover:scale-105 hover:bg-green-600">3</div>
            <h4 class="font-semibold text-lg mb-2 text-gray-800">Receive Orders & Fulfill</h4>
            <p class="text-gray-600 text-sm">Get notified of new orders, and prepare them for convenient pickup or local delivery.</p>
            
            <!-- Arrow Connector -->
            <div class="absolute right-[-25%] top-8 hidden md:block z-10 pointer-events-none">
              <i class="fas fa-arrow-right text-green-300 text-4xl"></i>
            </div>
            <!-- Vertical divider for mobile layout -->
            <div class="absolute left-1/2 -ml-0.5 bottom-0 h-1/4 w-0.5 bg-green-300 md:hidden"></div>
          </div>
          
          <!-- Step 4 (Last Step - No Arrow) -->
          <div class="relative pb-10">
            <div class="w-16 h-16 bg-green-700 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold shadow-xl transition transform hover:scale-105 hover:bg-green-600">4</div>
            <h4 class="font-semibold text-lg mb-2 text-gray-800">Get Paid & Grow</h4>
            <p class="text-gray-600 text-sm">Receive payments securely and track your sales performance to grow your business.</p>
          </div>
      </div>  
    </div>
  </section>

  <!-- Support Section -->
  <section id="support" class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-6 text-center">
      <h2 class="text-4xl font-bold mb-4 text-gray-800">SUPPORT WHEN YOU NEED IT</h2>
      <p class="mb-8 text-lg text-gray-600 max-w-3xl mx-auto">We’re committed to your success. Our Farmer’s Market platform provides a wealth of resources to help you every step of the way. From onboarding and setting up your shop to managing orders and marketing your products, we’ve got you covered. Access our comprehensive <a href="#" class="text-green-700 hover:underline font-medium">Seller Center</a>, browse the FAQ, join educational webinars, or reach out to our friendly <a href="#" class="text-green-700 hover:underline font-medium">Customer Service team</a>. We’re here to help you thrive.</p>
      <a href="#" class="bg-green-700 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition duration-150 shadow-lg">Explore Seller Center</a>
    </div>
  </section>
  
<?php
    include '../includes/footer.html';
  ?>

</body>
</html>