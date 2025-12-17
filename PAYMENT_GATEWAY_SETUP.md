# Payment Gateway Setup Guide

This guide will help you set up PayPal and GCash payment integration for Farmers Mall.

## 🎯 Overview

The payment system now supports:

- **Credit/Debit Cards** (existing)
- **PayPal** (new - with redirect to PayPal interface)
- **GCash** (new - via Paymongo)
- **Cash on Delivery** (existing)

## 📋 Prerequisites

1. Active PayPal Business Account
2. Paymongo Account (for GCash)
3. SSL Certificate (HTTPS required for production)

---

## 🔧 PayPal Setup

### Step 1: Create PayPal Developer Account

1. Go to [PayPal Developer Dashboard](https://developer.paypal.com/)
2. Sign in with your PayPal account
3. Navigate to **Dashboard** → **My Apps & Credentials**

### Step 2: Create REST API App

1. Click **Create App**
2. Enter app name: `Farmers Mall`
3. Select **Sandbox** for testing (or **Live** for production)
4. Click **Create App**

### Step 3: Get API Credentials

After creating the app, you'll see:

- **Client ID** - Copy this
- **Secret** - Click "Show" and copy this

### Step 4: Configure Credentials

Open `config/payment-config.php` and update:

```php
// For Testing (Sandbox)
define('PAYPAL_MODE', 'sandbox');
define('PAYPAL_CLIENT_ID', 'your_sandbox_client_id_here');
define('PAYPAL_CLIENT_SECRET', 'your_sandbox_secret_here');

// For Production (Live)
define('PAYPAL_MODE', 'live');
define('PAYPAL_CLIENT_ID', 'your_live_client_id_here');
define('PAYPAL_CLIENT_SECRET', 'your_live_secret_here');
```

### Step 5: Update PayPal SDK

Open `user/paymentmethod.php` and update the PayPal SDK script:

```html
<!-- Replace YOUR_PAYPAL_CLIENT_ID with your actual Client ID -->
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_ACTUAL_CLIENT_ID&currency=PHP"></script>
```

---

## 💰 GCash Setup (via Paymongo)

### Step 1: Create Paymongo Account

1. Go to [Paymongo](https://www.paymongo.com/)
2. Sign up for a business account
3. Complete verification process

### Step 2: Get API Keys

1. Log in to [Paymongo Dashboard](https://dashboard.paymongo.com/)
2. Navigate to **Developers** → **API Keys**
3. Copy:
   - **Secret Key** (starts with `sk_test_` for testing)
   - **Public Key** (starts with `pk_test_` for testing)

### Step 3: Configure Credentials

Open `config/payment-config.php` and update:

```php
// For Testing
define('PAYMONGO_SECRET_KEY', 'sk_test_your_secret_key_here');
define('PAYMONGO_PUBLIC_KEY', 'pk_test_your_public_key_here');

// For Production
define('PAYMONGO_SECRET_KEY', 'sk_live_your_secret_key_here');
define('PAYMONGO_PUBLIC_KEY', 'pk_live_your_public_key_here');
```

### Step 4: Enable GCash

In Paymongo dashboard:

1. Go to **Payment Methods**
2. Enable **GCash**
3. Complete any required verification

---

## 🧪 Testing

### Test PayPal

1. Use PayPal Sandbox accounts:
   - Go to [Sandbox Accounts](https://developer.paypal.com/dashboard/accounts)
   - Use the test buyer account credentials
2. Select PayPal payment method
3. You'll be redirected to PayPal sandbox
4. Log in with test account
5. Complete payment

### Test GCash

Paymongo provides test mode with these test numbers:

- **Success**: Use any valid mobile number
- **Test Amount**: ₱100.00 or above

---

## 🚀 Going Live

### PayPal Production Checklist

- [ ] Create live app in PayPal Developer Dashboard
- [ ] Update `PAYPAL_MODE` to `'live'`
- [ ] Update Client ID and Secret with live credentials
- [ ] Update PayPal SDK script with live Client ID
- [ ] Test with real PayPal account

### GCash/Paymongo Production Checklist

- [ ] Complete Paymongo business verification
- [ ] Switch to live API keys
- [ ] Enable GCash in production
- [ ] Test with real GCash wallet

### General Checklist

- [ ] Enable HTTPS/SSL certificate
- [ ] Update return URLs in `config/payment-config.php`
- [ ] Test all payment flows
- [ ] Set up webhook handlers (optional, for payment notifications)
- [ ] Configure proper error logging
- [ ] Review security settings

---

## 📱 How It Works

### PayPal Flow

1. User selects PayPal payment method
2. PayPal button appears automatically
3. User clicks PayPal button
4. Redirects to PayPal login/payment page
5. User completes payment on PayPal
6. Returns to your site
7. Order is created automatically

### GCash Flow

1. User selects GCash payment method
2. GCash button appears
3. User clicks "Pay with GCash"
4. Redirects to GCash payment page
5. User scans QR code with GCash app
6. Confirms payment in GCash app
7. Returns to your site
8. Order is created automatically

---

## 🔒 Security Notes

1. **Never commit API keys to Git**
   - Add `config/payment-config.php` to `.gitignore`
2. **Use environment variables** (recommended)

   ```php
   define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID'));
   ```

3. **Validate all payments server-side**

   - Don't trust client-side data
   - Always verify with payment provider

4. **Use HTTPS in production**
   - Required by PayPal and Paymongo
   - Protects sensitive data

---

## 🐛 Troubleshooting

### PayPal button doesn't appear

- Check browser console for errors
- Verify Client ID in SDK script
- Ensure PayPal SDK loaded successfully

### PayPal "Invalid Client ID" error

- Double-check Client ID in `paymentmethod.php`
- Ensure no extra spaces or characters
- Verify you're using correct environment (sandbox/live)

### GCash redirect fails

- Check Paymongo API keys
- Verify account is verified
- Check error logs in browser console

### Payment succeeds but order not created

- Check `api/order.php` for errors
- Verify database connection
- Check PHP error logs

---

## 📞 Support

- **PayPal Developer Support**: https://developer.paypal.com/support/
- **Paymongo Support**: support@paymongo.com
- **Documentation**:
  - PayPal: https://developer.paypal.com/docs/
  - Paymongo: https://developers.paymongo.com/docs

---

## 📝 Files Modified

1. `user/paymentmethod.php` - Added PayPal SDK and payment UI
2. `assets/js/paymentmethod.js` - Payment logic and redirects
3. `config/payment-config.php` - Payment credentials (NEW)
4. `api/paypal-payment.php` - PayPal API handler (NEW)
5. `api/gcash-payment.php` - GCash API handler (NEW)
6. `user/payment-success.php` - Return page handler (NEW)

---

## ✅ Quick Start

1. Get PayPal Client ID and Secret
2. Get Paymongo API keys
3. Update `config/payment-config.php`
4. Update PayPal SDK in `user/paymentmethod.php`
5. Test payments
6. Go live when ready!

---

**Need Help?** Check the troubleshooting section or contact support.
