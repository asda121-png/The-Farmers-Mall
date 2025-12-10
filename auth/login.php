<?php
// PHP SCRIPT START - SUPABASE LOGIN

// Ensure we're outputting JSON
header('Content-Type: application/json');
session_start();

// Load Supabase API
require_once __DIR__ . '/../config/supabase-api.php';

$login_status = '';
$login_message = '';
$redirect_url = '';

// Function to send JSON response and exit
function send_json_response($status, $message, $redirect_url = '') {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'redirect_url' => $redirect_url
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submitted'])) {
    try {
        $api = getSupabaseAPI();
        
        $input_identifier = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($input_identifier) || empty($password)) {
            send_json_response('error', 'Please enter both email/username and password.');
        } else {
            // Check for hardcoded admin credentials first
            if ($input_identifier === 'Admin1234@gmail.com' && $password === 'Admin123') {
                $_SESSION['loggedin'] = true;
                $_SESSION['role'] = 'admin';
                $_SESSION['username'] = 'Administrator';
                $_SESSION['user_id'] = 'admin';
                $_SESSION['email'] = 'Admin1234@gmail.com';
                $_SESSION['full_name'] = 'Administrator';
                
                send_json_response('success', 'Admin login successful! Redirecting...', '../admin/admin-dashboard.php');
            } else {
                // Query Supabase for user by email or username
                $user = null;
                
                // Try to find by email first
                $users = $api->select('users', ['email' => $input_identifier]);
                
                if (!empty($users)) {
                    $user = $users[0];
                } else {
                    // Try to find by username
                    $users = $api->select('users', ['username' => $input_identifier]);
                    if (!empty($users)) {
                        $user = $users[0];
                    }
                }
                
                if ($user) {
                    
                    // Verify password
                    if (password_verify($password, $user['password_hash'])) {
                        // Check if account is active
                        if ($user['status'] !== 'active') {
                            send_json_response('error', 'Your account is not active. Please contact support.');
                        } else {
                            // Login successful
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
                            if ($user['user_type'] === 'admin') {
                                send_json_response('success', 'Admin login successful! Redirecting...', '../admin/admin-dashboard.php');
                            } elseif ($user['user_type'] === 'retailer') {
                                send_json_response('success', 'Retailer login successful! Redirecting...', '../retailer/retailer-dashboard2.php');
                            } else {
                                // Default to customer
                                send_json_response('success', 'Login successful! Redirecting...', '../user/user-homepage.php');
                            }
                        }
                    } else {
                        send_json_response('error', 'Invalid email/username or password.');
                    }
                } else {
                    send_json_response('error', 'Invalid email/username or password.');
                }
            }
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        send_json_response('error', 'A system error occurred. Please try again later.');
    }
}
send_json_response('error', 'Invalid request method.');