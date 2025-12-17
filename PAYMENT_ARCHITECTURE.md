# Payment System Architecture

## 📐 System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        FARMERS MALL                              │
│                     Payment System v2.0                          │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│   User/Browser   │────▶│  Payment Method  │────▶│  Payment Gateway │
│                  │     │   Selection      │     │   (PayPal/GCash) │
└──────────────────┘     └──────────────────┘     └──────────────────┘
                                  │
                                  ▼
                         ┌──────────────────┐
                         │  Order Creation  │
                         │   & Processing   │
                         └──────────────────┘
```

## 🔄 Payment Flow Comparison

### Traditional Card/COD Flow

```
User → Select Payment → Enter Details → Click "Place Order" → Order Created → Success
       (1 step)         (same page)     (same page)           (database)
```

### PayPal Flow (NEW)

```
User → Select PayPal → PayPal Button Appears → Click Button → PayPal.com
                       (JavaScript SDK)         (redirect)     (login & pay)
                                                     ↓
Success ← Order Created ← Verify Payment ← Return ← Payment Complete
         (database)        (API call)      (callback)
```

### GCash Flow (NEW)

```
User → Select GCash → GCash Button Appears → Click Button → Paymongo/GCash
                      (custom button)         (redirect)     (scan QR & pay)
                                                   ↓
Success ← Order Created ← Verify Payment ← Return ← Payment Complete
         (database)        (API call)      (callback)
```

## 🗂️ File Structure

```
The-Farmers-Mall/
├── user/
│   ├── paymentmethod.php           ← Main payment page (MODIFIED)
│   └── payment-success.php         ← Return handler (NEW)
│
├── api/
│   ├── order.php                   ← Order creation
│   ├── paypal-payment.php          ← PayPal API handler (NEW)
│   └── gcash-payment.php           ← GCash API handler (NEW)
│
├── assets/js/
│   └── paymentmethod.js            ← Payment logic (MODIFIED)
│
├── config/
│   ├── payment-config.php          ← Credentials (NEW)
│   └── .env                        ← Environment vars
│
└── Documentation/
    ├── PAYMENT_GATEWAY_SETUP.md    ← Setup guide
    ├── PAYMENT_QUICK_TEST.md       ← Testing guide
    └── PAYMENT_INTEGRATION_SUMMARY.md ← This summary
```

## 🔌 API Integration Architecture

### PayPal Integration

```
┌─────────────────────────────────────────────────────────────┐
│                     PAYPAL WORKFLOW                          │
└─────────────────────────────────────────────────────────────┘

Client Side                Server Side              PayPal
    │                          │                       │
    │  1. Create Order         │                       │
    ├─────────────────────────▶│                       │
    │                          │  2. Request Token     │
    │                          ├──────────────────────▶│
    │                          │  3. Access Token      │
    │                          ◀──────────────────────┤
    │                          │  4. Create Order      │
    │                          ├──────────────────────▶│
    │  5. Order ID             │  6. Order Details     │
    ◀─────────────────────────┤◀──────────────────────┤
    │                          │                       │
    │  7. Redirect to PayPal   │                       │
    ├──────────────────────────────────────────────────▶
    │                          │  8. User pays         │
    │                          │                       │
    │  9. Return with token    │                       │
    ◀──────────────────────────────────────────────────┤
    │                          │                       │
    │  10. Capture Payment     │                       │
    ├─────────────────────────▶│  11. Capture Request  │
    │                          ├──────────────────────▶│
    │                          │  12. Capture Complete │
    │  13. Success             ◀──────────────────────┤
    ◀─────────────────────────┤                       │
    │  14. Create Order in DB  │                       │
    ├─────────────────────────▶│                       │
```

### GCash Integration (via Paymongo)

```
┌─────────────────────────────────────────────────────────────┐
│                  GCASH/PAYMONGO WORKFLOW                     │
└─────────────────────────────────────────────────────────────┘

Client Side                Server Side            Paymongo/GCash
    │                          │                       │
    │  1. Create Source        │                       │
    ├─────────────────────────▶│                       │
    │                          │  2. Create GCash      │
    │                          │     Payment Source    │
    │                          ├──────────────────────▶│
    │  3. Checkout URL         │  4. Source Created    │
    ◀─────────────────────────┤◀──────────────────────┤
    │                          │                       │
    │  5. Redirect to GCash    │                       │
    ├──────────────────────────────────────────────────▶
    │                          │  6. Display QR Code   │
    │                          │  7. User scans & pays │
    │                          │                       │
    │  8. Return with source   │                       │
    ◀──────────────────────────────────────────────────┤
    │                          │                       │
    │  9. Verify Payment       │                       │
    ├─────────────────────────▶│  10. Check Status     │
    │                          ├──────────────────────▶│
    │                          │  11. Payment Confirmed│
    │  12. Success             ◀──────────────────────┤
    ◀─────────────────────────┤                       │
    │  13. Create Order in DB  │                       │
    ├─────────────────────────▶│                       │
```

## 🎨 UI Component States

### Payment Method Selection

```
┌──────────────────────────────────────┐
│  ○ Credit/Debit Card                 │
│    [Card input fields visible]       │
├──────────────────────────────────────┤
│  ● PayPal                            │
│    [PayPal button visible]           │  ← Selected
│    [Card fields hidden]              │
├──────────────────────────────────────┤
│  ○ GCash                             │
│    [GCash button hidden]             │
├──────────────────────────────────────┤
│  ○ Cash on Delivery                  │
│    [COD info hidden]                 │
└──────────────────────────────────────┘
```

### Button Visibility Logic

```javascript
if (payment === 'paypal') {
  show: PayPal Button
  hide: Place Order Button, GCash Button, Card Fields
}
else if (payment === 'gcash') {
  show: GCash Button
  hide: Place Order Button, PayPal Button, Card Fields
}
else {
  show: Place Order Button, (Card Fields if card selected)
  hide: PayPal Button, GCash Button
}
```

## 🔐 Security Layers

```
┌─────────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                           │
└─────────────────────────────────────────────────────────────┘

Layer 1: Client-Side
  ├─ HTTPS/SSL Encryption
  ├─ Input Validation
  └─ Secure Token Storage (sessionStorage)

Layer 2: Server-Side
  ├─ Session Validation
  ├─ User Authentication Check
  ├─ Payment Amount Verification
  └─ API Credential Protection (.env)

Layer 3: Payment Gateway
  ├─ PayPal Security
  │  ├─ OAuth 2.0
  │  ├─ API Authentication
  │  └─ PCI Compliance
  │
  └─ Paymongo Security
     ├─ API Key Authentication
     ├─ 3D Secure Support
     └─ PCI DSS Level 1

Layer 4: Database
  ├─ Parameterized Queries
  ├─ Input Sanitization
  └─ Access Control (RLS)
```

## 📊 Data Flow

### Order Creation Data Flow

```
Cart Items ──────────────────┐
                             │
User Data ───────────────────┼──▶ Order API
                             │
Payment Info ────────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  Validate Data  │
                    └─────────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  Create Order   │
                    │   in Database   │
                    └─────────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  Clear Cart     │
                    └─────────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │ Send Notification│
                    └─────────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  Success Page   │
                    └─────────────────┘
```

## 🎯 Key Components

### 1. Payment Method Selector (paymentmethod.php)

- **Purpose**: Display payment options
- **Features**:
  - Radio button selection
  - Dynamic UI updates
  - PayPal SDK integration

### 2. Payment Logic (paymentmethod.js)

- **Purpose**: Handle payment processing
- **Features**:
  - PayPal button initialization
  - GCash redirect handling
  - Order creation coordination

### 3. PayPal API Handler (paypal-payment.php)

- **Purpose**: PayPal API communication
- **Features**:
  - Order creation
  - Payment capture
  - Status verification

### 4. GCash API Handler (gcash-payment.php)

- **Purpose**: Paymongo API communication
- **Features**:
  - Source creation
  - Payment verification
  - Status checking

### 5. Success Handler (payment-success.php)

- **Purpose**: Handle payment returns
- **Features**:
  - Payment verification
  - Order creation
  - User notification

## 🔄 State Management

```
Session Storage:
  ├─ pending_paypal_order
  │  ├─ paypal_order_id
  │  ├─ amount
  │  └─ cart_items
  │
  └─ pending_gcash_payment
     ├─ source_id
     ├─ amount
     └─ cart_items

Local Storage:
  ├─ userNotifications (order confirmations)
  └─ cart (cleared after order)

Session Storage (Browser):
  └─ selectedCartItems (for checkout)
```

---

## 📝 Summary

This architecture provides:

- ✅ Secure payment processing
- ✅ Multiple payment options
- ✅ Seamless user experience
- ✅ Proper error handling
- ✅ Transaction verification
- ✅ Scalable design

Ready for production with proper credentials!
