<?php
/**
 * Copy existing product images to create placeholders
 * This script doesn't require GD library
 */

$sourceDir = __DIR__ . '/../images';
$productsDir = __DIR__ . '/../images/products';

// Create products directory if it doesn't exist
if (!is_dir($productsDir)) {
    mkdir($productsDir, 0777, true);
}

// List of product image files we need
$productImages = [
    'tomatoes.jpg', 'lettuce.jpg', 'carrots.jpg', 'basil.jpg', 'spinach.jpg', 
    'bell-peppers.jpg', 'rosemary.jpg', 'cucumbers.jpg',
    'jalapenos.jpg', 'red-onions.jpg', 'cilantro.jpg', 'avocados.jpg', 
    'limes.jpg', 'chili-peppers.jpg', 'tomatillos.jpg', 'corn.jpg',
    'coffee-beans.jpg', 'sourdough.jpg', 'baguette.jpg', 'espresso.jpg', 
    'wheat-bread.jpg', 'croissants.jpg', 'cold-brew.jpg', 'multigrain.jpg', 'colombian-coffee.jpg',
    'salmon.jpg', 'prawns.jpg', 'tuna.jpg', 'sea-bass.jpg', 'squid.jpg', 
    'mussels.jpg', 'crab.jpg', 'red-snapper.jpg', 'oysters.jpg'
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Product Images</title>
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
        <h1>📦 Setup Product Images</h1>";

// Look for existing images in the images folder to use as templates
$existingImages = glob($sourceDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

if (!empty($existingImages)) {
    echo "<div class='info'>Found " . count($existingImages) . " existing images. Using them as templates...</div>";
    
    $created = 0;
    $templateImage = $existingImages[0]; // Use first available image as template
    
    foreach ($productImages as $productImage) {
        $destPath = $productsDir . '/' . $productImage;
        
        if (!file_exists($destPath)) {
            // Copy the template image
            if (copy($templateImage, $destPath)) {
                echo "<div class='success'>✅ Created: {$productImage}</div>";
                $created++;
            }
        } else {
            echo "<div class='success'>✓ Already exists: {$productImage}</div>";
        }
    }
    
    echo "<div class='info'><strong>✅ Setup Complete!</strong><br>Created {$created} product image placeholders.</div>";
    
} else {
    echo "<div class='info'><strong>No existing images found.</strong><br>
          The system will use online placeholder images instead.<br>
          This is perfectly fine and will work well!</div>";
}

echo "<hr>
        <p><strong>Next Step:</strong></p>
        <a href='populate-shops.php' class='btn'>→ Continue to Populate Shops</a>
    </div>
</body>
</html>";
?>
