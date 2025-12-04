<?php
/**
 * Update product images to use placeholder.co with different colors
 */

require_once __DIR__ . '/supabase-api.php';

set_time_limit(300);

echo "<!DOCTYPE html><html><head><title>Update to Colored Placeholders</title>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body><h1>Updating Product Images</h1>";

$api = getSupabaseAPI();

// Color-coded placeholders
$imageMap = [
    // Mesa Farm - Green/Red vegetables
    'Organic Tomatoes' => 'https://placehold.co/400x300/ff6347/white?text=Organic+Tomatoes',
    'Fresh Lettuce' => 'https://placehold.co/400x300/90ee90/black?text=Fresh+Lettuce',
    'Organic Carrots' => 'https://placehold.co/400x300/ff8c00/white?text=Organic+Carrots',
    'Fresh Basil' => 'https://placehold.co/400x300/228b22/white?text=Fresh+Basil',
    'Organic Spinach' => 'https://placehold.co/400x300/3cb371/white?text=Organic+Spinach',
    'Bell Peppers' => 'https://placehold.co/400x300/ff4500/white?text=Bell+Peppers',
    'Fresh Rosemary' => 'https://placehold.co/400x300/556b2f/white?text=Fresh+Rosemary',
    'Organic Cucumbers' => 'https://placehold.co/400x300/98fb98/black?text=Organic+Cucumbers',
    
    // Taco Bell - Mexican colors
    'Fresh Jalapeños' => 'https://placehold.co/400x300/32cd32/white?text=Fresh+Jalapeños',
    'Red Onions' => 'https://placehold.co/400x300/8b008b/white?text=Red+Onions',
    'Cilantro Bundle' => 'https://placehold.co/400x300/7cfc00/black?text=Cilantro+Bundle',
    'Avocados' => 'https://placehold.co/400x300/6b8e23/white?text=Avocados',
    'Fresh Limes' => 'https://placehold.co/400x300/00ff00/black?text=Fresh+Limes',
    'Mexican Chili Peppers' => 'https://placehold.co/400x300/dc143c/white?text=Chili+Peppers',
    'Tomatillos' => 'https://placehold.co/400x300/adff2f/black?text=Tomatillos',
    'Corn Kernels' => 'https://placehold.co/400x300/ffd700/black?text=Corn+Kernels',
    
    // Jay's Artisan - Brown/Tan bread and coffee
    'Arabica Coffee Beans' => 'https://placehold.co/400x300/6f4e37/white?text=Coffee+Beans',
    'Sourdough Bread' => 'https://placehold.co/400x300/d2691e/white?text=Sourdough+Bread',
    'French Baguette' => 'https://placehold.co/400x300/deb887/black?text=French+Baguette',
    'Espresso Blend' => 'https://placehold.co/400x300/3e2723/white?text=Espresso+Blend',
    'Whole Wheat Bread' => 'https://placehold.co/400x300/8b4513/white?text=Wheat+Bread',
    'Croissants' => 'https://placehold.co/400x300/f5deb3/black?text=Croissants',
    'Cold Brew Coffee' => 'https://placehold.co/400x300/4b3621/white?text=Cold+Brew',
    'Multigrain Bread' => 'https://placehold.co/400x300/a0522d/white?text=Multigrain+Bread',
    'Colombian Coffee' => 'https://placehold.co/400x300/654321/white?text=Colombian+Coffee',
    
    // Ocean Fresh - Ocean colors
    'Fresh Salmon' => 'https://placehold.co/400x300/fa8072/white?text=Fresh+Salmon',
    'Tiger Prawns' => 'https://placehold.co/400x300/ffb6c1/black?text=Tiger+Prawns',
    'Fresh Tuna' => 'https://placehold.co/400x300/ff69b4/white?text=Fresh+Tuna',
    'Sea Bass' => 'https://placehold.co/400x300/c0c0c0/black?text=Sea+Bass',
    'Squid' => 'https://placehold.co/400x300/f0f8ff/black?text=Squid',
    'Mussels' => 'https://placehold.co/400x300/2f4f4f/white?text=Mussels',
    'Crab Meat' => 'https://placehold.co/400x300/ff6347/white?text=Crab+Meat',
    'Red Snapper' => 'https://placehold.co/400x300/dc143c/white?text=Red+Snapper',
    'Oysters' => 'https://placehold.co/400x300/e6e6fa/black?text=Oysters',
];

$updated = 0;
$failed = 0;

try {
    // Get all products
    $products = $api->select('products', []);
    
    echo "<p class='info'>Found " . count($products) . " products in database</p>";
    
    foreach ($products as $product) {
        $productName = $product['name'];
        
        if (isset($imageMap[$productName])) {
            $newImageUrl = $imageMap[$productName];
            
            // Update product
            $result = $api->update('products', 
                ['image_url' => $newImageUrl],
                ['id' => $product['id']]
            );
            
            if ($result) {
                echo "<p class='success'>✓ Updated: $productName</p>";
                $updated++;
            } else {
                echo "<p class='error'>✗ Failed to update: $productName</p>";
                $failed++;
            }
        } else {
            echo "<p class='error'>⚠ No image mapping for: $productName</p>";
            $failed++;
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><h2>Update Complete!</h2>";
echo "<p class='success'>Successfully updated: $updated products</p>";
echo "<p class='error'>Failed: $failed products</p>";
echo "<p>Now each product will have a different colored placeholder with its name!</p>";
echo "<p><a href='../user/user-homepage.php'>Go to Homepage</a> | <a href='../user/shop-products.php?shop=Mesa%20Farm'>View Mesa Farm</a></p>";
echo "</body></html>";
?>
