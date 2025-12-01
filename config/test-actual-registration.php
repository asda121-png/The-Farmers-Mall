<?php
/**
 * Direct test of registration with actual form data
 */

echo "Testing registration with actual form data...\n\n";

// Simulate POST data from your form
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'firstname' => 'Saer',
    'lastname' => 'Libanon',
    'street' => 'Upper Salazar',
    'barangay' => 'Central',
    'city' => 'Mati City',
    'province' => 'Davao Oriental',
    'username' => 'saer',
    'phone' => '09483525886',
    'password' => 'Pass123!',
    'confirm' => 'Pass123!',
    'email' => 'saerlibanon0@gmail.com',
    'terms' => 'on',
    'register_submitted' => '1'
];

// Include the registration logic
require_once __DIR__ . '/../config/supabase-api.php';

try {
    $api = getSupabaseAPI();
    
    $firstName = trim($_POST['firstname']);
    $lastName = trim($_POST['lastname']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    
    $fullName = $firstName . ' ' . $lastName;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    echo "Inserting user:\n";
    echo "  Name: $fullName\n";
    echo "  Email: $email\n";
    echo "  Phone: $phone\n\n";
    
    $newUser = $api->insert('users', [
        'email' => $email,
        'password_hash' => $hashedPassword,
        'full_name' => $fullName,
        'phone' => $phone,
        'user_type' => 'customer',
        'status' => 'active'
    ]);
    
    echo "Result:\n";
    print_r($newUser);
    
    if (!empty($newUser)) {
        echo "\n✅ SUCCESS! User inserted.\n";
        echo "User ID: " . ($newUser[0]['id'] ?? 'unknown') . "\n\n";
        
        // Verify
        $check = $api->select('users', ['email' => $email]);
        echo "Verification: Found " . count($check) . " user(s)\n";
        
        // Cleanup
        if (!empty($newUser[0]['id'])) {
            $api->delete('users', ['id' => $newUser[0]['id']]);
            echo "Test user cleaned up.\n";
        }
    } else {
        echo "\n❌ FAILED! Insert returned empty.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
