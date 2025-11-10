# Quick Deployment Checklist
## Micronesian Teachers Digital Library - Manual Deployment on Hostinger

**Domain:** micronesian.school
**Hosting:** Hostinger Cloud/Shared Hosting (No Docker)
**Method:** Manual Laravel Deployment

---

## Pre-Deployment

- [ ] Access to Hostinger hPanel: https://hpanel.hostinger.com
- [ ] SSH credentials ready
- [ ] Domain micronesian.school is active
- [ ] GitHub repository access

---

## Step-by-Step Deployment

### 1. hPanel Configuration (5 minutes)

- [ ] **Enable SSL**
  - hPanel → SSL → Install Free Let's Encrypt SSL
  - Wait 5-10 minutes for activation

- [ ] **Create MySQL Database**
  - hPanel → Databases → MySQL Databases → New Database
  - Database name: `micronesian_library`
  - Username: `micronesian_user`
  - Password: `[SAVE THIS PASSWORD!]`

- [ ] **Set PHP Version**
  - hPanel → Advanced → PHP Configuration
  - Select PHP 8.2 or 8.3

### 2. Upload Application Files (10 minutes)

**Option A: Via SSH + Git (Recommended)**
```bash
ssh your-username@micronesian.school
cd domains/micronesian.school/public_html
rm -rf * .htaccess
git clone https://github.com/ShvedkoDev/book_library.git .
```

**Option B: Via SFTP**
- Use FileZilla to upload files to `public_html`

### 3. Install Dependencies (5 minutes)

```bash
cd domains/micronesian.school/public_html

# Check if Composer exists
composer --version

# If not, install Composer
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar composer
chmod +x composer
echo 'export PATH="$HOME:$PATH"' >> ~/.bashrc
source ~/.bashrc

# Install PHP dependencies
cd domains/micronesian.school/public_html
composer install --no-dev --optimize-autoloader
```

### 4. Configure Environment (10 minutes)

```bash
cd domains/micronesian.school/public_html

# Create .env file
cp .env.production .env

# Edit .env
nano .env
```

**Update these values:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://micronesian.school

DB_HOST=localhost
DB_DATABASE=micronesian_library
DB_USERNAME=micronesian_user
DB_PASSWORD=[your password from Step 1]

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

**Save:** Ctrl+O, Enter, Ctrl+X

```bash
# Secure .env file
chmod 600 .env
```

### 5. Run Laravel Setup (5 minutes)

```bash
cd domains/micronesian.school/public_html

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Configure Web Server (5 minutes)

**Option A: Update Document Root (Recommended)**
- hPanel → Advanced → Change Website Root
- Set to: `public_html/public`
- Wait 5-10 minutes

**Option B: If can't change document root**
```bash
cd domains/micronesian.school/public_html
mkdir -p ../app_root
mv * ../app_root/
mv ../app_root/public/* .
```
Then edit `index.php` to update paths to `../app_root/`

### 7. Set Permissions (2 minutes)

```bash
cd domains/micronesian.school/public_html

# Set permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod +x artisan
chmod -R 775 storage bootstrap/cache
```

### 8. Create Admin User (2 minutes)

```bash
cd domains/micronesian.school/public_html
php artisan make:filament-user
```

**Enter:**
- Name: Your Name
- Email: admin@micronesian.school
- Password: [SAVE THIS PASSWORD!]

### 9. Test Deployment (2 minutes)

- [ ] Visit: https://micronesian.school
- [ ] Should see homepage (no errors)

- [ ] Visit: https://micronesian.school/admin
- [ ] Login with credentials from Step 8
- [ ] Should see FilamentPHP admin dashboard

### 10. Check for Errors

```bash
# View logs
tail -f storage/logs/laravel.log

# Should be empty or show INFO logs only
```

---

## Troubleshooting Quick Fixes

### 500 Error
```bash
tail -50 storage/logs/laravel.log
php artisan config:clear
php artisan cache:clear
```

### Database Connection Error
```bash
# Verify database credentials
cat .env | grep DB_
# Check they match hPanel database settings
```

### Permission Errors
```bash
chmod -R 775 storage bootstrap/cache
```

### Assets Not Loading
- Check document root points to `public` folder
- Verify `.htaccess` exists in public folder

---

## Post-Deployment

### Build Frontend Assets (If Needed)

**If Node.js is available on server:**
```bash
cd domains/micronesian.school/public_html
npm install
npm run build
```

**If Node.js NOT available:**
```bash
# On local machine:
cd /home/gena/book_library
npm install
npm run build

# Upload public/build folder via SFTP
```

### Upload Book PDFs

```bash
# Create books directory
mkdir -p storage/app/public/books
chmod 775 storage/app/public/books

# Upload PDFs via SFTP to:
# domains/micronesian.school/public_html/storage/app/public/books/
```

### Regular Maintenance Commands

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Important URLs

- **Main Site:** https://micronesian.school
- **Admin Panel:** https://micronesian.school/admin
- **hPanel:** https://hpanel.hostinger.com
- **GitHub Repo:** https://github.com/ShvedkoDev/book_library

---

## Important Credentials to Save

- [ ] Database name, username, password
- [ ] Admin panel email and password
- [ ] SSH username and password
- [ ] hPanel login credentials

---

## Estimated Total Time: 45-60 minutes

**Need Help?**
- See PRODUCTION_DEPLOYMENT.md for detailed instructions
- Contact Hostinger support via hPanel live chat
- Check Laravel logs: `storage/logs/laravel.log`
