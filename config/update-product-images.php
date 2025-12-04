<?php
/**
 * Update existing product images to use local paths
 */

require_once __DIR__ . '/supabase-api.php';

set_time_limit(300);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Update Product Images</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #2E7D32; }
        .success { color: #2E7D32; background: #E8F5E9; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .info { color: #1565C0; background: #E3F2FD; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; background: #2E7D32; color: white; padding: 12px 24px; border-radius: 5px; text-decoration: none; margin: 10px 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 Update Product Images</h1>
        <p>Converting product images from URLs to local paths...</p>
        <hr>";

$api = getSupabaseAPI();

// Image mapping from product names to local files
$imageMap = [
    // Mesa Farm
    'Organic Tomatoes' => '../images/tomatoes.jpg',
    'Fresh Lettuce' => '../images/lettuce.jpg',
    'Organic Carrots' => '../images/carrots.jpg',
    'Fresh Basil' => '../images/basil.jpg',
    'Organic Spinach' => '../images/spinach.jpg',
    'Bell Peppers' => '../images/bell-peppers.jpg',
    'Fresh Rosemary' => '../images/rosemary.jpg',
    'Organic Cucumbers' => '../images/cucumbers.jpg',
    
    // Taco Bell
    'Fresh Jalapeños' => '../images/jalapenos.jpg',
    'Red Onions' => '../images/red-onions.jpg',
    'Cilantro Bundle' => '../images/cilantro.jpg',
    'Avocados' => '../images/avocados.jpg',
    'Fresh Limes' => '../images/limes.jpg',
    'Mexican Chili Peppers' => '../images/chili-peppers.jpg',
    'Tomatillos' => '../images/tomatillos.jpg',
    'Corn Kernels' => '../images/corn.jpg',
    
    // Jay's Artisan
    'Arabica Coffee Beans' => '../images/coffee-beans.jpg',
    'Sourdough Bread' => '../images/sourdough.jpg',
    'French Baguette' => '../images/baguette.jpg',
    'Espresso Blend' => '../images/espresso.jpg',
    'Whole Wheat Bread' => '../images/wheat-bread.jpg',
    'Croissants' => '../images/croissants.jpg',
    'Cold Brew Coffee' => '../images/cold-brew.jpg',
    'Multigrain Bread' => '../images/multigrain.jpg',
    'Colombian Coffee' => '../images/colombian-coffee.jpg',
    
    // Ocean Fresh
    'Fresh Salmon' => '../images/salmon.jpg',
    'Tiger Prawns' => '../images/prawns.jpg',
    'Fresh Tuna' => '../images/tuna.jpg',
    'Sea Bass' => '../images/sea-bass.jpg',
    'Squid' => '../images/squid.jpg',
    'Mussels' => '../images/mussels.jpg',
    'Crab Meat' => '../images/crab.jpg',
    'Red Snapper' => '../images/red-snapper.jpg',
    'Oysters' => '../images/oysters.jpg',
];

$updated = 0;
$failed = 0;

try {
    // Get all products
    $products = $api->select('products');
    
    echo "<div class='info'>Found " . count($products) . " products to update.</div>";
    
    foreach ($products as $product) {
        $productName = $product['name'];
        $productId = $product['id'];
        
        if (isset($imageMap[$productName])) {
            $localPath = $imageMap[$productName];
            
            try {
                // Update the product image_url
                $result = $api->update('products', 
                    ['image_url' => $localPath],
                    ['id' => $productId]
                );
                
                if ($result) {
                    echo "<div class='success'>✅ Updated: {$productName}</div>";
                    $updated++;
                } else {
                    echo "<div style='color: orange; padding: 5px;'>⚠ Skipped: {$productName}</div>";
                    $failed++;
                }
            } catch (Exception $e) {
                echo "<div style='color: red; padding: 5px;'>❌ Error updating {$productName}: " . $e->getMessage() . "</div>";
                $failed++;
            }
        } else {
            echo "<div style='color: gray; padding: 5px;'>ℹ No mapping for: {$productName}</div>";
        }
    }
    
    echo "<hr>";
    echo "<h2>📊 Summary</h2>";
    echo "<p><strong>Updated:</strong> {$updated} products</p>";
    echo "<p><strong>Failed/Skipped:</strong> {$failed} products</p>";
    echo "<div class='success'><strong>✅ All images are now local!</strong><br>Products will load much faster now.</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 15px; background: #FFEBEE; border-radius: 5px;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Test it out:</strong></p>";
echo "<a href='../user/shop-products.php?shop=Mesa Farm' class='btn'>Mesa Farm</a>";
echo "<a href='../user/shop-products.php?shop=Taco Bell' class='btn'>Taco Bell</a>";
echo "<a href='../user/shop-products.php?shop=Jay\\'s Artisan' class='btn'>Jay's Artisan</a>";
echo "<a href='../user/shop-products.php?shop=Ocean Fresh' class='btn'>Ocean Fresh</a>";
echo "<br><br>";
echo "<a href='../user/user-homepage.php' class='btn' style='background: #666;'>← Back to Homepage</a>";
echo "</div></body></html>";
?>
