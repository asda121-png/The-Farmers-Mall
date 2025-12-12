<?php
/**
 * Notification Helper
 * Utility functions to create notifications for retailers and users
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/supabase-api.php';

/**
 * Create a notification for a retailer
 * 
 * @param string $retailerId The retailer's UUID
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type (order, stock, review, payment, etc.)
 * @param string|null $link Optional link for the notification
 * @param string|null $orderId Optional order ID reference
 * @param array|null $relatedData Optional additional data as JSON
 * @return bool Success status
 */
function createRetailerNotification($retailerId, $title, $message, $type, $link = null, $orderId = null, $relatedData = null) {
    try {
        error_log("[NOTIFICATION] Creating notification for retailer: $retailerId");
        error_log("[NOTIFICATION] Title: $title, Type: $type");
        
        $api = getSupabaseAPI();
        
        // Get the user_id for this retailer
        $retailers = $api->select('retailers', ['id' => $retailerId]);
        if (empty($retailers)) {
            error_log("[NOTIFICATION ERROR] Retailer not found: $retailerId");
            return false;
        }
        
        $userId = $retailers[0]['user_id'];
        error_log("[NOTIFICATION] Found user_id: $userId for retailer: $retailerId");
        
        // Prepare notification data
        $notificationData = [
            'user_id' => $userId,
            'retailer_id' => $retailerId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($link) {
            $notificationData['link'] = $link;
        }
        
        if ($orderId) {
            $notificationData['order_id'] = $orderId;
        }
        
        if ($relatedData) {
            $notificationData['related_data'] = json_encode($relatedData);
        }
        
        error_log("[NOTIFICATION] Inserting notification: " . json_encode($notificationData));
        
        // Insert notification
        $result = $api->insert('notifications', $notificationData);
        
        if (!empty($result)) {
            error_log("[NOTIFICATION SUCCESS] Notification created successfully");
        } else {
            error_log("[NOTIFICATION ERROR] Failed to insert notification - empty result");
        }
        
        return !empty($result);
        
    } catch (Exception $e) {
        error_log("Error creating retailer notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a notification for a customer
 * 
 * @param string $userId The user's UUID
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type
 * @param string|null $link Optional link
 * @param string|null $orderId Optional order ID
 * @return bool Success status
 */
function createUserNotification($userId, $title, $message, $type, $link = null, $orderId = null) {
    try {
        $api = getSupabaseAPI();
        
        $notificationData = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($link) {
            $notificationData['link'] = $link;
        }
        
        if ($orderId) {
            $notificationData['order_id'] = $orderId;
        }
        
        $result = $api->insert('notifications', $notificationData);
        
        return !empty($result);
        
    } catch (Exception $e) {
        error_log("Error creating user notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify retailer about a new order
 * 
 * @param string $retailerId
 * @param string $orderId
 * @param string $customerName
 * @param float $orderTotal
 */
function notifyRetailerNewOrder($retailerId, $orderId, $customerName, $orderTotal) {
    error_log("[ORDER NOTIFICATION] Called for retailer: $retailerId, order: $orderId");
    error_log("[ORDER NOTIFICATION] Customer: $customerName, Total: $orderTotal");
    
    $title = 'New Order Received';
    $message = "New order from $customerName - Total: ₱" . number_format($orderTotal, 2);
    $link = '../retailer/retailerfulfillment.php?order_id=' . $orderId;
    
    $result = createRetailerNotification($retailerId, $title, $message, 'order', $link, $orderId, [
        'customer_name' => $customerName,
        'order_total' => $orderTotal
    ]);
    
    error_log("[ORDER NOTIFICATION] Result: " . ($result ? 'SUCCESS' : 'FAILED'));
    return $result;
}

/**
 * Notify retailer about order cancellation
 */
function notifyRetailerOrderCancelled($retailerId, $orderId, $customerName, $reason = '') {
    $title = 'Order Cancelled';
    $message = "Order #$orderId from $customerName has been cancelled";
    if ($reason) {
        $message .= " - Reason: $reason";
    }
    $link = '../retailer/retailerfulfillment.php?order_id=' . $orderId;
    
    createRetailerNotification($retailerId, $title, $message, 'order_cancelled', $link, $orderId);
}

/**
 * Notify retailer about low stock
 */
function notifyRetailerLowStock($retailerId, $productId, $productName, $currentStock) {
    $title = 'Low Stock Alert';
    $message = "$productName is running low on stock ($currentStock left)";
    $link = '../retailer/retailerinventory.php';
    
    createRetailerNotification($retailerId, $title, $message, 'stock', $link, null, [
        'product_id' => $productId,
        'product_name' => $productName,
        'current_stock' => $currentStock
    ]);
}

/**
 * Notify retailer about out of stock
 */
function notifyRetailerOutOfStock($retailerId, $productId, $productName) {
    $title = 'Out of Stock';
    $message = "$productName is now out of stock";
    $link = '../retailer/retailerinventory.php';
    
    createRetailerNotification($retailerId, $title, $message, 'stock', $link, null, [
        'product_id' => $productId,
        'product_name' => $productName
    ]);
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notificationId) {
    try {
        $api = getSupabaseAPI();
        $api->update('notifications', ['id' => $notificationId], ['is_read' => true]);
        return true;
    } catch (Exception $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete notification
 */
function deleteNotification($notificationId) {
    try {
        $api = getSupabaseAPI();
        $api->delete('notifications', ['id' => $notificationId]);
        return true;
    } catch (Exception $e) {
        error_log("Error deleting notification: " . $e->getMessage());
        return false;
    }
}
