<?php
/**
 * Migration Script: Add username column and admin user
 */

require_once __DIR__ . '/supabase-api.php';

echo "========================================\n";
echo "   Add Username Column Migration\n";
echo "========================================\n\n";

try {
    $api = getSupabaseAPI();
    
    // Step 1: Get all existing users
    echo "📊 Fetching existing users...\n";
    $users = $api->select('users', []);
    echo "Found " . count($users) . " existing users\n\n";
    
    // Step 2: Update each user with a username
    echo "📝 Adding usernames to existing users...\n";
    foreach ($users as $user) {
        if (!isset($user['username']) || empty($user['username'])) {
            // Generate username from email
            $emailParts = explode('@', $user['email']);
            $username = strtolower($emailParts[0]);
            
            // Check if username already exists, add number if needed
            $baseUsername = $username;
            $counter = 1;
            $usernameExists = true;
            
            while ($usernameExists) {
                $existingUser = $api->select('users', ['username' => $username]);
                if (empty($existingUser)) {
                    $usernameExists = false;
                } else {
                    $username = $baseUsername . $counter;
                    $counter++;
                }
            }
            
            // Update user with username
            try {
                $updated = $api->update('users', ['id' => $user['id']], ['username' => $username]);
                echo "  ✅ Updated {$user['email']} -> username: $username\n";
            } catch (Exception $e) {
                echo "  ❌ Failed to update {$user['email']}: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  ⏭️  {$user['email']} already has username: {$user['username']}\n";
        }
    }
    
    // Step 3: Add admin user if not exists
    echo "\n👤 Checking for admin user...\n";
    $adminEmail = 'Admin1234@gmail.com';
    $adminUsers = $api->select('users', ['email' => $adminEmail]);
    
    if (empty($adminUsers)) {
        echo "Creating admin user...\n";
        
        // Hash the admin password: Admin123
        $adminPassword = password_hash('Admin123', PASSWORD_DEFAULT);
        
        $adminData = [
            'email' => $adminEmail,
            'username' => 'admin',
            'password_hash' => $adminPassword,
            'full_name' => 'Administrator',
            'phone' => '09000000000',
            'user_type' => 'admin',
            'status' => 'active'
        ];
        
        try {
            $newAdmin = $api->insert('users', $adminData);
            if (!empty($newAdmin)) {
                echo "✅ Admin user created successfully!\n";
                echo "   Email: $adminEmail\n";
                echo "   Username: admin\n";
                echo "   Password: Admin123\n";
            } else {
                echo "❌ Failed to create admin user\n";
            }
        } catch (Exception $e) {
            echo "❌ Error creating admin: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✅ Admin user already exists\n";
        $admin = $adminUsers[0];
        
        // Update admin username if not set
        if (!isset($admin['username']) || empty($admin['username'])) {
            try {
                $api->update('users', ['id' => $admin['id']], ['username' => 'admin']);
                echo "✅ Updated admin username\n";
            } catch (Exception $e) {
                echo "❌ Failed to update admin username: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n========================================\n";
    echo "✅ Migration completed successfully!\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
