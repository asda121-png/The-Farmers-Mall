<?php
/**
 * Google OAuth Callback Handler
 * 
 * This script handles the callback from Google OAuth
 * Creates or updates user in database and logs them in
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/google-oauth.php';
require_once __DIR__ . '/../config/supabase-api.php';

/**
 * Get base URL for redirects
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Find /The-Farmers-Mall in the script path
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    if (strpos($scriptPath, '/The-Farmers-Mall/') !== false) {
        $basePath = '/The-Farmers-Mall';
    } else {
        $basePath = rtrim(dirname($scriptPath), '/\\');
    }
    
    return $protocol . $host . $basePath;
}

function sendJsonResponse($status, $message, $redirectUrl = '') {
    // If successful and has redirect URL, do actual redirect
    if ($status === 'success' && !empty($redirectUrl)) {
        header('Location: ' . $redirectUrl);
        exit();
    }
    
    // For errors or no redirect, return JSON for modal handling
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'redirect_url' => $redirectUrl
    ]);
    exit();
}

try {
    // Check if authorization code is present
    if (!isset($_GET['code'])) {
        $error = $_GET['error'] ?? 'Unknown error';
        $errorDescription = $_GET['error_description'] ?? 'No authorization code received';
        throw new Exception("Google OAuth Error: $error - $errorDescription");
    }
    
    // Initialize Google OAuth
    $oauth = new GoogleOAuth();
    
    // Exchange code for access token
    $code = $_GET['code'];
    $accessToken = $oauth->exchangeCodeForToken($code);
    
    // Get user information from Google
    $googleUser = $oauth->getUserInfo($accessToken);
    
    // Extract user information
    $email = $googleUser['email'];
    $fullName = $googleUser['name'] ?? 'Google User';
    $picture = $googleUser['picture'] ?? '';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email from Google');
    }
    
    // Get Supabase API instance
    $api = getSupabaseAPI();
    
    // Check if user already exists
    $existingUsers = $api->select('users', ['email' => $email]);
    
    if (!empty($existingUsers)) {
        // User exists - update last login and log them in
        $user = $existingUsers[0];
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            sendJsonResponse('error', 'Your account is not active. Please contact support.');
        }
        
        // Set session variables for existing user
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'] ?? $user['full_name'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['phone'] = $user['phone'] ?? '';
        $_SESSION['address'] = $user['address'] ?? '';
        $_SESSION['role'] = $user['user_type'];
        $_SESSION['user_type'] = $user['user_type'];
        
        // Redirect based on user type
        $baseUrl = getBaseUrl();
        if ($user['user_type'] === 'admin') {
            sendJsonResponse('success', 'Google login successful! Redirecting...', $baseUrl . '/admin/admin-dashboard.php');
        } elseif ($user['user_type'] === 'retailer') {
            sendJsonResponse('success', 'Google login successful! Redirecting...', $baseUrl . '/retailer/retailer-dashboard2.php');
        } else {
            sendJsonResponse('success', 'Google login successful! Redirecting...', $baseUrl . '/user/user-homepage.php');
        }
    } else {
        // New user - create account from Google information
        // Generate a unique username from email
        $baseUsername = explode('@', $email)[0];
        $username = $baseUsername;
        $counter = 1;
        
        // Check if username already exists, if so, add numbers
        while (!empty($api->select('users', ['username' => $username]))) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        // Extract first and last name from full name
        $nameParts = explode(' ', trim($fullName), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';
        
        // Generate a random password (won't be used for Google auth, but required)
        $tempPassword = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        // Prepare new user data
        $newUserData = [
            'email' => $email,
            'username' => $username,
            'full_name' => $fullName,
            'password_hash' => $hashedPassword,
            'user_type' => 'customer',
            'status' => 'active',
            'phone' => '',
            'address' => ''
        ];
        
        // Insert new user
        $newUsers = $api->insert('users', $newUserData);
        
        if (empty($newUsers)) {
            throw new Exception('Failed to create user account');
        }
        
        $newUser = $newUsers[0];
        
        // Set session variables for new user
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $newUser['id'];
        $_SESSION['email'] = $newUser['email'];
        $_SESSION['username'] = $newUser['username'];
        $_SESSION['full_name'] = $newUser['full_name'];
        $_SESSION['phone'] = '';
        $_SESSION['address'] = '';
        $_SESSION['role'] = 'customer';
        $_SESSION['user_type'] = 'customer';
        
        // Log new Google user creation
        error_log("New user created via Google OAuth: {$newUser['email']} (ID: {$newUser['id']})");
        
        // Redirect to user homepage
        $baseUrl = getBaseUrl();
        sendJsonResponse('success', 'Account created and logged in! Redirecting...', $baseUrl . '/user/user-homepage.php');
    }
    
} catch (Exception $e) {
    error_log("Google OAuth Error: " . $e->getMessage());
    sendJsonResponse('error', 'Google authentication failed: ' . $e->getMessage());
}
?>
