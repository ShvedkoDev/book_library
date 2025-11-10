# Quick Update - Hostinger Setup
## Two-Part Update Process

**Your Setup:**
- `app_root` - Laravel code (git updates here)
- `public_html` - Web root (assets go here)
- No npm on server - build locally

---

## ⚡ Fast Update (10 minutes)

### Part 1: Server (Run on Production)

```bash
# SSH to server
ssh your-username@micronesian.school

# Navigate to app
cd ~/app_root

# Run update script
./update-server.sh
```

**Script will:**
- ✓ Backup database and .env
- ✓ Enable maintenance mode
- ✓ Pull latest code
- ✓ Update Composer dependencies
- ✓ Run migrations
- ⏸️ Wait for you to upload assets

### Part 2: Local (Run on Your WSL/Dev Machine)

```bash
# In your local project
cd /home/gena/book_library

# First time: Edit deploy-assets.sh with your server details
# nano deploy-assets.sh
# Update SERVER_USER and SERVER_HOST

# Run deployment script
./deploy-assets.sh
```

**Script will:**
- ✓ Pull latest code locally
- ✓ Install npm dependencies
- ✓ Build production assets
- ✓ Upload to public_html
- ✓ Clear server caches

### Part 3: Verify

- Visit: https://micronesian.school
- Hard refresh: Ctrl+Shift+R
- Test search, book pages, login

---

## 📝 Manual Quick Update

### On Server

```bash
ssh user@server
cd ~/app_root
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
# Wait for asset upload...
php artisan cache:clear && php artisan view:clear
php artisan config:cache && php artisan route:cache
php artisan up
```

### On Local Machine

```bash
cd /home/gena/book_library
git pull origin main
npm install
npm run build

# Upload (adjust user@server)
rsync -avz public/build/ user@server:~/public_html/build/
rsync -avz public/library-assets/ user@server:~/public_html/library-assets/
```

---

## 🔧 One-Time Setup

### Configure deploy-assets.sh

```bash
# Edit the script
nano /home/gena/book_library/deploy-assets.sh

# Update these lines:
SERVER_USER="your-actual-username"
SERVER_HOST="micronesian.school"  # or your server IP

# Save and exit (Ctrl+X, Y, Enter)
```

### Create SSH Key (Optional - Passwordless Login)

```bash
# On local machine
ssh-keygen -t rsa -b 4096

# Copy to server
ssh-copy-id your-username@micronesian.school

# Test
ssh your-username@micronesian.school
# Should connect without password
```

### Install rsync (If Not Installed)

```bash
# On local WSL
sudo apt-get update
sudo apt-get install rsync

# Verify
rsync --version
```

---

## 🚨 Emergency Rollback

### On Server

```bash
ssh user@server
cd ~/app_root
php artisan down

# Find previous commit
git log --oneline -5

# Rollback (replace HASH)
git reset --hard COMMIT_HASH

# Rollback migrations
php artisan migrate:rollback

# Clear caches
php artisan cache:clear
php artisan config:clear

php artisan up
```

### Restore Database

```bash
# Find backup
ls -la ~/backups/

# Restore (replace date)
mysql -u dbuser -p dbname < ~/backups/20250110_123456/database.sql
```

---

## ✅ Quick Checklist

**Before Update:**
- [ ] Note current git commit: `git log -1 --oneline`
- [ ] Server has space: `df -h`
- [ ] Local build works: `npm run build`

**Server Update:**
- [ ] SSH to server
- [ ] `cd ~/app_root`
- [ ] `./update-server.sh`
- [ ] Wait at prompt for assets

**Local Deploy:**
- [ ] New terminal
- [ ] `cd /home/gena/book_library`
- [ ] `./deploy-assets.sh`

**After Update:**
- [ ] Homepage loads
- [ ] Search works
- [ ] Book pages display
- [ ] CSS/JS loading (F12 console)
- [ ] Admin panel accessible

---

## 🔍 Common Issues

### "Vite manifest not found"
```bash
# On local: rebuild and upload
npm run build
./deploy-assets.sh
```

### Assets not updating
```bash
# On server
cd ~/app_root
php artisan view:clear
php artisan cache:clear
```

### CSS changes not showing
```bash
# Hard refresh browser: Ctrl+Shift+R
# Check uploaded: ssh server "ls -la ~/public_html/build/"
```

### Upload fails
```bash
# Check SSH works
ssh your-username@micronesian.school

# Manual upload
cd /home/gena/book_library/public
scp -r build/* user@server:~/public_html/build/
```

---

## 💡 Pro Tips

**Speed up uploads:**
```bash
# rsync only uploads changed files
rsync -avz --delete public/build/ user@server:~/public_html/build/
```

**Skip unchanged assets:**
```bash
# Check what changed
git diff --name-only origin/main | grep "resources/"
# If no frontend changes, skip asset upload
```

**Monitor deployment:**
```bash
# On server
tail -f ~/app_root/storage/logs/laravel.log
```

**Test locally first:**
```bash
cd /home/gena/book_library
npm run build
php artisan serve
# Visit http://localhost:8000
```

---

## 📞 Help

**Scripts not working?**
- Check script is executable: `ls -la *.sh`
- Make executable: `chmod +x update-server.sh deploy-assets.sh`

**Can't connect to server?**
- Test SSH: `ssh your-username@micronesian.school`
- Check credentials: See hPanel for SSH details

**Build fails locally?**
- Check Node version: `node -v` (need 18+)
- Install dependencies: `npm install`
- Clear cache: `rm -rf node_modules package-lock.json && npm install`

---

## 📂 Your Directory Structure

```
Server (Production):
~/
├── app_root/                    ← Laravel app (git updates)
│   ├── app/
│   ├── config/
│   ├── public/                  ← Source public files
│   │   ├── build/              (not used)
│   │   └── library-assets/     (not used)
│   ├── resources/
│   ├── .env
│   └── artisan
│
└── public_html/                 ← Web-accessible
    ├── index.php
    ├── .htaccess
    ├── build/                   ← Upload here from local
    │   ├── assets/
    │   └── manifest.json
    └── library-assets/          ← Upload here from local
        ├── css/
        ├── js/
        └── images/

Local (Development):
/home/gena/book_library/
├── public/
│   ├── build/                   ← Built by npm
│   └── library-assets/          ← Source assets
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
└── [deploy-assets.sh]           ← Uploads to production
```

---

**Quick Reference Version:** 1.0
**For:** Hostinger shared hosting (app_root + public_html)
