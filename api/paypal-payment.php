<?php

/**
 * PayPal Payment Processing API
 * Handles PayPal order creation and capture
 */

// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

session_start();

// Clear any previous output
ob_clean();

header('Content-Type: application/json');

require_once __DIR__ . '/../config/payment-config.php';
require_once __DIR__ . '/../config/supabase-api.php';

// Get request body
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

/**
 * Get PayPal Access Token
 */
function getPayPalAccessToken()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log('PayPal cURL Error: ' . $curlError);
        return null;
    }

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    } else {
        error_log('PayPal Token Error (HTTP ' . $httpCode . '): ' . $response);
        return null;
    }
}

/**
 * Create PayPal Order
 */
if ($action === 'create_paypal_order') {
    try {
        error_log('PayPal Order Creation Started');
        error_log('Client ID: ' . substr(PAYPAL_CLIENT_ID, 0, 10) . '...');
        error_log('API URL: ' . PAYPAL_API_URL);

        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            error_log('PayPal Error: User not logged in');
            throw new Exception('User not logged in');
        }

        error_log('User ID: ' . $user_id);

        $amount = $input['amount'] ?? 0;
        $cart_items = $input['cart_items'] ?? [];
        $cart_ids = $input['cart_ids'] ?? [];

        error_log('Amount: ' . $amount);
        error_log('Cart items count: ' . count($cart_items));
        error_log('Cart IDs: ' . json_encode($cart_ids));

        if ($amount <= 0) {
            error_log('PayPal Error: Invalid amount');
            throw new Exception('Invalid amount');
        }

        // Get access token
        error_log('Requesting PayPal access token...');
        $accessToken = getPayPalAccessToken();
        if (!$accessToken) {
            error_log('PayPal Error: Failed to get access token');
            throw new Exception('Failed to get PayPal access token. Check your credentials.');
        }

        error_log('Access token received successfully');

        // Prepare order data
        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => PAYMENT_CURRENCY,
                        'value' => number_format($amount, 2, '.', '')
                    ],
                    'description' => 'Farmers Mall Order'
                ]
            ],
            'application_context' => [
                'return_url' => PAYMENT_RETURN_URL,
                'cancel_url' => PAYMENT_CANCEL_URL,
                'brand_name' => 'Farmers Mall',
                'user_action' => 'PAY_NOW'
            ]
        ];

        // Create PayPal order
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            $order = json_decode($response, true);

            // Store order info in session for later verification
            $_SESSION['pending_paypal_order'] = [
                'paypal_order_id' => $order['id'],
                'amount' => $amount,
                'cart_items' => $cart_items,
                'cart_ids' => $cart_ids
            ];

            // Find the approval URL (where user goes to pay)
            $approvalUrl = null;
            foreach ($order['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }

            error_log('PayPal order created successfully. Order ID: ' . $order['id']);
            error_log('Approval URL: ' . $approvalUrl);

            ob_clean();
            echo json_encode([
                'success' => true,
                'order_id' => $order['id'],
                'approval_url' => $approvalUrl
            ]);
        } else {
            error_log('PayPal Order Creation Failed (HTTP ' . $httpCode . '): ' . $response);
            throw new Exception('PayPal order creation failed: ' . $response);
        }
    } catch (Exception $e) {
        error_log('PayPal Order Creation Error: ' . $e->getMessage());
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

/**
 * Capture PayPal Order (after user approves)
 */
if ($action === 'capture_paypal_order') {
    try {
        $paypal_order_id = $input['order_id'] ?? '';

        if (empty($paypal_order_id)) {
            throw new Exception('Order ID is required');
        }

        // Get access token
        $accessToken = getPayPalAccessToken();
        if (!$accessToken) {
            throw new Exception('Failed to get PayPal access token');
        }

        // Capture the order
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v2/checkout/orders/' . $paypal_order_id . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            $capture = json_decode($response, true);

            // Get pending order from session
            $pending_order = $_SESSION['pending_paypal_order'] ?? null;

            ob_clean();
            echo json_encode([
                'success' => true,
                'capture_id' => $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                'status' => $capture['status'],
                'pending_order' => $pending_order
            ]);
        } else {
            throw new Exception('PayPal capture failed: ' . $response);
        }
    } catch (Exception $e) {
        error_log('PayPal Capture Error: ' . $e->getMessage());
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

/**
 * Verify PayPal Payment Status
 */
if ($action === 'verify_paypal_payment') {
    try {
        $paypal_order_id = $input['order_id'] ?? '';

        if (empty($paypal_order_id)) {
            throw new Exception('Order ID is required');
        }

        // Get access token
        $accessToken = getPayPalAccessToken();
        if (!$accessToken) {
            throw new Exception('Failed to get PayPal access token');
        }

        // Get order details
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v2/checkout/orders/' . $paypal_order_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $order = json_decode($response, true);

            ob_clean();
            echo json_encode([
                'success' => true,
                'status' => $order['status'],
                'order' => $order
            ]);
        } else {
            throw new Exception('PayPal verification failed');
        }
    } catch (Exception $e) {
        error_log('PayPal Verification Error: ' . $e->getMessage());
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Clear output buffer and send JSON
ob_clean();
echo json_encode([
    'success' => false,
    'message' => 'Invalid action'
]);
exit;
