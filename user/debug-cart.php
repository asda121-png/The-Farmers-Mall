<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../config/supabase-api.php';

$api = getSupabaseAPI();
$user_id = $_SESSION['user_id'] ?? null;

echo "<h1>Cart Debug Info</h1>";
echo "<p><strong>User ID:</strong> " . htmlspecialchars($user_id) . "</p>";

// Fetch cart items
try {
    $cartItems = $api->select('cart', ['customer_id' => $user_id]);
    echo "<h2>Cart Items in Database:</h2>";
    echo "<pre>" . print_r($cartItems, true) . "</pre>";
    
    // Fetch products
    echo "<h2>All Products in Database:</h2>";
    $products = $api->select('products', []);
    echo "<pre>" . print_r($products, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<br><a href='cart.php'>Back to Cart</a>";
?>
