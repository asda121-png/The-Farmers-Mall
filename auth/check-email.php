<?php
// FILE: check-email.php - Check if email already exists in database

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/supabase-api.php';

$response = ['exists' => false, 'message' => ''];

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format';
        echo json_encode($response);
        exit();
    }

    // Get Supabase API instance
    $api = SupabaseAPI::getInstance();

    // Query the users table to check if email exists
    $result = $api->query('users', 'select', [], ['email' => $email]);

    // Check if any results were returned
    if (is_array($result) && !empty($result)) {
        $response['exists'] = true;
        $response['message'] = 'Email already registered';
    } else {
        $response['exists'] = false;
        $response['message'] = 'Email available';
    }

} catch (Exception $e) {
    $response['exists'] = false;
    $response['message'] = 'Error checking email: ' . $e->getMessage();
    error_log('Check email error: ' . $e->getMessage());
}

echo json_encode($response);
?>
