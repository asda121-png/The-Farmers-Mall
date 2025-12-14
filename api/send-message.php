<?php
session_start();
require_once __DIR__ . '/../config/supabase-api.php';
require_once __DIR__ . '/../config/uuid-helper.php';
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

    // Get sender information
    $sender = safeGetUser($sender_id, $supabase);
    $sender_name = $sender['full_name'] ?? $sender['username'] ?? 'Unknown User';

    // Get receiver information
    $receiver = safeGetUser($receiver_id, $supabase);
    $receiver_name = $receiver['full_name'] ?? $receiver['username'] ?? 'Unknown User';

    // Insert message into 'chat_messages' table with names
    $result = $supabase->insert('chat_messages', [
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'sender_name' => $sender_name,
        'receiver_name' => $receiver_name,
        'message' => $message,
        'is_read' => false
    ]);

    echo json_encode(['success' => true, 'message_id' => $result[0]['id'] ?? null]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
