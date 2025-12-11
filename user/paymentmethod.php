<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'] ?? null;

// Fetch profile picture from database
$profile_picture = '';
$full_name = $_SESSION['full_name'] ?? 'User';
if ($user_id) {
    require_once __DIR__ . '/../config/supabase-api.php';
    require_once __DIR__ . '/../config/uuid-helper.php';
    $api = getSupabaseAPI();
    $user = safeGetUser($user_id, $api);
    if ($user) {
        $profile_picture = $user['profile_picture'] ?? '';
    }
}

// --- [NEW] Fetch Cart Items & Calculate Totals ---
$cart_items_with_products = [];
$subtotal = 0;
$tax = 0;
$tax_rate = 0.12; // 12% tax rate

$total = 0;

// Get customer name for cart lookup (cart uses customer_name, not customer_id)
$customer_name = 'Unknown Customer';
if ($user_id && isset($api)) {
    try {
        // First, get the customer's full name
        $customer = $api->select('users', ['id' => $user_id]);
        if (!empty($customer)) {
            $customer_data = $customer[0];
            
            // Get customer name from full_name field or build from first/last name
            if (!empty($customer_data['full_name'])) {
                $customer_name = trim($customer_data['full_name']);
            } else {
                // Fallback to first_name + last_name if full_name doesn't exist
                $first_name = $customer_data['first_name'] ?? '';
                $last_name = $customer_data['last_name'] ?? '';
                $customer_name = trim($first_name . ' ' . $last_name);
            }
            
            // If still empty, use username or email as fallback
            if (empty($customer_name)) {
                $customer_name = $customer_data['username'] ?? $customer_data['email'] ?? 'Unknown Customer';
            }
        }
        
        // Now fetch cart items using customer_name (matching cart.php logic)
        $cart_items = $api->select('cart', ['customer_name' => $customer_name]);
        
        if (!empty($cart_items)) {
            $product_ids = array_column($cart_items, 'product_id');
            
            if (!empty($product_ids)) {
                $products_data = $api->select('products', ['id' => ['in', '(' . implode(',', $product_ids) . ')']]);
                $products_map = array_column($products_data, null, 'id');
                
                foreach ($cart_items as $item) {
                    if (isset($products_map[$item['product_id']])) {
                        $prod = $products_map[$item['product_id']];
                        $price = $prod['price'] ?? 0;
                        $qty = $item['quantity'] ?? 1;
                        $item_total = $price * $qty;
                        
                        $subtotal += $item_total;
                        
                        $cart_items_with_products[] = [
                            'name' => $prod['name'],
                            'image' => $prod['image_url'] ?? $prod['image'] ?? '',
                            'price' => $price,
                            'quantity' => $qty,
                            'subtotal' => $item_total
                        ];
                    }
                }
            }
        }
        
       
        $tax = $subtotal * $tax_rate;
        $total = $subtotal + $tax;
        
    } catch (Exception $e) {
        error_log('Payment Page Cart Fetch Error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Method – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* 🎨 Brand Colors */
    .visa { color: #1A1F71; }          /* Visa Blue */
    .mastercard { color: #EB001B; }    /* Mastercard Red */
    .amex { color: #2E77BB; }          /* American Express Blue */
    .gpay { color: #4285F4; }          /* Google Pay Blue */
    .paypal { color: #003087; }        /* PayPal Deep Blue */

    /* Style to make an element invisible but still occupy space */
    .invisible-placeholder {
      visibility: hidden;
      opacity: 0;
      pointer-events: none;
    }

    .notification-dropdown { position: absolute; top: 100%; right: 0; margin-top: 8px; width: 320px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); z-index: 50; max-height: 400px; overflow-y: auto; }
    .notification-item { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; transition: all 0.2s ease; cursor: pointer; }
    .notification-item:hover { background-color: #f9f9f9; }
    .notification-item.unread { background-color: #f0f9f5; border-left: 4px solid #4CAF50; }
    .notification-item-title { font-weight: 600; color: #333; font-size: 14px; margin-bottom: 4px; }
    .notification-item-message { font-size: 12px; color: #666; margin-bottom: 4px; }
    .notification-item-time { font-size: 11px; color: #999; }
    .notification-empty { padding: 24px 16px; text-align: center; color: #999; font-size: 14px; }
    .notification-header { padding: 12px 16px; border-bottom: 1px solid #e0e0e0; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .notification-clear-btn { font-size: 12px; color: #2E7D32; cursor: pointer; background: none; border: none; }
    .notification-clear-btn:hover { color: #1B5E20; }
  </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">

  

<?php include __DIR__ . '/../includes/user-header.php'; ?>


  <!-- PAYMENT SECTION -->
  <main class="max-w-7xl mx-auto p-6 flex flex-col lg:flex-row gap-8 mt-6 flex-grow w-full mb-20">
    
    <!-- LEFT SIDE: Payment Form -->
    <section class="flex-1 bg-white p-6 rounded-xl shadow-sm min-h-[400px]">
      <div class="flex items-center space-x-4 mb-6">
        <button onclick="window.history.back()" class="text-gray-500 hover:text-gray-800 transition">
          <i class="fa-solid fa-arrow-left text-xl"></i>
        </button>
        <h2 class="text-2xl font-semibold">Payment Details</h2>
      </div>

      <!-- Payment Method Options -->
      <div class="space-y-3 mb-6">
        <!-- Card -->
        <label class="flex items-center justify-between border rounded-lg p-4 cursor-pointer hover:border-green-500">
          <div class="flex items-center gap-3">
            <input type="radio" name="payment" value="card" checked class="accent-green-600">
            <span class="font-medium">Credit/Debit Card</span>
          </div>
          <div class="flex items-center gap-2">
            <i class="fa-brands fa-cc-visa text-2xl visa"></i>
            <i class="fa-brands fa-cc-mastercard text-2xl mastercard"></i>
            <i class="fa-brands fa-cc-amex text-2xl amex"></i>
          </div>
        </label>

        <!-- Digital Wallets -->
        <label class="flex items-center justify-between border rounded-lg p-4 cursor-pointer hover:border-green-500">
          <div class="flex items-center gap-3">
            <input type="radio" name="payment" value="wallet" class="accent-green-600">
            <span class="font-medium">Digital Wallets</span>
          </div>
          <div class="flex items-center gap-3">
            <i class="fa-brands fa-google-pay text-2xl gpay"></i>
            <i class="fa-brands fa-paypal text-2xl paypal"></i>
          </div>
        </label>

        <!-- COD -->
        <label class="flex items-center border rounded-lg p-4 cursor-pointer hover:border-green-500">
          <input type="radio" name="payment" value="cod" class="accent-green-600 mr-3">
          <div>
            <span class="font-medium">Cash on Delivery</span>
            <p class="text-sm text-gray-500">Pay when your order arrives</p>
          </div>
        </label>
      </div>

      <!-- BILLING ADDRESS -->
      <div class="mt-6">
        <label class="flex items-center space-x-2">
          <input type="checkbox" checked class="accent-green-600">
          <span class="text-sm text-gray-700">Same as shipping address</span>
        </label>
      </div>

      <!-- SECURE PAYMENT NOTICE -->
      <div class="mt-6 p-4 bg-green-50 text-green-800 text-sm rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-lock"></i>
        <span>Your payment is secured — protected by 256-bit SSL encryption</span>
      </div>

      <!-- CARD INFORMATION -->
      <div id="card-info" class="space-y-4 transition-opacity duration-300">
        <div>
          <label class="block text-sm text-gray-700 mb-1">Card Number</label>
          <input type="text" placeholder="1234 5678 9012 3456" 
                 class="w-full border px-3 py-2 rounded-md focus:ring-green-500 focus:ring-1 outline-none">
        </div>

        <div>
          <label class="block text-sm text-gray-700 mb-1">Cardholder Name</label>
          <input type="text" placeholder="John Doe" 
                 class="w-full border px-3 py-2 rounded-md focus:ring-green-500 focus:ring-1 outline-none">
        </div>

        <div class="flex gap-4">
          <div class="flex-1">
            <label class="block text-sm text-gray-700 mb-1">Expiry Date</label>
            <input type="text" placeholder="MM/YY"
                   class="w-full border px-3 py-2 rounded-md focus:ring-green-500 focus:ring-1 outline-none">
          </div>
          <div class="flex-1">
            <label class="block text-sm text-gray-700 mb-1 flex items-center gap-1">
              CVV <i class="fa-regular fa-circle-question text-gray-400"></i>
            </label>
            <input type="text" placeholder="123"
                   class="w-full border px-3 py-2 rounded-md focus:ring-green-500 focus:ring-1 outline-none">
          </div>
        </div>
      </div>

    </section>

    <!-- RIGHT SIDE: Order Summary -->
    <aside class="bg-white shadow-sm p-6 rounded-xl w-full lg:w-80 h-fit">
      <h3 class="font-semibold text-lg mb-4">Order Summary</h3>

      <div id="orderItems" class="space-y-3 mb-4">
        <?php foreach ($cart_items_with_products as $item): 
            $img = $item['image'];
            if (empty($img)) $img = 'https://via.placeholder.com/100x100?text=No+Image';
            elseif (strpos($img, 'http') !== 0) $img = '../' . ltrim($img, '/');
        ?>
        <div class="flex gap-3">
            <img src="<?php echo htmlspecialchars($img); ?>" class="w-12 h-12 rounded object-cover border bg-gray-50">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate"><?php echo htmlspecialchars($item['name']); ?></p>
                <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
            </div>
            <div class="text-sm font-semibold text-gray-700">₱<?php echo number_format($item['subtotal'], 2); ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="text-sm text-gray-700 space-y-2">
        <div class="flex justify-between"><span>Subtotal</span><span id="subtotal">₱<?php echo number_format($subtotal, 2); ?></span></div>
        <div class="flex justify-between"><span>Shipping</span><span>Free</span></div>
        <div class="flex justify-between text-xs text-gray-500"><span>Tax (estimated 12%)</span><span id="tax">₱<?php echo number_format($tax, 2); ?></span></div>
      </div>

      <div class="border-t mt-3 pt-3 flex justify-between text-lg font-semibold text-gray-800">
        <span>Total</span><span id="total">₱<?php echo number_format($total, 2); ?></span>
      </div>

      <button id="placeOrderBtn"
              class="bg-green-600 w-full text-white py-3 rounded-md mt-6 font-medium hover:bg-green-700 transition">
        Place Order
      </button>
    </aside>
  </main>

  <!-- FOOTER -->
  <footer class="text-white py-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      <div>
        <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>
        <p class="text-gray-300 text-sm">
          Fresh, organic produce delivered straight to your home from local farmers.
        </p>
      </div>
      <div>
        <h3 class="font-bold text-lg mb-3">Quick Links</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="#" class="hover:underline">About Us</a></li>
          <li><a href="#" class="hover:underline">Contact</a></li>
          <li><a href="#" class="hover:underline">FAQ</a></li>
          <li><a href="#" class="hover:underline">Support</a></li>
        </ul>
      </div>
      <div>
        <h3 class="font-bold text-lg mb-3">Categories</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="#" class="hover:underline">Vegetables</a></li>
          <li><a href="#" class="hover:underline">Fruits</a></li>
          <li><a href="#" class="hover:underline">Dairy</a></li>
          <li><a href="#" class="hover:underline">Meat</a></li>
        </ul>
      </div>
      <div>
        <h3 class="font-bold text-lg mb-3">Follow Us</h3>
        <div class="flex space-x-4 text-xl">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>

  <script>
    // Pass PHP data to JavaScript
    window.paymentData = {
      cart: <?php echo json_encode($cart_items_with_products); ?>,
      subtotal: <?php echo json_encode($subtotal); ?>,
      tax: <?php echo json_encode($tax); ?>,
      total: <?php echo json_encode($total); ?>
    };
  </script>
  <script src="../assets/js/paymentmethod.js"></script>
 
</body>
</html>
