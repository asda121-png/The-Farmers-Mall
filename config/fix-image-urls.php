<?php
/**
 * Update to use placeholder.pics which works better with localhost
 */

require_once __DIR__ . '/supabase-api.php';

set_time_limit(300);

echo "<!DOCTYPE html><html><head><title>Update to Working Placeholders</title>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body><h1>Updating to Working Image Placeholders</h1>";

$api = getSupabaseAPI();

// Using via.placeholder.com which works better with HTTP localhost
$imageMap = [
    // Mesa Farm
    'Organic Tomatoes' => 'https://via.placeholder.com/400x300/ff6347/ffffff?text=Organic+Tomatoes',
    'Fresh Lettuce' => 'https://via.placeholder.com/400x300/90ee90/000000?text=Fresh+Lettuce',
    'Organic Carrots' => 'https://via.placeholder.com/400x300/ff8c00/ffffff?text=Organic+Carrots',
    'Fresh Basil' => 'https://via.placeholder.com/400x300/228b22/ffffff?text=Fresh+Basil',
    'Organic Spinach' => 'https://via.placeholder.com/400x300/3cb371/ffffff?text=Organic+Spinach',
    'Bell Peppers' => 'https://via.placeholder.com/400x300/ff4500/ffffff?text=Bell+Peppers',
    'Fresh Rosemary' => 'https://via.placeholder.com/400x300/556b2f/ffffff?text=Fresh+Rosemary',
    'Organic Cucumbers' => 'https://via.placeholder.com/400x300/98fb98/000000?text=Organic+Cucumbers',
    
    // Taco Bell
    'Fresh Jalapeños' => 'https://via.placeholder.com/400x300/32cd32/ffffff?text=Fresh+Jalapeños',
    'Red Onions' => 'https://via.placeholder.com/400x300/8b008b/ffffff?text=Red+Onions',
    'Cilantro Bundle' => 'https://via.placeholder.com/400x300/7cfc00/000000?text=Cilantro+Bundle',
    'Avocados' => 'https://via.placeholder.com/400x300/6b8e23/ffffff?text=Avocados',
    'Fresh Limes' => 'https://via.placeholder.com/400x300/00ff00/000000?text=Fresh+Limes',
    'Mexican Chili Peppers' => 'https://via.placeholder.com/400x300/dc143c/ffffff?text=Chili+Peppers',
    'Tomatillos' => 'https://via.placeholder.com/400x300/adff2f/000000?text=Tomatillos',
    'Corn Kernels' => 'https://via.placeholder.com/400x300/ffd700/000000?text=Corn+Kernels',
    
    // Jay's Artisan
    'Arabica Coffee Beans' => 'https://via.placeholder.com/400x300/6f4e37/ffffff?text=Coffee+Beans',
    'Sourdough Bread' => 'https://via.placeholder.com/400x300/d2691e/ffffff?text=Sourdough+Bread',
    'French Baguette' => 'https://via.placeholder.com/400x300/deb887/000000?text=French+Baguette',
    'Espresso Blend' => 'https://via.placeholder.com/400x300/3e2723/ffffff?text=Espresso+Blend',
    'Whole Wheat Bread' => 'https://via.placeholder.com/400x300/8b4513/ffffff?text=Wheat+Bread',
    'Croissants' => 'https://via.placeholder.com/400x300/f5deb3/000000?text=Croissants',
    'Cold Brew Coffee' => 'https://via.placeholder.com/400x300/4b3621/ffffff?text=Cold+Brew',
    'Multigrain Bread' => 'https://via.placeholder.com/400x300/a0522d/ffffff?text=Multigrain+Bread',
    'Colombian Coffee' => 'https://via.placeholder.com/400x300/654321/ffffff?text=Colombian+Coffee',
    
    // Ocean Fresh
    'Fresh Salmon' => 'https://via.placeholder.com/400x300/fa8072/ffffff?text=Fresh+Salmon',
    'Tiger Prawns' => 'https://via.placeholder.com/400x300/ffb6c1/000000?text=Tiger+Prawns',
    'Fresh Tuna' => 'https://via.placeholder.com/400x300/ff69b4/ffffff?text=Fresh+Tuna',
    'Sea Bass' => 'https://via.placeholder.com/400x300/c0c0c0/000000?text=Sea+Bass',
    'Squid' => 'https://via.placeholder.com/400x300/f0f8ff/000000?text=Squid',
    'Mussels' => 'https://via.placeholder.com/400x300/2f4f4f/ffffff?text=Mussels',
    'Crab Meat' => 'https://via.placeholder.com/400x300/ff6347/ffffff?text=Crab+Meat',
    'Red Snapper' => 'https://via.placeholder.com/400x300/dc143c/ffffff?text=Red+Snapper',
    'Oysters' => 'https://via.placeholder.com/400x300/e6e6fa/000000?text=Oysters',
];

$updated = 0;
$failed = 0;

try {
    $products = $api->select('products', []);
    
    echo "<p class='info'>Found " . count($products) . " products</p>";
    
    foreach ($products as $product) {
        $productName = $product['name'];
        
        if (isset($imageMap[$productName])) {
            $newImageUrl = $imageMap[$productName];
            
            try {
                $api->update('products', 
                    ['image_url' => $newImageUrl],
                    ['id' => $product['id']]
                );
                echo "<p class='success'>✓ Updated: $productName</p>";
                $updated++;
            } catch (Exception $e) {
                echo "<p class='error'>✗ Failed: $productName - " . htmlspecialchars($e->getMessage()) . "</p>";
                $failed++;
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><h2>Update Complete!</h2>";
echo "<p class='success'>Successfully updated: $updated products</p>";
echo "<p class='error'>Failed: $failed products</p>";
echo "<p><a href='../user/user-homepage.php'>Go to Homepage</a></p>";
echo "</body></html>";
?>
