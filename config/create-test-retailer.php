<?php
/**
 * Create Test Retailer Account
 * Run this script once to create a test retailer account
 */

require_once __DIR__ . '/supabase-api.php';

echo "==========================================\n";
echo "   Creating Test Retailer Account\n";
echo "==========================================\n\n";

try {
    $api = getSupabaseAPI();
    
    // Test retailer credentials
    $email = 'retailer@test.com';
    $username = 'testretailer';
    $password = 'Retailer123';
    $fullName = 'Test Retailer';
    $phone = '09123456789';
    
    // Check if user already exists
    $existingUsers = $api->select('users', ['email' => $email]);
    
    if (!empty($existingUsers)) {
        echo "✅ Retailer account already exists!\n\n";
        $userId = $existingUsers[0]['id'];
    } else {
        echo "Creating new retailer account...\n";
        
        // Create user account
        $userData = [
            'email' => $email,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
            'phone' => $phone,
            'user_type' => 'retailer',
            'status' => 'active'
        ];
        
        $newUser = $api->insert('users', $userData);
        
        if (empty($newUser)) {
            die("❌ Failed to create user account\n");
        }
        
        $userId = $newUser[0]['id'];
        echo "✅ User account created!\n";
        echo "   User ID: $userId\n\n";
    }
    
    // Check if retailer profile exists
    $existingRetailers = $api->select('retailers', ['user_id' => $userId]);
    
    if (!empty($existingRetailers)) {
        echo "✅ Retailer profile already exists!\n\n";
    } else {
        echo "Creating retailer profile...\n";
        
        // Create retailer profile
        $retailerData = [
            'user_id' => $userId,
            'shop_name' => 'Test Fresh Produce',
            'shop_description' => 'Quality organic produce for testing',
            'business_address' => 'Mati City, Davao Oriental',
            'verification_status' => 'verified',
            'status' => 'active'
        ];
        
        $newRetailer = $api->insert('retailers', $retailerData);
        
        if (empty($newRetailer)) {
            die("❌ Failed to create retailer profile\n");
        }
        
        echo "✅ Retailer profile created!\n\n";
    }
    
    echo "==========================================\n";
    echo "✅ TEST RETAILER ACCOUNT READY!\n";
    echo "==========================================\n\n";
    echo "📧 Email:    $email\n";
    echo "👤 Username: $username\n";
    echo "🔑 Password: $password\n\n";
    echo "🌐 Login URL: http://localhost/mywebsite/The-Farmers-Mall/auth/login.php\n\n";
    echo "After login, you'll be redirected to:\n";
    echo "   → retailer/retailer-dashboard2.php\n\n";
    echo "==========================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
