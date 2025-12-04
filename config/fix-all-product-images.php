<?php
require_once __DIR__ . '/supabase-api.php';

$api = getSupabaseAPI();

// Map product names to their correct image files
$imageMapping = [
    // Mesa Farm products
    'Fresh Lettuce' => '../images/products/lettuce.jpg',
    'Organic Carrots' => '../images/products/carrots.jpg',
    'Fresh Basil' => '../images/products/basil.jpg',
    'Organic Spinach' => '../images/products/spinach.jpg',
    'Bell Peppers' => '../images/products/bell pepper mix.png',
    'Fresh Rosemary' => '../images/products/rosemary.jpg',
    'Organic Cucumbers' => '../images/products/cucumbers.jpg',
    'Organic Tomatoes' => '../images/products/tomatoes.jpg',
    
    // Taco Bell products
    'Avocados' => '../images/products/avocados.jpg',
    'Fresh Limes' => '../images/products/limes.jpg',
    'Mexican Chili Peppers' => '../images/products/chili-peppers.jpg',
    'Tomatillos' => '../images/products/tomatillos.jpg',
    'Fresh Jalapeños' => '../images/products/jalapenos.jpg',
    'Red Onions' => '../images/products/red-onions.jpg',
    'Cilantro Bundle' => '../images/products/cilantro.jpg',
    'Corn Kernels' => '../images/products/corn.jpg',
    
    // Jay's Artisan products
    'Arabica Coffee Beans' => '../images/products/coffee-beans.jpg',
    'Sourdough Bread' => '../images/products/bread.png',
    'Croissants' => '../images/products/croissants.jpg',
    'Cold Brew Coffee' => '../images/products/cold-brew.jpg',
    'Multigrain Bread' => '../images/products/bread.png',
    'Colombian Coffee' => '../images/products/colombian-coffee.jpg',
    'French Baguette' => '../images/products/baguette.jpg',
    'Espresso Blend' => '../images/products/espresso.jpg',
    'Whole Wheat Bread' => '../images/products/bread.png',
    
    // Ocean Fresh products
    'Mussels' => '../images/products/mussels.jpg',
    'Crab Meat' => '../images/products/crab.jpg',
    'Red Snapper' => '../images/products/tilapia.jpg',
    'Oysters' => '../images/products/oysters.jpg',
    'Fresh Salmon' => '../images/products/salmon.jpg',
    'Tiger Prawns' => '../images/products/prawns.jpg',
    'Fresh Tuna' => '../images/products/tuna.jpg',
    'Sea Bass' => '../images/products/Bangus.jpg',
    'Squid' => '../images/products/squid.jpg'
];

echo "Starting to update product images...\n\n";

$updated = 0;
$failed = 0;

foreach ($imageMapping as $productName => $imagePath) {
    try {
        // Find the product
        $products = $api->select('products', ['name' => $productName]);
        
        if (empty($products)) {
            echo "❌ Product not found: $productName\n";
            $failed++;
            continue;
        }
        
        $product = $products[0];
        $productId = $product['id'];
        
        // Check if image file exists
        $fullPath = __DIR__ . '/../' . str_replace('../', '', $imagePath);
        if (!file_exists($fullPath)) {
            echo "⚠️  Image file not found for $productName: $fullPath\n";
        }
        
        // Update the image_url
        $api->update('products', ['image_url' => $imagePath], ['id' => $productId]);
        
        echo "✅ Updated: $productName → $imagePath\n";
        $updated++;
        
    } catch (Exception $e) {
        echo "❌ Failed to update $productName: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n===========================================\n";
echo "Summary:\n";
echo "✅ Successfully updated: $updated products\n";
echo "❌ Failed: $failed products\n";
echo "===========================================\n";
?>
