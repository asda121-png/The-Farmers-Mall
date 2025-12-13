# 🔐 SHARED CREDENTIALS FOR TEAM (CONFIDENTIAL)

This file contains the shared credentials that your team members should add to their local `.env` files.

**⚠️ KEEP THIS FILE PRIVATE - DO NOT COMMIT TO GIT**

---

## For Team Members Setting Up

When you clone the repository and see these placeholders in `config/.env`:

```
GOOGLE_CLIENT_ID=YOUR_SHARED_CLIENT_ID_HERE
GOOGLE_CLIENT_SECRET=YOUR_SHARED_CLIENT_SECRET_HERE
```

**Contact your team lead to get the actual shared credentials** and add them to your local `config/.env.local` file (which is not committed to Git).

---

## Steps to Set Up

1. Clone the repository
2. Open `config/.env` in your editor
3. Find these lines:
   ```
   GOOGLE_CLIENT_ID=YOUR_SHARED_CLIENT_ID_HERE
   GOOGLE_CLIENT_SECRET=YOUR_SHARED_CLIENT_SECRET_HERE
   ```
4. Replace with the credentials above
5. Save the file
6. Your local `.env` is in `.gitignore`, so it won't be committed
7. Test: Go to the homepage and click "Continue with Google" ✅

---

## Important Security Notes

✅ **What you should do:**
- Add credentials to your LOCAL `.env` file only
- Never commit `.env` to Git
- Never share these credentials in chat or email
- If leaked, ask team lead to rotate credentials

❌ **What NOT to do:**
- Don't add credentials to public documentation
- Don't share the `.env` file
- Don't commit credentials to the repository
- Don't hardcode credentials in code

---

## For Team Lead

The credentials are stored in this private file to:
1. Keep them out of Git/GitHub
2. Provide a single source of truth for your team
3. Make setup easier for new developers

If you need to rotate credentials:
1. Create new credentials in Google Cloud Console
2. Update this file
3. Tell team members to update their local `.env`

---

**Project:** The Farmers Mall
**Setup Date:** December 13, 2025
**Team Lead:** [Your name/email]

