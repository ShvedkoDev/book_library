# Production Update Guide
## Micronesian Teachers Digital Library

**Purpose:** Update an existing production deployment with the latest changes from the repository.

**Estimated Time:** 15-30 minutes (depending on changes)

---

## 📋 Pre-Update Checklist

Before starting the update process:

- [ ] **Backup your database**
- [ ] **Backup your files** (especially `storage/app/public/`)
- [ ] **Note current commit/version**
- [ ] **Check for scheduled maintenance window** (if possible)
- [ ] **Verify SSH access to production server**
- [ ] **Have rollback plan ready**

---

## 🚨 Important Notes

1. **Always backup first** - Never update without a backup
2. **Test locally first** - If possible, test the update process locally
3. **Maintenance mode** - Put site in maintenance mode during update
4. **Read commit history** - Review what changed since your last update
5. **Monitor logs** - Watch for errors during and after update

---

## 📦 Update Methods

Choose the method that matches your deployment setup:

### Method 1: SSH + Git (Recommended) ⭐
If your production site is connected to Git repository.

### Method 2: SFTP Upload
If you deployed via SFTP and don't have Git on server.

---

# Method 1: SSH + Git Update (Recommended)

## Step 1: Connect to Production Server

```bash
# Connect via SSH
ssh your-username@micronesian.school
# OR
ssh your-username@your-server-ip
```

## Step 2: Navigate to Project Directory

```bash
# Navigate to your Laravel project root
cd ~/domains/micronesian.school/public_html
# OR wherever your project is located
# Common paths:
# cd ~/public_html
# cd /var/www/micronesian.school
# cd ~/htdocs

# Verify you're in the right directory
pwd
ls -la
# You should see: artisan, composer.json, app/, etc.
```

## Step 3: Put Site in Maintenance Mode

```bash
# Enable maintenance mode
php artisan down --render="errors::503" --refresh=15

# Your site will now show "Service Unavailable" to visitors
```

**Alternative with custom message:**
```bash
php artisan down --message="Updating the library with new features. Back in 15 minutes!"
```

## Step 4: Create Backup

### A. Backup Database

```bash
# Create backup directory
mkdir -p ~/backups/$(date +%Y%m%d)

# Export database (adjust credentials)
php artisan db:backup
# OR manually:
mysqldump -u your_db_user -p your_db_name > ~/backups/$(date +%Y%m%d)/database_backup.sql
```

### B. Backup Files

```bash
# Backup storage folder (uploaded files)
tar -czf ~/backups/$(date +%Y%m%d)/storage_backup.tar.gz storage/

# Backup .env file
cp .env ~/backups/$(date +%Y%m%d)/.env.backup
```

## Step 5: Check Current Version

```bash
# Show current commit
git log -1 --oneline

# Check status
git status

# Note this commit hash in case you need to rollback
```

## Step 6: Stash Local Changes (if any)

```bash
# Check if you have local changes
git status

# If you have local changes, stash them
git stash save "Local changes before update $(date +%Y%m%d-%H%M%S)"

# You can restore these later if needed
```

## Step 7: Pull Latest Changes

```bash
# Fetch latest changes from repository
git fetch origin

# Show what will change
git log HEAD..origin/main --oneline

# Pull the changes
git pull origin main
```

**If you encounter conflicts:**
```bash
# View conflicted files
git status

# Option 1: Accept remote changes
git checkout --theirs conflicted-file.php

# Option 2: Accept your local changes
git checkout --ours conflicted-file.php

# After resolving, continue
git add .
git pull origin main
```

## Step 8: Update Dependencies

```bash
# Update Composer dependencies
composer install --no-dev --optimize-autoloader

# This will install any new packages added to composer.json
```

**If composer not found:**
```bash
# Download composer first
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Then use it
php composer.phar install --no-dev --optimize-autoloader
```

## Step 9: Update Node Dependencies (if frontend changed)

```bash
# Only if package.json was updated
npm install

# Rebuild assets
npm run build
```

## Step 10: Run Database Migrations

```bash
# Check what migrations will run
php artisan migrate:status

# Run new migrations
php artisan migrate --force

# The --force flag is required in production
```

**If migration fails:**
```bash
# Rollback the last migration batch
php artisan migrate:rollback --step=1

# Check what went wrong
tail -100 storage/logs/laravel.log
```

## Step 11: Sync Storage Links

```bash
# Recreate symbolic link if needed
php artisan storage:link
```

## Step 12: Clear and Rebuild Caches

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimized caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear OPcache if available
php artisan optimize:clear
php artisan optimize
```

## Step 13: Update File Permissions

```bash
# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/framework

# If you know your web server user (e.g., www-data, apache, nobody)
# sudo chown -R www-data:www-data storage bootstrap/cache
```

## Step 14: Test the Application

```bash
# Check for errors in logs
tail -50 storage/logs/laravel.log

# Test artisan commands work
php artisan route:list | head -10
```

## Step 15: Disable Maintenance Mode

```bash
# Bring site back online
php artisan up

# Site is now live with updates
```

## Step 16: Verify Everything Works

Visit your site and test:
- [ ] **Homepage loads:** https://micronesian.school
- [ ] **Library search works:** https://micronesian.school/library
- [ ] **Book pages load:** Click on a book
- [ ] **Login works:** Test user authentication
- [ ] **Admin panel:** https://micronesian.school/admin
- [ ] **PDF viewing/download:** Test file access
- [ ] **No errors in browser console:** Press F12 and check Console tab

## Step 17: Monitor for Issues

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log

# Check for PHP errors (varies by hosting)
tail -f /var/log/php-error.log
```

---

# Method 2: SFTP Upload Update

Use this method if you don't have Git on your production server.

## Step 1: Prepare Update Package Locally

```bash
# On your local machine, in project directory
cd /home/gena/book_library

# Pull latest changes
git pull origin main

# Create deployment package
./prepare-deployment.sh
# OR manually create archive
tar -czf book_library_update_$(date +%Y%m%d).tar.gz \
    --exclude=node_modules \
    --exclude=.git \
    --exclude=.env \
    --exclude=storage \
    --exclude=vendor \
    .
```

## Step 2: Backup Production Before Upload

**Via SSH (if available):**
```bash
# Connect and backup
ssh your-username@micronesian.school
cd ~/domains/micronesian.school/public_html

# Create backups
mkdir -p ~/backups/$(date +%Y%m%d)
cp .env ~/backups/$(date +%Y%m%d)/.env.backup
tar -czf ~/backups/$(date +%Y%m%d)/storage_backup.tar.gz storage/
mysqldump -u user -p database > ~/backups/$(date +%Y%m%d)/database.sql
```

**Via Hostinger hPanel:**
1. Go to https://hpanel.hostinger.com
2. Navigate to **Databases** → Select your database
3. Click **Backup** and download
4. Go to **Files** → **File Manager**
5. Download your `storage/app/public/` folder

## Step 3: Upload Files via SFTP

```bash
# Using SFTP from command line
sftp your-username@micronesian.school

# Navigate to project
cd domains/micronesian.school/public_html

# Upload new/changed files
put -r app/
put -r resources/
put -r routes/
put -r config/
put -r database/
put composer.json
put composer.lock

# DO NOT upload:
# - .env (already configured)
# - storage/ (contains user uploads)
# - vendor/ (will be regenerated)
# - node_modules/ (will be regenerated)

# Exit SFTP
bye
```

**Or use FileZilla/WinSCP:**
1. Connect to your server via SFTP
2. Navigate to project root
3. Upload changed files/folders
4. Skip: `.env`, `storage/`, `vendor/`, `node_modules/`

## Step 4: Update via SSH

```bash
# Connect via SSH
ssh your-username@micronesian.school
cd ~/domains/micronesian.school/public_html

# Put in maintenance mode
php artisan down

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Bring back online
php artisan up
```

---

# 🔄 Rollback Procedure

If something goes wrong, here's how to rollback:

## Quick Rollback (Git Method)

```bash
# Connect to server
ssh your-username@micronesian.school
cd ~/domains/micronesian.school/public_html

# Put in maintenance mode
php artisan down

# Find the previous commit
git log --oneline -10

# Rollback to previous commit (replace COMMIT_HASH)
git reset --hard COMMIT_HASH

# Reinstall dependencies
composer install --no-dev --optimize-autoloader

# Rollback migrations if needed
php artisan migrate:rollback --step=1

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Bring back online
php artisan up
```

## Full Restore from Backup

```bash
# Restore database
mysql -u your_db_user -p your_db_name < ~/backups/YYYYMMDD/database_backup.sql

# Restore storage files
rm -rf storage/
tar -xzf ~/backups/YYYYMMDD/storage_backup.tar.gz

# Restore .env if needed
cp ~/backups/YYYYMMDD/.env.backup .env

# Clear caches
php artisan cache:clear
php artisan config:clear

# Bring back online
php artisan up
```

---

# 📊 Common Update Scenarios

## Scenario 1: Only Code Changes (No Database Changes)

**Fastest update - No migrations needed**

```bash
ssh user@server
cd project
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan cache:clear && php artisan config:clear && php artisan view:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

**Time:** ~3-5 minutes

## Scenario 2: Code + Database Changes

**Requires migrations**

```bash
ssh user@server
cd project
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan cache:clear && php artisan config:clear && php artisan view:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

**Time:** ~5-10 minutes

## Scenario 3: Major Update (Code + DB + Assets)

**Full update with frontend rebuild**

```bash
ssh user@server
cd project
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan cache:clear && php artisan config:clear && php artisan view:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan optimize
php artisan up
```

**Time:** ~10-20 minutes

---

# 🐛 Troubleshooting

## Problem: "php artisan" commands not working

```bash
# Check PHP version
php -v

# Try using full path
/usr/bin/php artisan --version

# Check permissions
ls -la artisan
chmod +x artisan
```

## Problem: Composer not found

```bash
# Install composer locally
curl -sS https://getcomposer.org/installer | php

# Use it
php composer.phar install --no-dev --optimize-autoloader
```

## Problem: "Class not found" errors

```bash
# Clear and regenerate autoloader
composer dump-autoload --optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Rebuild caches
php artisan optimize
```

## Problem: Database migration fails

```bash
# Check migration status
php artisan migrate:status

# Check error
tail -100 storage/logs/laravel.log

# Rollback last batch
php artisan migrate:rollback --step=1

# Try again
php artisan migrate --force
```

## Problem: 500 Internal Server Error after update

```bash
# Check Laravel logs
tail -100 storage/logs/laravel.log

# Check permissions
chmod -R 755 storage bootstrap/cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Regenerate autoloader
composer dump-autoload

# Check .env file is correct
cat .env
```

## Problem: Assets not loading (CSS/JS)

```bash
# Rebuild assets
npm run build

# Check symbolic link
ls -la public/storage
php artisan storage:link

# Clear view cache
php artisan view:clear
```

## Problem: Git pull conflicts

```bash
# See conflicted files
git status

# Option 1: Keep remote version
git checkout --theirs filename

# Option 2: Keep local version
git checkout --ours filename

# Option 3: Reset to remote completely
git reset --hard origin/main

# After resolving
git add .
git commit -m "Resolved conflicts"
```

---

# 📝 Update Checklist

Print this and check off as you go:

## Pre-Update
- [ ] Backup database
- [ ] Backup storage files
- [ ] Backup .env file
- [ ] Note current Git commit
- [ ] Check what changed (git log)
- [ ] Plan maintenance window

## During Update
- [ ] SSH into server
- [ ] Navigate to project directory
- [ ] Enable maintenance mode (`php artisan down`)
- [ ] Pull latest code (`git pull origin main`)
- [ ] Update dependencies (`composer install`)
- [ ] Run migrations (`php artisan migrate --force`)
- [ ] Clear caches
- [ ] Rebuild caches
- [ ] Disable maintenance mode (`php artisan up`)

## Post-Update Testing
- [ ] Homepage loads
- [ ] Library search works
- [ ] Book pages display
- [ ] PDFs load/download
- [ ] Login works
- [ ] Admin panel accessible
- [ ] No console errors (F12)
- [ ] Check Laravel logs
- [ ] Test on mobile device

## If Something Goes Wrong
- [ ] Enable maintenance mode
- [ ] Check logs (`storage/logs/laravel.log`)
- [ ] Try clearing caches again
- [ ] Rollback Git if needed
- [ ] Restore database backup if needed
- [ ] Contact developer/support

---

# 🔧 Useful Commands Reference

## Git Commands
```bash
git status                          # Check current status
git log --oneline -10               # Show last 10 commits
git pull origin main                # Pull latest changes
git reset --hard COMMIT_HASH        # Rollback to specific commit
git stash                           # Save local changes temporarily
git stash pop                       # Restore stashed changes
```

## Laravel Artisan Commands
```bash
php artisan down                    # Enable maintenance mode
php artisan up                      # Disable maintenance mode
php artisan migrate --force         # Run migrations (production)
php artisan migrate:status          # Check migration status
php artisan migrate:rollback        # Undo last migration batch
php artisan cache:clear             # Clear application cache
php artisan config:clear            # Clear config cache
php artisan route:clear             # Clear route cache
php artisan view:clear              # Clear compiled views
php artisan optimize                # Optimize application
php artisan storage:link            # Create storage symlink
```

## Composer Commands
```bash
composer install --no-dev           # Install production dependencies
composer update                     # Update dependencies
composer dump-autoload              # Regenerate autoloader
```

## File Permissions
```bash
chmod -R 755 storage                # Set storage permissions
chmod -R 755 bootstrap/cache        # Set cache permissions
chown -R www-data:www-data storage  # Set owner (adjust user)
```

## Backup Commands
```bash
# Database backup
mysqldump -u user -p dbname > backup.sql

# Restore database
mysql -u user -p dbname < backup.sql

# File backup
tar -czf backup.tar.gz folder/

# File restore
tar -xzf backup.tar.gz
```

---

# 📅 Regular Maintenance Schedule

## Weekly
- [ ] Check Laravel logs for errors
- [ ] Monitor disk space usage
- [ ] Test critical functionality

## Monthly
- [ ] Update dependencies (`composer update`)
- [ ] Review and clean old log files
- [ ] Database backup
- [ ] Security updates check

## Before Major Updates
- [ ] Full backup (DB + files)
- [ ] Test update in staging/local
- [ ] Review changelog/commit history
- [ ] Plan rollback strategy

---

# 🆘 Emergency Contacts

## If Update Fails

1. **Enable maintenance mode immediately:**
   ```bash
   php artisan down
   ```

2. **Check logs:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

3. **Rollback if needed:**
   ```bash
   git reset --hard PREVIOUS_COMMIT
   php artisan cache:clear
   php artisan up
   ```

4. **Contact:**
   - **Developer:** Check GitHub repository issues
   - **Hosting Support:** Hostinger 24/7 chat
   - **Database Issues:** Check hPanel → Databases

---

# 📚 Additional Resources

- **GitHub Repository:** https://github.com/ShvedkoDev/book_library
- **Laravel Docs:** https://laravel.com/docs/11.x/deployment
- **FilamentPHP Docs:** https://filamentphp.com/docs/3.x/panels/installation
- **Hostinger Knowledge Base:** https://support.hostinger.com

---

**Last Updated:** 2025-01-10
**Version:** 1.0
**For:** Micronesian Teachers Digital Library Production Updates
