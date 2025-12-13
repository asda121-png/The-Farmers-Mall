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
    $uploadError = null;

    // Log upload attempt for debugging
    error_log("Upload attempt - Files: " . json_encode($_FILES));

    if (isset($_FILES['product_image'])) {
        $fileError = $_FILES['product_image']['error'];

        // Check for upload errors
        if ($fileError !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $uploadError = $uploadErrors[$fileError] ?? "Unknown upload error: $fileError";
            error_log("Upload error for user {$userId}: {$uploadError}");
        } elseif ($fileError === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/product/';

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    $uploadError = "Failed to create upload directory";
                    error_log("Failed to create directory: {$uploadDir}");
                }
            }

            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                $uploadError = "Upload directory is not writable";
                error_log("Directory not writable: {$uploadDir}");
            }

            if (!$uploadError) {
                // Validate file type
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $fileExtension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    $uploadError = "Invalid file type. Allowed: " . implode(', ', $allowedExtensions);
                    error_log("Invalid file type for user {$userId}: {$fileExtension}");
                } else {
                    $fileName = uniqid('product_') . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
                        $imagePath = 'assets/product/' . $fileName;
                        error_log("Image uploaded successfully for user {$userId}: {$imagePath}");

                        // Verify file was actually created and is readable
                        if (!file_exists($uploadPath)) {
                            $uploadError = "File upload succeeded but file not found";
                            error_log("File not found after upload: {$uploadPath}");
                            $imagePath = null;
                        } elseif (!is_readable($uploadPath)) {
                            $uploadError = "File uploaded but not readable";
                            error_log("File not readable: {$uploadPath}");
                        }
                    } else {
                        $uploadError = "Failed to move uploaded file";
                        error_log("Failed to move uploaded file for user {$userId} from {$_FILES['product_image']['tmp_name']} to {$uploadPath}");
                    }
                }
            }
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
        error_log("Setting image_url in product data: {$imagePath}");
    } else {
        error_log("No image uploaded or upload failed. Upload error: " . ($uploadError ?? 'None'));
    }

    if ($productId) {
        // Update existing product - correct parameter order: table, data, filters
        $result = $api->update('products', $productData, ['id' => $productId]);

        // Log the update attempt for debugging
        error_log("Updating product ID: " . $productId);
        error_log("Product data: " . json_encode($productData));

        // Verify update was successful
        if ($result !== false && $result !== null) {
            $response = ['success' => true, 'message' => 'Product updated successfully', 'product_id' => $productId];
            if ($uploadError) {
                $response['upload_warning'] = $uploadError;
            }
            echo json_encode($response);
        } else {
            error_log("Update failed for product: " . $productId);
            echo json_encode(['success' => false, 'message' => 'Failed to update product', 'upload_error' => $uploadError]);
        }
    } else {
        // Insert new product
        $productData['created_at'] = date('Y-m-d H:i:s');
        
        try {
            $result = $api->insert('products', $productData);
            
            // Handle different return types from insert
            $productIdResponse = null;
            if (is_array($result) && !empty($result[0]['id'])) {
                $productIdResponse = $result[0]['id'];
            }
            
            // If insert didn't throw an exception, consider it successful
            $response = ['success' => true, 'message' => 'Product created successfully'];
            if ($productIdResponse) {
                $response['product_id'] = $productIdResponse;
            }
            if ($uploadError) {
                $response['upload_warning'] = $uploadError;
                error_log("Product created but with upload error for user {$userId}: {$uploadError}");
            }
            echo json_encode($response);
        } catch (Exception $insertError) {
            error_log("Insert failed: " . $insertError->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to create product: ' . $insertError->getMessage(), 
                'upload_error' => $uploadError
            ]);
        }
    }
} catch (Exception $e) {
    error_log("Error saving product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error saving product: ' . $e->getMessage()]);
}