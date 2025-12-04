<?php
/**
 * Populate Shops and Products Script
 * This script creates retailers and their products in the database
 * Run this once to set up the shops with their products
 */

require_once __DIR__ . '/supabase-api.php';

// Set error reporting and increase execution time
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Populate Shops & Products</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2E7D32; }
        .success { color: #2E7D32; background: #E8F5E9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #C62828; background: #FFEBEE; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #1565C0; background: #E3F2FD; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .shop-section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #2E7D32; }
        .product-item { margin-left: 20px; padding: 5px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🛒 Populate Shops & Products</h1>
        <p>This script will create 4 shops and populate them with products.</p>
        <hr>";

$api = getSupabaseAPI();
$errors = [];
$success = [];

// Define retailers
$retailers = [
    [
        'shop_name' => 'Mesa Farm',
        'shop_description' => 'Organic vegetables & herbs grown with care and sustainability in mind. Fresh from our farm to your table.',
        'business_address' => '123 Farm Road, Mesa Valley',
        'verification_status' => 'verified',
        'rating' => 4.80,
        'total_sales' => 15000.00
    ],
    [
        'shop_name' => 'Taco Bell',
        'shop_description' => 'Fresh Mexican ingredients and authentic spices. Quality produce for authentic Mexican cuisine.',
        'business_address' => '456 Market Street, Downtown',
        'verification_status' => 'verified',
        'rating' => 4.50,
        'total_sales' => 12000.00
    ],
    [
        'shop_name' => "Jay's Artisan",
        'shop_description' => 'Premium coffees and freshly baked bread. Handcrafted with passion and expertise.',
        'business_address' => '789 Baker Avenue, City Center',
        'verification_status' => 'verified',
        'rating' => 4.90,
        'total_sales' => 20000.00
    ],
    [
        'shop_name' => 'Ocean Fresh',
        'shop_description' => 'Daily catch seafood delivered fresh from the ocean. Sustainable and high-quality seafood selection.',
        'business_address' => '321 Harbor Drive, Coastal Area',
        'verification_status' => 'verified',
        'rating' => 4.70,
        'total_sales' => 18000.00
    ]
];

// Define products for each shop (using local images - fast loading)
$shop_products = [
    'Mesa Farm' => [
        ['name' => 'Organic Tomatoes', 'description' => 'Fresh, juicy organic tomatoes grown without pesticides', 'category' => 'Vegetables', 'price' => 120.00, 'stock_quantity' => 50, 'unit' => 'kg', 'image_url' => '../images/tomatoes.jpg'],
        ['name' => 'Fresh Lettuce', 'description' => 'Crisp organic lettuce perfect for salads', 'category' => 'Vegetables', 'price' => 80.00, 'stock_quantity' => 40, 'unit' => 'piece', 'image_url' => '../images/lettuce.jpg'],
        ['name' => 'Organic Carrots', 'description' => 'Sweet and crunchy organic carrots', 'category' => 'Vegetables', 'price' => 90.00, 'stock_quantity' => 60, 'unit' => 'kg', 'image_url' => '../images/carrots.jpg'],
        ['name' => 'Fresh Basil', 'description' => 'Aromatic fresh basil herbs', 'category' => 'Herbs', 'price' => 50.00, 'stock_quantity' => 30, 'unit' => 'bunch', 'image_url' => '../images/basil.jpg'],
        ['name' => 'Organic Spinach', 'description' => 'Nutrient-rich organic spinach leaves', 'category' => 'Vegetables', 'price' => 70.00, 'stock_quantity' => 45, 'unit' => 'bunch', 'image_url' => '../images/spinach.jpg'],
        ['name' => 'Bell Peppers', 'description' => 'Colorful organic bell peppers', 'category' => 'Vegetables', 'price' => 150.00, 'stock_quantity' => 35, 'unit' => 'kg', 'image_url' => '../images/bell-peppers.jpg'],
        ['name' => 'Fresh Rosemary', 'description' => 'Fragrant rosemary herbs', 'category' => 'Herbs', 'price' => 45.00, 'stock_quantity' => 25, 'unit' => 'bunch', 'image_url' => '../images/rosemary.jpg'],
        ['name' => 'Organic Cucumbers', 'description' => 'Fresh and crispy organic cucumbers', 'category' => 'Vegetables', 'price' => 100.00, 'stock_quantity' => 55, 'unit' => 'kg', 'image_url' => '../images/cucumbers.jpg'],
    ],
    'Taco Bell' => [
        ['name' => 'Fresh Jalapeños', 'description' => 'Spicy jalapeño peppers for authentic Mexican dishes', 'category' => 'Vegetables', 'price' => 180.00, 'stock_quantity' => 40, 'unit' => 'kg', 'image_url' => '../images/jalapenos.jpg'],
        ['name' => 'Red Onions', 'description' => 'Fresh red onions perfect for salsas', 'category' => 'Vegetables', 'price' => 110.00, 'stock_quantity' => 50, 'unit' => 'kg', 'image_url' => '../images/red-onions.jpg'],
        ['name' => 'Cilantro Bundle', 'description' => 'Fresh cilantro for Mexican cuisine', 'category' => 'Herbs', 'price' => 60.00, 'stock_quantity' => 35, 'unit' => 'bunch', 'image_url' => '../images/cilantro.jpg'],
        ['name' => 'Avocados', 'description' => 'Ripe avocados for guacamole', 'category' => 'Fruits', 'price' => 250.00, 'stock_quantity' => 30, 'unit' => 'kg', 'image_url' => '../images/avocados.jpg'],
        ['name' => 'Fresh Limes', 'description' => 'Juicy limes for authentic flavor', 'category' => 'Fruits', 'price' => 140.00, 'stock_quantity' => 45, 'unit' => 'kg', 'image_url' => '../images/limes.jpg'],
        ['name' => 'Mexican Chili Peppers', 'description' => 'Authentic Mexican chili peppers', 'category' => 'Vegetables', 'price' => 200.00, 'stock_quantity' => 25, 'unit' => 'kg', 'image_url' => '../images/chili-peppers.jpg'],
        ['name' => 'Tomatillos', 'description' => 'Fresh tomatillos for salsa verde', 'category' => 'Vegetables', 'price' => 170.00, 'stock_quantity' => 30, 'unit' => 'kg', 'image_url' => '../images/tomatillos.jpg'],
        ['name' => 'Corn Kernels', 'description' => 'Sweet corn kernels for Mexican dishes', 'category' => 'Vegetables', 'price' => 130.00, 'stock_quantity' => 40, 'unit' => 'kg', 'image_url' => '../images/corn.jpg'],
    ],
    "Jay's Artisan" => [
        ['name' => 'Arabica Coffee Beans', 'description' => 'Premium Arabica coffee beans, freshly roasted', 'category' => 'Coffee', 'price' => 450.00, 'stock_quantity' => 50, 'unit' => 'kg', 'image_url' => '../images/coffee-beans.jpg'],
        ['name' => 'Sourdough Bread', 'description' => 'Artisan sourdough bread baked fresh daily', 'category' => 'Bread', 'price' => 180.00, 'stock_quantity' => 30, 'unit' => 'loaf', 'image_url' => '../images/sourdough.jpg'],
        ['name' => 'French Baguette', 'description' => 'Classic French baguette with crispy crust', 'category' => 'Bread', 'price' => 120.00, 'stock_quantity' => 40, 'unit' => 'piece', 'image_url' => '../images/baguette.jpg'],
        ['name' => 'Espresso Blend', 'description' => 'Rich espresso blend for perfect coffee', 'category' => 'Coffee', 'price' => 500.00, 'stock_quantity' => 35, 'unit' => 'kg', 'image_url' => '../images/espresso.jpg'],
        ['name' => 'Whole Wheat Bread', 'description' => 'Healthy whole wheat bread', 'category' => 'Bread', 'price' => 150.00, 'stock_quantity' => 35, 'unit' => 'loaf', 'image_url' => '../images/wheat-bread.jpg'],
        ['name' => 'Croissants', 'description' => 'Buttery, flaky croissants', 'category' => 'Bread', 'price' => 200.00, 'stock_quantity' => 25, 'unit' => 'pack of 6', 'image_url' => '../images/croissants.jpg'],
        ['name' => 'Cold Brew Coffee', 'description' => 'Smooth cold brew concentrate', 'category' => 'Coffee', 'price' => 350.00, 'stock_quantity' => 30, 'unit' => 'liter', 'image_url' => '../images/cold-brew.jpg'],
        ['name' => 'Multigrain Bread', 'description' => 'Nutritious multigrain bread', 'category' => 'Bread', 'price' => 170.00, 'stock_quantity' => 28, 'unit' => 'loaf', 'image_url' => '../images/multigrain.jpg'],
        ['name' => 'Colombian Coffee', 'description' => 'Premium Colombian coffee beans', 'category' => 'Coffee', 'price' => 480.00, 'stock_quantity' => 40, 'unit' => 'kg', 'image_url' => '../images/colombian-coffee.jpg'],
    ],
    'Ocean Fresh' => [
        ['name' => 'Fresh Salmon', 'description' => 'Wild-caught fresh salmon fillets', 'category' => 'Seafood', 'price' => 650.00, 'stock_quantity' => 25, 'unit' => 'kg', 'image_url' => '../images/salmon.jpg'],
        ['name' => 'Tiger Prawns', 'description' => 'Large tiger prawns, fresh from the ocean', 'category' => 'Seafood', 'price' => 800.00, 'stock_quantity' => 20, 'unit' => 'kg', 'image_url' => '../images/prawns.jpg'],
        ['name' => 'Fresh Tuna', 'description' => 'Premium tuna steaks', 'category' => 'Seafood', 'price' => 700.00, 'stock_quantity' => 18, 'unit' => 'kg', 'image_url' => '../images/tuna.jpg'],
        ['name' => 'Sea Bass', 'description' => 'Fresh sea bass, whole or filleted', 'category' => 'Seafood', 'price' => 600.00, 'stock_quantity' => 22, 'unit' => 'kg', 'image_url' => '../images/sea-bass.jpg'],
        ['name' => 'Squid', 'description' => 'Fresh squid, cleaned and ready to cook', 'category' => 'Seafood', 'price' => 450.00, 'stock_quantity' => 30, 'unit' => 'kg', 'image_url' => '../images/squid.jpg'],
        ['name' => 'Mussels', 'description' => 'Fresh mussels in shell', 'category' => 'Seafood', 'price' => 350.00, 'stock_quantity' => 35, 'unit' => 'kg', 'image_url' => '../images/mussels.jpg'],
        ['name' => 'Crab Meat', 'description' => 'Fresh crab meat, hand-picked', 'category' => 'Seafood', 'price' => 900.00, 'stock_quantity' => 15, 'unit' => 'kg', 'image_url' => '../images/crab.jpg'],
        ['name' => 'Red Snapper', 'description' => 'Fresh red snapper, daily catch', 'category' => 'Seafood', 'price' => 550.00, 'stock_quantity' => 24, 'unit' => 'kg', 'image_url' => '../images/red-snapper.jpg'],
        ['name' => 'Oysters', 'description' => 'Fresh oysters on the half shell', 'category' => 'Seafood', 'price' => 750.00, 'stock_quantity' => 20, 'unit' => 'dozen', 'image_url' => '../images/oysters.jpg'],
    ]
];

// Create retailers and their products
foreach ($retailers as $retailer_data) {
    $shop_name = $retailer_data['shop_name'];
    echo "<div class='shop-section'>";
    echo "<h2>🏪 {$shop_name}</h2>";
    
    // Check if retailer already exists
    $existing = $api->select('retailers', ['shop_name' => $shop_name]);
    
    if (!empty($existing)) {
        echo "<div class='info'>ℹ️ Shop '{$shop_name}' already exists. Using existing shop.</div>";
        $retailer_id = $existing[0]['id'];
    } else {
        // Insert retailer
        try {
            $result = $api->insert('retailers', $retailer_data);
            if ($result && is_array($result) && !empty($result)) {
                echo "<div class='success'>✅ Created shop: {$shop_name}</div>";
                $retailer_id = $result[0]['id'];
                $success[] = "Created shop: {$shop_name}";
            } else {
                echo "<div class='error'>❌ Failed to create shop: {$shop_name}</div>";
                $errors[] = "Failed to create shop: {$shop_name}";
                echo "</div>";
                continue;
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error creating shop {$shop_name}: " . $e->getMessage() . "</div>";
            $errors[] = "Error creating shop: {$shop_name}";
            echo "</div>";
            continue;
        }
    }
    
    // Insert products for this retailer
    if (isset($shop_products[$shop_name])) {
        $product_count = 0;
        foreach ($shop_products[$shop_name] as $product_data) {
            $product_data['retailer_id'] = $retailer_id;
            $product_data['status'] = 'active';
            
            // Check if product already exists
            try {
                $existing_product = $api->select('products', [
                    'retailer_id' => $retailer_id,
                    'name' => $product_data['name']
                ]);
                
                if (empty($existing_product)) {
                    $result = $api->insert('products', $product_data);
                    if ($result && is_array($result) && !empty($result)) {
                        $product_count++;
                        echo "<div class='product-item'>✓ {$product_data['name']} - ₱{$product_data['price']}</div>";
                    } else {
                        echo "<div class='product-item' style='color: orange;'>⚠ Failed: {$product_data['name']}</div>";
                    }
                } else {
                    echo "<div class='product-item' style='color: gray;'>↻ Already exists: {$product_data['name']}</div>";
                }
            } catch (Exception $e) {
                echo "<div class='product-item' style='color: red;'>✗ Error with {$product_data['name']}: " . $e->getMessage() . "</div>";
            }
        }
        echo "<div class='success'>📦 Added/Verified {$product_count} new products for {$shop_name}</div>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h2>📊 Summary</h2>";
echo "<div class='success'><strong>✅ Success:</strong> " . count($success) . " operations completed</div>";
if (!empty($errors)) {
    echo "<div class='error'><strong>❌ Errors:</strong> " . count($errors) . " operations failed</div>";
}

echo "<hr>";
echo "<p><a href='../user/user-homepage.php' style='color: #2E7D32; text-decoration: none; font-weight: bold;'>← Back to Homepage</a></p>";
echo "</div></body></html>";
?>
