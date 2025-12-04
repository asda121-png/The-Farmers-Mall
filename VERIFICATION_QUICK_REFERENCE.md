# ⚡ Quick Reference - Email Verification System

## 🎯 TL;DR

After `git pull`, the email verification system works automatically. Just try registering!

## 📍 Key URLs

| Page | URL | Purpose |
|------|-----|---------|
| Registration | `/auth/register.php` | Main registration form |
| Debug Session | `/auth/verify-debug.php` | See verification code in session |
| Test Email | `/test-email.php` | Send test email to yourself |

## 🔄 Registration Flow

```
Step 1: Personal Info ✅
    ↓ (Click Next)
Step 2: Address ✅
    ↓ (Click Next)
Step 3: Account Details ✅
    ↓ (Click Next)
Step 4: Verification ✅ ← YOU ARE HERE
    • Enter email
    • Click "Send Verification Code"
    • See code on screen (or in email)
    • Enter code
    • Click Next ← TO HERE
    ↓ (Click Next)
Step 5: Accept Terms ✅
    ↓ (Click Sign Up)
DONE! ✅
```

## 📧 Verification Code

- **Length:** 6 digits (e.g., `123456`)
- **Generated:** When you click "Send Code"
- **Expires:** After 5 minutes
- **Stored:** In PHP session
- **Where to get:** Email inbox OR `verify-debug.php`

## ✅ It's Working If:

- ✅ You get email with code
- ✅ Code shows on screen
- ✅ You can enter code
- ✅ Next button works
- ✅ You see Step 5 (Terms)

## ❌ It's NOT Working If:

- ❌ No email received → Check `verify-debug.php`
- ❌ Code not on screen → Check `debug_email.log`
- ❌ "Code does not match" → Use code from `verify-debug.php`
- ❌ "Code has expired" → Request new code
- ❌ Next button disabled → Enter valid code

## 🧪 Quick Test

1. Go to: `http://localhost/The-Farmers-Mall/auth/register.php`
2. Fill all steps (fake data is OK for testing)
3. Step 4: Enter your email
4. Click "Send Verification Code"
5. See code on screen
6. Enter code
7. Click Next
8. Click Sign Up
9. **Done!** ✅

## 🔧 If Code Doesn't Work

1. Open: `http://localhost/The-Farmers-Mall/auth/verify-debug.php`
2. Copy the code shown
3. Go back to registration
4. Paste code in verification field
5. Click Next

## 📋 File Locations (Don't Need to Edit)

```
✅ config/.env ...................... Database config (DO NOT EDIT)
✅ auth/register.php ................ Registration form (working)
✅ auth/verify-email.php ............ OTP generator (working)
✅ includes/mailer.php .............. Email sender (working)
```

## 📝 Debug Files (Read-Only)

```
debug_email.log ..................... Email sending logs (created on first use)
debug_verification.log ............. OTP generation logs (created on first use)
```

## 🔐 Remember

- ✅ Session data persists between pages
- ✅ Code expires in 5 minutes
- ✅ Email and code must match
- ✅ Server validates everything
- ✅ Session cleared after successful use

## ❓ FAQ

**Q: Do I need to configure anything?**  
A: No! Everything is pre-configured. Just register.

**Q: Why is code shown on screen?**  
A: Development mode. Helps test without real email.

**Q: Will real emails be sent?**  
A: Yes! But system works without them.

**Q: How long is code valid?**  
A: 5 minutes only.

**Q: Can I use any email?**  
A: Yes, any real email (Gmail, Outlook, etc.)

**Q: What if I lose the code?**  
A: Request a new one. Click "Send Verification Code" again.

---

**🎉 Registration is Ready to Use!**

Start here: `http://localhost/The-Farmers-Mall/auth/register.php`
