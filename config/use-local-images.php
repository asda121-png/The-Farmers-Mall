<?php
/**
 * Update products to use LOCAL images from /images/products/
 */

require_once __DIR__ . '/supabase-api.php';

set_time_limit(300);

echo "<!DOCTYPE html><html><head><title>Use Local Images</title>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body><h1>Updating to Use Local Images</h1>";

$api = getSupabaseAPI();

// Map products to actual LOCAL images in /images/products/
$imageMap = [
    // Mesa Farm - using existing images
    'Organic Tomatoes' => '../images/products/tomatoes.jpg',
    'Fresh Lettuce' => '../images/products/lettuce.jpg',
    'Organic Carrots' => '../images/products/carrots.jpg',
    'Fresh Basil' => '../images/products/basil.jpg',
    'Organic Spinach' => '../images/products/spinach.jpg',
    'Bell Peppers' => '../images/products/bell-peppers.jpg',
    'Fresh Rosemary' => '../images/products/rosemary.jpg',
    'Organic Cucumbers' => '../images/products/cucumbers.jpg',
    
    // Taco Bell
    'Fresh Jalapeños' => '../images/products/jalapenos.jpg',
    'Red Onions' => '../images/products/red-onions.jpg',
    'Cilantro Bundle' => '../images/products/cilantro.jpg',
    'Avocados' => '../images/products/avocados.jpg',
    'Fresh Limes' => '../images/products/limes.jpg',
    'Mexican Chili Peppers' => '../images/products/chili-peppers.jpg',
    'Tomatillos' => '../images/products/tomatillos.jpg',
    'Corn Kernels' => '../images/products/corn.jpg',
    
    // Jay's Artisan
    'Arabica Coffee Beans' => '../images/products/coffee-beans.jpg',
    'Sourdough Bread' => '../images/products/sourdough.jpg',
    'French Baguette' => '../images/products/baguette.jpg',
    'Espresso Blend' => '../images/products/espresso.jpg',
    'Whole Wheat Bread' => '../images/products/wheat-bread.jpg',
    'Croissants' => '../images/products/croissants.jpg',
    'Cold Brew Coffee' => '../images/products/cold-brew.jpg',
    'Multigrain Bread' => '../images/products/multigrain.jpg',
    'Colombian Coffee' => '../images/products/colombian-coffee.jpg',
    
    // Ocean Fresh
    'Fresh Salmon' => '../images/products/salmon.jpg',
    'Tiger Prawns' => '../images/products/prawns.jpg',
    'Fresh Tuna' => '../images/products/tuna.jpg',
    'Sea Bass' => '../images/products/sea-bass.jpg',
    'Squid' => '../images/products/squid.jpg',
    'Mussels' => '../images/products/mussels.jpg',
    'Crab Meat' => '../images/products/crab.jpg',
    'Red Snapper' => '../images/products/red-snapper.jpg',
    'Oysters' => '../images/products/oysters.jpg',
];

$updated = 0;
$failed = 0;

// First, verify the images exist
$imagePath = __DIR__ . '/../images/products/';
echo "<h3>Checking local images...</h3>";
$missingImages = [];
foreach ($imageMap as $product => $relPath) {
    $filename = basename($relPath);
    $fullPath = $imagePath . $filename;
    if (!file_exists($fullPath)) {
        $missingImages[] = $filename;
        echo "<p class='error'>⚠ Missing: $filename</p>";
    } else {
        echo "<p class='success'>✓ Found: $filename</p>";
    }
}

if (!empty($missingImages)) {
    echo "<h3 class='error'>Warning: " . count($missingImages) . " images are missing!</h3>";
    echo "<p>These images will use a fallback placeholder.</p>";
}

echo "<hr><h3>Updating database...</h3>";

try {
    $products = $api->select('products', []);
    
    echo "<p class='info'>Found " . count($products) . " products</p>";
    
    foreach ($products as $product) {
        $productName = $product['name'];
        
        if (isset($imageMap[$productName])) {
            $newImageUrl = $imageMap[$productName];
            
            $result = $api->update('products', 
                ['image_url' => $newImageUrl],
                ['id' => $product['id']]
            );
            
            if ($result) {
                echo "<p class='success'>✓ Updated: $productName → $newImageUrl</p>";
                $updated++;
            } else {
                echo "<p class='error'>✗ Failed: $productName</p>";
                $failed++;
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><h2>Update Complete!</h2>";
echo "<p class='success'>Successfully updated: $updated products to use LOCAL images</p>";
echo "<p class='error'>Failed: $failed products</p>";
echo "<p>Images will now load INSTANTLY from your local /images/products/ folder!</p>";
echo "<p><a href='../user/user-homepage.php'>Go to Homepage</a> | <a href='../user/shop-products.php?shop=Mesa%20Farm'>View Mesa Farm</a></p>";
echo "</body></html>";
?>
