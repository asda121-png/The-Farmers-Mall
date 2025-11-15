<?php
// --- Temporary Debugging ---
// Show all errors to help find the problem.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start a session to store registration status.
header('Content-Type: application/json'); // We'll respond with JSON.

// --- DATABASE CONNECTION ---
// Replace with your actual database credentials.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Default XAMPP username
define('DB_PASSWORD', '');     // Default XAMPP password
define('DB_NAME', 'farmers_mall'); // Change this to your database name

// Enable mysqli exception reporting for better error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // In a real app, you'd log this error, not display it to the user.
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

// --- FORM PROCESSING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Collect and Sanitize Data
    $firstName = trim($_POST['firstname'] ?? '');
    $lastName = trim($_POST['lastname'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $username = trim($_POST['username'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $terms = isset($_POST['terms']);

    $errors = [];

    // 2. Server-Side Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
        $errors[] = "Please fill in all required fields.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($phone) && !preg_match('/^09\d{9}$/', $phone)) {
        $errors[] = "Invalid phone number format. If provided, it must be 11 digits and start with 09.";
    }

    if (!$terms) {
        $errors[] = "You must agree to the Terms and Privacy Policy.";
    }

    // 3. Check if email or username already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM registration WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Username or email already exists.";
        }
        $stmt->close();
    }

    // 4. If no errors, insert into database
    if (empty($errors)) {
        try {
            // Hash the password for security
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
            // Prepare the INSERT statement
            $stmt = $conn->prepare("INSERT INTO registration (first_name, last_name, username, email, password_hash, phone, address, agreed_to_terms) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
            $terms_int = $terms ? 1 : 0; // Convert boolean to integer for DB
            // Bind parameters and execute
            $stmt->bind_param("sssssssi", $firstName, $lastName, $username, $email, $password_hash, $phone, $address, $terms_int);
    
            $stmt->execute();
    
            // --- ADDED: Auto-login on successful registration ---
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $stmt->insert_id; // Get the ID of the new user
            $_SESSION['username'] = $username;
    
            // On success, send a success response
            echo json_encode(['status' => 'success', 'message' => 'Registration successful! Redirecting...']);
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            // Catch any SQL errors (like duplicate entry, wrong table/column name)
            echo json_encode([
                'status' => 'error', 
                'message' => 'Database error: ' . $e->getMessage() // Send the specific SQL error for debugging
            ]);
        }
    } else {
        // If there are validation errors, send them back
        echo json_encode([
            'status' => 'error', 
            'message' => implode(" ", $errors) // Use a space for better readability
        ]);
    }

    $conn->close();
    exit();
}