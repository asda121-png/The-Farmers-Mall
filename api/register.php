<?php

/**
 * API Endpoint: Register User
 *
 * Description:
 * This endpoint handles the final step of user registration. It validates the
 * verification code from the session and inserts the new user into the Supabase 'users' table.
 *
 * Request Method: POST
 * Request Body (JSON):
 * {
 *   "verification_code": "123456",
 *   "full_name": "Juan Dela Cruz",
 *   "phone_number": "09171234567",
 *   "password": "a-strong-password"
 * }
 *
 * Responses:
 * - 201 Created: { "success": true, "message": "Account created successfully." }
 * - 400 Bad Request: { "success": false, "message": "Error message." }
 * - 500 Internal Server Error: { "success": false, "message": "Error message." }
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/supabase-api.php';
require_once __DIR__ . '/../config/uuid-helper.php';

$input = json_decode(file_get_contents('php://input'), true);

// --- Validation ---

// 1. Check if verification code is in session
if (!isset($_SESSION['verification_code']) || !isset($_SESSION['verification_email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Verification process not started. Please enter your email first.']);
    exit;
}

// 2. Check for code expiration (10 minutes)
$time_limit = 600; // 10 minutes
if (time() - $_SESSION['verification_time'] > $time_limit) {
    http_response_code(400);
    // Clear expired session data
    unset($_SESSION['verification_code'], $_SESSION['verification_email'], $_SESSION['verification_time']);
    echo json_encode(['success' => false, 'message' => 'Verification code has expired. Please request a new one.']);
    exit;
}

// 3. Validate submitted code
if (!isset($input['verification_code']) || $input['verification_code'] != $_SESSION['verification_code']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid verification code.']);
    exit;
}

// 4. Validate other inputs
$required_fields = ['full_name', 'phone_number', 'password'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// --- Registration ---

$api = getSupabaseAPI();

$user_data = [
    'user_id' => UUID::v4(),
    'email' => $_SESSION['verification_email'],
    'full_name' => $input['full_name'],
    'phone_number' => $input['phone_number'],
    'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
    'role' => 'user', // Default role
    'created_at' => date('c'),
    'updated_at' => date('c')
];

$response = $api->from('users')->insert([$user_data])->execute();

if ($response->error) {
    http_response_code(500);
    // Check for unique constraint violation (email already exists)
    if (strpos($response->error->message, 'duplicate key value violates unique constraint') !== false) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $response->error->message]);
    }
} else {
    // Clear session variables after successful registration
    unset($_SESSION['verification_code'], $_SESSION['verification_email'], $_SESSION['verification_time']);
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Account created successfully!']);
}
