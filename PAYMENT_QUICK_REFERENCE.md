# 🚀 Quick Reference Card

## 30-Second Setup

1. **Get PayPal Credentials** → https://developer.paypal.com/
2. **Update payment-config.php** → Add Client ID & Secret
3. **Update paymentmethod.php** → Add Client ID to SDK script (line 122)
4. **Test** → Open test-paypal-integration.html

---

## Essential Files

| File                        | What to Update                   |
| --------------------------- | -------------------------------- |
| `config/payment-config.php` | PayPal & GCash credentials       |
| `user/paymentmethod.php`    | PayPal Client ID in SDK script   |
| `config/.env`               | (Optional) Environment variables |

---

## Quick Commands

### Check if files exist:

```bash
# PowerShell
Test-Path api/paypal-payment.php
Test-Path api/gcash-payment.php
Test-Path user/payment-success.php
```

### Test API:

```bash
# Open in browser
http://localhost/The-Farmers-Mall/test-paypal-integration.html
```

---

## Credentials Needed

### PayPal (5 min to get)

- ✅ Client ID (from developer.paypal.com)
- ✅ Client Secret (from developer.paypal.com)
- ✅ Sandbox buyer account (auto-created)

### GCash/Paymongo (10 min to get)

- ✅ Secret Key (from dashboard.paymongo.com)
- ✅ Public Key (from dashboard.paymongo.com)
- ⏳ Account verification (may take hours/days)

---

## Configuration Checklist

```
□ Got PayPal Client ID
□ Got PayPal Secret
□ Updated payment-config.php
□ Updated SDK script in paymentmethod.php
□ Saved all files
□ Tested PayPal button appears
□ Tested payment redirect works
```

---

## Test Workflow

1. Add item to cart
2. Go to checkout
3. Select PayPal
4. Click PayPal button
5. Login with sandbox account
6. Complete payment
7. Verify order created

---

## Error Quick Fixes

| Error                 | Fix                    |
| --------------------- | ---------------------- |
| Button doesn't appear | Check Client ID in SDK |
| Invalid Client ID     | Verify credentials     |
| No redirect           | Check browser console  |
| Order not created     | Check api/order.php    |

---

## Important URLs

### PayPal:

- Dashboard: https://developer.paypal.com/dashboard/
- Sandbox Accounts: https://developer.paypal.com/dashboard/accounts
- Docs: https://developer.paypal.com/docs/

### Paymongo:

- Dashboard: https://dashboard.paymongo.com/
- API Keys: https://dashboard.paymongo.com/developers/api-keys
- Docs: https://developers.paymongo.com/docs

---

## File Locations

```
config/
  └─ payment-config.php        ← Update credentials here

user/
  ├─ paymentmethod.php         ← Update SDK script (line 122)
  └─ payment-success.php       ← Return handler

api/
  ├─ paypal-payment.php        ← PayPal API
  └─ gcash-payment.php         ← GCash API

assets/js/
  └─ paymentmethod.js          ← Payment logic

test-paypal-integration.html   ← Quick test page
```

---

## Code Snippets

### Update SDK Script (paymentmethod.php line 122)

```html
<!-- Replace YOUR_PAYPAL_CLIENT_ID -->
<script src="https://www.paypal.com/sdk/js?client-id=AaB1C2D3E4F5&currency=PHP"></script>
```

### Update Credentials (payment-config.php)

```php
define('PAYPAL_CLIENT_ID', 'AaB1C2D3E4F5G6H7');
define('PAYPAL_CLIENT_SECRET', 'EaB1C2D3E4F5G6H7');
define('PAYMONGO_SECRET_KEY', 'sk_test_xxxxx');
define('PAYMONGO_PUBLIC_KEY', 'pk_test_xxxxx');
```

---

## Payment Flow (Simple)

```
User selects PayPal
     ↓
PayPal button appears
     ↓
User clicks button
     ↓
Redirects to PayPal
     ↓
User pays
     ↓
Returns to site
     ↓
Order created
     ↓
Success!
```

---

## Testing Modes

### Sandbox (Testing)

- ✅ No real money
- ✅ Use test accounts
- ✅ Free unlimited testing
- Mode: `sandbox`

### Live (Production)

- ⚠️ Real money
- ⚠️ Real PayPal accounts
- ⚠️ Careful testing needed
- Mode: `live`

---

## Security Reminders

- ❌ Never commit credentials to Git
- ✅ Use .env for credentials
- ✅ Always verify server-side
- ✅ Enable HTTPS for production

---

## Next Steps

1. **NOW**: Get credentials
2. **NEXT**: Update config files
3. **THEN**: Test with test-paypal-integration.html
4. **FINALLY**: Test full checkout flow

---

## Support

- 📖 Full Setup: [PAYMENT_GATEWAY_SETUP.md](PAYMENT_GATEWAY_SETUP.md)
- ✅ Checklist: [PAYMENT_CHECKLIST.md](PAYMENT_CHECKLIST.md)
- 🎨 Visual Guide: [PAYMENT_VISUAL_GUIDE.md](PAYMENT_VISUAL_GUIDE.md)
- 🏗️ Architecture: [PAYMENT_ARCHITECTURE.md](PAYMENT_ARCHITECTURE.md)

---

**Print this card and keep it handy while setting up!** 📌
