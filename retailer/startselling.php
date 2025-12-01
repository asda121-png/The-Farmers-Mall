<?php
// PHP SCRIPT START - RETAILER REGISTRATION WITH SUPABASE
session_start();

// Load Supabase API
require_once __DIR__ . '/../config/supabase-api.php';

$registration_status = '';
$registration_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retailer_signup'])) {
    try {
        $api = getSupabaseAPI();
        
        // Collect form data
        $firstName = trim($_POST['firstname'] ?? '');
        $lastName = trim($_POST['lastname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $shopName = trim($_POST['shop_name'] ?? '');
        $shopAddress = trim($_POST['shop_address'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);
        
        $errors = [];
        
        // Validation
        if (empty($firstName)) $errors[] = "First name is required.";
        if (empty($lastName)) $errors[] = "Last name is required.";
        if (!preg_match('/^09\d{9}$/', $phone)) $errors[] = "Invalid phone number (must be 09XXXXXXXXX).";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
        if (empty($shopName)) $errors[] = "Shop name is required.";
        if (empty($shopAddress)) $errors[] = "Shop address is required.";
        if (empty($username)) $errors[] = "Username is required.";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
        if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";
        if (!$terms) $errors[] = "You must agree to the Terms and Conditions.";
        
        // Check if email already exists
        if (empty($errors)) {
            $existingUser = $api->select('users', ['email' => $email]);
            if (!empty($existingUser)) {
                $errors[] = "Email already registered.";
            }
            
            // Check if username already exists
            $existingUsername = $api->select('users', ['username' => $username]);
            if (!empty($existingUsername)) {
                $errors[] = "Username already taken.";
            }
        }
        
        if (empty($errors)) {
            // Create user account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $fullName = $firstName . ' ' . $lastName;
            
            $newUser = $api->insert('users', [
                'email' => $email,
                'username' => $username,
                'password_hash' => $hashedPassword,
                'full_name' => $fullName,
                'phone' => $phone,
                'user_type' => 'retailer',
                'status' => 'active'
            ]);
            
            if (!empty($newUser)) {
                $userId = $newUser[0]['id'];
                
                // Create retailer profile
                $retailerData = [
                    'user_id' => $userId,
                    'shop_name' => $shopName,
                    'business_address' => $shopAddress,
                    'verification_status' => 'pending',
                    'rating' => 0.00,
                    'total_sales' => 0.00
                ];
                
                $newRetailer = $api->insert('retailers', $retailerData);
                
                if (!empty($newRetailer)) {
                    // Success - redirect to login with success message
                    $registration_status = 'success';
                    header('Location: ../auth/login.php?registered=success&type=retailer');
                    exit();
                } else {
                    $errors[] = "Failed to create retailer profile.";
                }
            } else {
                $errors[] = "Failed to create user account.";
            }
        }
        
        if (!empty($errors)) {
            $registration_status = 'error';
            $registration_message = implode(" | ", $errors);
        }
        
    } catch (Exception $e) {
        error_log("Retailer Registration Error: " . $e->getMessage());
        $registration_status = 'error';
        $registration_message = "A system error occurred. Please try again.";
    }
}
// PHP SCRIPT END
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farmer's Market</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>

<style>
   /* How To Start Selling Section */
        .how-to-sell {
            padding: 80px 0;
            justify-content: center;
            align-content: center;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
        }
   .step-card {
            padding: 20px;
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .step-card:nth-child(1) { animation-delay: 0.1s; }
        .step-card:nth-child(2) { animation-delay: 0.2s; }
        .step-card:nth-child(3) { animation-delay: 0.3s; }
        .step-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-icon-wrapper {
            background-color: var(--color-secondary);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            border: 1px solid var(--color-accent-light);
        }

        .step-icon-wrapper i {
            font-size: 24px;
            color: white;
        }

        .step-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .step-description {
            font-size: 13px;
            color: #666;
        }

        /* Why Sell With Us Section */
        .benefit-card {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .benefit-card:nth-child(1) { animation-delay: 0.1s; }
        .benefit-card:nth-child(2) { animation-delay: 0.2s; }
        .benefit-card:nth-child(3) { animation-delay: 0.3s; }
        .benefit-card:nth-child(4) { animation-delay: 0.4s; }
        .benefit-card:nth-child(5) { animation-delay: 0.5s; }
        .benefit-card:nth-child(6) { animation-delay: 0.6s; }

        /* Styles for multi-step form */
        .form-step { display: none; }
        .form-step.active { display: block; }
        .form-input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; margin-bottom: 1rem; outline: none; transition: border-color 0.2s; }
        .form-input:focus { border-color: #15803d; }
        /* Custom style for file input */
        .form-input[type="file"] { padding: 0.5rem; }
        .form-input.error { border-color: #dc2626; } /* red-600 */
        /* Style for terms and conditions */
        .terms-box { height: 100px; overflow-y: auto; border: 1px solid #d1d5db; padding: 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; color: #4b5563; background-color: #f9fafb; margin-bottom: 1rem; }

        /* NEW: Styles for fixed-height form */
        .form-step.active { display: flex; flex-direction: column; flex-grow: 1; }
        .step-content { flex-grow: 1; }
</style>

<body class="bg-gray-50 font-sans">

  <!-- Header -->
<?php
// Include the header
include '../includes/header1.php';
?>

  <!-- Hero / Sign Up Section -->
  <section class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between py-16 px-6">
    <div class="md:w-1/2 space-y-6">
      <h2 class="text-3xl font-bold text-gray-900">Farmer's Market</h2>
      <h3 class="text-2xl font-semibold text-green-700">Grow Your Harvest and Sell More</h3>
      <p class="text-gray-600">Join our community of local growers and producers. Reach more customers and cultivate your business with us.</p>
      <ul class="space-y-2">
        <li class="flex items-center"><span class="text-green-700 mr-2">&#10003;</span>Leading platform for local, fresh produce.</li>
        <li class="flex items-center"><span class="text-green-700 mr-2">&#10003;</span>Connecting you with a wider customer base.</li>
        <li class="flex items-center"><span class="text-green-700 mr-2">&#10003;</span>Easy management via web and mobile app.</li>
      </ul>
    </div>
    <div class="md:w-2/5 bg-white p-8 rounded-lg shadow-lg mt-10 md:mt-0 w-full">
      <!-- Error/Success Messages -->
      <?php if ($registration_status === 'error'): ?>
      <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-700 text-sm"><?= htmlspecialchars($registration_message) ?></p>
      </div>
      <?php endif; ?>
      
      <!-- UPDATED: Added flex classes and a fixed height -->
      <form id="retailer-signup-form" method="POST" class="flex flex-col h-[480px]">
        <input type="hidden" name="retailer_signup" value="1">
        <h4 class="text-xl font-semibold mb-1">Become a Seller</h4>
        <p id="step-indicator" class="text-sm text-gray-500 mb-6">Step 1 of 5: Personal Info</p>

        <div class="flex-grow">
          <!-- Step 1: Personal Info -->
          <div class="form-step active" data-step="1">
            <div class="step-content">
              <div class="flex gap-4 mb-4">
                <input type="text" name="firstname" id="firstname" placeholder="First Name" class="form-input !mb-0" required>
                <input type="text" name="lastname" id="lastname" placeholder="Last Name" class="form-input !mb-0" required>
              </div>
              <input type="text" name="phone" id="phone" placeholder="Mobile Number (09XXXXXXXXX)" class="form-input" pattern="09[0-9]{9}" required>
              <input type="email" name="email" id="email" placeholder="Email Address" class="form-input" required>
            </div>
            <button type="button" onclick="nextStep()" class="w-full bg-green-700 text-white py-3 rounded hover:bg-green-800">Next</button>
          </div>

          <!-- Step 2: Shop Details -->
          <div class="form-step" data-step="2">
            <div class="step-content">
              <input type="text" name="shop_name" id="shop_name" placeholder="Shop / Farm Name" class="form-input" required>
              <input type="text" name="shop_address" id="shop_address" placeholder="Shop Address" class="form-input" required>
            </div>
            <div class="flex gap-4">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300">Previous</button>
              <button type="button" onclick="nextStep()" class="w-1/2 bg-green-700 text-white py-3 rounded hover:bg-green-800">Next</button>
            </div>
          </div>

          <!-- Step 3: Upload Permit -->
          <div class="form-step" data-step="3">
            <div class="step-content">
              <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
              <input type="text" name="username" id="username" placeholder="Choose a username" class="form-input" required>
              <p class="text-xs text-gray-500 mb-4">This will be your unique identifier on the platform.</p>
            </div>
            <div class="flex gap-4">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300">Previous</button>
              <button type="button" onclick="nextStep()" class="w-1/2 bg-green-700 text-white py-3 rounded hover:bg-green-800">Next</button>
            </div>
          </div>

          <!-- Step 4: Create Account -->
          <div class="form-step" data-step="4">
            <div class="step-content">
              <input type="password" name="password" id="password" placeholder="Create Password" class="form-input" minlength="6" required>
              <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" class="form-input" minlength="6" required>
            </div>
            <div class="flex gap-4">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300">Previous</button>
              <button type="button" onclick="nextStep()" class="w-1/2 bg-green-700 text-white py-3 rounded hover:bg-green-800">Next</button>
            </div>
          </div>

          <!-- Step 5: Terms & Conditions -->
          <div class="form-step" data-step="5">
            <div class="step-content">
              <div class="terms-box">
                <p class="mb-2"><strong>1. Introduction</strong><br>Welcome to Farmers Mall. These Terms and Conditions govern your use of our platform as a seller. By creating a seller account, you agree to be bound by these terms.</p>
                <p class="mb-2"><strong>2. Seller Account</strong><br>You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account. You must provide accurate and complete information and keep it updated.</p>
                <p class="mb-2"><strong>3. Product Listings & Quality</strong><br>You agree that all product descriptions will be accurate. All produce must be fresh, of high quality, and comply with local food safety regulations. Misleading information may result in account suspension.</p>
                <p class="mb-2"><strong>4. Fees and Payments</strong><br>Listing products is free. Farmers Mall will charge a commission fee on each completed sale. This fee is subject to change upon prior notification. Payments will be processed according to our payment schedule.</p>
                <p class="mb-2"><strong>5. Termination</strong><br>Farmers Mall reserves the right to terminate or suspend your seller account at any time for conduct that violates these Terms and Conditions or is harmful to other users of the platform.</p>
              </div>
              <label class="flex items-center mb-4"><input type="checkbox" name="terms" id="terms-checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mr-2" required> I agree to the Terms and Conditions</label>
            </div>
            <div class="flex gap-4">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300">Previous</button>
              <button type="submit" class="w-1/2 bg-green-700 text-white py-3 rounded hover:bg-green-800">Create Account</button>
            </div>
          </div>
        </div>

        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-300"></div></div>
          <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-gray-500">Or</span></div>
        </div>

        <button type="button" class="w-full flex items-center justify-center gap-2 border border-gray-300 py-3 rounded hover:bg-gray-100">
          <i class="fab fa-google text-red-500"></i> Continue with Google
        </button>
        <p class="text-xs text-gray-400 mt-4 text-center">By signing up, you agree to Farmers Mall <a href="#" class="text-green-700">Terms of Service & Privacy Policy</a>. Have an account? <a href="../auth/login.php" class="text-green-700">Log in</a>.</p>
      </form>
    </div>
  </section>

  <!-- Why Sell With Us Section -->
  <section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-6">
      <h2 class="text-3xl font-bold text-center mb-12 text-gray-900">WHY SELL WITH US</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-dollar-sign text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">0% No Listing Fees</h4>
          <p class="text-gray-600 text-sm">List your fresh produce and artisan goods for free. We only succeed when you do.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-bullhorn text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">In-app Marketing Tools</h4>
          <p class="text-gray-600 text-sm">Utilize our built-in tools to promote your products and reach a targeted local audience.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-truck text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">Hassle-free Logistics</h4>
          <p class="text-gray-600 text-sm">We provide streamlined options for local delivery and customer pickup to simplify your operations.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-rocket text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">High-Impact Campaigns</h4>
          <p class="text-gray-600 text-sm">Participate in seasonal campaigns and promotions to boost your visibility and sales.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-headset text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">Extensive Seller Support</h4>
          <p class="text-gray-600 text-sm">Our dedicated team is here to help you get started and grow your online farm stand.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-users text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">Robust Seller Community</h4>
          <p class="text-gray-600 text-sm">Connect with other local producers, share insights, and grow together in our network.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- How to Start Selling Section -->
<section class="how-to-sell">
        <div class="container mx-auto px-6">
            <h2 class="section-title text-3xl font-bold text-center mb-12 text-gray-900">HOW TO START SELLING</h2>
            <div class="steps-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="step-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <div class="step-icon-wrapper bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-clipboard-list text-white text-2xl"></i>
                    </div>
                    <h3 class="step-title text-lg font-semibold mb-2 text-gray-800">Register Your Farm/Shop</h3>
                    <p class="step-description text-gray-600 text-sm">
                        Create your seller account and verify your farm or business details.
                    </p>
                </div>
                <div class="step-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <div class="step-icon-wrapper bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-box-open text-white text-2xl"></i>
                    </div>
                    <h3 class="step-title text-lg font-semibold mb-2 text-gray-800">List Your Products</h3>
                    <p class="step-description text-gray-600 text-sm">
                        Upload high-quality photos and descriptions of your fresh produce or artisan goods.
                    </p>
                </div>
                <div class="step-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <div class="step-icon-wrapper bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-shopping-cart text-white text-2xl"></i>
                    </div>
                    <h3 class="step-title text-lg font-semibold mb-2 text-gray-800">Receive Orders & Fulfill</h3>
                    <p class="step-description text-gray-600 text-sm">
                        Get notified of new orders and handle shipping or independent pickup or local delivery.
                    </p>
                </div>
                <div class="step-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <div class="step-icon-wrapper bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-hand-holding-usd text-white text-2xl"></i>
                    </div>
                    <h3 class="step-title text-lg font-semibold mb-2 text-gray-800">Get Paid & Grow</h3>
                    <p class="step-description text-gray-600 text-sm">
                        Receive payments securely and track your sales performance to grow your business.
                    </p>
                </div>
            </div>
        </div>
    </section>
  <!-- Support Section -->
  <section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-6 text-center">
      <h2 class="text-3xl font-bold mb-4">SUPPORT WHEN YOU NEED IT</h2>
      <p class="mb-6 text-gray-600">We’re committed to your success. Our Farmer’s Market platform provides a wealth of resources to help you every step of the way. From onboarding and setting up your shop to managing orders and marketing your products, we’ve got you covered. Access our comprehensive <a href="#" class="text-green-700">Seller Center</a>, browse the FAQ, join educational webinars, or reach out to our friendly <a href="#" class="text-green-700">Customer Service team</a>. We’re here to help you thrive.</p>
      <a href="#" class="bg-green-700 text-white px-6 py-3 rounded hover:bg-green-800">Explore Seller Center</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="text-white py-12 mt-16" style="background-color: #1B5E20;">
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
          <a href="#" class="hover:text-gray-300"><i class="fab fa-facebook"></i></a>
          <a href="#" class="hover:text-gray-300"><i class="fab fa-twitter"></i></a>
          <a href="#" class="hover:text-gray-300"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>

  <script>
    let currentStep = 1;
    const totalSteps = 5;
    const stepIndicator = document.getElementById('step-indicator');
    const stepTitles = [
      "Step 1 of 5: Personal Info", 
      "Step 2 of 5: Shop Details", 
      "Step 3 of 5: Username", 
      "Step 4 of 5: Create Password", 
      "Step 5 of 5: Terms & Conditions"
    ];

    function showStep(step) {
      document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
      const nextStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
      if (nextStepElement) {
        nextStepElement.classList.add('active');
        stepIndicator.textContent = stepTitles[step - 1];
      }
    }

    function validateStep(step) {
      const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
      let isValid = true;

      // Get all inputs within the current step
      const inputs = currentStepElement.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
      
      inputs.forEach(input => {
        input.classList.remove('error');
        if (input.value.trim() === '') {
          isValid = false;
          input.classList.add('error');
        }
      });

      // Step 1: Validate phone number
      if (step === 1) {
        const phone = document.getElementById('phone');
        if (!/^09\d{9}$/.test(phone.value)) {
          isValid = false;
          phone.classList.add('error');
          alert('Phone number must be in format: 09XXXXXXXXX');
        }
        
        const email = document.getElementById('email');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
          isValid = false;
          email.classList.add('error');
          alert('Please enter a valid email address');
        }
      }

      // Step 4: Password confirmation
      if (step === 4) {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password.value.length < 6) {
          isValid = false;
          password.classList.add('error');
          alert('Password must be at least 6 characters');
        }
        
        if (password.value !== confirmPassword.value) {
          isValid = false;
          password.classList.add('error');
          confirmPassword.classList.add('error');
          alert('Passwords do not match');
        }
      }

      return isValid;
    }

    function nextStep() {
      if (validateStep(currentStep)) {
        if (currentStep < totalSteps) {
          currentStep++;
          showStep(currentStep);
        }
      }
    }

    function prevStep() {
      if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
      }
    }

    // Final form submission handler
    document.getElementById('retailer-signup-form').addEventListener('submit', function(event) {
      if (currentStep === totalSteps) {
        const termsCheckbox = document.getElementById('terms-checkbox');
        if (!termsCheckbox.checked) {
          event.preventDefault();
          alert('You must agree to the Terms and Conditions to create an account.');
          return false;
        }
        // Form will submit normally to PHP
      } else {
        event.preventDefault();
      }
    });
  </script>

</html>
