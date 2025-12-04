<?php
/**
 * Image Loading Debugging Tool
 * Run this to diagnose image loading issues
 */

require_once __DIR__ . '/config/supabase-api.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Image Debug</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".test{background:white;padding:15px;margin:10px 0;border-radius:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}";
echo ".pass{border-left:4px solid #4CAF50;} .fail{border-left:4px solid #f44336;}";
echo "img{max-width:200px;height:auto;margin:10px;border:2px solid #ddd;}";
echo "</style></head><body>";

echo "<h1>🔍 Image Loading Debug Tool</h1>";

// 1. Check if images directory exists
echo "<div class='test " . (is_dir(__DIR__ . '/images/products') ? 'pass' : 'fail') . "'>";
echo "<h3>1. Images Directory Check</h3>";
if (is_dir(__DIR__ . '/images/products')) {
    $imageCount = count(glob(__DIR__ . '/images/products/*.{jpg,jpeg,png,gif}', GLOB_BRACE));
    echo "✅ Directory exists: <code>" . __DIR__ . "/images/products</code><br>";
    echo "📁 Found {$imageCount} image files<br>";
    
    // Show some sample files
    $samples = array_slice(glob(__DIR__ . '/images/products/*.{jpg,jpeg,png,gif}', GLOB_BRACE), 0, 5);
    echo "<br><strong>Sample files:</strong><br>";
    foreach ($samples as $file) {
        $size = round(filesize($file) / 1024, 2);
        echo "• " . basename($file) . " ({$size} KB)<br>";
    }
} else {
    echo "❌ Directory NOT found: " . __DIR__ . "/images/products";
}
echo "</div>";

// 2. Check database image URLs
echo "<div class='test'>";
echo "<h3>2. Database Image URL Check</h3>";
try {
    $api = getSupabaseAPI();
    $products = $api->select('products', [], 5); // Get 5 products
    
    if (!empty($products)) {
        echo "✅ Connected to database<br>";
        echo "📊 Sample product image URLs:<br><br>";
        
        echo "<table border='1' style='border-collapse:collapse;width:100%;background:white;'>";
        echo "<tr><th style='padding:8px;'>Product</th><th>Image URL</th><th>File Exists?</th><th>Preview</th></tr>";
        
        foreach ($products as $product) {
            $imageUrl = $product['image_url'] ?? '';
            $name = $product['name'] ?? 'Unknown';
            
            // Check if file exists
            $localPath = __DIR__ . '/' . str_replace('../', '', $imageUrl);
            $exists = file_exists($localPath);
            
            echo "<tr>";
            echo "<td style='padding:8px;'>{$name}</td>";
            echo "<td style='padding:8px;'><code>{$imageUrl}</code></td>";
            echo "<td style='padding:8px;text-align:center;'>" . ($exists ? "✅" : "❌") . "</td>";
            echo "<td style='padding:8px;'>";
            if (!empty($imageUrl)) {
                echo "<img src='{$imageUrl}' alt='{$name}' onerror=\"this.style.border='2px solid red'; this.alt='FAILED TO LOAD';\">";
            } else {
                echo "No URL";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No products found in database";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}
echo "</div>";

// 3. Check server configuration
echo "<div class='test pass'>";
echo "<h3>3. Server Configuration</h3>";
echo "📍 <strong>Document Root:</strong> <code>" . $_SERVER['DOCUMENT_ROOT'] . "</code><br>";
echo "📍 <strong>Script Path:</strong> <code>" . __DIR__ . "</code><br>";
echo "📍 <strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "📍 <strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "📍 <strong>Current URL:</strong> <code>" . ($_SERVER['REQUEST_SCHEME'] ?? 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</code><br>";
echo "</div>";

// 4. Test relative paths
echo "<div class='test'>";
echo "<h3>4. Relative Path Test</h3>";
echo "Testing if browser can load images with different path formats:<br><br>";

$testImage = 'Fresh Milk.png';
echo "<strong>A. Relative path (../images/products/):</strong><br>";
echo "<img src='../images/products/{$testImage}' alt='Test 1'><br><br>";

echo "<strong>B. Relative path (./images/products/):</strong><br>";
echo "<img src='./images/products/{$testImage}' alt='Test 2'><br><br>";

echo "<strong>C. Relative path (images/products/):</strong><br>";
echo "<img src='images/products/{$testImage}' alt='Test 3'><br><br>";

echo "<strong>D. Absolute path from root (/The-Farmers-Mall/images/products/):</strong><br>";
echo "<img src='/The-Farmers-Mall/images/products/{$testImage}' alt='Test 4'><br><br>";

echo "</div>";

// 5. Browser info via JavaScript
echo "<div class='test pass'>";
echo "<h3>5. Browser & Network Info</h3>";
echo "<div id='browserInfo'>Loading...</div>";
echo "<script>
document.getElementById('browserInfo').innerHTML = 
    '🌐 <strong>User Agent:</strong> ' + navigator.userAgent + '<br>' +
    '📱 <strong>Platform:</strong> ' + navigator.platform + '<br>' +
    '🌍 <strong>Language:</strong> ' + navigator.language + '<br>' +
    '📶 <strong>Online Status:</strong> ' + (navigator.onLine ? '✅ Online' : '❌ Offline') + '<br>' +
    '🔌 <strong>Connection:</strong> ' + (navigator.connection ? navigator.connection.effectiveType : 'Unknown');
</script>";
echo "</div>";

echo "<hr style='margin:30px 0;'>";
echo "<p><strong>🎯 Instructions for your team:</strong></p>";
echo "<ol>";
echo "<li>Send this URL to your team members</li>";
echo "<li>Ask them to open it and screenshot the results</li>";
echo "<li>Compare which tests pass ✅ and which fail ❌</li>";
echo "<li>This will help identify the exact issue</li>";
echo "</ol>";

echo "</body></html>";
?>
