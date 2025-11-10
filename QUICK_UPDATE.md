# Quick Update Reference Card
## Micronesian Teachers Digital Library

**📋 Use this for regular, routine updates**

---

## ⚡ Fast Update (5 minutes)

For routine updates with no major changes.

### Step-by-Step Commands

```bash
# 1. Connect to server
ssh your-username@micronesian.school

# 2. Navigate to project
cd ~/domains/micronesian.school/public_html

# 3. Enable maintenance mode
php artisan down --message="Updating library - back in 5 minutes"

# 4. Backup database (quick)
php artisan db:backup || mysqldump -u dbuser -p dbname > ~/backup_$(date +%Y%m%d_%H%M).sql

# 5. Pull latest changes
git pull origin main

# 6. Update dependencies (if composer.json changed)
composer install --no-dev --optimize-autoloader

# 7. Run migrations (if database changed)
php artisan migrate --force

# 8. Clear all caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# 9. Rebuild caches
php artisan config:cache && php artisan route:cache && php artisan view:cache

# 10. Disable maintenance mode
php artisan up

# 11. Check for errors
tail -50 storage/logs/laravel.log
```

---

## 🚨 Emergency Rollback

If something breaks after update:

```bash
# 1. Enable maintenance
php artisan down

# 2. Check what commit you're on
git log -1 --oneline

# 3. Rollback to previous commit (replace HASH)
git reset --hard PREVIOUS_COMMIT_HASH

# 4. Clear caches
php artisan cache:clear && php artisan config:clear && php artisan view:clear

# 5. Bring back online
php artisan up
```

---

## 📊 Check Before Update

```bash
# Current commit
git log -1 --oneline

# What will change
git fetch origin
git log HEAD..origin/main --oneline

# Check disk space
df -h

# Check current status
php artisan --version
composer --version
```

---

## ✅ Test After Update

- [ ] Visit homepage: https://micronesian.school
- [ ] Search books: https://micronesian.school/library
- [ ] Open a book page
- [ ] Test login
- [ ] Check admin: https://micronesian.school/admin
- [ ] Check logs: `tail -50 storage/logs/laravel.log`

---

## 🔧 Common Fixes

### Fix: Class not found
```bash
composer dump-autoload --optimize
php artisan optimize:clear
```

### Fix: Config cached
```bash
php artisan config:clear
php artisan cache:clear
```

### Fix: Routes not working
```bash
php artisan route:clear
php artisan route:cache
```

### Fix: Views not updating
```bash
php artisan view:clear
php artisan view:cache
```

### Fix: Permission denied
```bash
chmod -R 755 storage bootstrap/cache
```

---

## 📞 Need Help?

- **Full Guide:** See UPDATE_GUIDE.md
- **Logs:** `tail -100 storage/logs/laravel.log`
- **Hosting:** https://hpanel.hostinger.com
- **Repository:** https://github.com/ShvedkoDev/book_library

---

**Pro Tip:** Bookmark this file for quick access during updates!
