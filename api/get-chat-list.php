<?php
session_start();
require_once __DIR__ . '/../config/supabase-api.php';
require_once __DIR__ . '/../config/uuid-helper.php';
header('Content-Type: application/json');

// Prevent any HTML output
ob_start();

$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role'] ?? 'customer';

if (!$current_user_id) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Not logged in', 'chats' => []]);
    exit;
}

try {
    $supabase = getSupabaseAPI();
    $conversations = [];

    // If retailer, show ALL customers
    if ($current_user_role === 'retailer') {
        $all_customers = $supabase->select('users', ['user_type' => 'customer']);

        foreach ($all_customers as $customer) {
            $profile_img = 'images/default-avatar.svg';
            if (!empty($customer['profile_picture'])) {
                $profile_img = ltrim($customer['profile_picture'], '/');
            }

            $conversations[$customer['id']] = [
                'partner_id' => $customer['id'],
                'partner_name' => $customer['full_name'] ?? $customer['username'] ?? 'Customer',
                'partner_role' => 'customer',
                'profile_img' => $profile_img,
                'last_message' => 'Start a conversation...',
                'last_message_time' => $customer['created_at'] ?? date('Y-m-d H:i:s'),
                'last_message_sender' => '',
                'unread_count' => 0,
                'is_online' => false
            ];
        }
    }

    // If customer, show ALL retailers
    if ($current_user_role === 'customer') {
        $all_retailers = $supabase->select('users', ['user_type' => 'retailer']);

        foreach ($all_retailers as $retailer) {
            $profile_img = 'images/default-avatar.svg';
            if (!empty($retailer['profile_picture'])) {
                $profile_img = ltrim($retailer['profile_picture'], '/');
            }

            $conversations[$retailer['id']] = [
                'partner_id' => $retailer['id'],
                'partner_name' => $retailer['full_name'] ?? $retailer['username'] ?? 'Shop',
                'partner_role' => 'retailer',
                'profile_img' => $profile_img,
                'last_message' => 'Send a message to start chatting...',
                'last_message_time' => $retailer['created_at'] ?? date('Y-m-d H:i:s'),
                'last_message_sender' => '',
                'unread_count' => 0,
                'is_online' => false
            ];
        }
    }

    // Now get actual messages to override with real last messages
    try {
        // Get messages involving current user (try/catch in case table is empty)
        $url = getenv('SUPABASE_URL') . '/rest/v1/chat_messages?or=(sender_id.eq.' . $current_user_id . ',receiver_id.eq.' . $current_user_id . ')&order=created_at.desc';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . getenv('SUPABASE_ANON_KEY'),
            'Authorization: Bearer ' . getenv('SUPABASE_ANON_KEY')
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $all_messages = json_decode($response, true);

            // Update conversations with actual message data
            foreach ($all_messages as $msg) {
                $partner_id = null;
                $is_sender = ($msg['sender_id'] === $current_user_id);

                if ($is_sender) {
                    $partner_id = $msg['receiver_id'];
                } elseif ($msg['receiver_id'] === $current_user_id) {
                    $partner_id = $msg['sender_id'];
                }

                if ($partner_id && isset($conversations[$partner_id])) {
                    // Only update if this is the first message (most recent)
                    if (
                        $conversations[$partner_id]['last_message'] === 'Start a conversation...' ||
                        $conversations[$partner_id]['last_message'] === 'Send a message to start chatting...'
                    ) {

                        $conversations[$partner_id]['last_message'] = $msg['message'];
                        $conversations[$partner_id]['last_message_time'] = $msg['created_at'];
                        $conversations[$partner_id]['last_message_sender'] = $is_sender ? 'You' : $conversations[$partner_id]['partner_name'];

                        // Count unread
                        if (!$is_sender && isset($msg['is_read']) && !$msg['is_read']) {
                            $conversations[$partner_id]['unread_count']++;
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        // If no messages yet, that's okay - we'll show all users with no messages
    }

    // Convert to array and sort by last message time
    $chats = array_values($conversations);
    usort($chats, function ($a, $b) {
        return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
    });

    ob_end_clean();
    echo json_encode(['success' => true, 'chats' => $chats]);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'chats' => []]);
}