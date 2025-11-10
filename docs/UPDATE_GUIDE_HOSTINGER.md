# Production Update Guide - Hostinger Specific
## Micronesian Teachers Digital Library

**For your specific setup:**
- `app_root` - Laravel application (where you run git pull)
- `public_html` - Web-accessible directory (Laravel's public folder)
- No npm/node on server - assets built locally

---

## 📋 Your Production Structure

```
~/
├── app_root/              ← Laravel application (git repository)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── public/           ← Source public folder
│   ├── .env
│   └── artisan
│
└── public_html/          ← Web root (exposed to internet)
    ├── index.php         ← Symlink or copy from app_root/public/
    ├── css/
    ├── js/
    └── library-assets/
```

---

## 🚀 Quick Update Process (10 minutes)

### Step 1: Build Assets Locally

```bash
# On your LOCAL machine (WSL/development environment)
cd /home/gena/book_library

# Pull latest changes
git pull origin main

# Install/update npm dependencies
npm install

# Build production assets
npm run build

# Verify build completed
ls -la public/build/
```

This creates optimized assets in `public/build/` and `public/library-assets/`

### Step 2: Connect to Production Server

```bash
# SSH to your Hostinger server
ssh your-username@micronesian.school
# or
ssh your-username@your-server-ip
```

### Step 3: Update Application Code

```bash
# Navigate to Laravel app root
cd ~/app_root

# Enable maintenance mode
php artisan down --message="Updating library - back in 10 minutes!"

# Backup database first
mkdir -p ~/backups/$(date +%Y%m%d_%H%M%S)
php artisan db:backup || mysqldump -u dbuser -p dbname > ~/backups/$(date +%Y%m%d_%H%M%S)/database.sql

# Backup .env
cp .env ~/backups/$(date +%Y%m%d_%H%M%S)/.env.backup

# Check current version
git log -1 --oneline

# Pull latest code
git pull origin main

# Update Composer dependencies
composer install --no-dev --optimize-autoloader

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 4: Upload Built Assets from Local

**Option A: Using SCP from Local Machine**

```bash
# From your LOCAL machine (new terminal, don't close SSH)
cd /home/gena/book_library

# Upload built assets to public_html
scp -r public/build/* your-username@micronesian.school:~/public_html/build/

# Upload library assets
scp -r public/library-assets/* your-username@micronesian.school:~/public_html/library-assets/

# If you have other changed public files
scp public/index.php your-username@micronesian.school:~/public_html/
scp public/.htaccess your-username@micronesian.school:~/public_html/
```

**Option B: Using SFTP Client (FileZilla/WinSCP)**

1. Connect via SFTP to your server
2. Navigate to `public_html/`
3. Upload from local `public/build/` → server `public_html/build/`
4. Upload from local `public/library-assets/` → server `public_html/library-assets/`

**Option C: Using rsync (Recommended - faster, syncs only changes)**

```bash
# From your LOCAL machine
cd /home/gena/book_library/public

# Sync build folder
rsync -avz --progress build/ your-username@micronesian.school:~/public_html/build/

# Sync library assets
rsync -avz --progress library-assets/ your-username@micronesian.school:~/public_html/library-assets/

# Sync other public files if changed
rsync -avz --progress index.php .htaccess your-username@micronesian.school:~/public_html/
```

### Step 5: Finalize Update on Server

```bash
# Back in SSH session on server
cd ~/app_root

# Rebuild optimized caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize application
php artisan optimize

# Set permissions
chmod -R 755 storage bootstrap/cache

# Disable maintenance mode
php artisan up

# Check for errors
tail -50 storage/logs/laravel.log
```

### Step 6: Verify Everything Works

- [ ] Visit: https://micronesian.school
- [ ] Test search functionality
- [ ] Open a book page
- [ ] Check login works
- [ ] Admin panel: https://micronesian.school/admin
- [ ] Check browser console (F12) for any errors

---

## 🤖 Automated Update Script for Your Setup

Save this as `~/app_root/update-hostinger.sh`:

```bash
#!/bin/bash
################################################################################
# Hostinger-Specific Production Update Script
# Run this ON THE SERVER in ~/app_root directory
################################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  Micronesian Teachers Digital Library - Update${NC}"
echo -e "${BLUE}════════════════════════════════════════════════════════${NC}"
echo ""

# Confirm
read -p "This will update the production site. Continue? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Update cancelled.${NC}"
    exit 0
fi

# Check we're in app_root
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan not found. Run this from ~/app_root${NC}"
    exit 1
fi

echo -e "${BLUE}[1/10]${NC} Creating backup..."
BACKUP_DIR=~/backups/$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"
cp .env "$BACKUP_DIR/.env.backup"
echo -e "${GREEN}✓ Backup created: $BACKUP_DIR${NC}"

echo -e "${BLUE}[2/10]${NC} Backing up database..."
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
mysqldump -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_DIR/database.sql" 2>/dev/null || echo -e "${YELLOW}⚠ DB backup requires password (enter manually if needed)${NC}"

echo -e "${BLUE}[3/10]${NC} Enabling maintenance mode..."
php artisan down --message="Updating library with new features!" --refresh=15

echo -e "${BLUE}[4/10]${NC} Current version:"
git log -1 --oneline

echo -e "${BLUE}[5/10]${NC} Pulling latest code..."
git pull origin main

echo -e "${BLUE}[6/10]${NC} Updating Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${BLUE}[7/10]${NC} Running database migrations..."
php artisan migrate --force

echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  ATTENTION: UPLOAD BUILT ASSETS NOW${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "From your ${GREEN}LOCAL machine${NC}, run these commands:"
echo ""
echo -e "${GREEN}cd /home/gena/book_library${NC}"
echo -e "${GREEN}npm install && npm run build${NC}"
echo -e "${GREEN}rsync -avz public/build/ $(whoami)@$(hostname):~/public_html/build/${NC}"
echo -e "${GREEN}rsync -avz public/library-assets/ $(whoami)@$(hostname):~/public_html/library-assets/${NC}"
echo ""
read -p "Press ENTER after you've uploaded the assets..."

echo -e "${BLUE}[8/10]${NC} Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo -e "${BLUE}[9/10]${NC} Rebuilding optimized caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo -e "${BLUE}[10/10]${NC} Disabling maintenance mode..."
php artisan up

echo ""
echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Update Completed Successfully!${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "New version: $(git log -1 --oneline)"
echo -e "Backup location: $BACKUP_DIR"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "  1. Visit: https://micronesian.school"
echo "  2. Test search and book pages"
echo "  3. Check admin: https://micronesian.school/admin"
echo "  4. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
```

Make it executable:
```bash
chmod +x ~/app_root/update-hostinger.sh
```

---

## 📦 Complete Update Workflow

### On Your Local Machine (WSL)

```bash
# 1. Build assets
cd /home/gena/book_library
git pull origin main
npm install
npm run build

# 2. Sync to production (run this AFTER server-side git pull)
rsync -avz --progress public/build/ your-username@micronesian.school:~/public_html/build/
rsync -avz --progress public/library-assets/ your-username@micronesian.school:~/public_html/library-assets/

# Or create an alias for easier updates
alias deploy-assets='cd /home/gena/book_library && npm run build && rsync -avz public/build/ user@server:~/public_html/build/ && rsync -avz public/library-assets/ user@server:~/public_html/library-assets/'
```

### On Production Server

```bash
# 1. Update code and backend
ssh your-username@micronesian.school
cd ~/app_root
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# 2. Wait for local build and upload (from local machine)

# 3. Finalize
php artisan cache:clear && php artisan config:clear && php artisan view:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

---

## 🔄 Alternative: One-Time Setup with Symlinks

If your hosting allows symlinks, you can link `public_html` to `app_root/public`:

```bash
# Backup current public_html
cd ~
mv public_html public_html_backup

# Create symlink
ln -s ~/app_root/public ~/public_html

# Test
ls -la public_html
```

**Benefits:**
- No need to upload assets separately
- `git pull` updates everything automatically

**Check if it works:**
```bash
ls -la ~/public_html
# Should show: public_html -> app_root/public
```

⚠️ **Note:** Some shared hosting providers restrict symlinks. If this doesn't work, stick with the upload method.

---

## 🔧 Helper Script for Local Asset Upload

Create `~/book_library/deploy-assets.sh` on your local machine:

```bash
#!/bin/bash
################################################################################
# Deploy Built Assets to Hostinger Production
# Run this from your LOCAL machine after building
################################################################################

SERVER_USER="your-username"
SERVER_HOST="micronesian.school"
PROJECT_PATH="/home/gena/book_library"

echo "Building production assets..."
cd "$PROJECT_PATH"
npm install
npm run build

echo "Uploading to production..."

# Upload build folder
rsync -avz --progress \
    --exclude='*.map' \
    public/build/ \
    "${SERVER_USER}@${SERVER_HOST}:~/public_html/build/"

# Upload library assets
rsync -avz --progress \
    public/library-assets/ \
    "${SERVER_USER}@${SERVER_HOST}:~/public_html/library-assets/"

# Upload index.php and .htaccess if changed
rsync -avz --progress \
    public/index.php \
    public/.htaccess \
    "${SERVER_USER}@${SERVER_HOST}:~/public_html/"

echo "✓ Assets deployed successfully!"
echo ""
echo "Now run on server:"
echo "  cd ~/app_root"
echo "  php artisan cache:clear"
echo "  php artisan view:clear"
echo "  php artisan up"
```

Make it executable:
```bash
chmod +x deploy-assets.sh
```

Usage:
```bash
./deploy-assets.sh
```

---

## 🐛 Troubleshooting

### Problem: Assets not loading after update

**Check on server:**
```bash
# Verify files exist
ls -la ~/public_html/build/
ls -la ~/public_html/library-assets/

# Check permissions
chmod -R 755 ~/public_html/build/
chmod -R 755 ~/public_html/library-assets/
```

**From local:**
```bash
# Re-upload assets
cd /home/gena/book_library
npm run build
rsync -avz public/build/ user@server:~/public_html/build/
rsync -avz public/library-assets/ user@server:~/public_html/library-assets/
```

### Problem: "Mix manifest not found"

```bash
# On server
cd ~/app_root
php artisan view:clear
php artisan cache:clear

# Check if manifest exists
ls -la ~/public_html/build/manifest.json

# If not, rebuild locally and upload again
```

### Problem: CSS/JS changes not appearing

```bash
# On server - clear browser cache headers
cd ~/app_root
php artisan cache:clear
php artisan view:clear

# Check .htaccess in public_html has proper cache headers
cat ~/public_html/.htaccess
```

**Force browser cache clear:**
- Press Ctrl+Shift+R (hard refresh)
- Or add version query: `?v=$(date +%s)` to asset URLs temporarily

### Problem: rsync not working

**Install rsync locally (WSL):**
```bash
sudo apt-get install rsync
```

**Alternative - use scp:**
```bash
cd /home/gena/book_library/public
scp -r build/* user@server:~/public_html/build/
scp -r library-assets/* user@server:~/public_html/library-assets/
```

---

## ✅ Quick Update Checklist (Your Setup)

### Local Machine:
- [ ] `git pull origin main`
- [ ] `npm install`
- [ ] `npm run build`
- [ ] Verify `public/build/` has new files

### Production Server:
- [ ] `cd ~/app_root`
- [ ] `php artisan down`
- [ ] `git pull origin main`
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`

### Back to Local:
- [ ] Upload assets: `rsync -avz public/build/ user@server:~/public_html/build/`
- [ ] Upload library assets: `rsync -avz public/library-assets/ user@server:~/public_html/library-assets/`

### Finalize on Server:
- [ ] `php artisan cache:clear && php artisan view:clear`
- [ ] `php artisan config:cache && php artisan route:cache`
- [ ] `php artisan up`
- [ ] Test site works

---

## 📊 Comparison: With vs Without Node

| Task | With Node on Server | Without Node (Your Setup) |
|------|-------------------|---------------------------|
| Code Update | `git pull` | `git pull` ✓ Same |
| Dependencies | `composer install` | `composer install` ✓ Same |
| Frontend Build | `npm run build` on server | `npm run build` locally → upload |
| Time Added | 0 minutes | ~2-3 minutes (upload) |
| Complexity | Lower | Slightly higher |

**Your advantage:** You always test builds locally before deploying!

---

## 📝 Summary for Your Setup

**Key Differences:**
1. ✓ Git pull happens in `app_root`
2. ✓ Assets built locally on your WSL machine
3. ✓ Built assets uploaded to `public_html` separately
4. ✓ No npm/node needed on server

**Update Time:** ~10-15 minutes (including upload)

**Typical Workflow:**
```
Local: git pull → npm run build → upload assets
Server: git pull → composer install → migrate → cache clear → up
```

---

**Pro Tip:** Add your server details to `~/.ssh/config` for easier access:

```bash
# In ~/.ssh/config on your local machine
Host mtdl-prod
    HostName micronesian.school
    User your-username
    IdentityFile ~/.ssh/id_rsa
```

Then you can use:
```bash
ssh mtdl-prod
rsync -avz public/build/ mtdl-prod:~/public_html/build/
```

---

**Last Updated:** 2025-01-10
**Version:** 1.0 - Hostinger Specific
