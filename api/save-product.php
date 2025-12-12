<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase-api.php';

try {
    $api = getSupabaseAPI();
    $userId = $_SESSION['user_id'];
    
    // Get retailer info
    $retailers = $api->select('retailers', ['user_id' => $userId]);
    if (empty($retailers)) {
        echo json_encode(['success' => false, 'message' => 'Retailer not found']);
        exit;
    }
    $retailerId = $retailers[0]['id'];
    
    // Get form data
    $productId = $_POST['product_id'] ?? null;
    $productName = $_POST['product_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $shortDescription = $_POST['short_description'] ?? '';
    $regularPrice = !empty($_POST['regular_price']) ? floatval($_POST['regular_price']) : 0;
    $salePrice = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $sku = $_POST['sku'] ?? '';
    $stockQuantity = !empty($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;
    $unit = $_POST['unit'] ?? 'kg';
    $tags = $_POST['tags'] ?? '';
    $categories = $_POST['categories'] ?? [];
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/product/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('product_') . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
            $imagePath = 'assets/product/' . $fileName;
        }
    }
    
    // Prepare product data (matching database schema)
    // Only include fields that exist in the products table
    $productData = [
        'name' => $productName,
        'description' => $description,
        'price' => $regularPrice,
        'stock_quantity' => $stockQuantity,
        'unit' => $unit,
        'category' => is_array($categories) ? implode(',', $categories) : $categories,
        'retailer_id' => $retailerId,
        'status' => 'active',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Only update image if a new one was uploaded
    if ($imagePath) {
        $productData['image_url'] = $imagePath;
    }
    
    if ($productId) {
        // Update existing product - correct parameter order: table, data, filters
        $result = $api->update('products', $productData, ['id' => $productId]);
        
        // Log the update attempt for debugging
        error_log("Updating product ID: " . $productId);
        error_log("Product data: " . json_encode($productData));
        
        // Verify update was successful
        if ($result !== false && $result !== null) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully', 'product_id' => $productId]);
        } else {
            error_log("Update failed for product: " . $productId);
            echo json_encode(['success' => false, 'message' => 'Failed to update product']);
        }
    } else {
        // Insert new product
        $productData['created_at'] = date('Y-m-d H:i:s');
        $result = $api->insert('products', $productData);
        
        if ($result && !empty($result[0]['id'])) {
            echo json_encode(['success' => true, 'message' => 'Product created successfully', 'product_id' => $result[0]['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create product']);
        }
    }
    
} catch (Exception $e) {
    error_log("Error saving product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error saving product: ' . $e->getMessage()]);
}
