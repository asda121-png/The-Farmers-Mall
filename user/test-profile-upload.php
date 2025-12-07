<?php
session_start();
header('Content-Type: application/json');

// Enhanced debugging
error_log("=== PROFILE UPDATE DEBUG ===");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));
error_log("Session ID: " . session_id());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));

$response = [
    'debug' => [
        'post_received' => $_POST,
        'files_received' => $_FILES,
        'session_id' => session_id(),
        'user_id' => $_SESSION['user_id'] ?? null,
        'server_method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
        'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'not set',
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
