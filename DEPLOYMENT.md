# Production Deployment Guide

## Folder Structure on Production Server

```
~/domains/micronesian.school/
├── app_root/              # Laravel application & Git repository
│   ├── app/
│   ├── public/           # Source files (NOT web root)
│   ├── storage/
│   ├── .env
│   ├── artisan
│   └── scripts/
│       └── deploy-production.sh
└── public_html/          # Web root (served by nginx/apache)
    ├── build/           # Vite compiled assets
    ├── css/
    ├── js/
    ├── library-assets/
    ├── index.php        # Laravel entry point
    └── storage -> ../app_root/storage/app/public
```

## Deployment Process

### Prerequisites
1. SSH access to production server
2. Git repository configured with proper credentials
3. Composer installed on server
4. Database access configured in `.env`
5. **Local machine** with Node.js/npm for building assets

### Build Workflow
**IMPORTANT:** The production server does NOT have Node.js/npm installed. Assets must be built locally and committed to Git.

1. **Local Development:**
   ```bash
   # Make your changes
   npm run build              # Build production assets
   git add .                  # Include build files
   git commit -m "Your changes"
   git push origin main
   ```

2. **Production Deployment:**
   - Production pulls pre-built assets from Git
   - No building happens on server

### Steps to Deploy

1. **SSH into production server:**
   ```bash
   ssh username@micronesian.school
   ```

2. **Navigate to app_root:**
   ```bash
   cd ~/domains/micronesian.school/app_root
   ```

3. **Run deployment script:**
   ```bash
   ./scripts/deploy-production.sh
   ```

### What the Script Does

1. ✅ **Maintenance Mode** - Puts site in maintenance mode
2. ✅ **Git Pull** - Pulls latest code from GitHub (includes pre-built assets)
3. ✅ **Composer** - Installs/updates PHP dependencies (production only)
4. ✅ **Migrations** - Runs database migrations
5. ✅ **Cache Clear** - Clears all Laravel caches
6. ✅ **Verify Assets** - Checks pre-built assets exist
7. ✅ **Copy Files** - Copies files to public_html:
   - `index.php` - Laravel entry point
   - `.htaccess` - Apache configuration
   - `build/` - Vite compiled assets
   - `css/`, `js/` - Static assets
   - `library-assets/` - Library images and files
   - `ui-test/` - UI templates
   - `admin-assets/` - Admin panel assets
8. ✅ **Storage Symlink** - Verifies storage symlink
9. ✅ **Optimize** - Caches config, routes, views
10. ✅ **Permissions** - Sets proper file permissions
11. ✅ **Queue Restart** - Restarts queue workers (if enabled)
12. ✅ **Maintenance Mode Off** - Brings site back online

### Manual Deployment Steps

If you need to run steps manually:

**On Local Machine (before deploying):**
```bash
# Build assets locally
npm run build

# Commit and push
git add public/build
git commit -m "Update build assets"
git push origin main
```

**On Production Server:**
```bash
# Navigate to app_root
cd ~/domains/micronesian.school/app_root

# Pull latest code (includes pre-built assets)
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Copy files to public_html
cp -f public/index.php ../public_html/
cp -rf public/build ../public_html/
cp -rf public/css ../public_html/
cp -rf public/js ../public_html/
cp -rf public/library-assets ../public_html/

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Troubleshooting

#### Script Fails
- Check the deployment log file: `deploy-YYYYMMDD-HHMMSS.log`
- Ensure you're in the app_root directory
- Verify Git credentials are configured
- Check file permissions

#### Site Shows 500 Error
- Check Laravel logs: `storage/logs/laravel.log`
- Verify `.env` file exists and is configured
- Check database connection
- Ensure storage permissions: `chmod -R 775 storage bootstrap/cache`

#### Assets Not Loading
- Verify files copied to public_html
- Check storage symlink: `ls -la ../public_html/storage`
- Clear browser cache
- Check `.htaccess` file exists in public_html

#### Database Errors
- Verify database credentials in `.env`
- Check migrations ran successfully
- Verify database user has proper permissions

### Rollback

If deployment fails and you need to rollback:

```bash
cd ~/domains/micronesian.school/app_root

# Rollback to previous commit
git reset --hard HEAD~1

# Re-run deployment
./scripts/deploy-production.sh
```

### Git Configuration for Build Files

**IMPORTANT:** By default, Laravel's `.gitignore` excludes the `public/build` directory. Since the production server doesn't have Node.js, you MUST track build files in Git.

**One-time setup:**
```bash
# Edit .gitignore to allow build files
nano .gitignore

# Remove or comment out this line:
# /public/build

# Or add exception after it:
!/public/build
```

**After making changes:**
```bash
# Build assets
npm run build

# Add build files
git add public/build -f

# Commit everything
git commit -m "Your changes with build assets"
git push origin main
```

### Environment Variables

Ensure these are set in `.env` on production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://micronesian.school

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

QUEUE_CONNECTION=database  # or sync
```

### First-Time Setup

If deploying for the first time:

1. **Clone repository:**
   ```bash
   cd ~/domains/micronesian.school/app_root
   git clone <repository-url> .
   ```

2. **Copy environment file:**
   ```bash
   cp .env.example .env
   nano .env  # Edit with production values
   ```

3. **Generate app key:**
   ```bash
   php artisan key:generate
   ```

4. **Run initial deployment:**
   ```bash
   ./scripts/deploy-production.sh
   ```

5. **Create storage symlink manually if needed:**
   ```bash
   ln -sf ~/domains/micronesian.school/app_root/storage/app/public ~/domains/micronesian.school/public_html/storage
   ```

### Security Checklist

- ✅ `.env` file has proper permissions (600)
- ✅ `APP_DEBUG=false` in production
- ✅ Database credentials are secure
- ✅ Git repository is private
- ✅ SSH keys are used (not passwords)
- ✅ Storage directory has proper permissions

### Monitoring

After deployment:
1. Check the website is accessible
2. Test login functionality
3. Test library search and filters
4. Verify admin panel works
5. Check error logs for any issues
6. Test PDF downloads

### Support

For issues:
1. Check deployment log
2. Check Laravel logs: `storage/logs/laravel.log`
3. Check web server error logs
4. Review this guide's troubleshooting section
