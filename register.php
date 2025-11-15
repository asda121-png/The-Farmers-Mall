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
            overflow: auto;
        }
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
<body class="flex items-center justify-center p-4">

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
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden relative z-10">
        <div class="bg-green-600 py-6 px-8 text-center relative">
            <a href="login.php" class="absolute left-6 top-6 text-white hover:text-green-200 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="floating-icon w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-leaf text-green-600 text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-white">Shop Smart, Eat Local</h2>
            <p class="text-green-100 mt-1">Create your customer account</p>
        </div>

        <div class="p-8">
            <!-- Form is set to POST to the same PHP file for processing -->
            <form method="POST" onsubmit="return validateForm(event)">
                <input type="hidden" name="register_submitted" value="1">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                            <i class="far fa-user text-gray-400 mr-2"></i>
                            <input type="text" id="firstname" name="firstname" required placeholder="Enter your first name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>">
                        </div>
                    </div>
                    <div>
                        <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                            <i class="far fa-user text-gray-400 mr-2"></i>
                            <input type="text" id="lastname" name="lastname" required placeholder="Enter your last name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="far fa-envelope text-gray-400 mr-2"></i>
                        <input type="email" id="email" name="email" required placeholder="Enter your email" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="far fa-user text-gray-400 mr-2"></i>
                        <input type="text" id="username" name="username" required placeholder="Choose a username" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                        <input type="text" id="address" name="address" required placeholder="Enter your address" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-phone text-gray-400 mr-2"></i>
                        <input 
                            type="text" 
                            id="phone"
                            name="phone" 
                            required 
                            placeholder="09XXXXXXXXX" 
                            class="w-full outline-none text-gray-700 text-sm placeholder:text-sm"
                            maxlength="11"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                        >
                    </div>
                    <p id="phoneError" class="text-red-500 text-sm mt-1 hidden">Phone number must start with 09 and be 11 digits long.</p>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>
                        <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>
                        <input type="password" id="confirm" name="confirm" required placeholder="Confirm your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                    </div>
                    <p id="passwordError" class="text-red-500 text-sm mt-1 hidden">Passwords do not match!</p>
                </div>

                <div class="flex items-center mb-6">
                    <input type="checkbox" id="terms" name="terms" required class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" <?= isset($_POST['terms']) ? 'checked' : '' ?>>
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        I agree to the <a href="#" class="text-green-600 font-medium hover:underline">Terms</a> and 
                        <a href="#" class="text-green-600 font-medium hover:underline">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-md hover:shadow-lg">
                    Sign Up
                </button>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or login with</span>
                    </div>
                </div>

                <div class="flex justify-center">
                    <button type="button" class="flex items-center justify-center gap-2 bg-white border border-gray-300 rounded-lg py-2 px-6 text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                        <i class="fab fa-google text-red-500"></i>
                        <span>Google</span>
                    </button>
                </div>

                <p class="text-center text-sm text-gray-600 mt-6">
                    Already have an account? 
                    <a href="login.php" class="text-green-600 font-medium hover:underline">Log In</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        feather.replace();

        function showToast(message, type = "error") {
            const toast = document.getElementById("toast");
            const msg = document.getElementById("toastMsg");

            msg.textContent = message;
            toast.classList.remove("hidden");
            toast.classList.add("show");
            // Set background color based on status
            toast.style.backgroundColor = type === "success" ? "#16a34a" : "#dc2626";
            // Update icon based on status
            const icon = toast.querySelector('.fa-solid');
            icon.className = type === "success" ? "fa-solid fa-check-circle" : "fa-solid fa-circle-exclamation";

            setTimeout(() => {
                toast.classList.remove("show");
                setTimeout(() => toast.classList.add("hidden"), 400);
            }, 3500); // Give user time to read the message
        }

        function validateForm(e) {
            // Client-side validation to improve UX, PHP handles final server-side validation.
            
            const pass = document.getElementById('password').value.trim();
            const confirm = document.getElementById('confirm').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
            const phoneError = document.getElementById("phoneError");
            const passError = document.getElementById("passwordError");

            phoneError.classList.add("hidden");
            passError.classList.add("hidden");

            let isValid = true;

            // Phone check (must start with 09 and be 11 digits long)
            const phonePattern = /^09\d{9}$/;
            if (!phonePattern.test(phone)) {
                phoneError.classList.remove("hidden");
                isValid = false;
            }

            // Password check
            if (pass !== confirm) {
                passError.classList.remove("hidden");
                isValid = false;
            }

            // If client-side validation fails, prevent submission.
            if (!isValid) {
                e.preventDefault();
                showToast("Please correct the errors in the form.", "error");
                return false;
            }
            
            // If client-side validation passes, allow the form to submit to PHP.
            return true;
        }
        
        // Inject PHP status check after page load for ERRORS only
        window.onload = function() {
            const status = '<?= $registration_status ?>';
            // The addslashes is crucial here to prevent PHP data from breaking the JS string
            const message = '<?= addslashes($registration_message) ?>'; 
            
            if (status === 'error') {
                showToast(message, 'error');
            }
            // If status is 'success', the server already redirected via header(), so no JS needed.

            // Re-apply icons
            feather.replace();
        };
    </script>
</body>
</html>