<?php

/**
 * Payment Success Page
 * Handles return from PayPal and GCash after successful payment
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$payment_status = 'processing';
$error_message = '';

// Handle PayPal return
if (isset($_GET['token']) && !isset($_GET['source_id'])) {
    // PayPal payment
    $paypal_token = $_GET['token'];

    // Verify and capture payment
    require_once __DIR__ . '/../config/payment-config.php';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '../api/paypal-payment.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'action' => 'capture_paypal_order',
        'order_id' => $paypal_token
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($result['success'] ?? false) {
        // Get pending order from session
        $pending_order = $_SESSION['pending_paypal_order'] ?? null;

        if ($pending_order) {
            // Create order in database
            require_once __DIR__ . '/../config/supabase-api.php';

            // Place order via API
            $order_response = file_get_contents('../api/order.php', false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode([
                        'action' => 'place_order',
                        'payment_method' => 'paypal',
                        'cart_ids' => array_column($pending_order['cart_items'], 'cart_id'),
                        'payment_details' => [
                            'paypal_order_id' => $paypal_token,
                            'capture_id' => $result['capture_id']
                        ]
                    ])
                ]
            ]));

            $order_result = json_decode($order_response, true);

            if ($order_result['success'] ?? false) {
                $payment_status = 'success';
                $order_id = $order_result['order_id'];

                // Clear session
                unset($_SESSION['pending_paypal_order']);

                // Redirect to order success page
                header("Location: ordersuccessfull.php?order_id=$order_id");
                exit();
            } else {
                $payment_status = 'error';
                $error_message = 'Payment successful but order creation failed.';
            }
        }
    } else {
        $payment_status = 'error';
        $error_message = $result['message'] ?? 'Payment verification failed.';
    }
}

// Handle GCash return
if (isset($_GET['source_id']) || isset($_SESSION['pending_gcash_payment'])) {
    $source_id = $_GET['source_id'] ?? $_SESSION['pending_gcash_payment']['source_id'] ?? null;

    if ($source_id) {
        // Verify GCash payment
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '../api/gcash-payment.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'action' => 'verify_gcash_payment',
            'source_id' => $source_id
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (($result['success'] ?? false) && ($result['status'] === 'chargeable' || $result['status'] === 'paid')) {
            // Get pending payment from session
            $pending_payment = $_SESSION['pending_gcash_payment'] ?? null;

            if ($pending_payment) {
                // Create order in database
                $order_response = file_get_contents('../api/order.php', false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/json',
                        'content' => json_encode([
                            'action' => 'place_order',
                            'payment_method' => 'gcash',
                            'cart_ids' => array_column($pending_payment['cart_items'], 'cart_id'),
                            'payment_details' => [
                                'gcash_source_id' => $source_id,
                                'status' => $result['status']
                            ]
                        ])
                    ]
                ]));

                $order_result = json_decode($order_response, true);

                if ($order_result['success'] ?? false) {
                    $payment_status = 'success';
                    $order_id = $order_result['order_id'];

                    // Clear session
                    unset($_SESSION['pending_gcash_payment']);

                    // Redirect to order success page
                    header("Location: ordersuccessfull.php?order_id=$order_id");
                    exit();
                } else {
                    $payment_status = 'error';
                    $error_message = 'Payment successful but order creation failed.';
                }
            }
        } else {
            $payment_status = 'processing';
            $error_message = 'Payment is still being processed. Please wait...';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - Farmers Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full text-center">
            <?php if ($payment_status === 'success'): ?>
                <div class="text-green-600 mb-4">
                    <i class="fas fa-check-circle text-6xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Successful!</h1>
                <p class="text-gray-600 mb-6">Your payment has been processed successfully.</p>
                <a href="my-purchases.php" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 inline-block">
                    View My Orders
                </a>
            <?php elseif ($payment_status === 'processing'): ?>
                <div class="text-blue-600 mb-4">
                    <i class="fas fa-spinner fa-spin text-6xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Processing Payment...</h1>
                <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($error_message ?: 'Please wait while we verify your payment.'); ?></p>
                <button onclick="location.reload()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Refresh Page
                </button>
            <?php else: ?>
                <div class="text-red-600 mb-4">
                    <i class="fas fa-exclamation-circle text-6xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Failed</h1>
                <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($error_message ?: 'Something went wrong with your payment.'); ?></p>
                <a href="paymentmethod.php" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 inline-block">
                    Try Again
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>