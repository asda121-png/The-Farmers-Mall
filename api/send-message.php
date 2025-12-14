<?php
session_start();
require_once __DIR__ . '/../config/supabase-api.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$receiver_id = $data['receiver_id'] ?? null;
$message = trim($data['message'] ?? '');
$sender_id = $_SESSION['user_id'] ?? null;

if (!$receiver_id || !$message || !$sender_id) {
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
    exit;
}

try {
    $supabase = getSupabaseAPI();
    // Insert message into 'chat_messages' table
    $supabase->insert('chat_messages', [
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'message' => $message,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}