<?php
// PHP SCRIPT START - SERVER-SIDE REGISTRATION WITH MYSQL INTEGRATION

// --- MySQL Connection Details (Based on user input) ---
// WARNING: In a production environment, these should never be hardcoded 
// and should be loaded from a secure configuration file outside the web root.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // No password as specified by the user
define('DB_NAME', 'farmers');

$registration_status = '';
$registration_message = '';

/**
 * Establishes a connection to the MySQL database.
 * @return mysqli|null The database connection object or null on failure.
 */
function connectToDatabase() {
    // The @ suppresses warnings/errors, we handle the error via $conn->connect_error below
    $conn = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        // In a real app, this would log to a file, not output directly.
        error_log("Connection failed: " . $conn->connect_error);
        return null;
    }
    return $conn;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submitted'])) {
    // 1. Connect to Database
    $conn = connectToDatabase();
    
    if (!$conn) {
        $registration_status = 'error';
        $registration_message = "Registration failed: Database connection failed. Please ensure MySQL is running and database '" . DB_NAME . "' exists.";
    } else {
        // 2. Collect and Sanitize Data
        // Use real_escape_string for safety, although prepared statements below are the primary defense.
        $firstName = $conn->real_escape_string(trim($_POST['firstname'] ?? ''));
        $lastName = $conn->real_escape_string(trim($_POST['lastname'] ?? ''));
        $email = $conn->real_escape_string(filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL));
        $username = $conn->real_escape_string(trim($_POST['username'] ?? ''));
        $address = $conn->real_escape_string(trim($_POST['address'] ?? ''));
        $phone = $conn->real_escape_string(trim($_POST['phone'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $terms = isset($_POST['terms']);
        
        $errors = [];

        // 3. Server-Side Validation
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }
        if (!preg_match('/^09\d{9}$/', $phone)) {
            $errors[] = "Invalid phone number format.";
        }
        if (!$terms) {
            $errors[] = "You must agree to the Terms and Privacy Policy.";
        }
        
        // IMPORTANT: In a production app, you would check here to see if the username/email already exists.
        
        if (empty($errors)) {
            // 4. Set Static Role as requested
            $role = 'customer';
            
            // 5. Hash the password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // --- MYSQL INSERTION USING PREPARED STATEMENTS (Assumes 'users' table exists) ---
            // NOTE: Must ensure 8 columns match 8 placeholders in VALUES
            $sql = "INSERT INTO users (first_name, last_name, email, role, username, address, phone, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Prepare statement
            if ($stmt = $conn->prepare($sql)) {
                // Bind parameters (s = string)
                // Binding 8 variables: $firstName, $lastName, $email, $role, $username, $address, $phone, $hashedPassword
                $stmt->bind_param("ssssssss", 
                    $firstName, 
                    $lastName, 
                    $email, 
                    $role,             // <--- The static 'customer' role
                    $username, 
                    $address, 
                    $phone, 
                    $hashedPassword
                );

                // Execute statement
                if ($stmt->execute()) {
                    // *** SUCCESS: IMMEDIATE REDIRECT TO LOGIN.PHP ***
                    header("Location: login.php");
                    exit(); // Stop further script execution
                } else {
                    // Log detailed database error
                    error_log("MySQL Insertion Error: " . $stmt->error);
                    $registration_status = 'error';
                    $registration_message = "Registration failed due to a database error. Please contact support. (Code: 500)";
                }

                // Close statement
                $stmt->close();
            } else {
                error_log("MySQL Prepare Error: " . $conn->error);
                $registration_status = 'error';
                $registration_message = "Registration failed: Could not prepare SQL statement. (Code: 501)";
            }
            
        } else {
            // Errors from validation
            $registration_status = 'error';
            $registration_message = "Registration failed: " . implode(" | ", $errors);
        }

        // 6. Close Connection
        $conn->close();
    }
}
// PHP SCRIPT END
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register – Farmers Mall</title>

    <!-- Tailwind + Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }
        .input-focus:focus-within {
            box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.2);
            border-color: #15803d;
        }
        body {
            background: #228B22;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow: hidden;
        }
        /* Background Circles */
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .circle1 { width: 150px; height: 150px; top: 10%; left: 5%; }
        .circle2 { width: 100px; height: 100px; bottom: 20%; left: 15%; }
        .circle3 { width: 120px; height: 120px; top: 30%; right: 10%; }
        .circle4 { width: 80px; height: 80px; bottom: 15%; right: 20%; }
        .circle5 { width: 200px; height: 200px; bottom: 5%; left: 50%; transform: translateX(-50%); }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-50px);
            background-color: #dc2626;
            color: white;
            padding: 14px 22px;
            border-radius: 10px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transition: all 0.4s ease;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4" style="background: #228B22;">

    <!-- Background Circles -->
    <div class="bg-circle circle1"></div>
    <div class="bg-circle circle2"></div>
    <div class="bg-circle circle3"></div>
    <div class="bg-circle circle4"></div>
    <div class="bg-circle circle5"></div>

    <!-- Toast Notification -->
    <div id="toast" class="toast hidden">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span id="toastMsg"></span>
    </div>

    <!-- Register Card -->
  <div class="w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden relative z-10 lg:flex" style="min-height: 680px;">
    <!-- Left Side - Branding with Image -->
    <div class="hidden lg:flex lg:w-1/2 p-16 flex-col justify-center items-center text-white text-center relative bg-cover bg-center" style="background-image: url('images/img.png');">
      <!-- Overlay -->
      <div class="absolute inset-0 bg-green-800 opacity-60"></div>
      
      <a href="index.php" class="absolute top-6 left-6 h-12 w-12 flex items-center justify-center bg-black bg-opacity-30 rounded-full text-white hover:bg-opacity-50 transition-all z-20">
        <i class="fas fa-arrow-left text-xl"></i>
      </a>

      <!-- Content -->
      <div class="relative z-10">
        <div class="floating-icon w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
          <i class="fas fa-leaf text-green-600 text-4xl"></i>
        </div>
        <h2 class="text-3xl font-bold">Join The Community</h2>
        <p class="mt-2 text-green-100">Connecting farmers and consumers directly, offering fresh, local, and organic produce.</p>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div class="w-full lg:w-1/2 p-8 md:p-16 flex flex-col">
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Create an Account</h2>
        <p class="text-gray-600 mt-1">Join us and start shopping for fresh produce!</p>
      </div>

      <!-- Progress Bar -->
      <div class="mb-8">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span id="step-name">Personal Info</span>
          <span>Step <span id="step-current">1</span> of 5</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div id="progress-bar" class="bg-green-600 h-2 rounded-full progress-bar-fill" style="width: 25%;"></div>
        </div>
      </div>

      <div class="flex-grow flex flex-col">
        <!-- The form action is currently pointing to a sample script, adjust as needed -->
        <form id="registerForm" method="POST" action="php/register.php" class="flex-grow flex flex-col">
          <div class="flex-grow" style="height: 320px; overflow-y: auto;"> <!-- Static height container with scroll -->
            <!-- Step 1: Personal Info -->
            <div class="form-step active space-y-4">
              <div>
                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="firstname" name="firstname" required placeholder="Enter your first name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
              <div>
                <label for="middlename" class="block text-sm font-medium text-gray-700 mb-1">Middle Name <span class="text-gray-400">(Optional)</span></label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="middlename" name="middlename" placeholder="Enter your middle name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
              <div>
                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="lastname" name="lastname" required placeholder="Enter your last name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
              <div>
                <label for="suffix" class="block text-sm font-medium text-gray-700 mb-1">Suffix <span class="text-gray-400">(Optional)</span></label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="suffix" name="suffix" placeholder="e.g. Jr., Sr." class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
            </div>
    
            <!-- Step 2: Account Details -->
            <div class="form-step hidden space-y-4"> <!-- This is now the Address step -->
              <div>
                <label for="street" class="block text-sm font-medium text-gray-700 mb-1">Street</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="street" name="street" required placeholder="Enter your street address" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
              <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="city" name="city" required placeholder="Enter your city" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
              <div>
                <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3">
                  <select id="country" name="country" required class="w-full outline-none text-gray-700 text-sm bg-transparent py-2">
                    <option value="">Select Country</option>
                    <option value="PH">Philippines</option>
                    <option value="US">United States</option>
                  </select>
                </div>
              </div>
              <div>
                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3">
                  <select id="province" name="province" required class="w-full outline-none text-gray-700 text-sm bg-transparent py-2">
                    <option value="">Select Province</option>
                    <!-- Options will be populated by JavaScript -->
                  </select>
                </div>
              </div>
            </div>
     
             <!-- Step 3: Contact Info -->
            <div class="form-step hidden space-y-4"> <!-- This is now the Account Details step -->
              <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="username" name="username" required placeholder="Choose a username" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
              <div>
                <label for="confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="password" id="confirm" name="confirm" required placeholder="Confirm your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <p id="passwordError" class="text-red-500 text-sm mt-1 hidden">Passwords do not match!</p>
              </div>
            </div>
     
             <!-- Step 4: Finalize -->
            <div class="form-step hidden space-y-4"> <!-- This is now the Verification step -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email or Phone Number for Verification</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="email" name="email" required placeholder="Enter your email or phone" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
              <button type="button" class="w-full text-center text-sm text-green-600 hover:underline">Send Verification Code</button>
              <div>
                <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="otp" name="otp" required placeholder="Enter the code you received" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
              </div>
            </div>

            <!-- Step 5: Finalize (Terms & Conditions) -->
            <div class="form-step hidden space-y-4">
              <h3 class="text-lg font-semibold text-gray-800">Final Agreement</h3>
              <p class="text-sm text-gray-600">Please review and agree to the following terms before creating your account. By proceeding, you acknowledge and accept:</p>
              
              <ul class="space-y-2 text-sm text-gray-600 list-disc list-inside bg-gray-50 p-4 rounded-lg">
                <li>You agree to our <a href="#" class="text-green-600 font-medium hover:underline">Terms of Service</a>, which govern your use of our platform.</li>
                <li>You have read and understood our <a href="#" class="text-green-600 font-medium hover:underline">Privacy Policy</a>, which details how we handle your data.</li>
                <li>You consent to receive communications from us regarding your account and our services.</li>
                <li>You confirm that all information provided is accurate and that you are at least 18 years of age.</li>
              </ul>

              <div class="flex items-start mt-4">
                <input type="checkbox" id="terms" name="terms" required class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-1">
                <label for="terms" class="ml-3 block text-sm text-gray-700">
                  I have read and agree to all the terms and conditions listed above.
                </label>
              </div>
            </div>
          </div>
  
          <!-- Navigation Buttons (now inside the form) -->
          <div id="navigation-buttons" class="mt-auto pt-6 flex gap-4">
            <button type="button" id="prevBtn" class="prev-btn w-32 justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-5 rounded-lg transition-colors text-sm hidden">Previous</button>
            <button type="button" id="nextBtn" class="next-btn w-32 justify-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg transition-colors text-sm ml-auto">Next</button>
            <button type="submit" id="submitBtn" form="registerForm" class="w-32 justify-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg transition-colors shadow-md hover:shadow-lg text-sm hidden ml-auto">
              Sign Up
            </button>
          </div>
        </form>
  
        <p class="text-center text-sm text-gray-600 mt-8">
          Already have an account? 
          <a href="login.php" class="text-green-600 font-medium hover:underline">Log In</a>
        </p>
      </div>
    </div>
  </div>
  
  <script>
    feather.replace();

    const steps = Array.from(document.querySelectorAll('.form-step'));
    const form = document.getElementById('registerForm');
    const progressBar = document.getElementById('progress-bar');
    const stepNameEl = document.getElementById('step-name');
    const stepCurrentEl = document.getElementById('step-current');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const countrySelect = document.getElementById('country');
    const provinceSelect = document.getElementById('province');

    let currentStep = 0;
    const stepNames = ["Personal Info", "Address", "Account Details", "Verification", "Finalize"];

    const provinces = {
      "PH": ["Davao Oriental", "Davao de Oro", "Davao del Norte", "Metro Manila"],
      "US": ["California", "Texas", "Florida", "New York"]
    };

    function populateProvinces() {
      const selectedCountry = countrySelect.value;
      provinceSelect.innerHTML = '<option value="">Select Province</option>'; // Clear existing options

      if (selectedCountry && provinces[selectedCountry]) {
        provinces[selectedCountry].forEach(province => {
          const option = document.createElement('option');
          option.value = province;
          option.textContent = province;
          provinceSelect.appendChild(option);
        });
      }
    }

    function updateProgress() {
      // Hide all steps first to handle logic cleanly
      steps.forEach(step => step.classList.add('hidden'));
      // Then show the current one
      steps[currentStep].classList.remove('hidden');
      progressBar.style.width = `${((currentStep + 1) / steps.length) * 100}%`;
      stepNameEl.textContent = stepNames[currentStep];
      stepCurrentEl.textContent = currentStep + 1;
    }

    function goToStep(stepIndex) {
      steps[currentStep].classList.add('hidden');
      currentStep = stepIndex;
      updateProgress();
      updateButtons();
    }

    function updateButtons() {
      const navContainer = document.getElementById('navigation-buttons');
      const isFirstStep = currentStep === 0;
      const isLastStep = currentStep === steps.length - 1;

      prevBtn.classList.toggle('hidden', currentStep === 0);
      nextBtn.classList.toggle('hidden', isLastStep);
      submitBtn.classList.toggle('hidden', !isLastStep);
    }

    function validateStep(stepIndex) {
      const inputs = steps[stepIndex].querySelectorAll('input[required]');
      let isValid = true;
      inputs.forEach(input => {
        if (!input.value.trim()) {
          isValid = false;
        }
      });

      if (!isValid) {
        showToast("Please fill out all required fields.", "error");
        return false;
      }

      // Step-specific validation
      if (stepIndex === 1) { // Address
        if (!document.getElementById('country').value || !document.getElementById('province').value) {
          showToast("Please select a country and province.", "error");
          return false;
        }
      }

      if (stepIndex === 2) { // Account Details
        const pass = document.getElementById('password').value.trim();
        const confirm = document.getElementById('confirm').value.trim();
        const passError = document.getElementById("passwordError");
        if (pass !== confirm) {
          passError.classList.remove("hidden");
          showToast("Passwords do not match.", "error");
          return false;
        }
        passError.classList.add("hidden");
      }

      if (stepIndex === 3) { // Verification
        if (!document.getElementById('otp').value.trim()) {
          showToast("Please enter the verification code.", "error");
          return false;
        }
      }

      if (stepIndex === 4) { // Finalize
        if (!document.getElementById('terms').checked) {
          showToast("You must agree to the terms and conditions.", "error");
          return false;
        }
      }

      return true;
    }

    nextBtn.addEventListener('click', () => {
      if (validateStep(currentStep)) {
        if (currentStep < steps.length - 1) {
          goToStep(currentStep + 1);
        }
      }
    });

    prevBtn.addEventListener('click', () => {
      if (currentStep > 0) {
        goToStep(currentStep - 1);
      }
    });

    countrySelect.addEventListener('change', populateProvinces);


    function showToast(message, type = "error") {
      const toast = document.getElementById("toast");
      const msg = document.getElementById("toastMsg");

      msg.textContent = message;
      toast.classList.remove("hidden");
      toast.classList.add("show");
      toast.style.backgroundColor = type === "success" ? "#16a34a" : "#dc2626";

      setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.classList.add("hidden"), 400);
      }, 3500); // Give user time to read the message
    }

    // Handle form submission with AJAX to the PHP script
    form.addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent the default form submission

      if (validateStep(currentStep)) { // Final validation on the last step
        const formData = new FormData(this);

        fetch('php/register.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => window.location.href = "login.html", 2500);
          } else {
            showToast(data.message || "An unknown error occurred.", 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast("A network error occurred. Please try again.", 'error');
        });
      }
    });

    // Initial setup
    updateProgress();
  </script>
</body>

</html>