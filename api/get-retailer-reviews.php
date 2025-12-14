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

    // Get all products for this retailer
    $products = $api->select('products', ['retailer_id' => $retailerId]);
    $productIds = array_column($products, 'id');

    if (empty($productIds)) {
        echo json_encode([
            'success' => true,
            'ratingSummary' => [
                'averageRating' => 0,
                'totalReviews' => 0,
                'ratingDistribution' => [
                    5 => 0,
                    4 => 0,
                    3 => 0,
                    2 => 0,
                    1 => 0
                ]
            ],
            'recentReviews' => []
        ]);
        exit;
    }

    // Get all reviews for retailer's products
    $allReviews = [];
    foreach ($productIds as $productId) {
        $reviews = $api->select('reviews', ['product_id' => $productId]);
        $allReviews = array_merge($allReviews, $reviews);
    }

    // Calculate rating summary
    $totalReviews = count($allReviews);
    $ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    $totalRating = 0;

    foreach ($allReviews as $review) {
        $rating = intval($review['rating'] ?? 0);
        if ($rating >= 1 && $rating <= 5) {
            $ratingDistribution[$rating]++;
            $totalRating += $rating;
        }
    }

    $averageRating = $totalReviews > 0 ? round($totalRating / $totalReviews, 1) : 0;

    // Get recent reviews (last 10)
    usort($allReviews, function($a, $b) {
        return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
    });

    $recentReviews = array_slice($allReviews, 0, 10);

    // Enrich reviews with customer and product information
    $enrichedReviews = [];
    foreach ($recentReviews as $review) {
        // Get customer info
        $customerInfo = null;
        if (!empty($review['customer_id'])) {
            $customers = $api->select('users', ['id' => $review['customer_id']]);
            if (!empty($customers)) {
                $customerInfo = [
                    'id' => $customers[0]['id'],
                    'full_name' => $customers[0]['full_name'] ?? 'Anonymous Customer'
                ];
            }
        }

        // Get product info
        $productInfo = null;
        if (!empty($review['product_id'])) {
            $products = $api->select('products', ['id' => $review['product_id']]);
            if (!empty($products)) {
                $productInfo = [
                    'id' => $products[0]['id'],
                    'name' => $products[0]['name'] ?? 'Unknown Product'
                ];
            }
        }

        $enrichedReviews[] = [
            'id' => $review['id'],
            'rating' => intval($review['rating'] ?? 0),
            'comment' => $review['comment'] ?? '',
            'created_at' => $review['created_at'] ?? '',
            'customer' => $customerInfo,
            'product' => $productInfo
        ];
    }

    echo json_encode([
        'success' => true,
        'ratingSummary' => [
            'averageRating' => $averageRating,
            'totalReviews' => $totalReviews,
            'ratingDistribution' => $ratingDistribution
        ],
        'recentReviews' => $enrichedReviews
    ]);

} catch (Exception $e) {
    error_log("Error fetching retailer reviews: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching reviews: ' . $e->getMessage()
    ]);
}
