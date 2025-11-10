# Simple Update Guide - Git Only
## No scp/rsync Required!

**This workflow:**
- ✅ Commits built assets to GitHub
- ✅ Updates via git pull on server
- ✅ Copies files from app_root to public_html
- ✅ No scp, rsync, or file transfer commands needed

---

## 📁 Your Setup

```
Production Server:
~/app_root/          ← Git repository, code updates here
~/public_html/       ← Web root, files copied here
```

## ⚡ Quick Update (5 commands total)

### Part 1: Build Locally (Your WSL Machine)

```bash
cd /home/gena/book_library
./build-and-commit.sh
```

**Script will:**
1. Pull latest code
2. Install npm dependencies
3. Build production assets
4. Commit built assets to git
5. Push to GitHub

### Part 2: Update Server (Production)

```bash
ssh your-username@micronesian.school
cd ~/app_root
./update-simple.sh
```

**Script will:**
1. Backup database and .env
2. Enable maintenance mode
3. Pull latest code (including built assets)
4. Update Composer dependencies
5. Run migrations
6. Copy assets from app_root/public to ~/public_html
7. Clear and rebuild caches
8. Disable maintenance mode

**Done!** Your site is updated.

---

## 🛠️ First-Time Setup (5 minutes)

### Step 1: Make Scripts Executable Locally

```bash
cd /home/gena/book_library
chmod +x build-and-commit.sh update-simple.sh
```

### Step 2: Upload Server Script

```bash
# Copy script to production (one time only)
scp update-simple.sh your-username@micronesian.school:~/app_root/

# Or manually upload via FileZilla/SFTP to ~/app_root/
```

### Step 3: Make Server Script Executable

```bash
ssh your-username@micronesian.school
chmod +x ~/app_root/update-simple.sh
```

### Step 4: Verify .gitignore Allows Built Assets

Built assets are now tracked in git (already configured).

**Check it works:**
```bash
cd /home/gena/book_library
git status public/build
# Should NOT say "Untracked files" - built assets are tracked
```

---

## 📝 Manual Update (Without Scripts)

If scripts don't work or you prefer manual control:

### On Local Machine:

```bash
cd /home/gena/book_library

# Pull, build, commit
git pull origin main
npm install
npm run build
git add public/build public/library-assets
git commit -m "build: Update production assets"
git push origin main
```

### On Production Server:

```bash
ssh your-username@micronesian.school
cd ~/app_root

# Backup
BACKUP=~/backups/$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP
cp .env $BACKUP/
mysqldump -u dbuser -p dbname > $BACKUP/database.sql

# Update code
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# Copy assets to public_html
cp -r public/build ~/public_html/
cp -r public/library-assets ~/public_html/
cp public/index.php ~/public_html/
cp public/.htaccess ~/public_html/

# Clear caches and bring back online
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

---

## 🔄 Update Workflow Explained

### What Happens:

**Local Machine:**
```
1. npm run build
   → Creates public/build/assets/app-abc123.js
   → Creates public/build/assets/app-abc123.css
   → Creates public/build/manifest.json

2. git add public/build
   → Tracks built files in git

3. git commit && git push
   → Uploads to GitHub
```

**Production Server:**
```
1. cd ~/app_root
   → Navigate to git repository

2. git pull origin main
   → Downloads code + built assets from GitHub
   → Now app_root/public/build has the built files

3. cp -r public/build ~/public_html/
   → Copies built assets to web root
   → Now public_html/build has the built files

4. Browser requests: micronesian.school/build/assets/app-abc123.js
   → Web server serves from ~/public_html/build/assets/app-abc123.js
   → ✅ Works!
```

---

## 📊 Pros and Cons

### ✅ Advantages of This Approach

1. **Simpler Deployment**
   - No scp/rsync commands needed
   - No file transfer configuration
   - Everything via git

2. **Works Anywhere**
   - Any hosting with SSH + git
   - No special server setup
   - No firewall/port issues

3. **Consistent Builds**
   - Built assets version-controlled
   - Easy to rollback (just git reset)
   - Same build everywhere

4. **Beginner Friendly**
   - Fewer tools to learn
   - Fewer things that can go wrong
   - Clear step-by-step process

### ⚠️ Considerations

1. **Git Repository Size**
   - Built assets increase repo size
   - Each build creates new files
   - Solution: Periodic cleanup or git lfs

2. **Merge Conflicts**
   - Built assets can conflict in git
   - Solution: Always build on main branch
   - Or: Pull before building

3. **Build Artifacts in Git**
   - Some developers avoid this
   - But: Common for deployment simplicity
   - Many projects do this (even GitHub!)

---

## 🚨 Troubleshooting

### Problem: "public/build is ignored by git"

```bash
# Check gitignore
cat .gitignore | grep build

# Should see:
# !public/build/        ← This allows it
# # /public/build/      ← This is commented out

# If /public/build/ is not commented, edit .gitignore:
nano .gitignore
# Comment out the line: # /public/build/
```

### Problem: Built files not appearing after git pull

```bash
# On server, check if files were pulled
cd ~/app_root
ls -la public/build/assets/

# If empty, check .gitignore on server
cat .gitignore | grep build

# Force update
git fetch origin
git reset --hard origin/main
```

### Problem: Assets copied but not loading

```bash
# Check files exist in public_html
ls -la ~/public_html/build/assets/

# Check permissions
chmod -R 755 ~/public_html/build

# Clear Laravel cache
cd ~/app_root
php artisan view:clear
php artisan cache:clear
```

### Problem: Build command fails locally

```bash
# Check Node version (need 18+)
node -v

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install

# Try build again
npm run build

# Check for errors
cat storage/logs/laravel.log
```

---

## 💡 Pro Tips

### Tip 1: Check What Changed Before Building

```bash
git diff origin/main --name-only | grep "resources/"

# If no files in resources/, no need to rebuild
# Just commit code changes without rebuilding
```

### Tip 2: Automate Even More

Add to your `~/.bashrc` or `~/.zshrc`:

```bash
alias update-prod='cd /home/gena/book_library && ./build-and-commit.sh'
```

Then just type:
```bash
update-prod
```

### Tip 3: Quick Rollback

```bash
# On server
cd ~/app_root
git log --oneline -5  # Find previous commit
git reset --hard COMMIT_HASH
cp -r public/build ~/public_html/
php artisan migrate:rollback
php artisan cache:clear && php artisan up
```

### Tip 4: Verify Before Pushing

```bash
# Test locally first
npm run build
php artisan serve
# Visit http://localhost:8000
# If it works, commit and push
```

---

## ✅ Update Checklist

Print this and check off each step:

### Local Machine:
- [ ] `cd /home/gena/book_library`
- [ ] `./build-and-commit.sh`
- [ ] Verify git push succeeded
- [ ] Note commit hash: `git log -1 --oneline`

### Production Server:
- [ ] `ssh your-username@micronesian.school`
- [ ] `cd ~/app_root`
- [ ] `./update-simple.sh`
- [ ] Wait for completion (~2-3 minutes)

### Verification:
- [ ] Visit https://micronesian.school
- [ ] Hard refresh: Ctrl+Shift+R
- [ ] Search works
- [ ] Book pages load
- [ ] Images display
- [ ] No console errors (F12)
- [ ] Admin panel accessible

### If Issues:
- [ ] Check logs: `tail -50 ~/app_root/storage/logs/laravel.log`
- [ ] Check assets: `ls -la ~/public_html/build/assets/`
- [ ] Clear cache: `php artisan cache:clear && php artisan view:clear`
- [ ] Rollback if needed

---

## 📂 Files in This Workflow

**Local Scripts:**
- `build-and-commit.sh` - Builds assets and commits to git
- `.gitignore` - Configured to track public/build/

**Server Script:**
- `update-simple.sh` - Updates code and copies assets

**Documentation:**
- `SIMPLE_UPDATE.md` - This file (quick reference)
- `UPDATE_GUIDE_HOSTINGER.md` - Detailed guide (alternative methods)

---

## 🆚 Comparison with Other Methods

| Method | Commands | Requires | Speed |
|--------|----------|----------|-------|
| **Git Only** (This) | 2 | git, ssh | ⚡⚡⚡ Fast |
| rsync Upload | 3-4 | git, ssh, rsync | ⚡⚡ Medium |
| scp Upload | 4-5 | git, ssh, scp | ⚡ Slower |
| SFTP Manual | Many | FTP client | 🐌 Slowest |

---

## 📞 Need Help?

**Script not working?**
```bash
# Check script is executable
ls -la *.sh

# Make executable
chmod +x build-and-commit.sh update-simple.sh

# Run with bash directly
bash build-and-commit.sh
```

**Can't connect to server?**
```bash
# Test SSH
ssh your-username@micronesian.school

# Check credentials in Hostinger hPanel
```

**Assets not loading?**
```bash
# On server, verify files
ls -la ~/public_html/build/assets/

# Check one file
cat ~/public_html/build/manifest.json

# If empty or missing, run update again
cd ~/app_root
git pull origin main
cp -r public/build ~/public_html/
```

---

## 🎯 Summary

**This is the simplest update method for your setup:**

1. **Build locally** → Commit to git → Push to GitHub
2. **Pull on server** → Copy to public_html → Done!

No file transfers, no rsync configuration, no scp commands.
Just git and simple file copying on the server.

Perfect for beginners and shared hosting! 🎉

---

**Version:** 1.0
**Last Updated:** 2025-01-10
**For:** Hostinger (app_root + public_html) with git-based deployment
