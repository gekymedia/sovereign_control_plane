# OAuth Setup Guide - Google & GitHub

This guide will help you configure Google and GitHub OAuth authentication for the Sovereign Control Plane.

## Prerequisites

- Laravel Socialite is already installed ✅
- You have a Google account
- You have a GitHub account

---

## Part 1: Google OAuth Setup

### Step 1: Create Google OAuth Credentials

1. **Go to Google Cloud Console**: https://console.cloud.google.com/

2. **Create a new project** (or select existing):
   - Click "Select a project" → "New Project"
   - Name: "Sovereign Control Plane"
   - Click "Create"

3. **Enable Google+ API**:
   - Go to "APIs & Services" → "Library"
   - Search for "Google+ API" or "People API"
   - Click "Enable"

4. **Create OAuth 2.0 Credentials**:
   - Go to "APIs & Services" → "Credentials"
   - Click "Create Credentials" → "OAuth client ID"
   - If prompted, configure OAuth consent screen first:
     - User Type: External (for testing) or Internal (for Google Workspace)
     - App name: "Sovereign Control Plane"
     - User support email: Your email
     - Developer contact: Your email
     - Click "Save and Continue"
     - Scopes: Add `email`, `profile`, `openid`
     - Click "Save and Continue"
     - Test users: Add your email (for testing)
     - Click "Save and Continue"

5. **Create OAuth Client**:
   - Application type: "Web application"
   - Name: "Sovereign Control Plane Web"
   - Authorized redirect URIs:
     ```
     http://localhost:8000/auth/google/callback
     ```
     (For production, add your production URL)
   - Click "Create"

6. **Copy Credentials**:
   - You'll see a popup with:
     - **Client ID**: `xxxxx.apps.googleusercontent.com`
     - **Client Secret**: `xxxxx`
   - Copy both values

### Step 2: Add to .env

Add these to your `.env` file:

```env
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## Part 2: GitHub OAuth Setup

### Step 1: Create GitHub OAuth App

1. **Go to GitHub**: https://github.com

2. **Go to Settings**:
   - Click your profile picture (top right)
   - Click "Settings"

3. **Developer settings**:
   - Scroll down → "Developer settings" (left sidebar)
   - Click "OAuth Apps"

4. **New OAuth App**:
   - Click "New OAuth App"
   - Application name: "Sovereign Control Plane"
   - Homepage URL: `http://localhost:8000`
   - Authorization callback URL: `http://localhost:8000/auth/github/callback`
   - Click "Register application"

5. **Copy Credentials**:
   - You'll see:
     - **Client ID**: `xxxxx`
     - **Client Secret**: Click "Generate a new client secret"
     - Copy both values

### Step 2: Add to .env

Add these to your `.env` file:

```env
GITHUB_CLIENT_ID=your_github_client_id_here
GITHUB_CLIENT_SECRET=your_github_client_secret_here
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

---

## Part 3: Run Migration

Run the migration to add OAuth fields to users table:

```bash
php artisan migrate
```

---

## Part 4: Test OAuth Login

1. **Start your server**:
   ```bash
   php artisan serve
   ```

2. **Go to login page**: http://localhost:8000/login

3. **Click "Google" or "GitHub" button**

4. **Authorize the application**

5. **You should be redirected back and logged in!**

---

## Production Setup

### For Production URLs:

1. **Update Google OAuth**:
   - Add production callback URL in Google Cloud Console:
     ```
     https://yourdomain.com/auth/google/callback
     ```

2. **Update GitHub OAuth**:
   - Edit your GitHub OAuth App
   - Update "Authorization callback URL" to:
     ```
     https://yourdomain.com/auth/github/callback
     ```

3. **Update .env**:
   ```env
   APP_URL=https://yourdomain.com
   GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback
   GITHUB_REDIRECT_URI=https://yourdomain.com/auth/github/callback
   ```

---

## How It Works

1. **User clicks OAuth button** → Redirects to provider (Google/GitHub)
2. **User authorizes** → Provider redirects back with code
3. **Laravel exchanges code** → Gets user info from provider
4. **User is created/updated** → With provider info stored
5. **User is logged in** → Redirected to dashboard

### New Users:
- Automatically created with `viewer` role
- Email is verified automatically
- Can be upgraded to `admin` or `oracle` by existing admins

### Existing Users:
- If email matches, account is linked to OAuth provider
- Can now login with OAuth or password

---

## Troubleshooting

### "Redirect URI mismatch"
- **Google**: Check that callback URL in Google Console matches exactly (including http/https, port, trailing slash)
- **GitHub**: Check callback URL in GitHub OAuth App settings

### "Invalid credentials"
- Verify `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` in `.env`
- Verify `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET` in `.env`
- Make sure no extra spaces or quotes

### "Email not provided"
- **GitHub**: Make sure you've authorized the `user:email` scope
- **Google**: Should work automatically

### "User not found" / "Login fails"
- Check logs: `storage/logs/laravel.log`
- Verify migration ran: `php artisan migrate:status`
- Check database has `provider`, `provider_id`, `avatar` columns

---

## Security Notes

1. **Never commit `.env`** - OAuth secrets are sensitive
2. **Use HTTPS in production** - Required by OAuth providers
3. **Limit OAuth scopes** - Only request what you need
4. **Review authorized apps** - Periodically check OAuth apps in provider dashboards
5. **Rotate secrets** - If compromised, regenerate immediately

---

## Environment Variables Summary

Add these to your `.env`:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# GitHub OAuth
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

---

## Quick Links

- **Google Cloud Console**: https://console.cloud.google.com/
- **GitHub OAuth Apps**: https://github.com/settings/developers
- **Laravel Socialite Docs**: https://laravel.com/docs/socialite

---

**That's it! Your OAuth authentication is now configured! 🎉**
