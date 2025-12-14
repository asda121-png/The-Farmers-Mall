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

    // Get all orders for this retailer
    $orders = $api->select('orders', ['retailer_id' => $userId]);

    $totalRevenue = 0;
    $thisWeekRevenue = 0;
    $thisMonthRevenue = 0;
    $totalSales = 0;
    $transactions = [];

    // Calculate date ranges
    $now = new DateTime();
    $weekStart = new DateTime('monday this week');
    $monthStart = new DateTime('first day of this month');

    foreach ($orders as $order) {
        $orderStatus = $order['status'] ?? '';
        $orderDate = new DateTime($order['created_at'] ?? 'now');
        $orderAmount = floatval($order['total_amount'] ?? 0);

        // Only count completed/delivered orders for revenue
        if (in_array($orderStatus, ['completed', 'delivered'])) {
            $totalRevenue += $orderAmount;
            $totalSales++;

            // Check if order is from this week
            if ($orderDate >= $weekStart) {
                $thisWeekRevenue += $orderAmount;
            }

            // Check if order is from this month
            if ($orderDate >= $monthStart) {
                $thisMonthRevenue += $orderAmount;
            }

            // Add to transactions list (limit to recent 10)
            $transactions[] = [
                'id' => $order['id'],
                'type' => 'Sale',
                'description' => 'Order #' . substr($order['id'], 0, 8),
                'amount' => $orderAmount,
                'date' => $order['created_at'] ?? '',
                'status' => $orderStatus,
                'customer_name' => $order['customer_name'] ?? 'Customer'
            ];
        }
    }

    // Sort transactions by date (newest first) and limit to 10
    usort($transactions, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    $transactions = array_slice($transactions, 0, 10);

    echo json_encode([
        'success' => true,
        'financeData' => [
            'totalRevenue' => $totalRevenue,
            'thisWeekRevenue' => $thisWeekRevenue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'totalSales' => $totalSales
        ],
        'transactions' => $transactions
    ]);

} catch (Exception $e) {
    error_log("Error fetching retailer finance data: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching finance data: ' . $e->getMessage()
    ]);
}
