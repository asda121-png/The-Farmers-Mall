<?php

/**
 * Payment Gateway Configuration
 * Configure your PayPal and GCash credentials here
 */

// Load environment variables if available
if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
}

// PayPal Configuration
define('PAYPAL_MODE', getenv('PAYPAL_MODE') ?: 'sandbox'); // 'sandbox' or 'live'
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'Acrpz8PXXG3uTexf1Ap9c7ADDza2qxiuyRfbM8O9AVbM_qJ0dAZ7SBEFrXnNziKGCjmbIcToiNbcbQqj');
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') ?: 'EJ066PJfc_OENvwoy3Rlak6RMl_hfBo4hsrlrCG20Ns5NMHSDbbxTAJOoQYserqjFk_2z2eVgmGXbJdI');

// PayPal API URLs
if (PAYPAL_MODE === 'sandbox') {
    define('PAYPAL_API_URL', 'https://api-m.sandbox.paypal.com');
} else {
    define('PAYPAL_API_URL', 'https://api-m.paypal.com');
}

// GCash Configuration (Paymongo for GCash)
define('PAYMONGO_SECRET_KEY', getenv('PAYMONGO_SECRET_KEY') ?: 'YOUR_PAYMONGO_SECRET_KEY');
define('PAYMONGO_PUBLIC_KEY', getenv('PAYMONGO_PUBLIC_KEY') ?: 'YOUR_PAYMONGO_PUBLIC_KEY');
define('PAYMONGO_API_URL', 'https://api.paymongo.com/v1');

// Currency
define('PAYMENT_CURRENCY', 'PHP');

// Return URLs (adjust these to your domain)
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$script_path = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
define('PAYMENT_RETURN_URL', $base_url . $script_path . '/user/payment-success.php');
define('PAYMENT_CANCEL_URL', $base_url . $script_path . '/user/paymentmethod.php');

?>






























































































































































































**Questions?** Check the setup guides or the troubleshooting section!---**Total setup time: ~30 minutes**4. Go live! (when ready)3. Test payments (10 minutes)2. Configure the system (5 minutes)1. Get your credentials (15 minutes)Just follow the **Next Steps** above to:## 🎉 You're Ready!---- **Secure payment flow** with server-side verification- **Better UX** with dynamic button display- **Automatic order creation** after successful payment- **Real GCash integration** via Paymongo- **Real PayPal integration** with redirect### After:- Manual payment tracking- No actual payment processing- Only card and COD options### Before:## ✨ What's Different Now?---- **Paymongo docs** → https://developers.paymongo.com/docs- **PayPal docs** → https://developer.paypal.com/docs/- **Quick testing?** → Read `PAYMENT_QUICK_TEST.md`- **Setup issues?** → Read `PAYMENT_GATEWAY_SETUP.md`## 📞 Get Help---5. **Use webhooks** (advanced) - Get real-time payment notifications4. **Monitor logs** - Check PHP error logs and browser console3. **Test edge cases** - What if user cancels? What if payment fails?2. **Keep credentials secure** - Use .env file, never commit to Git1. **Start with Sandbox/Test mode** - Never test with real money## 💡 Pro Tips---- Check session data persistence- Verify database connection- Check `api/order.php` logs### Payment succeeds but no order- Look at browser network tab for API errors- Check account verification status- Verify Paymongo API keys### GCash redirect fails- Check PayPal dashboard for app status- Verify sandbox vs live mode matches- Double-check Client ID (no spaces)### "Invalid Client ID" error- Make sure jQuery/dependencies loaded- Verify Client ID in SDK script- Check browser console for errors### PayPal button doesn't show## 🐛 Troubleshooting---```Show Success Page ↓Create Order in Database ↓Verify Payment ↓Redirect Back to Your Site ↓PayPal Processes Payment ↓User Logs in & Pays ↓Redirect to PayPal.com ↓User Clicks Button ↓PayPal Button Appears ↓User Selects PayPal```## 📊 Payment Flow Diagram---- [ ] Monitor transaction logs- [ ] Test all payment flows before going live- [ ] Verify payments server-side (already implemented)- [ ] Enable HTTPS for production- [ ] Use environment variables for credentials- [ ] Never commit real API keys to Git## 🔒 Security Checklist---- Use test credentials (keys starting with `sk_test_` and `pk_test_`)- No real money charged during testing- Paymongo provides **Test Mode** automatically### For GCash:- Test transactions appear in your PayPal developer dashboard- Login with PayPal sandbox test account- Use **Sandbox Mode** (free, no real money)### For PayPal:## 🧪 Testing Guide---4. Complete test payment → Should return and create order3. Click it → Should redirect to PayPal sandbox2. Select PayPal → PayPal button should appear1. Go to your checkout page### Step 5: Test!Replace `YOUR_PAYPAL_CLIENT_ID` with your actual PayPal Client ID.```<script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=PHP"></script>```htmlOpen `user/paymentmethod.php` and find this line (around line 122):### Step 4: Update PayPal SDKOpen `config/payment-config.php` and replace the placeholder values.**Option B: Direct in payment-config.php**```PAYMONGO_PUBLIC_KEY=pk_test_your_public_keyPAYMONGO_SECRET_KEY=sk_test_your_secret_keyPAYPAL_CLIENT_SECRET=your_actual_secret_herePAYPAL_CLIENT_ID=your_actual_client_id_herePAYPAL_MODE=sandbox```env2. Add these lines:1. Open `config/.env` file**Option A: Using .env file (Recommended)**### Step 3: Configure Your Credentials4. Copy **Secret Key** and **Public Key**3. Go to **Dashboard** → **Developers** → **API Keys**2. Sign up for free account1. Go to https://paymongo.com/### Step 2: Get Paymongo Credentials (10 minutes)5. Copy the **Client ID** and **Secret**4. Click **Create App** under REST API apps3. Click **My Apps & Credentials**2. Sign in with your PayPal account1. Go to https://developer.paypal.com/### Step 1: Get PayPal Credentials (5 minutes)## 🚀 Next Steps to Make It Work---3. **`config/.env.example`** - Added payment credentials template2. **`assets/js/paymentmethod.js`** - Enhanced with payment logic1. **`user/paymentmethod.php`** - Added PayPal SDK and payment UI### Modified Files:6. **`PAYMENT_QUICK_TEST.md`** - Quick testing guide5. **`PAYMENT_GATEWAY_SETUP.md`** - Complete setup guide4. **`user/payment-success.php`** - Return page after payment3. **`api/gcash-payment.php`** - GCash payment API handler2. **`api/paypal-payment.php`** - PayPal payment API handler1. **`config/payment-config.php`** - Payment credentials configuration### New Files Created:## 📁 Files Created/Modified--- - Works as before (existing functionality)5. **For Card/COD:** - Returns to your site → Order created automatically - User scans QR code with GCash app - Click button → Redirects to GCash payment page - GCash button appears4. **For GCash:** - Returns to your site → Order created automatically - User logs in and confirms payment - Click button → Redirects to PayPal login - PayPal button appears automatically3. **For PayPal:**2. **Chooses payment method** → PayPal, GCash, Card, or COD1. **User goes to checkout** → Selects items from cart### User Experience## 🎯 How It Works Now---4. **💵 Cash on Delivery** (existing)3. **💚 GCash** (NEW - via Paymongo)2. **💙 PayPal** (NEW - with redirect to PayPal)1. **💳 Credit/Debit Card** (existing)Your payment system now supports **4 payment methods**:## ✅ What's Been Implemented * Payment Gateway Configuration
* Configure your PayPal and GCash credentials here
*/

// Load environment variables if available
if (file_exists(__DIR__ . '/env.php')) {
require_once __DIR__ . '/env.php';
}

// PayPal Configuration
define('PAYPAL_MODE', getenv('PAYPAL_MODE') ?: 'sandbox'); // 'sandbox' or 'live'
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'YOUR_PAYPAL_CLIENT_ID'); // Get from https://developer.paypal.com/
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') ?: 'YOUR_PAYPAL_CLIENT_SECRET');

// PayPal API URLs
if (PAYPAL_MODE === 'sandbox') {
define('PAYPAL_API_URL', 'https://api-m.sandbox.paypal.com');
} else {
define('PAYPAL_API_URL', 'https://api-m.paypal.com');
}

// GCash Configuration (Paymongo for GCash)
define('PAYMONGO_SECRET_KEY', getenv('PAYMONGO_SECRET_KEY') ?: 'YOUR_PAYMONGO_SECRET_KEY'); // Get from https://dashboard.paymongo.com/
define('PAYMONGO_PUBLIC_KEY', getenv('PAYMONGO_PUBLIC_KEY') ?: 'YOUR_PAYMONGO_PUBLIC_KEY');
define('PAYMONGO_API_URL', 'https://api.paymongo.com/v1');

// Currency
define('PAYMENT_CURRENCY', 'PHP');

// Return URLs (adjust these to your domain)
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$script_path = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
define('PAYMENT_RETURN_URL', $base_url . $script_path . '/user/payment-success.php');
define('PAYMENT_CANCEL_URL', $base_url . $script_path . '/user/paymentmethod.php');

?>