# 🔐 Google OAuth Setup Guide for Team

## The Issue Your Team Member is Experiencing

They're getting: **"Error 400: redirect_uri_mismatch"**

This means the URL where Google tries to redirect them after login doesn't match what's registered in the Google Cloud Console.

## Quick Fix (5 minutes)

### Step 1: Get Your Redirect URI
1. Open this page in your browser: `http://localhost:8080/The-Farmers-Mall/oauth-diagnostic.php`
2. Copy the **Redirect URI** shown on that page
3. It should look like: `http://localhost:8080/The-Farmers-Mall/auth/google-callback.php`

### Step 2: Add It to Google Cloud Console
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select the project that has this app's OAuth credentials
3. Click **APIs & Services** → **Credentials** (left sidebar)
4. Find your **OAuth 2.0 Client ID** and click it
5. Under **Authorized redirect URIs**, click **Add URI**
6. Paste your redirect URI from Step 1
7. Click **Save**

### Step 3: Wait & Retry
- Wait 30 seconds for Google to update
- Try logging in again

## Important Notes

### Port 8080 is Critical!
⚠️ If your application runs on a different port, update the redirect URI accordingly:
- If running on port 3000: `http://localhost:3000/The-Farmers-Mall/auth/google-callback.php`
- If running on port 5000: `http://localhost:5000/The-Farmers-Mall/auth/google-callback.php`

### For Team Members with Their Own Google OAuth App
If someone has their own Google OAuth credentials:

1. Create their own `.env.local` file (don't commit to Git):
   ```
   # config/.env.local
   GOOGLE_CLIENT_ID=their_client_id_here
   GOOGLE_CLIENT_SECRET=their_client_secret_here
   ```

2. Get the diagnostic page redirect URI and add it to **their** Google Cloud Console

3. The path `/The-Farmers-Mall/auth/google-callback.php` must be the same

## Credentials Security

✅ **Shared Credentials** (stored in `.env.local` - NOT committed to Git):
- `GOOGLE_CLIENT_ID=your_shared_client_id_here`
- `GOOGLE_CLIENT_SECRET=your_shared_client_secret_here`

⚠️ **Keep `.env.local` private** - it's in `.gitignore` and should never be committed
📝 **Contact the team lead** to get the actual credentials for `.env.local`

## Verification Features (NEW)

The email verification system is now working on both:
- ✅ Customer registration (`/auth/register.php`)
- ✅ Retailer registration (`/retailer/startselling.php`)

Both use the same email verification code system (6-digit codes, 5-minute expiration).

## Still Having Issues?

1. **Clear browser cache** (Ctrl+Shift+Delete or Cmd+Shift+Delete)
2. **Check .env.local exists** in `config/` directory with your credentials
3. **Verify the exact redirect URI** - every character must match Google Cloud Console
4. **Check localhost vs 127.0.0.1** - must be consistent
5. **Verify http vs https** - usually http for localhost development

## Testing the Setup

Visit these pages to test:
- Customer signup: `http://localhost:8080/The-Farmers-Mall/public/index.php` (click Register)
- Retailer signup: `http://localhost:8080/The-Farmers-Mall/retailer/startselling.php`
- OAuth diagnostic: `http://localhost:8080/The-Farmers-Mall/oauth-diagnostic.php`
