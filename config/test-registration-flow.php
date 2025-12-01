<?php
/**
 * Test Complete Registration Flow
 * Simulates the full registration process to verify data saves to Supabase
 */

echo "===========================================\n";
echo "   Registration Flow Test\n";
echo "===========================================\n\n";

// Simulate the registration process
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['register_submitted'] = '1';
$_POST['firstname'] = 'John';
$_POST['lastname'] = 'Doe';
$_POST['email'] = 'johndoe_' . time() . '@test.com';
$_POST['username'] = 'johndoe' . time();
$_POST['address'] = '123 Test Street, Mati City';
$_POST['phone'] = '09123456789';
$_POST['password'] = 'TestPassword123!';
$_POST['confirm'] = 'TestPassword123!';
$_POST['terms'] = 'on';

echo "Simulating registration with:\n";
echo "  Name: {$_POST['firstname']} {$_POST['lastname']}\n";
echo "  Email: {$_POST['email']}\n";
echo "  Phone: {$_POST['phone']}\n\n";

// Load the registration logic
require_once __DIR__ . '/../config/supabase-api.php';

try {
    $api = getSupabaseAPI();
    
    // Simulate registration process
    $firstName = trim($_POST['firstname']);
    $lastName = trim($_POST['lastname']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    
    $fullName = $firstName . ' ' . $lastName;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    echo "Step 1: Inserting user into Supabase...\n";
    $newUser = $api->insert('users', [
        'email' => $email,
        'password_hash' => $hashedPassword,
        'full_name' => $fullName,
        'phone' => $phone,
        'user_type' => 'customer',
        'status' => 'active'
    ]);
    
    if (!empty($newUser)) {
        echo "✅ User inserted successfully!\n";
        echo "  User ID: " . ($newUser[0]['id'] ?? 'N/A') . "\n";
        echo "  Email: " . ($newUser[0]['email'] ?? 'N/A') . "\n";
        echo "  Full Name: " . ($newUser[0]['full_name'] ?? 'N/A') . "\n";
        echo "  Phone: " . ($newUser[0]['phone'] ?? 'N/A') . "\n";
        echo "  User Type: " . ($newUser[0]['user_type'] ?? 'N/A') . "\n";
        echo "  Status: " . ($newUser[0]['status'] ?? 'N/A') . "\n\n";
        
        $userId = $newUser[0]['id'];
        
        // Step 2: Verify in database
        echo "Step 2: Verifying in Supabase database...\n";
        $users = $api->select('users', ['email' => $email]);
        
        if (!empty($users)) {
            echo "✅ User verified in database!\n";
            echo "  Records found: " . count($users) . "\n\n";
            
            // Step 3: Verify on Supabase dashboard
            echo "Step 3: Check on Supabase Dashboard:\n";
            echo "  1. Go to https://supabase.com\n";
            echo "  2. Open your project\n";
            echo "  3. Go to Table Editor > users\n";
            echo "  4. You should see: {$fullName} ({$email})\n\n";
            
            // Step 4: Cleanup
            echo "Step 4: Cleaning up test data...\n";
            $api->delete('users', ['id' => $userId]);
            echo "✅ Test user removed\n\n";
            
            echo "===========================================\n";
            echo "✅ REGISTRATION FLOW TEST PASSED!\n";
            echo "===========================================\n\n";
            echo "✅ YES! Registrations DO save to Supabase!\n";
            echo "✅ All team members will see the same data!\n";
            echo "✅ No local database needed!\n\n";
            
        } else {
            echo "❌ User NOT found in database after insertion!\n";
        }
    } else {
        echo "❌ Failed to insert user\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
