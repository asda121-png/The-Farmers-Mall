<?php

/**
 * Upload Debug Script
 * Use this to test file uploads and diagnose issues
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

$uploadDir = __DIR__ . '/assets/product/';
$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['email'] ?? 'unknown';

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'user_id' => $userId,
    'user_email' => $userEmail,
    'php_version' => PHP_VERSION,
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'upload_dir' => $uploadDir,
    'upload_dir_exists' => is_dir($uploadDir),
    'upload_dir_writable' => is_writable($uploadDir),
    'upload_dir_permissions' => is_dir($uploadDir) ? substr(sprintf('%o', fileperms($uploadDir)), -4) : 'N/A',
    'current_user' => get_current_user(),
    'files_received' => isset($_FILES) ? array_keys($_FILES) : [],
    'post_keys' => isset($_POST) ? array_keys($_POST) : []
];

// If this is a file upload test
if (isset($_FILES['test_image'])) {
    $file = $_FILES['test_image'];
    $debug['file_details'] = [
        'name' => $file['name'],
        'type' => $file['type'],
        'size' => $file['size'],
        'tmp_name' => $file['tmp_name'],
        'error' => $file['error'],
        'error_message' => match ($file['error']) {
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            default => 'Unknown error'
        },
        'tmp_exists' => file_exists($file['tmp_name']),
        'tmp_readable' => is_readable($file['tmp_name'])
    ];

    // Try to upload
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'test_' . uniqid() . '_' . basename($file['name']);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $debug['upload_result'] = 'SUCCESS';
            $debug['uploaded_file'] = $uploadPath;
            $debug['file_exists_after_upload'] = file_exists($uploadPath);
            $debug['file_size_after_upload'] = filesize($uploadPath);
            $debug['file_permissions'] = substr(sprintf('%o', fileperms($uploadPath)), -4);
        } else {
            $debug['upload_result'] = 'FAILED';
            $debug['upload_error'] = error_get_last();
        }
    }
}

echo json_encode($debug, JSON_PRETTY_PRINT);
