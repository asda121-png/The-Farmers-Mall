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
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto flex justify-between items-center py-4 px-6">
      <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
      <nav class="space-x-6">
        <a href="../public/index.php" class="text-gray-700 hover:text-green-700">Home</a>
        <a href="../public/about.php" class="text-gray-700 hover:text-green-700">About</a>
        <a href="../public/how.php" class="text-gray-700 hover:text-green-700">How It Works</a>
        <a href="../index.php#shop" class="text-gray-700 hover:text-green-700">Shop</a>
        <a href="../public/support.php" class="text-gray-700 hover:text-green-700">Support</a>
      </nav>
      <div class="space-x-2">
        <a href="../auth/register.php" class="bg-white border border-green-700 text-green-700 px-4 py-2 rounded-full hover:bg-green-700 hover:text-white">Sign Up</a>
        <a href="../auth/login.php" class="bg-green-700 text-white px-4 py-2 rounded-full hover:bg-green-800">Shop Now</a>
      </div>
    </div>
  </header>

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
      <!-- UPDATED: Added flex classes and a fixed height -->
      <form id="retailer-signup-form" class="flex flex-col h-[480px]">
        <h4 class="text-xl font-semibold mb-1">Become a Seller</h4>
        <p id="step-indicator" class="text-sm text-gray-500 mb-6">Step 1 of 5: Personal Info</p>

        <div class="flex-grow">
          <!-- Step 1: Personal Info -->
          <div class="form-step active" data-step="1">
            <div class="step-content">
              <div class="flex gap-4 mb-4">
                <input type="text" placeholder="First Name" class="form-input !mb-0">
                <input type="text" placeholder="Last Name" class="form-input !mb-0">
              </div>
              <input type="text" placeholder="Mobile Number" class="form-input">
              <input type="email" placeholder="Email Address" class="form-input">
            </div>
            <button type="button" onclick="nextStep()" class="w-full bg-green-700 text-white py-3 rounded hover:bg-green-800">Next</button>
          </div>

          <!-- Step 2: Shop Details -->
          <div class="form-step" data-step="2">
            <div class="step-content">
              <input type="text" placeholder="Shop / Farm Name" class="form-input">
              <input type="text" placeholder="Shop Address" class="form-input">
            </div>
            <div class="flex gap-4">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300">Previous</button>
              <button type="button" onclick="nextStep()" class="w-1/2 bg-green-700 text-white py-3 rounded hover:bg-green-800">Next</button>
            </div>
          </div>

          <!-- Step 3: Upload Permit -->
          <div class="form-step" data-step="3">
            <div class="step-content">
              <label for="permit-upload" class="block text-sm font-medium text-gray-700 mb-2">Upload Business Permit</label>
              <input type="file" id="permit-upload" class="form-input">
              <p class="text-xs text-gray-500 mb-4">Please upload a clear photo of your business or seller's permit.</p>
            </div>
            <div class="flex gap-4">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded hover:bg-gray-300">Previous</button>
              <button type="button" onclick="nextStep()" class="w-1/2 bg-green-700 text-white py-3 rounded hover:bg-green-800">Next</button>
            </div>
          </div>

          <!-- Step 4: Create Account -->
          <div class="form-step" data-step="4">
            <div class="step-content">
              <input type="password" placeholder="Create Password" class="form-input">
              <input type="password" placeholder="Confirm Password" class="form-input">
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
              <label class="flex items-center mb-4"><input type="checkbox" id="terms-checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mr-2"> I agree to the Terms and Conditions</label>
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
    const stepTitles = ["Step 1 of 5: Personal Info", "Step 2 of 5: Shop Details", "Step 3 of 5: Upload Permit", "Step 4 of 5: Create Account", "Step 5 of 5: Terms & Conditions"];

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
      const inputs = currentStepElement.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="file"]');
      
      inputs.forEach(input => {
        input.classList.remove('error'); // Clear previous errors
        if (input.value.trim() === '') {
          isValid = false;
          input.classList.add('error');
        }
      });

      // Special validation for password confirmation
      if (step === 4) {
        const password = currentStepElement.querySelector('input[placeholder="Create Password"]');
        const confirmPassword = currentStepElement.querySelector('input[placeholder="Confirm Password"]');
        if (password.value !== confirmPassword.value) {
          isValid = false;
          password.classList.add('error');
          confirmPassword.classList.add('error');
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

    // Final form submission validation
    document.getElementById('retailer-signup-form').addEventListener('submit', function(event) {
      event.preventDefault(); // Stop form submission to handle it with JavaScript

      const termsCheckbox = document.getElementById('terms-checkbox');
      if (!termsCheckbox.checked) {
        alert('You must agree to the Terms and Conditions to create an account.');
      } else {
        // On successful validation, redirect to the login page.
        window.location.href = '../auth/login.php';
      }
    });
  </script>

</html>
