<?php
/**
 * Create simple colored placeholder images for each product using GD library
 */

set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Create Product Placeholders</title>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body><h1>Creating Product Placeholder Images</h1>";

// Check if GD is available
if (!extension_loaded('gd')) {
    echo "<p class='error'>GD library is not installed. Cannot create images.</p>";
    echo "<p>Please manually add different product images to /images/products/ folder.</p>";
    echo "</body></html>";
    exit;
}

$products = [
    // Mesa Farm - Green tones
    'tomatoes.jpg' => ['Red Tomatoes', '#FF6347'],
    'lettuce.jpg' => ['Fresh Lettuce', '#90EE90'],
    'carrots.jpg' => ['Carrots', '#FF8C00'],
    'basil.jpg' => ['Basil', '#228B22'],
    'spinach.jpg' => ['Spinach', '#3CB371'],
    'bell-peppers.jpg' => ['Bell Peppers', '#FF4500'],
    'rosemary.jpg' => ['Rosemary', '#556B2F'],
    'cucumbers.jpg' => ['Cucumbers', '#98FB98'],
    
    // Taco Bell - Orange/Red tones
    'jalapenos.jpg' => ['Jalapeños', '#32CD32'],
    'red-onions.jpg' => ['Red Onions', '#8B008B'],
    'cilantro.jpg' => ['Cilantro', '#7CFC00'],
    'avocados.jpg' => ['Avocados', '#6B8E23'],
    'limes.jpg' => ['Limes', '#00FF00'],
    'chili-peppers.jpg' => ['Chili Peppers', '#DC143C'],
    'tomatillos.jpg' => ['Tomatillos', '#ADFF2F'],
    'corn.jpg' => ['Corn', '#FFD700'],
    
    // Jay's Artisan - Brown tones
    'coffee-beans.jpg' => ['Coffee Beans', '#6F4E37'],
    'sourdough.jpg' => ['Sourdough', '#D2691E'],
    'baguette.jpg' => ['Baguette', '#DEB887'],
    'espresso.jpg' => ['Espresso', '#3E2723'],
    'wheat-bread.jpg' => ['Wheat Bread', '#8B4513'],
    'croissants.jpg' => ['Croissants', '#F5DEB3'],
    'cold-brew.jpg' => ['Cold Brew', '#4B3621'],
    'multigrain.jpg' => ['Multigrain', '#A0522D'],
    'colombian-coffee.jpg' => ['Colombian Coffee', '#654321'],
    
    // Ocean Fresh - Blue/Silver tones
    'salmon.jpg' => ['Salmon', '#FA8072'],
    'prawns.jpg' => ['Prawns', '#FFB6C1'],
    'tuna.jpg' => ['Tuna', '#FF69B4'],
    'sea-bass.jpg' => ['Sea Bass', '#C0C0C0'],
    'squid.jpg' => ['Squid', '#F0F8FF'],
    'mussels.jpg' => ['Mussels', '#2F4F4F'],
    'crab.jpg' => ['Crab', '#FF6347'],
    'red-snapper.jpg' => ['Red Snapper', '#DC143C'],
    'oysters.jpg' => ['Oysters', '#E6E6FA'],
];

$destPath = __DIR__ . '/../images/products/';
$success = 0;
$failed = 0;

foreach ($products as $filename => $data) {
    list($text, $color) = $data;
    $filePath = $destPath . $filename;
    
    // Create image
    $width = 400;
    $height = 300;
    $image = imagecreatetruecolor($width, $height);
    
    // Allocate colors
    list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
    $bgColor = imagecolorallocate($image, $r, $g, $b);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $shadowColor = imagecolorallocate($image, 0, 0, 0);
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add text
    $fontSize = 5;
    $textWidth = imagefontwidth($fontSize) * strlen($text);
    $textHeight = imagefontheight($fontSize);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    // Shadow
    imagestring($image, $fontSize, $x + 2, $y + 2, $text, $shadowColor);
    // Text
    imagestring($image, $fontSize, $x, $y, $text, $textColor);
    
    // Save image
    if (imagejpeg($image, $filePath, 90)) {
        echo "<p class='success'>✓ Created $filename ($text)</p>";
        $success++;
    } else {
        echo "<p class='error'>✗ Failed to save $filename</p>";
        $failed++;
    }
    
    // imagedestroy() is deprecated in PHP 8+, resources are auto-freed
}

echo "<hr><h2>Creation Complete!</h2>";
echo "<p class='success'>Successfully created: $success images</p>";
echo "<p class='error'>Failed: $failed images</p>";
echo "<p><a href='../user/user-homepage.php'>Go to Homepage</a></p>";
echo "</body></html>";
?>
