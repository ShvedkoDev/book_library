# Production Deployment Guide

## Gmail SMTP Configuration for Password Reset Emails

This guide will help you configure Gmail SMTP for sending password reset emails on your production server (micronesian.school).

---

## Part 1: Generate Gmail App Password

### Step 1: Enable 2-Factor Authentication

1. Go to your Google Account: https://myaccount.google.com/
2. Click on **Security** in the left sidebar
3. Under "How you sign in to Google", click on **2-Step Verification**
4. Follow the prompts to enable 2FA if not already enabled

### Step 2: Generate App Password

1. After enabling 2FA, go back to **Security**
2. Under "How you sign in to Google", click on **2-Step Verification**
3. Scroll down and click on **App passwords**
4. You may need to sign in again
5. Under "Select app", choose **Mail**
6. Under "Select device", choose **Other (Custom name)**
7. Enter a name like "Micronesian Library" and click **Generate**
8. Copy the 16-character password that appears
   - It will look like: `xxxx xxxx xxxx xxxx`
   - **Important**: Remove all spaces when using it in .env file
   - Example: Use `xxxxxxxxxxxxxxxx` not `xxxx xxxx xxxx xxxx`

---

## Part 2: Update Production Server Configuration

### Step 1: Connect to Your Production Server

Connect via SSH, FTP, or your hosting control panel file manager to access:
```
/home/u309806638/domains/micronesian.school/public_html/
```

### Step 2: Backup Current .env File

Before making changes, create a backup:
```bash
cp .env .env.backup
```

### Step 3: Update .env File

Open the `.env` file on your production server and update these mail configuration values:

**Find these lines:**
```env
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Replace with:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your16charapppassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Important replacements:**
- Replace `your-email@gmail.com` with your actual Gmail address
- Replace `your16charapppassword` with the app password (no spaces!)
- Remove `MAIL_SCHEME=null` line if present

### Step 4: Clear Application Cache

After updating the .env file, clear all caches:

**Via SSH:**
```bash
cd /home/u309806638/domains/micronesian.school/public_html
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

**Via File Manager (if SSH not available):**
1. Delete all files in `bootstrap/cache/` (keep the directory)
2. Delete all files in `storage/framework/cache/data/` (keep the directory)
3. Delete all files in `storage/framework/views/` (keep the directory)

---

## Part 3: Test Email Configuration

### Test 1: Try Password Reset

1. Go to https://micronesian.school/forgot-password
2. Enter a valid user email (e.g., `admin@micronesianlib.edu`)
3. Click "Email password reset link"
4. Check the Gmail inbox for the reset email
5. Verify the reset link works

### Test 2: Check Laravel Logs (if email doesn't arrive)

If the password reset email doesn't arrive, check the logs:

```bash
tail -f /home/u309806638/domains/micronesian.school/public_html/storage/logs/laravel.log
```

Look for any SMTP connection errors.

---

## Common Issues & Solutions

### Issue 1: "Invalid credentials" error

**Solution:**
- Make sure you're using an **App Password**, not your regular Gmail password
- Verify the app password is entered without spaces
- Confirm 2-Factor Authentication is enabled on the Gmail account

### Issue 2: "Connection timeout" error

**Solution:**
- Some hosting providers block port 587
- Try using port 465 with SSL encryption instead:
  ```env
  MAIL_PORT=465
  MAIL_ENCRYPTION=ssl
  ```

### Issue 3: Gmail blocking the connection

**Solution:**
1. Check Gmail's "Recent security activity" at https://myaccount.google.com/security-checkup
2. You may need to allow "Less secure app access" (though App Passwords should avoid this)
3. Check if your hosting provider's IP is blocked by Gmail

### Issue 4: Changes not taking effect

**Solution:**
- Make sure you cleared the config cache (Step 4 above)
- Verify you're editing the correct .env file on production
- Check file permissions: .env should be readable by the web server

---

## Alternative: Using Mailtrap for Testing

If you want to test emails without actually sending them (recommended for staging):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@micronesian.school
MAIL_FROM_NAME="${APP_NAME}"
```

Get credentials from: https://mailtrap.io/

---

## Security Best Practices

1. **Never commit .env files to Git** - They contain sensitive credentials
2. **Use App Passwords** - Never use your actual Gmail password
3. **Rotate credentials periodically** - Generate new app passwords every few months
4. **Monitor usage** - Check Gmail's activity log for unusual access
5. **Use environment-specific configs** - Different settings for local/staging/production

---

## Quick Reference: Production vs Local

### Production (.env on server)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://micronesian.school
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
```

### Local (.env on your computer)
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
MAIL_MAILER=log
```

---

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify Gmail app password is correct
3. Test SMTP connection manually
4. Contact your hosting provider if port 587/465 is blocked

---

**Last Updated:** December 10, 2025
**Production Server:** micronesian.school
**Framework:** Laravel 12.37.0
**PHP Version:** 8.2.28
