<?php
/**
 * Generate placeholder product images
 * This script creates simple placeholder images for all products
 */

// Ensure the products directory exists
$productsDir = __DIR__ . '/../images/products';
if (!is_dir($productsDir)) {
    mkdir($productsDir, 0777, true);
}

// Define all product images needed
$products = [
    // Mesa Farm
    'tomatoes' => ['Tomatoes', '#FF6347'],
    'lettuce' => ['Lettuce', '#90EE90'],
    'carrots' => ['Carrots', '#FFA500'],
    'basil' => ['Basil', '#228B22'],
    'spinach' => ['Spinach', '#006400'],
    'bell-peppers' => ['Bell Peppers', '#FF4500'],
    'rosemary' => ['Rosemary', '#556B2F'],
    'cucumbers' => ['Cucumbers', '#3CB371'],
    
    // Taco Bell
    'jalapenos' => ['Jalapeños', '#228B22'],
    'red-onions' => ['Red Onions', '#8B0000'],
    'cilantro' => ['Cilantro', '#32CD32'],
    'avocados' => ['Avocados', '#568203'],
    'limes' => ['Limes', '#00FF00'],
    'chili-peppers' => ['Chili Peppers', '#DC143C'],
    'tomatillos' => ['Tomatillos', '#9ACD32'],
    'corn' => ['Corn', '#FFD700'],
    
    // Jay's Artisan
    'coffee-beans' => ['Coffee Beans', '#6F4E37'],
    'sourdough' => ['Sourdough', '#D2691E'],
    'baguette' => ['Baguette', '#DEB887'],
    'espresso' => ['Espresso', '#3E2723'],
    'wheat-bread' => ['Wheat Bread', '#8B4513'],
    'croissants' => ['Croissants', '#F4A460'],
    'cold-brew' => ['Cold Brew', '#4A3C31'],
    'multigrain' => ['Multigrain', '#A0522D'],
    'colombian-coffee' => ['Colombian', '#5D4037'],
    
    // Ocean Fresh
    'salmon' => ['Salmon', '#FA8072'],
    'prawns' => ['Prawns', '#FFB6C1'],
    'tuna' => ['Tuna', '#FF6347'],
    'sea-bass' => ['Sea Bass', '#C0C0C0'],
    'squid' => ['Squid', '#F5F5DC'],
    'mussels' => ['Mussels', '#2F4F4F'],
    'crab' => ['Crab', '#FF7F50'],
    'red-snapper' => ['Red Snapper', '#DC143C'],
    'oysters' => ['Oysters', '#D3D3D3'],
];

// Check if GD library is available
if (!function_exists('imagecreatetruecolor')) {
    die("<h1 style='color: red;'>Error: GD Library is not installed</h1>
         <p>Please enable the GD extension in your php.ini file:</p>
         <ol>
             <li>Find your php.ini file</li>
             <li>Search for <code>;extension=gd</code></li>
             <li>Remove the semicolon to uncomment: <code>extension=gd</code></li>
             <li>Restart your web server</li>
         </ol>");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Generate Product Images</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #2E7D32; }
        .success { color: green; padding: 5px 0; }
        .error { color: red; padding: 5px 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
        .product-img { width: 100%; height: 150px; object-fit: cover; border-radius: 8px; }
        .product-name { text-align: center; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🖼️ Generate Product Images</h1>
        <p>Creating placeholder images for all products...</p>
        <hr>";

$created = 0;
$skipped = 0;

foreach ($products as $filename => $data) {
    list($name, $color) = $data;
    $filepath = $productsDir . '/' . $filename . '.jpg';
    
    if (file_exists($filepath)) {
        echo "<div class='success'>✓ {$name} - Already exists</div>";
        $skipped++;
        continue;
    }
    
    // Create image
    $width = 400;
    $height = 300;
    $image = @imagecreatetruecolor($width, $height);
    
    if (!$image) {
        echo "<div class='error'>❌ Failed to create image for: {$name}</div>";
        continue;
    }
    
    // Convert hex to RGB
    $rgb = sscanf($color, "#%02x%02x%02x");
    $bgColor = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $shadowColor = imagecolorallocate($image, 0, 0, 0);
    
    // Fill background
    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
    
    // Add text
    $fontSize = 5;
    $textWidth = imagefontwidth($fontSize) * strlen($name);
    $textHeight = imagefontheight($fontSize);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    // Add shadow
    imagestring($image, $fontSize, $x + 2, $y + 2, $name, $shadowColor);
    // Add text
    imagestring($image, $fontSize, $x, $y, $name, $textColor);
    
    // Save image
    if (imagejpeg($image, $filepath, 85)) {
        echo "<div class='success'>✅ Created: {$name}</div>";
        $created++;
    } else {
        echo "<div class='error'>❌ Failed: {$name}</div>";
    }
    
    imagedestroy($image);
}

echo "<hr>
        <h2>📊 Summary</h2>
        <p><strong>Created:</strong> {$created} images</p>
        <p><strong>Skipped:</strong> {$skipped} images (already exist)</p>
        <p><strong>Total:</strong> " . count($products) . " product images</p>
        
        <h2>🖼️ Preview</h2>
        <div class='grid'>";

// Display all images
foreach ($products as $filename => $data) {
    list($name) = $data;
    $filepath = '../images/products/' . $filename . '.jpg';
    echo "<div>
            <img src='{$filepath}' alt='{$name}' class='product-img'>
            <div class='product-name'>{$name}</div>
          </div>";
}

echo "</div>
        <hr>
        <p><a href='populate-shops.php' style='color: #2E7D32; font-weight: bold;'>→ Next: Populate Shops with Products</a></p>
    </div>
</body>
</html>";
?>
