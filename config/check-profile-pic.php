<?php
require_once __DIR__ . '/supabase-api.php';

$api = getSupabaseAPI();

// Check user profile picture
$users = $api->select('users', ['email' => 'saerlibanon0@gmail.com']);

if (!empty($users)) {
    $user = $users[0];
    echo "User: " . $user['full_name'] . PHP_EOL;
    echo "Profile Picture: " . ($user['profile_picture'] ?? 'None') . PHP_EOL;
    
    // Check if file exists
    if (!empty($user['profile_picture'])) {
        $filepath = __DIR__ . '/../' . $user['profile_picture'];
        echo "File path: " . $filepath . PHP_EOL;
        echo "File exists: " . (file_exists($filepath) ? 'Yes' : 'No') . PHP_EOL;
    }
} else {
    echo "User not found" . PHP_EOL;
}
