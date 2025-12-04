<?php
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/supabase-api.php';

$api = getSupabaseAPI();

echo "<!DOCTYPE html><html><head><title>Reset & Populate Products</title>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body>";
echo "<h1>Reset & Populate Products</h1>";

// Step 1: Delete all existing products
echo "<h2>Step 1: Deleting all existing products...</h2>";
try {
    // Get all products first
    $all_products = $api->select('products', []);
    echo "<p class='info'>Found " . count($all_products) . " products to delete.</p>";
    
    // Delete each product
    foreach ($all_products as $product) {
        $result = $api->delete('products', ['id' => $product['id']]);
        echo "<p class='success'>✓ Deleted product ID: " . $product['id'] . " - " . htmlspecialchars($product['name']) . "</p>";
    }
    echo "<p class='success'><strong>All products deleted successfully!</strong></p>";
} catch (Exception $e) {
    echo "<p class='error'>Error deleting products: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 2: Get retailer IDs
echo "<h2>Step 2: Fetching retailer IDs...</h2>";
$retailers = $api->select('retailers', []);
$retailer_map = [];
foreach ($retailers as $retailer) {
    $retailer_map[$retailer['shop_name']] = $retailer['id'];
    echo "<p class='info'>Found: " . htmlspecialchars($retailer['shop_name']) . " (ID: " . $retailer['id'] . ")</p>";
}

// Step 3: Insert products with correct image paths
echo "<h2>Step 3: Creating products with correct image paths...</h2>";

$shop_products = [
    'Mesa Farm' => [
        ['name' => 'Organic Tomatoes', 'description' => 'Fresh, juicy organic tomatoes grown without pesticides', 'category' => 'Vegetables', 'price' => 120.00, 'stock_quantity' => 50, 'unit' => 'kg', 'image_url' => '../images/products/tomatoes.jpg'],
        ['name' => 'Fresh Lettuce', 'description' => 'Crisp organic lettuce perfect for salads', 'category' => 'Vegetables', 'price' => 80.00, 'stock_quantity' => 40, 'unit' => 'piece', 'image_url' => '../images/products/lettuce.jpg'],
        ['name' => 'Organic Carrots', 'description' => 'Sweet and crunchy organic carrots', 'category' => 'Vegetables', 'price' => 90.00, 'stock_quantity' => 60, 'unit' => 'kg', 'image_url' => '../images/products/carrots.jpg'],
        ['name' => 'Fresh Basil', 'description' => 'Aromatic fresh basil herbs', 'category' => 'Herbs', 'price' => 50.00, 'stock_quantity' => 30, 'unit' => 'bunch', 'image_url' => '../images/products/basil.jpg'],
        ['name' => 'Organic Spinach', 'description' => 'Nutrient-rich organic spinach leaves', 'category' => 'Vegetables', 'price' => 70.00, 'stock_quantity' => 45, 'unit' => 'bunch', 'image_url' => '../images/products/spinach.jpg'],
        ['name' => 'Bell Peppers', 'description' => 'Colorful organic bell peppers', 'category' => 'Vegetables', 'price' => 150.00, 'stock_quantity' => 35, 'unit' => 'kg', 'image_url' => '../images/products/bell-peppers.jpg'],
        ['name' => 'Fresh Rosemary', 'description' => 'Fragrant rosemary herbs', 'category' => 'Herbs', 'price' => 45.00, 'stock_quantity' => 25, 'unit' => 'bunch', 'image_url' => '../images/products/rosemary.jpg'],
        ['name' => 'Organic Cucumbers', 'description' => 'Fresh and crispy organic cucumbers', 'category' => 'Vegetables', 'price' => 100.00, 'stock_quantity' => 55, 'unit' => 'kg', 'image_url' => '../images/products/cucumbers.jpg'],
    ],
    'Taco Bell' => [
        ['name' => 'Fresh Jalapeños', 'description' => 'Spicy jalapeño peppers for authentic Mexican dishes', 'category' => 'Vegetables', 'price' => 180.00, 'stock_quantity' => 40, 'unit' => 'kg', 'image_url' => '../images/products/jalapenos.jpg'],
        ['name' => 'Red Onions', 'description' => 'Fresh red onions perfect for salsas', 'category' => 'Vegetables', 'price' => 110.00, 'stock_quantity' => 50, 'unit' => 'kg', 'image_url' => '../images/products/red-onions.jpg'],
        ['name' => 'Cilantro Bundle', 'description' => 'Fresh cilantro for Mexican cuisine', 'category' => 'Herbs', 'price' => 60.00, 'stock_quantity' => 35, 'unit' => 'bunch', 'image_url' => '../images/products/cilantro.jpg'],
        ['name' => 'Avocados', 'description' => 'Ripe avocados for guacamole', 'category' => 'Fruits', 'price' => 250.00, 'stock_quantity' => 30, 'unit' => 'kg', 'image_url' => '../images/products/avocados.jpg'],
        ['name' => 'Fresh Limes', 'description' => 'Juicy limes for authentic flavor', 'category' => 'Fruits', 'price' => 140.00, 'stock_quantity' => 45, 'unit' => 'kg', 'image_url' => '../images/products/limes.jpg'],
        ['name' => 'Mexican Chili Peppers', 'description' => 'Authentic Mexican chili peppers', 'category' => 'Vegetables', 'price' => 200.00, 'stock_quantity' => 25, 'unit' => 'kg', 'image_url' => '../images/products/chili-peppers.jpg'],
        ['name' => 'Tomatillos', 'description' => 'Fresh tomatillos for salsa verde', 'category' => 'Vegetables', 'price' => 170.00, 'stock_quantity' => 30, 'unit' => 'kg', 'image_url' => '../images/products/tomatillos.jpg'],
        ['name' => 'Corn Kernels', 'description' => 'Sweet corn kernels for Mexican dishes', 'category' => 'Vegetables', 'price' => 130.00, 'stock_quantity' => 40, 'unit' => 'kg', 'image_url' => '../images/products/corn.jpg'],
    ],
    "Jay's Artisan" => [
        ['name' => 'Arabica Coffee Beans', 'description' => 'Premium Arabica coffee beans, freshly roasted', 'category' => 'Coffee', 'price' => 450.00, 'stock_quantity' => 50, 'unit' => 'kg', 'image_url' => '../images/products/coffee-beans.jpg'],
        ['name' => 'Sourdough Bread', 'description' => 'Artisan sourdough bread baked fresh daily', 'category' => 'Bread', 'price' => 180.00, 'stock_quantity' => 30, 'unit' => 'loaf', 'image_url' => '../images/products/sourdough.jpg'],
        ['name' => 'French Baguette', 'description' => 'Classic French baguette with crispy crust', 'category' => 'Bread', 'price' => 120.00, 'stock_quantity' => 40, 'unit' => 'piece', 'image_url' => '../images/products/baguette.jpg'],
        ['name' => 'Espresso Blend', 'description' => 'Rich espresso blend for perfect coffee', 'category' => 'Coffee', 'price' => 500.00, 'stock_quantity' => 35, 'unit' => 'kg', 'image_url' => '../images/products/espresso.jpg'],
        ['name' => 'Whole Wheat Bread', 'description' => 'Healthy whole wheat bread', 'category' => 'Bread', 'price' => 150.00, 'stock_quantity' => 35, 'unit' => 'loaf', 'image_url' => '../images/products/wheat-bread.jpg'],
        ['name' => 'Croissants', 'description' => 'Buttery, flaky croissants', 'category' => 'Bread', 'price' => 200.00, 'stock_quantity' => 25, 'unit' => 'pack of 6', 'image_url' => '../images/products/croissants.jpg'],
        ['name' => 'Cold Brew Coffee', 'description' => 'Smooth cold brew concentrate', 'category' => 'Coffee', 'price' => 350.00, 'stock_quantity' => 30, 'unit' => 'liter', 'image_url' => '../images/products/cold-brew.jpg'],
        ['name' => 'Multigrain Bread', 'description' => 'Nutritious multigrain bread', 'category' => 'Bread', 'price' => 170.00, 'stock_quantity' => 28, 'unit' => 'loaf', 'image_url' => '../images/products/multigrain.jpg'],
        ['name' => 'Colombian Coffee', 'description' => 'Premium Colombian coffee beans', 'category' => 'Coffee', 'price' => 480.00, 'stock_quantity' => 40, 'unit' => 'kg', 'image_url' => '../images/products/colombian-coffee.jpg'],
    ],
    'Ocean Fresh' => [
        ['name' => 'Fresh Salmon', 'description' => 'Wild-caught fresh salmon fillets', 'category' => 'Seafood', 'price' => 650.00, 'stock_quantity' => 25, 'unit' => 'kg', 'image_url' => '../images/products/salmon.jpg'],
        ['name' => 'Tiger Prawns', 'description' => 'Large tiger prawns, fresh from the ocean', 'category' => 'Seafood', 'price' => 800.00, 'stock_quantity' => 20, 'unit' => 'kg', 'image_url' => '../images/products/prawns.jpg'],
        ['name' => 'Fresh Tuna', 'description' => 'Premium tuna steaks', 'category' => 'Seafood', 'price' => 700.00, 'stock_quantity' => 18, 'unit' => 'kg', 'image_url' => '../images/products/tuna.jpg'],
        ['name' => 'Sea Bass', 'description' => 'Fresh sea bass, whole or filleted', 'category' => 'Seafood', 'price' => 600.00, 'stock_quantity' => 22, 'unit' => 'kg', 'image_url' => '../images/products/sea-bass.jpg'],
        ['name' => 'Squid', 'description' => 'Fresh squid, cleaned and ready to cook', 'category' => 'Seafood', 'price' => 450.00, 'stock_quantity' => 30, 'unit' => 'kg', 'image_url' => '../images/products/squid.jpg'],
        ['name' => 'Mussels', 'description' => 'Fresh mussels in shell', 'category' => 'Seafood', 'price' => 350.00, 'stock_quantity' => 35, 'unit' => 'kg', 'image_url' => '../images/products/mussels.jpg'],
        ['name' => 'Crab Meat', 'description' => 'Fresh crab meat, hand-picked', 'category' => 'Seafood', 'price' => 900.00, 'stock_quantity' => 15, 'unit' => 'kg', 'image_url' => '../images/products/crab.jpg'],
        ['name' => 'Red Snapper', 'description' => 'Fresh red snapper, daily catch', 'category' => 'Seafood', 'price' => 550.00, 'stock_quantity' => 24, 'unit' => 'kg', 'image_url' => '../images/products/red-snapper.jpg'],
        ['name' => 'Oysters', 'description' => 'Fresh oysters on the half shell', 'category' => 'Seafood', 'price' => 750.00, 'stock_quantity' => 20, 'unit' => 'dozen', 'image_url' => '../images/products/oysters.jpg'],
    ]
];

$total_created = 0;
foreach ($shop_products as $shop_name => $products) {
    if (!isset($retailer_map[$shop_name])) {
        echo "<p class='error'>❌ Retailer '$shop_name' not found. Skipping products.</p>";
        continue;
    }
    
    $retailer_id = $retailer_map[$shop_name];
    echo "<h3>Creating products for: $shop_name</h3>";
    
    foreach ($products as $product) {
        $product['retailer_id'] = $retailer_id;
        $product['status'] = 'active';
        $product['created_at'] = date('Y-m-d H:i:s');
        
        try {
            $result = $api->insert('products', $product);
            if ($result) {
                echo "<p class='success'>✓ Created: " . htmlspecialchars($product['name']) . " (Image: " . htmlspecialchars($product['image_url']) . ")</p>";
                $total_created++;
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Failed to create " . htmlspecialchars($product['name']) . ": " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

echo "<h2>Summary</h2>";
echo "<p class='success'><strong>Total products created: $total_created</strong></p>";
echo "<p><a href='check-products.php'>View All Products</a> | <a href='../user/user-homepage.php'>Go to Homepage</a></p>";
echo "</body></html>";
?>
