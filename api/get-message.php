
<?php
session_start();
require_once __DIR__ . '/../config/supabase-api.php';
header('Content-Type: application/json');

$partner_id = $_GET['partner_id'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;

if (!$partner_id || !$current_user_id) {
    echo json_encode([]);
    exit;
}

try {
    $supabase = getSupabaseAPI();

    // Fetch all messages between the current user and the selected partner
    // Since the API doesn't support complex OR queries, we'll use a simpler approach
    // Get all messages where current user is involved, then filter client-side
    $all_messages = $supabase->select('chat_messages', [
        'or' => [
            'sender_id' => ['eq', $current_user_id],
            'receiver_id' => ['eq', $current_user_id]
        ]
    ]);

    // Filter messages to only include conversations with the specific partner
    $messages = array_filter($all_messages, function($msg) use ($current_user_id, $partner_id) {
        return ($msg['sender_id'] === $current_user_id && $msg['receiver_id'] === $partner_id) ||
               ($msg['sender_id'] === $partner_id && $msg['receiver_id'] === $current_user_id);
    });

    // Sort by created_at
    usort($messages, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });

    echo json_encode(array_values($messages));
} catch (Exception $e) {
    echo json_encode([]);
}
