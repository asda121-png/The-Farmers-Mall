<?php
/**
 * Test Retailer Registration
 */

require_once __DIR__ . '/supabase-api.php';

echo "========================================\n";
echo "   Test Retailer Registration\n";
echo "========================================\n\n";

try {
    $api = getSupabaseAPI();
    
    // Test data
    $timestamp = time();
    $testData = [
        'firstname' => 'Test',
        'lastname' => 'Retailer',
        'phone' => '09987654321',
        'email' => "retailer{$timestamp}@test.com",
        'shop_name' => 'Test Farm Shop',
        'shop_address' => '123 Farm Road, Test City',
        'username' => "retailer{$timestamp}",
        'password' => 'Test123!@#',
        'confirm_password' => 'Test123!@#',
        'terms' => true
    ];
    
    echo "Creating retailer account...\n";
    echo "Email: {$testData['email']}\n";
    echo "Username: {$testData['username']}\n";
    echo "Shop: {$testData['shop_name']}\n\n";
    
    // Create user
    $hashedPassword = password_hash($testData['password'], PASSWORD_DEFAULT);
    $fullName = $testData['firstname'] . ' ' . $testData['lastname'];
    
    $newUser = $api->insert('users', [
        'email' => $testData['email'],
        'username' => $testData['username'],
        'password_hash' => $hashedPassword,
        'full_name' => $fullName,
        'phone' => $testData['phone'],
        'user_type' => 'retailer',
        'status' => 'active'
    ]);
    
    if (!empty($newUser)) {
        $userId = $newUser[0]['id'];
        echo "✅ User created! ID: $userId\n\n";
        
        // Create retailer profile
        $retailerData = [
            'user_id' => $userId,
            'shop_name' => $testData['shop_name'],
            'business_address' => $testData['shop_address'],
            'verification_status' => 'pending',
            'rating' => 0.00,
            'total_sales' => 0.00
        ];
        
        $newRetailer = $api->insert('retailers', $retailerData);
        
        if (!empty($newRetailer)) {
            $retailerId = $newRetailer[0]['id'];
            echo "✅ Retailer profile created! ID: $retailerId\n\n";
            
            // Verify
            echo "Verifying...\n";
            $user = $api->select('users', ['id' => $userId]);
            $retailer = $api->select('retailers', ['id' => $retailerId]);
            
            if (!empty($user) && !empty($retailer)) {
                echo "✅ SUCCESS! Retailer account fully created\n\n";
                echo "User Details:\n";
                print_r($user[0]);
                echo "\nRetailer Details:\n";
                print_r($retailer[0]);
                
                // Clean up test data
                echo "\n\nCleaning up test data...\n";
                $api->delete('retailers', ['id' => $retailerId]);
                $api->delete('users', ['id' => $userId]);
                echo "✅ Test data cleaned up\n";
            }
        } else {
            echo "❌ Failed to create retailer profile\n";
        }
    } else {
        echo "❌ Failed to create user\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
