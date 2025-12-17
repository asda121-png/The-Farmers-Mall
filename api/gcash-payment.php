<?php

/**
 * GCash Payment Processing API (via Paymongo)
 * Handles GCash payment source creation and checkout
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/payment-config.php';
require_once __DIR__ . '/../config/supabase-api.php';

// Get request body
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

/**
 * Create GCash Payment Source
 */
if ($action === 'create_gcash_source') {
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            throw new Exception('User not logged in');
        }

        $amount = $input['amount'] ?? 0;
        $cart_items = $input['cart_items'] ?? [];

        if ($amount <= 0) {
            throw new Exception('Invalid amount');
        }

        // Convert amount to centavos (Paymongo uses centavos)
        $amountInCentavos = intval($amount * 100);

        // Prepare source data
        $sourceData = [
            'data' => [
                'attributes' => [
                    'amount' => $amountInCentavos,
                    'redirect' => [
                        'success' => PAYMENT_RETURN_URL,
                        'failed' => PAYMENT_CANCEL_URL
                    ],
                    'type' => 'gcash',
                    'currency' => 'PHP'
                ]
            ]
        ];

        // Create GCash source via Paymongo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYMONGO_API_URL . '/sources');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sourceData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            $source = json_decode($response, true);

            // Store source info in session for later verification
            $_SESSION['pending_gcash_payment'] = [
                'source_id' => $source['data']['id'],
                'amount' => $amount,
                'cart_items' => $cart_items
            ];

            echo json_encode([
                'success' => true,
                'source_id' => $source['data']['id'],
                'checkout_url' => $source['data']['attributes']['redirect']['checkout_url'] ?? null
            ]);
        } else {
            throw new Exception('GCash source creation failed: ' . $response);
        }
    } catch (Exception $e) {
        error_log('GCash Source Creation Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

/**
 * Verify GCash Payment
 */
if ($action === 'verify_gcash_payment') {
    try {
        $source_id = $input['source_id'] ?? '';

        if (empty($source_id)) {
            throw new Exception('Source ID is required');
        }

        // Retrieve source details from Paymongo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYMONGO_API_URL . '/sources/' . $source_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $source = json_decode($response, true);
            $status = $source['data']['attributes']['status'] ?? 'pending';

            // Get pending payment from session
            $pending_payment = $_SESSION['pending_gcash_payment'] ?? null;

            echo json_encode([
                'success' => true,
                'status' => $status,
                'source' => $source,
                'pending_payment' => $pending_payment
            ]);
        } else {
            throw new Exception('GCash verification failed: ' . $response);
        }
    } catch (Exception $e) {
        error_log('GCash Verification Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

/**
 * Create Payment Intent (alternative method)
 */
if ($action === 'create_gcash_payment_intent') {
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            throw new Exception('User not logged in');
        }

        $amount = $input['amount'] ?? 0;

        if ($amount <= 0) {
            throw new Exception('Invalid amount');
        }

        // Convert amount to centavos
        $amountInCentavos = intval($amount * 100);

        // Create payment intent
        $intentData = [
            'data' => [
                'attributes' => [
                    'amount' => $amountInCentavos,
                    'payment_method_allowed' => ['gcash'],
                    'payment_method_options' => [
                        'card' => [
                            'request_three_d_secure' => 'any'
                        ]
                    ],
                    'currency' => 'PHP',
                    'capture_type' => 'automatic'
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYMONGO_API_URL . '/payment_intents');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($intentData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            $intent = json_decode($response, true);

            echo json_encode([
                'success' => true,
                'client_key' => $intent['data']['attributes']['client_key'],
                'payment_intent_id' => $intent['data']['id']
            ]);
        } else {
            throw new Exception('Payment intent creation failed: ' . $response);
        }
    } catch (Exception $e) {
        error_log('GCash Payment Intent Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid action'
]);
