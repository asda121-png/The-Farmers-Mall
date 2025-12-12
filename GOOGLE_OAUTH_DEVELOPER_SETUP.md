# 🔐 Google OAuth - Secure Setup for Developers

## ⚠️ IMPORTANT: Each Developer Must Use Their Own Credentials

This project uses Google OAuth for authentication. **Each team member must create and use their OWN Google credentials** for security reasons.

**DO NOT share credentials. DO NOT commit .env file to Git.**

---

## 🛡️ Security Principles

✅ **Each developer has their own credentials**
✅ **Credentials are NEVER committed to Git** (.env is in .gitignore)
✅ **Credentials are stored locally in .env file only**
✅ **Easy for anyone to set up their own credentials**

---

## 👤 FOR EACH DEVELOPER: Setup Instructions

### Step 1: Create Your Own Google OAuth Credentials (10 minutes)

1. **Go to:** https://console.cloud.google.com/
2. **Sign in** with your Google account
3. **Create a new project:**
   - Click "Select a Project" (top left)
   - Click "NEW PROJECT"
   - Name: `Farmers Mall - YourName` (so it's clear who owns it)
   - Click "CREATE"
   - Wait 1-2 minutes

4. **Enable Google+ API:**
   - Search "Google+ API"
   - Click it → "ENABLE"

5. **Configure OAuth consent screen:**
   - Go to "OAuth consent screen" (left sidebar)
   - Select "External"
   - Click "CREATE"
   - Fill in:
     - App name: `Farmers Mall`
     - User support email: your-email
     - Developer contact: your-email
   - Click "SAVE AND CONTINUE" (twice)

6. **Create OAuth credentials:**
   - Go to "Credentials" (left sidebar)
   - Click "Create Credentials" → "OAuth 2.0 Client ID"
   - Choose "Web application"
   - Name: `Farmers Mall Login`
   - Add authorized redirect URIs:
     ```
     http://localhost/The-Farmers-Mall/auth/google-callback.php
     http://127.0.0.1/The-Farmers-Mall/auth/google-callback.php
     ```
   - Click "CREATE"
   - **COPY your Client ID and Client Secret**

### Step 2: Update Your Local .env File (2 minutes)

1. **Copy the template:**
   ```bash
   cp config/.env.example config/.env
   ```

2. **Open `config/.env`** and fill in YOUR credentials:
   ```
   GOOGLE_CLIENT_ID=YOUR_CLIENT_ID_HERE
   GOOGLE_CLIENT_SECRET=YOUR_CLIENT_SECRET_HERE
   ```

3. **Save the file**

4. **⚠️ NEVER commit this file** (it's in .gitignore - Git will ignore it automatically)

### Step 3: Test Your Setup (2 minutes)

1. **Visit diagnostic tool:**
   ```
   http://localhost/The-Farmers-Mall/google-oauth-diagnostic.php
   ```

2. **Verify:**
   - ✅ Your redirect URI is shown
   - ✅ Client ID and Secret are configured
   - ✅ No error messages

3. **Test login:**
   - Go to: http://localhost/The-Farmers-Mall/auth/login.php
   - Click "Continue with Google"
   - Should see Google consent screen (not error)

---

## 🔄 For Production Deployment

When deploying to production:

1. **Create NEW Google OAuth credentials** for production domain
2. **Get new Client ID and Secret** for production
3. **On production server:**
   - Create `.env` file with production credentials
   - Never share or commit this file

---

## ❓ FAQ

### Q: Why can't I use someone else's credentials?
**A:** For security. If credentials are compromised, only one person's app is affected, not the whole team.

### Q: What if my credentials leak?
**A:** 
1. Delete the OAuth app in Google Cloud Console
2. Create new credentials
3. Update your .env file
4. Problem is isolated to you

### Q: Can I see other developers' credentials?
**A:** No - they're stored in `.env` which is NOT committed to Git. Each developer's credentials are completely private.

### Q: Do I need the same Google account as other developers?
**A:** No - each developer can use their own Google account to create their own credentials.

### Q: What's the difference between development and production?
**A:** 
- **Development:** Runs locally, uses `http://localhost` redirect URI
- **Production:** Runs on your domain, uses `https://yourdomain.com` redirect URI

You need separate credentials for each environment.

---

## 🔍 Troubleshooting

### Issue: "redirect_uri_mismatch" error

**Solution:**
1. Visit: http://localhost/The-Farmers-Mall/google-oauth-diagnostic.php
2. Copy the redirect URI shown
3. Go to Google Cloud Console
4. Add that exact URI to your OAuth credentials
5. Wait 30 seconds and try again

### Issue: "Access blocked" error

**Solution:**
1. Make sure OAuth consent screen is configured
2. Make sure Google+ API is ENABLED
3. Check that Client ID and Secret are correct in .env
4. Clear browser cache (Ctrl+Shift+Delete)

### Issue: Can't find my Client ID/Secret

**Solution:**
1. Go to: https://console.cloud.google.com/apis/credentials
2. Look for "OAuth 2.0 Client IDs"
3. Click on "Web application" type (not "Desktop" or "Mobile")
4. Click on your credential
5. Client ID and Secret are shown at the top

---

## 📝 Checklist for New Developers

Before you start working:

- [ ] Created your own Google Cloud project
- [ ] Enabled Google+ API
- [ ] Configured OAuth consent screen
- [ ] Created OAuth 2.0 credentials
- [ ] Copied Client ID and Secret
- [ ] Created `.env` file from `.env.example`
- [ ] Filled in your credentials in `.env`
- [ ] Verified .env file is NOT committed to Git
- [ ] Tested login/signup with "Continue with Google"
- [ ] Credentials work without errors

---

## 🚀 For Project Maintainers

When setting up the project:

1. **Keep `config/.env.example` updated** with current instructions
2. **Make sure `.env` is in `.gitignore`** (it already is)
3. **Document the setup process** (this file does that)
4. **Never commit real `.env` file** to the repository
5. **Remind team members** not to share credentials

---

## 📞 Support

If you get stuck:

1. Check the **Troubleshooting** section above
2. Visit **Diagnostic Tool:** http://localhost/The-Farmers-Mall/google-oauth-diagnostic.php
3. Check the main setup guide: GOOGLE_OAUTH_SETUP.md
4. Ask the project maintainer for help

---

## ✅ Security Checklist

- ✅ .env file NOT in Git repository
- ✅ Each developer has OWN credentials
- ✅ Credentials NOT shared between team members
- ✅ Credentials NOT hardcoded in source files
- ✅ .env.example shows how to set up (no real values)
- ✅ Credentials stored securely locally

---

**Remember: Your credentials are like passwords. Keep them private and secure!** 🔐
