<?php
/**
 * Download real product images using PHP
 */

set_time_limit(600);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Download Product Images</title>";
echo "<style>body{font-family:sans-serif;padding:20px;background:#f5f5f5;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body><h1>Downloading Product Images</h1>";

$products = [
    // Mesa Farm
    'tomatoes.jpg' => 'https://images.pexels.com/photos/1435735/pexels-photo-1435735.jpeg?w=400&h=300',
    'lettuce.jpg' => 'https://images.pexels.com/photos/1352199/pexels-photo-1352199.jpeg?w=400&h=300',
    'carrots.jpg' => 'https://images.pexels.com/photos/143133/pexels-photo-143133.jpeg?w=400&h=300',
    'basil.jpg' => 'https://images.pexels.com/photos/4198926/pexels-photo-4198926.jpeg?w=400&h=300',
    'spinach.jpg' => 'https://images.pexels.com/photos/2255935/pexels-photo-2255935.jpeg?w=400&h=300',
    'bell-peppers.jpg' => 'https://images.pexels.com/photos/3850555/pexels-photo-3850555.jpeg?w=400&h=300',
    'rosemary.jpg' => 'https://images.pexels.com/photos/4033148/pexels-photo-4033148.jpeg?w=400&h=300',
    'cucumbers.jpg' => 'https://images.pexels.com/photos/37528/cucumber-salad-food-healthy-37528.jpeg?w=400&h=300',
    
    // Taco Bell
    'jalapenos.jpg' => 'https://images.pexels.com/photos/2802527/pexels-photo-2802527.jpeg?w=400&h=300',
    'red-onions.jpg' => 'https://images.pexels.com/photos/2255801/pexels-photo-2255801.jpeg?w=400&h=300',
    'cilantro.jpg' => 'https://images.pexels.com/photos/4198903/pexels-photo-4198903.jpeg?w=400&h=300',
    'avocados.jpg' => 'https://images.pexels.com/photos/557659/pexels-photo-557659.jpeg?w=400&h=300',
    'limes.jpg' => 'https://images.pexels.com/photos/327098/pexels-photo-327098.jpeg?w=400&h=300',
    'chili-peppers.jpg' => 'https://images.pexels.com/photos/2802527/pexels-photo-2802527.jpeg?w=400&h=300',
    'tomatillos.jpg' => 'https://images.pexels.com/photos/1435735/pexels-photo-1435735.jpeg?w=400&h=300',
    'corn.jpg' => 'https://images.pexels.com/photos/547263/pexels-photo-547263.jpeg?w=400&h=300',
    
    // Jay's Artisan
    'coffee-beans.jpg' => 'https://images.pexels.com/photos/1695052/pexels-photo-1695052.jpeg?w=400&h=300',
    'sourdough.jpg' => 'https://images.pexels.com/photos/4109998/pexels-photo-4109998.jpeg?w=400&h=300',
    'baguette.jpg' => 'https://images.pexels.com/photos/209206/pexels-photo-209206.jpeg?w=400&h=300',
    'espresso.jpg' => 'https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?w=400&h=300',
    'wheat-bread.jpg' => 'https://images.pexels.com/photos/1775043/pexels-photo-1775043.jpeg?w=400&h=300',
    'croissants.jpg' => 'https://images.pexels.com/photos/2135677/pexels-photo-2135677.jpeg?w=400&h=300',
    'cold-brew.jpg' => 'https://images.pexels.com/photos/1251175/pexels-photo-1251175.jpeg?w=400&h=300',
    'multigrain.jpg' => 'https://images.pexels.com/photos/209540/pexels-photo-209540.jpeg?w=400&h=300',
    'colombian-coffee.jpg' => 'https://images.pexels.com/photos/1695052/pexels-photo-1695052.jpeg?w=400&h=300',
    
    // Ocean Fresh
    'salmon.jpg' => 'https://images.pexels.com/photos/3296/food-plate-salmon-rice.jpg?w=400&h=300',
    'prawns.jpg' => 'https://images.pexels.com/photos/725991/pexels-photo-725991.jpeg?w=400&h=300',
    'tuna.jpg' => 'https://images.pexels.com/photos/4109998/pexels-photo-4109998.jpeg?w=400&h=300',
    'sea-bass.jpg' => 'https://images.pexels.com/photos/248804/pexels-photo-248804.jpeg?w=400&h=300',
    'squid.jpg' => 'https://images.pexels.com/photos/5639944/pexels-photo-5639944.jpeg?w=400&h=300',
    'mussels.jpg' => 'https://images.pexels.com/photos/3296/food-plate-salmon-rice.jpg?w=400&h=300',
    'crab.jpg' => 'https://images.pexels.com/photos/725997/pexels-photo-725997.jpeg?w=400&h=300',
    'red-snapper.jpg' => 'https://images.pexels.com/photos/248804/pexels-photo-248804.jpeg?w=400&h=300',
    'oysters.jpg' => 'https://images.pexels.com/photos/5638529/pexels-photo-5638529.jpeg?w=400&h=300',
];

$destPath = __DIR__ . '/../images/products/';
$success = 0;
$failed = 0;

foreach ($products as $filename => $url) {
    $filePath = $destPath . $filename;
    
    echo "<p class='info'>Downloading $filename...</p>";
    
    $imageData = @file_get_contents($url);
    
    if ($imageData !== false) {
        if (file_put_contents($filePath, $imageData)) {
            echo "<p class='success'>✓ Successfully downloaded $filename</p>";
            $success++;
        } else {
            echo "<p class='error'>✗ Failed to save $filename</p>";
            $failed++;
        }
    } else {
        echo "<p class='error'>✗ Failed to download $filename from URL</p>";
        $failed++;
    }
    
    // Small delay to avoid rate limiting
    usleep(500000); // 0.5 seconds
    
    // Flush output so user can see progress
    flush();
    ob_flush();
}

echo "<hr><h2>Download Complete!</h2>";
echo "<p class='success'>Successfully downloaded: $success images</p>";
echo "<p class='error'>Failed: $failed images</p>";
echo "<p><a href='../user/user-homepage.php'>Go to Homepage</a> | <a href='check-products.php'>View Products</a></p>";
echo "</body></html>";
?>
