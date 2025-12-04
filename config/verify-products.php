<?php
/**
 * Verify Products in Database
 * This script displays all products currently stored in the database
 */

require_once __DIR__ . '/supabase-api.php';

// Initialize API
$api = getSupabaseAPI();

// Fetch all products
$products = [];
$retailers = [];
$errorMessage = '';

try {
    $products = $api->select('products', []); // Get all products
    $retailers = $api->select('retailers', []);
    
    // Create retailer lookup
    $retailerMap = [];
    foreach ($retailers as $retailer) {
        $retailerMap[$retailer['id']] = $retailer['shop_name'];
    }
} catch (Exception $e) {
    $errorMessage = "Error fetching products: " . $e->getMessage();
}

// Group products by category
$categories = [];
foreach ($products as $product) {
    $category = $product['category'] ?? 'uncategorized';
    if (!isset($categories[$category])) {
        $categories[$category] = [];
    }
    $categories[$category][] = $product;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Products - Farmers Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-green-700 mb-2">
                        <i class="fas fa-database"></i> Product Database Verification
                    </h1>
                    <p class="text-gray-600">Total Products: <strong><?php echo count($products); ?></strong></p>
                    <p class="text-gray-600">Total Retailers: <strong><?php echo count($retailers); ?></strong></p>
                    <p class="text-gray-600">Categories: <strong><?php echo count($categories); ?></strong></p>
                </div>
                <div class="flex gap-3">
                    <a href="import-all-products.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-upload"></i> Re-run Import
                    </a>
                    <a href="../user/products.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-eye"></i> View Products Page
                    </a>
                </div>
            </div>
        </div>

        <?php if ($errorMessage): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>

        <!-- Retailers List -->
        <?php if (!empty($retailers)): ?>
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">
                <i class="fas fa-store"></i> Retailers
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($retailers as $retailer): ?>
                <div class="border rounded-lg p-4 hover:shadow-md transition">
                    <h3 class="font-bold text-lg text-green-700"><?php echo htmlspecialchars($retailer['shop_name'] ?? 'N/A'); ?></h3>
                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($retailer['shop_description'] ?? 'No description'); ?></p>
                    <div class="mt-2 text-xs text-gray-500">
                        <div><strong>ID:</strong> <?php echo htmlspecialchars($retailer['id']); ?></div>
                        <div><strong>Status:</strong> 
                            <span class="px-2 py-1 rounded <?php echo ($retailer['verification_status'] ?? '') === 'verified' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                <?php echo htmlspecialchars($retailer['verification_status'] ?? 'N/A'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Products by Category -->
        <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $categoryName => $categoryProducts): ?>
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 capitalize">
                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($categoryName); ?> 
                <span class="text-sm text-gray-500 font-normal">(<?php echo count($categoryProducts); ?> products)</span>
            </h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retailer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($categoryProducts as $product): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="w-16 h-16 object-cover rounded"
                                         onerror="this.src='../images/placeholder.png'">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">
                                <?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 100)); ?>
                                <?php if (strlen($product['description'] ?? '') > 100) echo '...'; ?>
                            </td>
                            <td class="px-4 py-3 text-green-700 font-semibold">
                                ₱<?php echo number_format($product['price'], 2); ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="<?php echo ($product['stock_quantity'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $product['stock_quantity'] ?? 0; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?php echo htmlspecialchars($product['unit'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?php echo htmlspecialchars($retailerMap[$product['retailer_id']] ?? 'Unknown'); ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded
                                    <?php 
                                    $status = $product['status'] ?? 'inactive';
                                    if ($status === 'active') echo 'bg-green-100 text-green-700';
                                    elseif ($status === 'out_of_stock') echo 'bg-red-100 text-red-700';
                                    else echo 'bg-gray-100 text-gray-700';
                                    ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            <strong>No products found!</strong> Run the import script to add products to the database.
        </div>
        <?php endif; ?>

        <!-- Product Statistics -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">
                <i class="fas fa-chart-bar"></i> Product Statistics
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <?php
                $totalStock = 0;
                $totalValue = 0;
                $activeProducts = 0;
                
                foreach ($products as $product) {
                    $totalStock += $product['stock_quantity'] ?? 0;
                    $totalValue += ($product['price'] ?? 0) * ($product['stock_quantity'] ?? 0);
                    if (($product['status'] ?? '') === 'active') $activeProducts++;
                }
                ?>
                <div class="bg-blue-100 rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-blue-700"><?php echo count($products); ?></div>
                    <div class="text-gray-600 mt-2">Total Products</div>
                </div>
                <div class="bg-green-100 rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-green-700"><?php echo $activeProducts; ?></div>
                    <div class="text-gray-600 mt-2">Active Products</div>
                </div>
                <div class="bg-purple-100 rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-purple-700"><?php echo number_format($totalStock); ?></div>
                    <div class="text-gray-600 mt-2">Total Stock</div>
                </div>
                <div class="bg-yellow-100 rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-yellow-700">₱<?php echo number_format($totalValue, 2); ?></div>
                    <div class="text-gray-600 mt-2">Total Inventory Value</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
