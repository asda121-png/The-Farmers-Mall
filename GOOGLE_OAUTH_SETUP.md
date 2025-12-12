# Google OAuth Integration Setup Guide

## 🔐 SECURITY FIRST: Each Developer Needs Their Own Credentials

**⚠️ IMPORTANT:** This project requires each team member to create and use their **OWN Google OAuth credentials**. Never share credentials with other developers.

**For detailed developer setup instructions, see:** [GOOGLE_OAUTH_DEVELOPER_SETUP.md](GOOGLE_OAUTH_DEVELOPER_SETUP.md)

---

## Overview
The Farmers Mall application now supports Google OAuth login and registration. Users can seamlessly log in or create an account using their Google credentials without entering passwords.

## Features Added
- ✅ "Continue with Google" button on login page
- ✅ "Continue with Google" button on registration page
- ✅ Automatic user account creation from Google profile
- ✅ Automatic user login when Google authentication succeeds
- ✅ Secure credential management (credentials stored in .env file, not hardcoded)
- ✅ Support for both new and existing users

## Files Modified/Created

### New Files:
1. **config/google-oauth.php** - Google OAuth handler class
2. **auth/google-callback.php** - OAuth callback handler
3. **GOOGLE_OAUTH_SETUP.md** - This setup guide

### Modified Files:
1. **auth/login.php** - Added Google login functionality
2. **auth/register.php** - Added Google signup functionality
3. **config/.env** - Added Google OAuth credentials
4. **config/.env.example** - Added Google OAuth setup instructions

## How It Works

### Login Flow:
1. User clicks "Continue with Google" on login page
2. Redirected to Google OAuth authorization
3. User logs in with Google account
4. Google redirects back to `/auth/google-callback.php`
5. System checks if user exists:
   - If exists: User is logged in
   - If new: User account is automatically created and logged in

### Registration Flow:
1. User clicks "Continue with Google" on registration page
2. Same OAuth flow as login
3. New user account is automatically created with Google information
4. User is logged in immediately

## Setting Up Google OAuth Credentials

### For Local Development:

1. **Create Google Cloud Project:**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Click "Create Project" and enter your project name
   - Wait for project creation to complete

2. **Enable Google+ API:**
   - In the Google Cloud Console, search for "Google+ API"
   - Click on it and press "Enable"

3. **Create OAuth 2.0 Credentials:**
   - Go to "Credentials" in the left sidebar
   - Click "Create Credentials" → "OAuth 2.0 Client ID"
   - Choose "Web application"
   - Add authorized redirect URIs:
     ```
     http://localhost/The-Farmers-Mall/auth/google-callback.php
     ```

4. **Copy Credentials:**
   - Copy the **Client ID**
   - Copy the **Client Secret**

5. **Update .env File:**
   - Open `config/.env`
   - Add your Google OAuth credentials:
     ```
     GOOGLE_CLIENT_ID=YOUR_GOOGLE_CLIENT_ID
     GOOGLE_CLIENT_SECRET=YOUR_GOOGLE_CLIENT_SECRET
     ```

### For Production Deployment:

1. Use the same Google Cloud project credentials
2. Add your production domain to authorized redirect URIs:
   ```
   https://yourdomain.com/auth/google-callback.php
   ```
3. Deploy the code to your production server
4. The .env file on production should have the same Google credentials

## Security Considerations

✅ **Credentials Protection:**
- Google OAuth credentials are stored in `.env` file
- `.env` file is excluded from Git (.gitignore)
- Credentials are never exposed in code or browser

✅ **User Data Safety:**
- Passwords are securely hashed with PASSWORD_DEFAULT
- Google accounts created automatically get random secure passwords
- User accounts can only be accessed via email or Google OAuth

✅ **HTTPS Requirement:**
- Google OAuth works with both HTTP (localhost) and HTTPS (production)
- Ensure production uses HTTPS for security

## How Others Can Use This in Their Repository

1. **Pull the latest code** from your repository
2. **Copy the .env.example file to .env:**
   ```bash
   cp config/.env.example config/.env
   ```
3. **Get their own Google OAuth credentials:**
   - Follow "Setting Up Google OAuth Credentials" section above
4. **Update their .env file with their credentials:**
   ```
   GOOGLE_CLIENT_ID=their_client_id
   GOOGLE_CLIENT_SECRET=their_client_secret
   ```
5. **Update their authorized redirect URIs in Google Console** to match their domain

**That's it!** The Google OAuth functionality will work automatically.

## Testing

### Test Login:
1. Go to your login page
2. Click "Continue with Google"
3. Log in with a Google account
4. You should be redirected to the user dashboard

### Test Registration:
1. Go to your registration page
2. Click "Continue with Google"
3. Log in with a different Google account
4. New account should be created automatically
5. You should be logged in and redirected to the dashboard

## Troubleshooting

### Issue: "Google authentication is not configured"
- **Solution:** Ensure .env file has GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET filled in correctly

### Issue: "Failed to exchange authorization code for token"
- **Solution:** Verify the authorized redirect URI in Google Cloud Console matches your local/production URL exactly

### Issue: User created but not logged in
- **Solution:** Check error logs. User may have inactive status or other database issues.

### Issue: OAuth button not working
- **Solution:** Ensure PHP curl extension is enabled. Add this to test:
  ```php
  <?php echo extension_loaded('curl') ? 'curl enabled' : 'curl disabled'; ?>
  ```

## Future Enhancements

Possible improvements:
- Add Google profile picture integration
- Link existing accounts with Google
- Add OAuth logout functionality
- Support for other OAuth providers (Facebook, GitHub)
- Enhanced user profile data from Google

## Support

For issues or questions:
1. Check the .env configuration
2. Verify Google Cloud Console settings
3. Review error logs in the browser console (F12)
4. Check PHP error logs for server-side errors

---

**Created:** December 13, 2025
**Last Updated:** December 13, 2025
