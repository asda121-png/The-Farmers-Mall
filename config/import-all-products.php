<?php
/**
 * Import All Products Script
 * This script extracts product information from all pages and stores them in the database
 */

require_once __DIR__ . '/supabase-api.php';

// Set execution time limit for large imports
set_time_limit(300);

// Initialize API
$api = getSupabaseAPI();

// Log file for tracking imports
$logFile = __DIR__ . '/product_import.log';
$timestamp = date('Y-m-d H:i:s');

function logMessage($message) {
    global $logFile, $timestamp;
    $logEntry = "[$timestamp] $message\n";
    echo $logEntry;
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

logMessage("========== PRODUCT IMPORT STARTED ==========");

// First, get or create a default retailer for these products
$defaultRetailer = null;
try {
    $retailers = $api->select('retailers', ['shop_name' => 'Farmers Mall']);
    if (!empty($retailers)) {
        $defaultRetailer = $retailers[0];
        logMessage("Found existing retailer: Farmers Mall (ID: {$defaultRetailer['id']})");
    } else {
        // Create default retailer
        $newRetailer = [
            'shop_name' => 'Farmers Mall',
            'shop_description' => 'Main Farmers Mall store offering fresh produce, dairy, meat, and more',
            'business_address' => 'Main Branch',
            'verification_status' => 'verified',
            'rating' => 4.8
        ];
        $result = $api->insert('retailers', $newRetailer);
        if (!empty($result)) {
            $defaultRetailer = $result[0];
            logMessage("Created new retailer: Farmers Mall (ID: {$defaultRetailer['id']})");
        }
    }
} catch (Exception $e) {
    logMessage("ERROR: Could not get/create retailer - " . $e->getMessage());
    die("Failed to initialize retailer. Check log file.");
}

if (!$defaultRetailer) {
    logMessage("ERROR: No retailer available for product import");
    die("Failed to get retailer information.");
}

$retailerId = $defaultRetailer['id'];

// Comprehensive product list from all pages
$products = [
    // From user-homepage.php - Top Products Section
    [
        'name' => 'Fresh Vegetable Box',
        'description' => 'A curated box of fresh vegetables, including a variety of greens and roots, sourced directly from local farms for maximum freshness.',
        'category' => 'vegetables',
        'price' => 45.00,
        'stock_quantity' => 50,
        'unit' => 'box',
        'image_url' => 'images/products/Fresh Vegetable Box.png',
        'status' => 'active'
    ],
    [
        'name' => 'Organic Lettuce',
        'description' => 'Crisp and fresh organic lettuce, perfect for salads and sandwiches.',
        'category' => 'vegetables',
        'price' => 30.00,
        'stock_quantity' => 100,
        'unit' => 'kg',
        'image_url' => 'images/products/organic lettuce.png',
        'status' => 'active'
    ],
    [
        'name' => 'Fresh Milk',
        'description' => 'Pure and fresh milk straight from local farms, rich in nutrients and perfect for daily consumption.',
        'category' => 'dairy',
        'price' => 50.00,
        'stock_quantity' => 80,
        'unit' => 'liter',
        'image_url' => 'images/products/fresh milk.jpeg',
        'status' => 'active'
    ],
    [
        'name' => 'Tilapia',
        'description' => 'Fresh tilapia fish, cleaned and ready to cook, sourced from local fish farms.',
        'category' => 'seafood',
        'price' => 80.00,
        'stock_quantity' => 40,
        'unit' => 'kg',
        'image_url' => 'images/products/tilapia.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Farm Eggs',
        'description' => 'Fresh eggs from free-range chickens, packed with nutrients.',
        'category' => 'dairy',
        'price' => 60.00,
        'stock_quantity' => 120,
        'unit' => 'dozen',
        'image_url' => 'images/products/fresh eggs.jpeg',
        'status' => 'active'
    ],
    
    // From user-homepage.php - Other Products Section
    [
        'name' => 'Emsaymada',
        'description' => 'Soft and buttery ensaymada, a Filipino favorite pastry topped with butter and sugar.',
        'category' => 'bakery',
        'price' => 25.00,
        'stock_quantity' => 60,
        'unit' => 'piece',
        'image_url' => 'images/products/Emsaymada.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Butter Spread',
        'description' => 'Creamy butter spread, perfect for bread and baking.',
        'category' => 'dairy',
        'price' => 70.00,
        'stock_quantity' => 50,
        'unit' => '250g',
        'image_url' => 'images/products/Butter Spread.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Bangus',
        'description' => 'Fresh milkfish (bangus), a Filipino staple, perfect for grilling or frying.',
        'category' => 'seafood',
        'price' => 140.00,
        'stock_quantity' => 35,
        'unit' => 'kg',
        'image_url' => 'images/products/Bangus.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Fresh Pork Liempo',
        'description' => 'Premium pork belly (liempo), tender and flavorful, ideal for grilling or roasting.',
        'category' => 'meat',
        'price' => 180.00,
        'stock_quantity' => 45,
        'unit' => 'kg',
        'image_url' => 'images/products/fresh pork liempo.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Fresh Avocado',
        'description' => 'Ripe and creamy avocados, perfect for salads, smoothies, or toast.',
        'category' => 'fruits',
        'price' => 50.00,
        'stock_quantity' => 70,
        'unit' => 'kg',
        'image_url' => 'images/products/fresh avocado.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Native Tomatoes',
        'description' => 'Juicy and ripe native tomatoes, perfect for salads, sauces, or cooking.',
        'category' => 'vegetables',
        'price' => 30.00,
        'stock_quantity' => 90,
        'unit' => 'kg',
        'image_url' => 'images/products/Native tomato.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Fresh Okra',
        'description' => 'Fresh green okra, perfect for soups and vegetable dishes.',
        'category' => 'vegetables',
        'price' => 25.00,
        'stock_quantity' => 60,
        'unit' => 'kg',
        'image_url' => 'images/products/fresh okra.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Native Chicken',
        'description' => 'Free-range native chicken (manok), organic and flavorful, perfect for traditional Filipino dishes.',
        'category' => 'meat',
        'price' => 260.00,
        'stock_quantity' => 30,
        'unit' => 'kg',
        'image_url' => 'images/products/native chicken.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Pork Ribs',
        'description' => 'Tender pork ribs, perfect for grilling, barbecue, or slow cooking.',
        'category' => 'meat',
        'price' => 310.00,
        'stock_quantity' => 25,
        'unit' => 'kg',
        'image_url' => 'images/products/pork ribs.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Shrimp',
        'description' => 'Fresh shrimp (hipon), perfect for stir-fries, soups, and seafood dishes.',
        'category' => 'seafood',
        'price' => 400.00,
        'stock_quantity' => 20,
        'unit' => 'kg',
        'image_url' => 'images/products/shrimp.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Chocolate Milk',
        'description' => 'Rich and creamy chocolate milk, a delicious treat for all ages.',
        'category' => 'dairy',
        'price' => 55.00,
        'stock_quantity' => 100,
        'unit' => '250ml',
        'image_url' => 'images/products/chocolate milk.jpg',
        'status' => 'active'
    ],
    [
        'name' => 'Ube Cheese Pandesal',
        'description' => 'Filipino bread roll filled with ube and cheese, a sweet and savory combination.',
        'category' => 'bakery',
        'price' => 50.00,
        'stock_quantity' => 80,
        'unit' => '5 pcs',
        'image_url' => 'images/products/ube cheese pandesal.jpg',
        'status' => 'active'
    ],
    
    // From products.php
    [
        'name' => 'Fresh Vegetable Bundle',
        'description' => 'A fresh assortment of seasonal vegetables including carrots, spinach, and broccoli, perfect for healthy meals.',
        'category' => 'vegetables',
        'price' => 24.99,
        'stock_quantity' => 55,
        'unit' => 'kg',
        'image_url' => 'images/products/Fresh Vegetable Box.png',
        'status' => 'active'
    ],
    [
        'name' => 'Fresh Strawberries',
        'description' => 'Juicy and sweet strawberries, handpicked at peak ripeness.',
        'category' => 'fruits',
        'price' => 89.99,
        'stock_quantity' => 40,
        'unit' => 'kg',
        'image_url' => 'images/products/strawberry.png',
        'status' => 'active'
    ],
    [
        'name' => 'Farm Fresh Milk',
        'description' => 'Pure and fresh milk straight from local farms, rich in nutrients and perfect for daily consumption.',
        'category' => 'dairy',
        'price' => 95.00,
        'stock_quantity' => 75,
        'unit' => 'liter',
        'image_url' => 'images/products/Fresh Milk.png',
        'status' => 'active'
    ],
    [
        'name' => 'Baby Carrots',
        'description' => 'Sweet and crunchy baby carrots, perfect for snacking or adding to meals, grown locally for freshness.',
        'category' => 'vegetables',
        'price' => 32.75,
        'stock_quantity' => 85,
        'unit' => 'kg',
        'image_url' => 'images/products/carrots.png',
        'status' => 'active'
    ],
    [
        'name' => 'Artisan Bread',
        'description' => 'Freshly baked artisan bread with a crispy crust and soft interior, made with traditional methods and high-quality ingredients.',
        'category' => 'bakery',
        'price' => 28.00,
        'stock_quantity' => 50,
        'unit' => 'loaf',
        'image_url' => 'images/products/bread.png',
        'status' => 'active'
    ],
    [
        'name' => 'Ripe Bananas',
        'description' => 'Perfectly ripe bananas, sweet and ready to eat, sourced from local plantations for optimal taste and nutrition.',
        'category' => 'fruits',
        'price' => 28.99,
        'stock_quantity' => 100,
        'unit' => 'kg',
        'image_url' => 'images/products/banana.png',
        'status' => 'active'
    ],
    [
        'name' => 'Aged Cheddar',
        'description' => 'Rich and sharp aged cheddar cheese, matured to perfection for a bold flavor, ideal for cheese boards and cooking.',
        'category' => 'dairy',
        'price' => 120.00,
        'stock_quantity' => 40,
        'unit' => '250g',
        'image_url' => 'images/products/cheese.png',
        'status' => 'active'
    ],
    [
        'name' => 'Tomato',
        'description' => 'Juicy and ripe tomatoes, perfect for salads, sauces, or cooking, grown in local greenhouses for optimal flavor.',
        'category' => 'vegetables',
        'price' => 28.00,
        'stock_quantity' => 95,
        'unit' => 'kg',
        'image_url' => 'images/products/Native tomato.jpg',
        'status' => 'active'
    ],
    
    // From productdetails.php - Related Products
    [
        'name' => 'Organic Broccoli',
        'description' => 'Fresh organic broccoli, packed with nutrients and perfect for healthy meals.',
        'category' => 'vegetables',
        'price' => 45.50,
        'stock_quantity' => 60,
        'unit' => 'kg',
        'image_url' => 'images/products/fresh brocoli.png',
        'status' => 'active'
    ],
    [
        'name' => 'Free-Range Chicken',
        'description' => 'Organic free-range chicken, tender and flavorful, raised without antibiotics.',
        'category' => 'meat',
        'price' => 280.00,
        'stock_quantity' => 25,
        'unit' => 'kg',
        'image_url' => 'images/products/native chicken.jpg',
        'status' => 'active'
    ]
];

// Remove duplicates by name
$uniqueProducts = [];
$productNames = [];
foreach ($products as $product) {
    if (!in_array($product['name'], $productNames)) {
        $productNames[] = $product['name'];
        $uniqueProducts[] = $product;
    }
}

logMessage("Total unique products to import: " . count($uniqueProducts));

// Import products
$successCount = 0;
$failCount = 0;
$skippedCount = 0;

foreach ($uniqueProducts as $product) {
    try {
        // Check if product already exists
        $existing = $api->select('products', ['name' => $product['name'], 'retailer_id' => $retailerId]);
        
        if (!empty($existing)) {
            logMessage("SKIPPED: Product '{$product['name']}' already exists in database");
            $skippedCount++;
            continue;
        }
        
        // Add retailer_id to product
        $product['retailer_id'] = $retailerId;
        
        // Insert product
        $result = $api->insert('products', $product);
        
        if (!empty($result)) {
            logMessage("SUCCESS: Added product '{$product['name']}' (Price: ₱{$product['price']}, Stock: {$product['stock_quantity']})");
            $successCount++;
        } else {
            logMessage("FAILED: Could not add product '{$product['name']}'");
            $failCount++;
        }
        
    } catch (Exception $e) {
        logMessage("ERROR: Failed to import '{$product['name']}' - " . $e->getMessage());
        $failCount++;
    }
    
    // Small delay to prevent overwhelming the API
    usleep(100000); // 0.1 second
}

// Summary
logMessage("========== IMPORT SUMMARY ==========");
logMessage("Total products processed: " . count($uniqueProducts));
logMessage("Successfully imported: $successCount");
logMessage("Skipped (already exists): $skippedCount");
logMessage("Failed: $failCount");
logMessage("========================================");

// Display summary in browser
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Import Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold mb-6 text-green-700">
                <i class="fas fa-box"></i> Product Import Results
            </h1>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-green-100 rounded-lg p-6 text-center">
                    <div class="text-4xl font-bold text-green-700"><?php echo $successCount; ?></div>
                    <div class="text-gray-600 mt-2">Successfully Imported</div>
                </div>
                
                <div class="bg-yellow-100 rounded-lg p-6 text-center">
                    <div class="text-4xl font-bold text-yellow-700"><?php echo $skippedCount; ?></div>
                    <div class="text-gray-600 mt-2">Skipped (Exists)</div>
                </div>
                
                <div class="bg-red-100 rounded-lg p-6 text-center">
                    <div class="text-4xl font-bold text-red-700"><?php echo $failCount; ?></div>
                    <div class="text-gray-600 mt-2">Failed</div>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Import Details</h2>
                <ul class="space-y-2">
                    <li><strong>Total Products Processed:</strong> <?php echo count($uniqueProducts); ?></li>
                    <li><strong>Retailer:</strong> <?php echo htmlspecialchars($defaultRetailer['shop_name']); ?></li>
                    <li><strong>Retailer ID:</strong> <?php echo htmlspecialchars($retailerId); ?></li>
                    <li><strong>Timestamp:</strong> <?php echo $timestamp; ?></li>
                </ul>
            </div>
            
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-4">Log File</h2>
                <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-auto max-h-96">
                    <?php echo nl2br(htmlspecialchars(file_get_contents($logFile))); ?>
                </div>
            </div>
            
            <div class="mt-8 flex gap-4">
                <a href="verify-products.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Verify Products in Database
                </a>
                <a href="../user/products.php" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                    View Products Page
                </a>
                <a href="../user/user-homepage.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                    Go to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html>
