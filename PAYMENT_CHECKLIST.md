# Payment Integration Checklist

Use this checklist to ensure your payment system is properly set up and working.

## 📋 Pre-Setup Checklist

- [ ] Have a PayPal account (personal or business)
- [ ] Can access PayPal Developer Portal
- [ ] Have a Paymongo account (or can create one)
- [ ] Local development environment is running
- [ ] Can test with browser (Chrome/Firefox recommended)

---

## 🔧 Setup Phase

### PayPal Setup

- [ ] Created PayPal Developer account
- [ ] Created a REST API app in PayPal Dashboard
- [ ] Copied Client ID
- [ ] Copied Client Secret
- [ ] Created sandbox test buyer account
- [ ] Noted test buyer email and password

### Paymongo Setup (for GCash)

- [ ] Created Paymongo account
- [ ] Account verification started (may take time)
- [ ] Accessed API Keys section
- [ ] Copied Secret Key (sk*test*...)
- [ ] Copied Public Key (pk*test*...)
- [ ] Enabled GCash payment method

---

## ⚙️ Configuration Phase

### File Configuration

- [ ] Opened `config/payment-config.php`
- [ ] Updated PAYPAL_CLIENT_ID
- [ ] Updated PAYPAL_CLIENT_SECRET
- [ ] Updated PAYMONGO_SECRET_KEY
- [ ] Updated PAYMONGO_PUBLIC_KEY
- [ ] Verified PAYPAL_MODE is set to 'sandbox' for testing

### PayPal SDK Configuration

- [ ] Opened `user/paymentmethod.php`
- [ ] Found PayPal SDK script tag (line ~122)
- [ ] Replaced YOUR_PAYPAL_CLIENT_ID with actual Client ID
- [ ] Saved file

### Environment Variables (Optional but Recommended)

- [ ] Opened `config/.env`
- [ ] Added PAYPAL_MODE=sandbox
- [ ] Added PAYPAL_CLIENT_ID=your_id_here
- [ ] Added PAYPAL_CLIENT_SECRET=your_secret_here
- [ ] Added PAYMONGO_SECRET_KEY=your_key_here
- [ ] Added PAYMONGO_PUBLIC_KEY=your_key_here
- [ ] Saved file
- [ ] Verified .env is in .gitignore (never commit credentials!)

---

## 🧪 Testing Phase

### Basic Functionality Test

- [ ] Opened browser and navigated to payment page
- [ ] Cart has at least one item
- [ ] Payment page loads without errors
- [ ] Can see 4 payment options: Card, PayPal, GCash, COD

### PayPal Testing

- [ ] Selected PayPal option
- [ ] Card input fields disappeared
- [ ] PayPal button appeared automatically
- [ ] PayPal button is clickable (not grayed out)
- [ ] Clicked PayPal button
- [ ] Redirected to PayPal sandbox login page
- [ ] URL shows paypal.com or sandbox.paypal.com
- [ ] Logged in with test buyer account
- [ ] Confirmed payment details are correct
- [ ] Completed payment on PayPal
- [ ] Redirected back to your site
- [ ] Order was created successfully
- [ ] Received success message
- [ ] Can see order in "My Orders" or database

### GCash Testing (if Paymongo is verified)

- [ ] Selected GCash option
- [ ] Card input fields disappeared
- [ ] GCash button appeared
- [ ] Clicked GCash button
- [ ] Redirected to Paymongo/GCash page
- [ ] Saw GCash payment interface
- [ ] In test mode, payment processed
- [ ] Redirected back to your site
- [ ] Order created successfully

### Card/COD Testing (Existing)

- [ ] Selected Card option
- [ ] Card input fields visible
- [ ] Place Order button visible
- [ ] Can complete card payment (if implemented)
- [ ] Selected COD option
- [ ] Can place COD order

---

## 🔍 Error Checking

### Browser Console Check

- [ ] Opened browser Developer Tools (F12)
- [ ] Checked Console tab for errors
- [ ] No red errors related to PayPal SDK
- [ ] No errors when selecting payment methods
- [ ] No JavaScript errors on button clicks

### Network Tab Check

- [ ] Opened Network tab in Developer Tools
- [ ] Selected PayPal and clicked button
- [ ] Saw successful API calls to ../api/paypal-payment.php
- [ ] Response shows "success": true
- [ ] No 404 or 500 errors

### PHP Error Log Check

- [ ] Checked PHP error logs
- [ ] No errors in paypal-payment.php
- [ ] No errors in gcash-payment.php
- [ ] No errors in payment-success.php

---

## 🔐 Security Verification

### Credentials Security

- [ ] Payment credentials are NOT in Git repository
- [ ] .env file is in .gitignore
- [ ] No credentials visible in browser source code
- [ ] API keys are server-side only

### HTTPS Check (for production)

- [ ] Site uses HTTPS (required for live payments)
- [ ] SSL certificate is valid
- [ ] No mixed content warnings

### Payment Verification

- [ ] Payments are verified server-side
- [ ] Amount cannot be modified client-side
- [ ] User authentication is checked
- [ ] Order creation requires valid payment

---

## 📊 Database Verification

### Order Data

- [ ] Order created in database
- [ ] Payment method recorded correctly
- [ ] Payment details stored (PayPal order ID, etc.)
- [ ] Order status is correct
- [ ] Cart items cleared after order

### User Data

- [ ] User ID associated with order
- [ ] Customer name populated
- [ ] Notification created (if applicable)

---

## 🚀 Production Readiness (Before Going Live)

### PayPal Production Setup

- [ ] Created live PayPal app (not sandbox)
- [ ] Copied live Client ID and Secret
- [ ] Updated payment-config.php with live credentials
- [ ] Changed PAYPAL_MODE to 'live'
- [ ] Updated PayPal SDK script with live Client ID
- [ ] Tested with real PayPal account (small amount first!)
- [ ] Verified order creation works in production
- [ ] Checked PayPal transaction appears in your PayPal account

### GCash/Paymongo Production Setup

- [ ] Paymongo account fully verified
- [ ] Switched to live API keys (sk*live*...)
- [ ] Updated payment-config.php
- [ ] GCash enabled in live mode
- [ ] Tested with real GCash wallet (small amount first!)
- [ ] Verified transaction in Paymongo dashboard

### General Production Checks

- [ ] HTTPS/SSL enabled on domain
- [ ] Updated return URLs in payment-config.php
- [ ] Removed all test/debug code
- [ ] Enabled proper error logging
- [ ] Set up monitoring/alerts
- [ ] Prepared customer support for payment issues
- [ ] Created backup of database before launch
- [ ] Documented rollback procedure
- [ ] Tested all payment flows one final time
- [ ] Informed team of go-live time

---

## 🎯 Success Criteria

Your payment system is ready when:

✅ **User Experience**

- Users can select any payment method
- Payment process is smooth and intuitive
- Redirects work correctly
- Success/error messages are clear

✅ **Technical**

- No JavaScript errors
- No PHP errors
- API calls succeed
- Orders created correctly
- Database updated properly

✅ **Business**

- Payments actually process
- Money reaches your account
- Transaction records are accurate
- Can reconcile payments with orders

✅ **Security**

- Credentials are secure
- HTTPS enabled
- Payments verified server-side
- No sensitive data exposed

---

## 🐛 Common Issues Quick Fix

| Issue                         | Quick Fix                               |
| ----------------------------- | --------------------------------------- |
| PayPal button doesn't appear  | Check Client ID in SDK script           |
| "Invalid Client ID" error     | Verify credentials, check for spaces    |
| Payment succeeds but no order | Check api/order.php, verify cart_ids    |
| Redirect doesn't work         | Check return URLs in payment-config.php |
| GCash not working             | Verify Paymongo account status          |
| Console errors                | Check browser console, fix JS errors    |

---

## 📞 Support Resources

- **PayPal**: https://developer.paypal.com/support/
- **Paymongo**: support@paymongo.com
- **Setup Guide**: See PAYMENT_GATEWAY_SETUP.md
- **Quick Test**: See PAYMENT_QUICK_TEST.md
- **Architecture**: See PAYMENT_ARCHITECTURE.md

---

## ✅ Final Check

Before declaring "DONE":

- [ ] Completed all items in Setup Phase
- [ ] Completed all items in Configuration Phase
- [ ] All tests pass in Testing Phase
- [ ] No errors in Error Checking section
- [ ] Security measures verified
- [ ] Database working correctly

**If all checked: CONGRATULATIONS! 🎉 Your payment system is ready!**

---

**Current Status**: _[Mark your progress: Not Started / In Progress / Testing / Production Ready]_

**Notes**: _[Add any specific notes for your setup]_
