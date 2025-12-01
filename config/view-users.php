<?php
/**
 * View All Users in Supabase Database
 */

require_once __DIR__ . '/../config/supabase-api.php';

echo "===========================================\n";
echo "   Current Users in Supabase Database\n";
echo "===========================================\n\n";

try {
    $api = getSupabaseAPI();
    
    // Get all users
    $users = $api->select('users');
    
    if (empty($users)) {
        echo "📊 No users in database yet.\n";
        echo "   Register at: http://localhost:8000/auth/register.php\n";
    } else {
        echo "📊 Total Users: " . count($users) . "\n\n";
        
        foreach ($users as $index => $user) {
            echo "User #" . ($index + 1) . ":\n";
            echo "  ID: " . ($user['id'] ?? 'N/A') . "\n";
            echo "  Name: " . ($user['full_name'] ?? 'N/A') . "\n";
            echo "  Email: " . ($user['email'] ?? 'N/A') . "\n";
            echo "  Phone: " . ($user['phone'] ?? 'N/A') . "\n";
            echo "  Type: " . ($user['user_type'] ?? 'N/A') . "\n";
            echo "  Status: " . ($user['status'] ?? 'N/A') . "\n";
            echo "  Created: " . ($user['created_at'] ?? 'N/A') . "\n";
            echo "\n";
        }
    }
    
    echo "===========================================\n";
    echo "✅ This is LIVE data from Supabase!\n";
    echo "✅ All team members see the same data!\n";
    echo "===========================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
