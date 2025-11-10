# Deployment Guide
## Micronesian Teachers Digital Library

---

## 📖 Which Guide Do I Need?

### 🆕 **First Time Deployment** (Setting up from scratch)
👉 Follow this guide (DEPLOYMENT_README.md) and DEPLOYMENT_CHECKLIST.md

### 🔄 **Updating Existing Site** (Pulling latest changes)
👉 See **[UPDATE_GUIDE.md](./UPDATE_GUIDE.md)** for comprehensive update instructions
👉 See **[QUICK_UPDATE.md](./QUICK_UPDATE.md)** for quick reference card

---

## 🚨 IMPORTANT: Hostinger Hosting Limitation

**Hostinger has confirmed:** Docker requires root access, which is **NOT available** on Hostinger's shared Web or Cloud hosting plans. Docker can only run on VPS plans.

**Your current hosting:** Cloud/Shared hosting for domain `micronesian.school`

---

## Choose Your Deployment Method

### Option 1: Manual Deployment (Recommended) ✅

Deploy the Laravel application **without Docker** on your current Hostinger Cloud/Shared hosting.

- **Pros:**
  - Use your existing hosting plan (no additional cost)
  - Can deploy immediately
  - Fully functional application

- **Cons:**
  - Requires manual configuration
  - No containerization benefits

**👉 Follow:** [DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md) (Quick guide)
**📖 Full Guide:** [PRODUCTION_DEPLOYMENT.md](./PRODUCTION_DEPLOYMENT.md) (Part 1)

### Option 2: Upgrade to VPS + Docker

Upgrade your Hostinger plan to VPS to get root access and use Docker.

- **Pros:**
  - Full Docker support
  - Root server access
  - Automated deployment via Docker Compose

- **Cons:**
  - Additional cost (~$5-10/month for VPS)
  - Requires plan upgrade
  - Migration from current hosting

**📖 Full Guide:** [PRODUCTION_DEPLOYMENT.md](./PRODUCTION_DEPLOYMENT.md) (Part 2)

---

## Quick Start: Manual Deployment

### Prerequisites

1. Access to Hostinger hPanel: https://hpanel.hostinger.com
2. SSH access to your hosting
3. Domain `micronesian.school` active

### Deployment Steps Overview

1. **Prepare locally** (5 minutes)
   ```bash
   ./prepare-deployment.sh
   ```
   This creates a deployment-ready archive.

2. **Configure in hPanel** (5 minutes)
   - Enable SSL
   - Create MySQL database
   - Set PHP 8.2/8.3

3. **Upload files** (10 minutes)
   - Via SSH + Git (recommended), or
   - Via SFTP

4. **Install dependencies** (5 minutes)
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

5. **Configure environment** (10 minutes)
   - Create `.env` file
   - Set database credentials
   - Generate app key

6. **Run migrations** (5 minutes)
   ```bash
   php artisan migrate --force
   ```

7. **Set up web server** (5 minutes)
   - Point document root to `public` folder

8. **Create admin user** (2 minutes)
   ```bash
   php artisan make:filament-user
   ```

9. **Test** (2 minutes)
   - Visit https://micronesian.school
   - Login to https://micronesian.school/admin

**Total Time: ~45-60 minutes**

---

## Documentation Files

### Initial Deployment
- **[DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md)** - Quick step-by-step checklist (START HERE)
- **[PRODUCTION_DEPLOYMENT.md](./PRODUCTION_DEPLOYMENT.md)** - Complete detailed guide
  - Part 1: Manual deployment (no Docker)
  - Part 2: Docker deployment (VPS only)
- **[prepare-deployment.sh](./prepare-deployment.sh)** - Script to prepare deployment package

### Updating Existing Deployment

**For Hostinger with app_root + public_html structure (recommended):**
- **[UPDATE_GUIDE_HOSTINGER.md](./UPDATE_GUIDE_HOSTINGER.md)** - ⭐ Hostinger-specific update guide
- **[QUICK_UPDATE_HOSTINGER.md](./QUICK_UPDATE_HOSTINGER.md)** - ⚡ Quick reference for Hostinger setup
- **[update-server.sh](./update-server.sh)** - Server-side update script
- **[deploy-assets.sh](./deploy-assets.sh)** - Local asset deployment script

**For standard Laravel setup (npm on server):**
- **[UPDATE_GUIDE.md](./UPDATE_GUIDE.md)** - Comprehensive update guide with troubleshooting
- **[QUICK_UPDATE.md](./QUICK_UPDATE.md)** - Quick reference card for routine updates

---

## Current Project Status

- **Development:** Complete ✅
- **Local Testing:** Complete ✅
- **Production Deployment:** Ready for manual deployment
- **Domain:** micronesian.school
- **Hosting:** Hostinger Cloud/Shared hosting
- **Deployment Method:** Manual (no Docker support)

---

## Need Help?

### During Deployment
1. Check **DEPLOYMENT_CHECKLIST.md** for quick reference
2. Review **PRODUCTION_DEPLOYMENT.md** for detailed instructions
3. Check Laravel logs: `storage/logs/laravel.log`

### Hostinger Support
- **hPanel Live Chat:** Available 24/7
- **Email:** support@hostinger.com
- **Knowledge Base:** https://support.hostinger.com

### Technical Issues
- Check logs: `tail -f storage/logs/laravel.log`
- Clear caches: `php artisan config:clear && php artisan cache:clear`
- Verify permissions: `ls -la storage/ bootstrap/cache/`

---

## After Successful Deployment

### 1. Test Everything
- [ ] Main site loads: https://micronesian.school
- [ ] Admin panel works: https://micronesian.school/admin
- [ ] Can login with admin credentials
- [ ] Books display correctly (if added)
- [ ] Search functionality works
- [ ] PDF viewing/download works

### 2. Optimize Performance
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Set Up Monitoring
- Check Laravel logs regularly
- Monitor disk space (for PDFs)
- Set up backups via hPanel

### 4. Import Book Data
- Use admin panel to add books
- Upload PDFs to `storage/app/public/books/`
- Import metadata if you have Excel file

---

## Migration to VPS (Optional Future Step)

If you later decide to upgrade to VPS for Docker support:

1. **Purchase Hostinger VPS plan**
2. **Follow Part 2** of PRODUCTION_DEPLOYMENT.md
3. **Migrate data:**
   - Export database from current hosting
   - Download uploaded files
   - Import to VPS
4. **Update DNS** to point to new VPS IP
5. **Deploy with Docker Compose**

---

## Important URLs

- **Production Site:** https://micronesian.school
- **Admin Panel:** https://micronesian.school/admin
- **Hostinger hPanel:** https://hpanel.hostinger.com
- **GitHub Repository:** https://github.com/ShvedkoDev/book_library

---

## Questions?

- **Deployment questions:** See PRODUCTION_DEPLOYMENT.md
- **Technical issues:** Check Laravel logs
- **Hosting questions:** Contact Hostinger support
- **Application features:** See CLAUDE.md in project root

---

**Last Updated:** 2025-01-28
**Version:** 4.0
**Deployment Status:** Ready for manual deployment on Hostinger Cloud/Shared hosting
