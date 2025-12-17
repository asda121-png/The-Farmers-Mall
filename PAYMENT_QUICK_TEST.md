# Quick Test Configuration

## For Immediate Testing (Sandbox Mode)

### 1. PayPal Sandbox Test Account

**Test Login Credentials** (provided by PayPal Sandbox):

- Go to: https://developer.paypal.com/dashboard/accounts
- Click on the "Personal" account email
- Use these credentials to test payments

**Default Sandbox Buyer Account:**

- Email: Usually auto-generated like `sb-xxxxx@personal.example.com`
- Password: Check in your PayPal Developer Dashboard

### 2. Update paymentmethod.php

Find this line in `user/paymentmethod.php`:

```html
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=PHP"></script>
```

Replace `YOUR_PAYPAL_CLIENT_ID` with your actual sandbox Client ID from PayPal Developer Dashboard.

### 3. Example Configuration

Here's what your `config/payment-config.php` should look like for testing:

```php
// SANDBOX MODE (for testing)
define('PAYPAL_MODE', 'sandbox');
define('PAYPAL_CLIENT_ID', 'AaB1C2D3E4F5G6H7I8J9K0L1M2N3O4P5Q6R7S8T9U0');  // Replace with your sandbox client ID
define('PAYPAL_CLIENT_SECRET', 'EaB1C2D3E4F5G6H7I8J9K0L1M2N3O4P5Q6R7S8T9U0'); // Replace with your sandbox secret

// GCash via Paymongo (TEST MODE)
define('PAYMONGO_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxxxxx'); // Replace with your test secret key
define('PAYMONGO_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxxxxxxxxxxx'); // Replace with your test public key
```

## Test Payment Scenarios

### PayPal Testing

1. Select PayPal payment method
2. Click PayPal button
3. Login with sandbox buyer account
4. Complete payment
5. Should redirect back and create order

### GCash Testing (Paymongo)

1. Select GCash payment method
2. Click "Pay with GCash"
3. Use test mode - no real money charged
4. Complete in Paymongo test interface
5. Should redirect back and create order

## Quick Verification Checklist

- [ ] PayPal Client ID added to SDK script
- [ ] PayPal credentials in payment-config.php
- [ ] Paymongo keys in payment-config.php
- [ ] HTTPS enabled (or localhost for testing)
- [ ] Browser console shows no errors
- [ ] Payment buttons appear when selected

## Need Credentials Fast?

### Get PayPal Sandbox Credentials:

1. Visit: https://developer.paypal.com/
2. Sign in
3. Go to: Dashboard > My Apps & Credentials
4. Click "Create App" under "REST API apps"
5. Copy Client ID and Secret

### Get Paymongo Test Credentials:

1. Visit: https://paymongo.com/
2. Sign up for free
3. Go to: Dashboard > Developers > API Keys
4. Copy test keys (start with sk*test* and pk*test*)

---

**Remember:** These are TEST credentials. Never use sandbox/test keys in production!
