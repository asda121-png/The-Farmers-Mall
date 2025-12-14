<?php
session_start();
require_once __DIR__ . '/../config/supabase-api.php';
require_once __DIR__ . '/../config/uuid-helper.php';
header('Content-Type: application/json');

// Prevent any HTML output
ob_start();

$partner_id = $_GET['partner_id'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;

if (!$partner_id || !$current_user_id) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Missing parameters', 'messages' => []]);
    exit;
}

try {
    $supabase = getSupabaseAPI();

    // Use direct API call to get messages between two users
    $url = getenv('SUPABASE_URL') . '/rest/v1/chat_messages?or=(and(sender_id.eq.' . $current_user_id . ',receiver_id.eq.' . $partner_id . '),and(sender_id.eq.' . $partner_id . ',receiver_id.eq.' . $current_user_id . '))&order=created_at.asc';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . getenv('SUPABASE_ANON_KEY'),
        'Authorization: Bearer ' . getenv('SUPABASE_ANON_KEY')
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to fetch messages');
    }

    $messages = json_decode($response, true);

    // Mark messages as read when the current user is the receiver
    if (!empty($messages)) {
        foreach ($messages as $msg) {
            if ($msg['receiver_id'] === $current_user_id && !$msg['is_read']) {
                $updateUrl = getenv('SUPABASE_URL') . '/rest/v1/chat_messages?id=eq.' . $msg['id'];
                $ch = curl_init($updateUrl);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['is_read' => true]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'apikey: ' . getenv('SUPABASE_ANON_KEY'),
                    'Authorization: Bearer ' . getenv('SUPABASE_ANON_KEY'),
                    'Content-Type: application/json'
                ]);
                curl_exec($ch);
                curl_close($ch);
            }
        }
    }

    // Get partner info
    $partner = safeGetUser($partner_id, $supabase);

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'partner' => [
            'name' => $partner['full_name'] ?? $partner['username'] ?? 'User',
            'profile_img' => $partner['profile_picture'] ?? 'images/default-avatar.svg'
        ]
    ]);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'messages' => []]);
}
