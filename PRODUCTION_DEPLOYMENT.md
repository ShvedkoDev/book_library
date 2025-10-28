# Production Deployment Guide for Hostinger
## Micronesian Teachers Digital Library

This guide provides comprehensive deployment instructions for the Micronesian Teachers Digital Library application on **Hostinger hosting**.

**Current Hosting Details:**
- **Hosting Provider:** Hostinger
- **Domain:** micronesian.school
- **Plan Type:** Cloud/Shared Hosting (NOT VPS)
- **Access:** SSH access available
- **Docker Support:** ❌ NOT AVAILABLE (requires VPS with root access)

---

## 🚨 CRITICAL: Choose Your Deployment Method

Hostinger has confirmed that **Docker requires root access**, which is NOT available on shared Web or Cloud hosting plans. Docker can only run on VPS plans.

### **You Have Two Options:**

#### **Option A: Manual Deployment on Current Hosting (Recommended for Now)** ✅
- Deploy Laravel application without Docker
- Use Hostinger's built-in PHP/MySQL stack
- Configure via hPanel (Hostinger control panel)
- **Cost:** Use your existing Cloud/Shared hosting plan
- **Complexity:** Moderate - requires manual configuration
- **See:** [Manual Deployment Guide](#manual-deployment-without-docker) below

#### **Option B: Upgrade to VPS and Use Docker**
- Upgrade to Hostinger VPS plan (additional cost)
- Get full root access
- Use Docker containers as originally planned
- **Cost:** VPS plans start at ~$5-10/month
- **Complexity:** Lower - automated with Docker
- **See:** [Docker Deployment Guide](#docker-deployment-on-vps) (starts at line 300)

---

> **RECOMMENDATION:** Start with **Option A (Manual Deployment)** on your current hosting to get the application running quickly. You can always migrate to VPS with Docker later if needed.

---

## Table of Contents

### Manual Deployment (Shared/Cloud Hosting - No Docker)
1. [Manual Deployment Without Docker](#manual-deployment-without-docker) ⭐ **START HERE**
2. [Requirements for Shared Hosting](#requirements-for-shared-hosting)
3. [Step 1: Access Hostinger hPanel](#step-1-access-hostinger-hpanel)
4. [Step 2: Configure Domain & SSL](#step-2-configure-domain--ssl)
5. [Step 3: Create MySQL Database](#step-3-create-mysql-database)
6. [Step 4: Upload Application Files](#step-4-upload-application-files)
7. [Step 5: Install Composer Dependencies](#step-5-install-composer-dependencies)
8. [Step 6: Configure Environment](#step-6-configure-environment)
9. [Step 7: Run Migrations & Setup](#step-7-run-migrations--setup)
10. [Step 8: Configure Web Server](#step-8-configure-web-server)
11. [Step 9: Set Permissions](#step-9-set-permissions)
12. [Step 10: Create Admin User](#step-10-create-admin-user)
13. [Manual Deployment Troubleshooting](#manual-deployment-troubleshooting)

### Docker Deployment (VPS Only - Requires Upgrade)
14. [Docker Deployment on VPS](#docker-deployment-on-vps)
15. [VPS Requirements](#vps-requirements)
16. [Docker Installation](#docker-installation)
17. [Docker Application Deployment](#docker-application-deployment)
18. [Docker Environment Configuration](#docker-environment-configuration)
19. [Docker Troubleshooting](#docker-troubleshooting)

### Common Sections
20. [Maintenance & Updates](#maintenance--updates)
21. [Backup Strategy](#backup-strategy)
22. [Performance Optimization](#performance-optimization)

---

# PART 1: MANUAL DEPLOYMENT (SHARED/CLOUD HOSTING)

## Manual Deployment Without Docker

This section provides complete instructions for deploying your Laravel application on Hostinger's Cloud or Shared hosting **without Docker**.

### Overview

Since Docker requires root access (only available on VPS), we'll deploy the application using Hostinger's built-in LAMP/LEMP stack:

- **Web Server:** Apache or Nginx (managed by Hostinger)
- **PHP:** 8.2 or 8.3 (selectable in hPanel)
- **Database:** MySQL 8.0 (via hPanel)
- **SSL:** Free Let's Encrypt SSL (via hPanel)
- **File Management:** SFTP/SSH + hPanel File Manager

---

## Requirements for Shared Hosting

### What You Need

1. **Hostinger Account Access**
   - Login credentials for https://hpanel.hostinger.com
   - Domain: micronesian.school (already confirmed)

2. **Minimum Hosting Plan Requirements**
   - **Plan:** Cloud Startup or higher (Business/Premium shared hosting also works)
   - **PHP Version:** 8.2 or 8.3
   - **MySQL:** 5.7+ (8.0 recommended)
   - **Disk Space:** 10+ GB (for ~2,000 PDFs)
   - **SSH Access:** Enabled (already confirmed)

3. **Required PHP Extensions**
   - OpenSSL
   - PDO
   - Mbstring
   - Tokenizer
   - XML
   - Ctype
   - JSON
   - BCMath
   - Fileinfo
   - GD (for image processing)
   - Zip

   *(These are usually enabled by default on Hostinger)*

4. **Local Tools**
   - SFTP client (FileZilla, Cyberduck, or use SSH)
   - SSH client (Terminal on Mac/Linux, PuTTY on Windows)
   - Code editor (optional, for configuration)

---

## Step 1: Access Hostinger hPanel

### 1.1 Login to Hostinger Control Panel

```
1. Go to: https://hpanel.hostinger.com
2. Enter your Hostinger credentials
3. You should see your domain: micronesian.school
```

### 1.2 Verify SSH Access

```bash
# From your local terminal
ssh your-username@micronesian.school
# Or use the SSH details from hPanel → Advanced → SSH Access
```

**Find SSH credentials in hPanel:**
- Go to **Advanced** → **SSH Access**
- Note: **Hostname**, **Port**, **Username**, **Password**

---

## Step 2: Configure Domain & SSL

### 2.1 Verify Domain is Active

1. In hPanel, go to **Websites**
2. Find **micronesian.school**
3. Verify it's pointing to your hosting account

### 2.2 Enable SSL Certificate

1. In hPanel, go to your website → **SSL**
2. Click **Install SSL**
3. Select **Free Let's Encrypt SSL**
4. Wait 5-10 minutes for activation

**Verify SSL:**
```bash
# Should redirect to HTTPS and show valid certificate
curl -I https://micronesian.school
```

---

## Step 3: Create MySQL Database

### 3.1 Create Database via hPanel

1. In hPanel, go to **Websites** → **micronesian.school**
2. Click **Databases** → **MySQL Databases**
3. Click **+ New Database**

**Database Details:**
```
Database Name: micronesian_library
Username: micronesian_user
Password: [Generate a strong password - save it!]
```

**⚠️ IMPORTANT:** Save these credentials - you'll need them in Step 6.

### 3.2 Note Database Connection Details

After creating the database, note:
```
DB_HOST: localhost (or the hostname shown in hPanel)
DB_PORT: 3306 (default)
DB_DATABASE: [your_database_name]
DB_USERNAME: [your_database_user]
DB_PASSWORD: [your_database_password]
```

**Optional:** Access database via phpMyAdmin (available in hPanel → Databases → phpMyAdmin)

---

## Step 4: Upload Application Files

You have two options: **SSH + Git (Recommended)** or **SFTP Upload**

### Option A: Upload via SSH + Git (Recommended)

```bash
# 1. SSH into your hosting
ssh your-username@micronesian.school

# 2. Navigate to your public_html directory
cd domains/micronesian.school/public_html

# Or it might be:
# cd public_html
# cd www
# Check with: pwd

# 3. Clear out default files (if any)
rm -rf * .htaccess

# 4. Clone your repository
git clone https://github.com/ShvedkoDev/book_library.git .

# Note the dot (.) at the end - it clones into current directory

# 5. Verify files
ls -la
# You should see: app/, public/, resources/, artisan, composer.json, etc.
```

**If repository is private:**
```bash
# Option 1: Use HTTPS with Personal Access Token
git clone https://YOUR_GITHUB_TOKEN@github.com/ShvedkoDev/book_library.git .

# Option 2: Set up SSH key (recommended for future)
ssh-keygen -t ed25519 -C "your_email@example.com"
cat ~/.ssh/id_ed25519.pub
# Add this key to GitHub → Settings → SSH Keys
```

### Option B: Upload via SFTP

1. **Connect with SFTP Client (FileZilla):**
   - Host: `sftp://micronesian.school` (or IP from hPanel)
   - Username: [from hPanel SSH Access]
   - Password: [from hPanel SSH Access]
   - Port: 22

2. **Prepare files locally:**
   ```bash
   # On your local machine
   cd /home/gena/book_library

   # Create a clean archive (exclude development files)
   tar --exclude='node_modules' \
       --exclude='vendor' \
       --exclude='.git' \
       --exclude='storage/logs/*' \
       --exclude='storage/framework/cache/*' \
       --exclude='storage/framework/sessions/*' \
       --exclude='storage/framework/views/*' \
       -czf library-deploy.tar.gz .
   ```

3. **Upload to server:**
   - Upload `library-deploy.tar.gz` to your public_html directory

4. **Extract via SSH:**
   ```bash
   ssh your-username@micronesian.school
   cd domains/micronesian.school/public_html
   tar -xzf library-deploy.tar.gz
   rm library-deploy.tar.gz
   ```

---

## Step 5: Install Composer Dependencies

### 5.1 Check if Composer is Available

```bash
# SSH into your hosting
ssh your-username@micronesian.school
cd domains/micronesian.school/public_html

# Check Composer
composer --version
```

**If Composer is available:** Great! Continue to 5.2

**If Composer is NOT available:**

```bash
# Install Composer in your home directory
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar composer
chmod +x composer

# Add to PATH
echo 'export PATH="$HOME:$PATH"' >> ~/.bashrc
source ~/.bashrc

# Verify
composer --version
```

### 5.2 Install PHP Dependencies

```bash
# Navigate to your application
cd domains/micronesian.school/public_html

# Install dependencies (production mode)
composer install --no-dev --optimize-autoloader

# This may take 2-5 minutes
```

**If you encounter memory issues:**
```bash
php -d memory_limit=512M /home/your-username/composer install --no-dev --optimize-autoloader
```

---

## Step 6: Configure Environment

### 6.1 Create .env File

```bash
# Navigate to application root
cd domains/micronesian.school/public_html

# Copy the production template
cp .env.production .env

# Or if .env.production doesn't exist, copy from example
cp .env.example .env
```

### 6.2 Edit .env File

```bash
# Edit with nano or vim
nano .env
```

**Update these critical values:**

```env
# Application
APP_NAME="Micronesian Teachers Digital Library"
APP_ENV=production
APP_KEY=          # Will generate in next step
APP_DEBUG=false
APP_URL=https://micronesian.school

# Database (use values from Step 3)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=micronesian_library
DB_USERNAME=micronesian_user
DB_PASSWORD=your_database_password_here

# Session & Cache
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=database

# Mail (optional - configure later if needed)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@micronesian.school
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@micronesian.school"
MAIL_FROM_NAME="${APP_NAME}"

# File Upload
FILESYSTEM_DISK=local

# Broadcasting & Queue
BROADCAST_DRIVER=log
QUEUE_CONNECTION=database
```

**Save the file:** Ctrl+O, Enter, Ctrl+X (in nano)

### 6.3 Set File Permissions

```bash
# Make .env secure (readable only by you)
chmod 600 .env

# Verify
ls -la .env
```

---

## Step 7: Run Migrations & Setup

### 7.1 Generate Application Key

```bash
cd domains/micronesian.school/public_html

# Generate encryption key
php artisan key:generate

# Verify it was added to .env
grep APP_KEY .env
# Should show: APP_KEY=base64:...
```

### 7.2 Run Database Migrations

```bash
# Run migrations
php artisan migrate --force

# The --force flag is required in production
```

**Expected output:**
```
Migration table created successfully.
Migrating: 2019_12_14_000001_create_personal_access_tokens_table
Migrated:  2019_12_14_000001_create_personal_access_tokens_table
...
```

### 7.3 Create Storage Link

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`

### 7.4 Clear and Cache Configuration

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 8: Configure Web Server

### 8.1 Check Current Document Root

In Hostinger, the web server needs to point to the `public` folder, not the application root.

**Current structure:**
```
domains/micronesian.school/public_html/
├── app/
├── public/          ← Web server should point HERE
├── resources/
├── routes/
├── artisan
└── ...
```

### Option A: Update Document Root via hPanel (Recommended)

1. **In hPanel:**
   - Go to **Websites** → **micronesian.school**
   - Click **Advanced** → **Change Website Root**
   - Set to: `public_html/public`
   - Save changes

2. **Wait 5-10 minutes** for changes to propagate

### Option B: Move Files to Match Current Document Root

If you can't change document root, restructure:

```bash
# Current location
cd domains/micronesian.school/public_html

# Create temporary directory
mkdir -p ../app_root
mv * ../app_root/
mv .* ../app_root/ 2>/dev/null || true

# Move public folder contents to public_html
mv ../app_root/public/* .
mv ../app_root/public/.htaccess . 2>/dev/null || true

# Create symbolic links for assets
ln -s ../app_root/storage/app/public storage

# Update index.php to point to correct paths
```

**Edit `index.php`** (in public_html):
```php
// Change these lines:
require __DIR__.'/../app_root/vendor/autoload.php';
$app = require_once __DIR__.'/../app_root/bootstrap/app.php';
```

### 8.2 Configure .htaccess

Ensure `.htaccess` exists in your public directory:

```bash
# If in public_html/public/
cd domains/micronesian.school/public_html/public

# Or if you used Option B:
cd domains/micronesian.school/public_html

# Check for .htaccess
ls -la .htaccess
```

**If `.htaccess` is missing, create it:**

```bash
nano .htaccess
```

Add:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# PHP Settings
php_value upload_max_filesize 100M
php_value post_max_size 100M
php_value max_execution_time 300
php_value max_input_time 300
```

### 8.3 Select PHP Version via hPanel

1. In hPanel, go to **Advanced** → **PHP Configuration**
2. Select **PHP 8.2** or **PHP 8.3**
3. Verify required extensions are enabled (they usually are by default)

---

## Step 9: Set Permissions

### 9.1 Set Correct File Permissions

```bash
# Navigate to application root
cd domains/micronesian.school/public_html

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make artisan executable
chmod +x artisan

# Storage and cache directories need write permissions
chmod -R 775 storage bootstrap/cache

# If using Option A (public subdirectory):
chmod 755 public
```

### 9.2 Verify Permissions

```bash
# Check critical directories
ls -la storage/
ls -la bootstrap/cache/
ls -la .env

# .env should be: -rw------- (600)
# storage/ should be: drwxrwxr-x (775)
# bootstrap/cache/ should be: drwxrwxr-x (775)
```

---

## Step 10: Create Admin User

### 10.1 Create FilamentPHP Admin User

```bash
cd domains/micronesian.school/public_html

# Create admin user
php artisan make:filament-user
```

**Follow the prompts:**
```
Name: [Your Name]
Email: admin@micronesian.school
Password: [Strong Password - save it!]
```

---

## Step 11: Test Your Application

### 11.1 Test Main Site

```bash
# From SSH
curl -I https://micronesian.school
```

**Expected:** HTTP/2 200 OK

**Or visit in browser:**
- https://micronesian.school

### 11.2 Test Admin Panel

Visit: https://micronesian.school/admin

- Login with credentials from Step 10
- You should see the FilamentPHP dashboard

### 11.3 Check for Errors

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# If you see errors, they'll appear here
```

---

## Step 12: Upload Book PDFs (Optional)

### 12.1 Create Books Directory

```bash
mkdir -p storage/app/public/books
chmod 775 storage/app/public/books
```

### 12.2 Upload PDFs via SFTP

1. Connect via SFTP (FileZilla, etc.)
2. Navigate to: `domains/micronesian.school/public_html/storage/app/public/books/`
3. Upload your PDF files

### 12.3 Import Book Metadata

If you have an Excel file with book metadata, you can import via:
- Admin Panel → Books → Import
- Or use `php artisan` commands (if you have import commands set up)

---

## Manual Deployment Troubleshooting

### Issue: "500 Internal Server Error"

**Causes & Solutions:**

1. **Check Laravel logs:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

2. **Verify .env configuration:**
   ```bash
   cat .env | grep -E "APP_KEY|DB_"
   ```

3. **Check permissions:**
   ```bash
   ls -la storage/
   ls -la bootstrap/cache/
   ```

4. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Issue: "Database connection error"

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# If error, verify:
# 1. Database credentials in .env
# 2. Database exists (check phpMyAdmin in hPanel)
# 3. Database user has permissions
```

### Issue: "Page not found (404)"

**Check document root:**
- Verify web server is pointing to `public` folder
- Check `.htaccess` exists and has correct rewrite rules

### Issue: "Permission denied" errors

```bash
# Reset permissions
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;
```

### Issue: Composer dependencies not installing

```bash
# Install with more memory
php -d memory_limit=512M $(which composer) install --no-dev --optimize-autoloader

# Or install dependencies locally and upload
# On local machine:
composer install --no-dev --optimize-autoloader
# Then upload entire vendor folder via SFTP
```

### Issue: Assets not loading (CSS/JS)

```bash
# Rebuild assets (if Node.js is available)
npm install
npm run build

# Or build locally and upload
# On local machine:
npm install
npm run build
# Then upload public/build folder via SFTP
```

### Check PHP Version and Extensions

```bash
# Check PHP version
php -v

# Check installed extensions
php -m

# Required extensions:
# - OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo, GD
```

---

## Manual Deployment Summary

**✅ Deployment Checklist:**

- [ ] SSL enabled for micronesian.school
- [ ] MySQL database created
- [ ] Application files uploaded
- [ ] Composer dependencies installed
- [ ] .env file configured
- [ ] APP_KEY generated
- [ ] Database migrations run
- [ ] Storage link created
- [ ] Cache cleared and regenerated
- [ ] Document root points to `public` folder
- [ ] .htaccess configured
- [ ] File permissions set correctly
- [ ] Admin user created
- [ ] Site accessible at https://micronesian.school
- [ ] Admin panel accessible at https://micronesian.school/admin

---

# PART 2: DOCKER DEPLOYMENT (VPS ONLY)

> **Note:** The following sections apply ONLY if you upgrade to a Hostinger VPS plan with root access.

---

## Docker Deployment on VPS

### Recommended Hostinger Plan
- **VPS Plan**: VPS 2 or higher (VPS 4 recommended for production)
- **Cloud Hosting**: Startup or higher plan
- **Alternative**: Business or Premium shared hosting (with SSH access)

### Minimum VPS Specifications
- **CPU**: 2 cores minimum (4 cores recommended)
- **RAM**: 4 GB minimum (8 GB recommended for optimal performance)
- **Storage**: 50 GB SSD minimum (100 GB+ recommended for ~2,000 book PDFs)
- **OS**: AlmaLinux 9 / Rocky Linux 9 / RHEL 9 (Enterprise Linux 9 - Hostinger default)
- **Bandwidth**: Unlimited (standard with Hostinger VPS)

### Required Software (Will be installed)
- **Docker**: 24.x or later
- **Docker Compose**: 2.x or later
- **Git**: For repository management
- **UFW Firewall**: For security

### Network & Access Requirements
- **SSH Access**: Available on your Hostinger plan ✓
- **Root/Sudo Access**: Required for Docker installation
- **Ports**: 80 (HTTP), 443 (HTTPS), 22 (SSH)
- **Domain**: Pointed to your Hostinger VPS IP address

### Hostinger Panel Access
- **hPanel**: For domain management and DNS settings
- **SSH Credentials**: Available in your Hostinger VPS dashboard

---

## Pre-Deployment Checklist

### Hostinger Account Setup
- [ ] Hostinger VPS or Cloud hosting plan active
- [ ] SSH access enabled in Hostinger panel
- [ ] VPS IP address noted (found in hPanel → VPS → Overview)
- [ ] Root password or SSH key configured

### Domain & DNS (in Hostinger hPanel)
- [ ] Domain name registered (can be done through Hostinger)
- [ ] Domain added to your Hostinger account
- [ ] DNS A record pointing to VPS IP address
- [ ] DNS propagation completed (check with `dig your-domain.com`)
- [ ] SSL certificate will be configured (Hostinger provides free SSL)

### Access Credentials
- [ ] SSH username and password/key ready
- [ ] GitHub repository access (if private repo)
- [ ] Database credentials prepared (will be set in .env)
- [ ] SMTP credentials ready (optional, for email features)

### Preparation
- [ ] Code repository up to date on GitHub
- [ ] `.env.production` file reviewed
- [ ] Book PDF files ready for upload (if not in repo)
- [ ] Backup of any existing data

---

## Initial Server Access & Setup

### 1. Access Your Hostinger VPS via SSH

#### Get SSH Credentials from Hostinger hPanel:
1. Log in to https://hpanel.hostinger.com
2. Go to **VPS** → Select your VPS
3. Click **Overview** → Note your **IP address**
4. Go to **SSH Access** → Note username (usually `root`)
5. Use the password provided or set up SSH key

#### Connect via SSH:
```bash
# From your local machine (or WSL if on Windows)
ssh root@YOUR_VPS_IP_ADDRESS

# Example:
# ssh root@123.45.67.89

# Enter password when prompted
```

**First time connection:** You'll see a fingerprint verification message - type `yes` to continue.

### 2. Check System Information
```bash
# Verify OS and kernel version
uname -a
# Expected: Linux us-bos-web1679.main-hosting.eu 5.14.0-503.35.1.el9_5.x86_64

# Check OS release
cat /etc/os-release
```

### 3. Verify Available Package Manager
```bash
# Check if dnf is available
which dnf || echo "dnf not found"

# Check if yum is available
which yum || echo "yum not found"

# Check if apt is available (unlikely on RHEL)
which apt || echo "apt not found"
```

**⚠️ MANAGED HOSTING LIMITATION:**
If package managers are not available, this indicates a **restricted managed hosting environment**. You have several options:

**Option 1: Contact Hosting Provider**
- Request installation of required packages: `docker`, `docker-compose`, `git`
- Ask if Docker is available or can be enabled
- Request firewall configuration for ports 80, 443

**Option 2: Check for Pre-installed Software**
```bash
# Check if Docker is already installed
docker --version
docker-compose --version || docker compose version

# Check if Git is already installed
git --version
```

**Option 3: Use Static Binaries**
If your hosting provider doesn't support package installation, you can use static binaries:
```bash
# Download Docker Compose as standalone binary (if Docker is available)
mkdir -p ~/.docker/cli-plugins/
curl -SL https://github.com/docker/compose/releases/download/v2.24.0/docker-compose-linux-x86_64 -o ~/.docker/cli-plugins/docker-compose
chmod +x ~/.docker/cli-plugins/docker-compose
```

### 4. Install Essential Tools (If Package Manager Available)
```bash
# ONLY if dnf/yum is available - otherwise skip or contact provider
# Update system packages
sudo dnf update -y || sudo yum update -y

# Install essential tools
sudo dnf install -y git curl wget vim nano htop || sudo yum install -y git curl wget vim nano htop
```

**If you cannot install packages:**
```bash
# Check what's already available
git --version
curl --version
wget --version
vim --version || nano --version
```

### 5. Check User Permissions
```bash
# Check current user
whoami

# Check sudo access
sudo -v

# Check groups
groups
```

**⚠️ MANAGED HOSTING NOTE:**
- In managed hosting environments, you may already have a non-root user
- Creating additional users may require hosting provider support
- Check your current permissions before proceeding
- You may not have sudo access - contact provider if needed

### 6. Configure Firewall (If Available)
```bash
# Check if firewalld is available and you have access
sudo systemctl status firewalld 2>/dev/null || echo "Firewall may be managed by hosting provider"

# If available, configure firewall
sudo systemctl start firewalld
sudo systemctl enable firewalld

# Allow SSH (IMPORTANT: This should already be allowed, but verify)
sudo firewall-cmd --permanent --add-service=ssh

# Allow HTTP and HTTPS
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https

# Reload firewall to apply changes
sudo firewall-cmd --reload

# Check status
sudo firewall-cmd --list-all
```

**⚠️ MANAGED HOSTING FIREWALL:**
If you cannot access firewall commands:
- Firewall is likely managed by your hosting provider
- Contact them to request ports 80 and 443 be opened
- Check hosting control panel for firewall settings
- SSH (port 22) is already open since you can connect

Expected output should show:
```
services: ssh http https
```

### 7. Set Timezone (Optional, if permitted)
```bash
# Check current timezone
timedatectl status || date

# Set to your timezone (if you have permission)
sudo timedatectl set-timezone Pacific/Guam 2>/dev/null || echo "Timezone change requires provider support"

# Or list available timezones:
# timedatectl list-timezones
```

### 8. Create Application Directory
```bash
# Check where you have write permissions
pwd

# Create application directory (adjust path based on your hosting environment)
mkdir -p ~/library || mkdir -p /var/www/library || mkdir -p /home/$(whoami)/library

# Navigate to it
cd ~/library || cd /var/www/library || cd /home/$(whoami)/library
```

**Note:** Managed hosting may restrict where you can create directories. Common locations:
- `~/` (your home directory)
- `/home/username/`
- `/var/www/` (if you have access)
- Check your hosting documentation for allowed paths

---

## Docker Installation (Managed Hosting Environment)

Your server runs **RHEL 9 Compatible OS** (Linux 5.14.0-503.35.1.el9_5.x86_64) on **managed hosting** where package managers are restricted.

### ⚠️ CRITICAL: Managed Hosting Docker Setup

Since `dnf` is not available on your managed hosting environment, you have these options:

### Option 1: Request Docker from Hosting Provider (RECOMMENDED)
**Contact your hosting provider (main-hosting.eu) and request:**
- Docker Engine installation
- Docker Compose installation
- Permissions to run Docker containers
- Firewall configuration for ports 80, 443

**This is the safest and most reliable approach for managed hosting.**

### Option 2: Check if Docker is Pre-installed
```bash
# Check if Docker is already available
docker --version
docker compose version

# Check if docker service is running
systemctl status docker 2>/dev/null || service docker status 2>/dev/null || echo "Docker not found or no service access"

# Check your groups (you may already be in docker group)
groups
```

If Docker is already installed but not running:
```bash
# Try to start Docker (may require sudo or hosting provider)
sudo systemctl start docker || sudo service docker start
```

### Option 3: Install Docker Without Package Manager (Advanced)
**Only attempt this if your hosting provider cannot help and you have sufficient permissions.**

#### Download Docker Static Binary
```bash
# Create directory for Docker
mkdir -p ~/docker-install
cd ~/docker-install

# Download Docker static binary
curl -fsSL https://download.docker.com/linux/static/stable/x86_64/docker-24.0.7.tgz -o docker.tgz

# Extract
tar -xzf docker.tgz

# Move to local bin
mkdir -p ~/bin
mv docker/* ~/bin/
chmod +x ~/bin/*

# Add to PATH
echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc

# Verify
docker --version
```

**⚠️ WARNING:** Running Docker without proper package management may have limitations:
- No systemd integration (manual daemon management)
- Missing dependencies
- No automatic updates
- May violate hosting provider terms of service

#### Download Docker Compose Standalone
```bash
# Download Docker Compose standalone binary
mkdir -p ~/.docker/cli-plugins/
curl -SL https://github.com/docker/compose/releases/download/v2.24.0/docker-compose-linux-x86_64 -o ~/.docker/cli-plugins/docker-compose
chmod +x ~/.docker/cli-plugins/docker-compose

# Verify
docker compose version
```

### Option 4: Alternative Deployment Without Docker
If Docker cannot be installed on your managed hosting:
- Consider using the hosting provider's native PHP/MySQL stack
- Deploy Laravel directly without Docker
- Use the hosting provider's control panel for database/web server setup
- This would require significant changes to the deployment approach

### 1. Verify Current Docker Status (Start Here)
```bash
# First, check if Docker is already available
docker --version
docker compose version

# Check if you're in the docker group
groups | grep docker

# Check Docker service status
systemctl status docker 2>/dev/null || echo "No systemd access - managed environment"
```

**If Docker is available, skip to section "Starting Docker Service" below.**

### 2. Contact Hosting Provider Checklist
If Docker is not available, contact main-hosting.eu support and provide:
- Server hostname: `us-bos-web1679.main-hosting.eu`
- Request: Docker Engine and Docker Compose installation
- Ports needed: 80 (HTTP), 443 (HTTPS)
- Use case: Running containerized Laravel web application

### Starting Docker Service (If Docker is Already Installed)

```bash
# Start Docker service (may require sudo)
sudo systemctl start docker || sudo service docker start

# Enable Docker to start on boot
sudo systemctl enable docker

# Check status
sudo systemctl status docker || sudo service docker status
```

### Configure SELinux (If You Have Access)
```bash
# Check SELinux status
getenforce

# If SELinux is enforcing and causing issues, you may need to adjust
# Option 1: Set to permissive mode (if you have permission)
sudo setenforce 0

# Option 2: Check for SELinux denials
ausearch -m avc -ts recent 2>/dev/null || echo "No ausearch access"

# Note: SELinux configuration may be managed by hosting provider
```

**⚠️ MANAGED HOSTING:** SELinux configuration may require hosting provider assistance.

### Add User to Docker Group
```bash
# Add current user to docker group (if not already)
sudo usermod -aG docker $USER

# Check if you're in docker group
groups

# Activate the changes (choose one):
# Option 1: Log out and log back in
# Option 2: Run this command
newgrp docker
```

### Verify Docker Installation
```bash
# Check versions
docker --version
docker compose version

# Check Docker is running
docker ps

# Test with hello-world
docker run hello-world
```

**Expected output:**
```
Docker version 24.x.x, build xxxxx
Docker Compose version v2.x.x
Hello from Docker! (from test)
```

If you see errors, check:
- Do you have permission to run Docker? (`groups | grep docker`)
- Is Docker service running? (`systemctl status docker`)
- Contact your hosting provider if issues persist

### Configure Docker Daemon (If You Have Access)
```bash
# Check if you can modify Docker config
ls -la /etc/docker/

# If you have access, optimize Docker logging
sudo mkdir -p /etc/docker
sudo tee /etc/docker/daemon.json > /dev/null <<EOF
{
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "10m",
    "max-file": "3"
  }
}
EOF

# Restart Docker to apply
sudo systemctl restart docker || sudo service docker restart
```

**⚠️ MANAGED HOSTING:** You may not have access to `/etc/docker/`. This is optional and won't prevent deployment.

---

## Application Deployment

### 1. Navigate to Application Directory
```bash
cd /var/www
```

### 2. Clone Repository from GitHub
```bash
# Clone the repository
git clone https://github.com/ShvedkoDev/book_library.git library

# Navigate into the project
cd library
```

**If repository is private:** You'll need to authenticate with GitHub
```bash
# Option 1: Use Personal Access Token
git clone https://YOUR_TOKEN@github.com/ShvedkoDev/book_library.git library

# Option 2: Set up SSH key (recommended)
# See GitHub docs: https://docs.github.com/en/authentication
```

### 3. Set Correct Permissions
```bash
# Set ownership (replace with your user if not root)
chown -R www-data:www-data /var/www/library

# Set directory permissions
find /var/www/library -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/library -type f -exec chmod 644 {} \;

# Make scripts executable
chmod +x /var/www/library/docker/entrypoint.sh
```

### 4. Verify Project Files
```bash
# List files to verify
ls -la /var/www/library

# You should see:
# - docker-compose.yml
# - Dockerfile
# - .env.production
# - app/, public/, resources/, etc.
```

---

## Environment Configuration

### 1. Create Production Environment File
```bash
cd /var/www/library

# Copy the production template to .env
cp .env.production .env
```

### 2. Edit Environment Variables
```bash
# Use nano or vim to edit
nano .env
```

### 3. Configure Critical Variables

**REQUIRED CHANGES** - Update these values:

#### Application Settings
```env
APP_NAME="Micronesian Teachers Digital Library"
APP_ENV=production
APP_KEY=    # Will be generated later - leave empty for now
APP_DEBUG=false
APP_URL=https://your-domain.com    # ← CHANGE THIS to your actual domain
```

#### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=db    # ← Keep as 'db' (Docker service name)
DB_PORT=3306
DB_DATABASE=book_library
DB_USERNAME=library_user
DB_PASSWORD=    # ← GENERATE STRONG PASSWORD (see below)
```

**Generate a strong database password:**
```bash
# Generate a random password
openssl rand -base64 32

# Or use this command and copy the output:
date +%s | sha256sum | base64 | head -c 32 ; echo
```

#### Database Root Password
```env
# Add this line to .env if not present:
DB_ROOT_PASSWORD=    # ← Different strong password for MySQL root
```

#### Cache, Session & Queue (Production Settings)
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=redis

# Redis connection (Docker service name)
REDIS_HOST=redis    # ← Keep as 'redis'
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### Mail Configuration (Optional - for user notifications)
```env
# If using Hostinger email or external SMTP:
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com    # Or smtp.gmail.com, etc.
MAIL_PORT=587
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Note:** Hostinger provides email hosting - you can use their SMTP settings.

### 4. Verify Configuration
```bash
# Check that .env file exists and has content
cat .env | grep -E "APP_URL|DB_PASSWORD|REDIS_HOST"

# Make sure .env is not publicly accessible
chmod 600 .env
```

### 5. Important Security Notes

**DO NOT commit .env to Git:**
```bash
# Verify .env is in .gitignore
grep -q ".env" .gitignore && echo "✓ .env is ignored" || echo "✗ Add .env to .gitignore!"
```

**Reference:** See `.env.production` file for all available configuration options and detailed comments.

---

## Production Docker Compose Setup

### Understanding the Current Docker Compose Structure

The project includes `docker-compose.yml` which works for both development and production. We'll create a production override file to customize settings for Hostinger deployment.

The current `docker-compose.yml` includes these services:
- **app**: PHP-FPM 8.3 application container
- **db**: MySQL 8.0 database
- **nginx**: Nginx web server
- **phpmyadmin**: Database management tool (optional for production)

### 1. Create Production Docker Compose Override

```bash
cd /var/www/library
nano docker-compose.production.yml
```

**Add this configuration:**

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: book_library_app_prod
    restart: always
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./storage/logs:/var/www/storage/logs
    networks:
      - book_library_network_prod
    depends_on:
      - db
      - redis
    environment:
      - DB_HOST=db
      - DB_PORT=3306
      - REDIS_HOST=redis
      - REDIS_PORT=6379

  db:
    image: mysql:8.0
    container_name: book_library_db_prod
    restart: always
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-secret}
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - book_library_network_prod
    command: --default-authentication-plugin=mysql_native_password
    # No port exposure for production - database only accessible internally

  redis:
    image: redis:7-alpine
    container_name: book_library_redis_prod
    restart: always
    command: redis-server --appendonly yes --maxmemory 256mb --maxmemory-policy allkeys-lru
    volumes:
      - redis_data:/data
    networks:
      - book_library_network_prod

  nginx:
    image: nginx:alpine
    container_name: book_library_nginx_prod
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www:ro
      - ./docker/nginx/nginx.production.conf:/etc/nginx/conf.d/default.conf:ro
      - ./docker/nginx/ssl:/etc/nginx/ssl:ro
      - nginx_logs:/var/log/nginx
    networks:
      - book_library_network_prod
    depends_on:
      - app

  # Optional: Queue Worker (if using queued jobs)
  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: book_library_queue_prod
    restart: always
    working_dir: /var/www
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    volumes:
      - ./:/var/www
    networks:
      - book_library_network_prod
    depends_on:
      - db
      - redis

  # Optional: Laravel Scheduler (for scheduled tasks)
  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: book_library_scheduler_prod
    restart: always
    working_dir: /var/www
    command: sh -c "while true; do php artisan schedule:run >> /dev/null 2>&1; sleep 60; done"
    volumes:
      - ./:/var/www
    networks:
      - book_library_network_prod
    depends_on:
      - db
      - redis

volumes:
  mysql_data:
    driver: local
  redis_data:
    driver: local
  nginx_logs:
    driver: local

networks:
  book_library_network_prod:
    driver: bridge
```

**Key Production Changes:**
- `restart: always` - Containers auto-restart on failure
- No exposed database ports - MySQL only accessible within Docker network
- Redis memory limits configured
- Separate production network name
- PHPMyAdmin removed (use CLI instead)
- Read-only mounts for application files in nginx

### 2. Create Production Nginx Configuration
```bash
mkdir -p docker/nginx
nano docker/nginx/nginx.production.conf
```

Add:
```nginx
# HTTP Server - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;

    # Let's Encrypt validation
    location /.well-known/acme-challenge/ {
        root /var/www/public;
        allow all;
    }

    # Redirect all other traffic to HTTPS
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# HTTPS Server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/public;

    # SSL Configuration
    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    index index.php;
    charset utf-8;

    # Max upload size
    client_max_body_size 100M;

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to sensitive files
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_hide_header X-Powered-By;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot|pdf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Error pages
    error_page 404 /index.php;
    error_page 500 502 503 504 /50x.html;
    location = /50x.html {
        root /usr/share/nginx/html;
    }
}
```

---

## Domain & DNS Configuration (Hostinger)

### 1. Add Domain in Hostinger hPanel

1. Log in to https://hpanel.hostinger.com
2. Go to **Domains** section
3. If you haven't registered a domain yet:
   - Click **Register New Domain** or add an existing one
4. If domain is already registered, make sure it's added to your account

### 2. Point Domain to VPS

#### Configure DNS Records in hPanel:

1. In hPanel, go to **Domains** → Select your domain
2. Click **DNS / Name Servers**
3. Make sure Name Servers are set to Hostinger's:
   - `ns1.dns-parking.com`
   - `ns2.dns-parking.com`
4. Go to **DNS Records** and add/update:

**A Records (IPv4):**
```
Type: A
Name: @ (or leave blank)
Points to: YOUR_VPS_IP_ADDRESS
TTL: 14400 (or default)
```

```
Type: A
Name: www
Points to: YOUR_VPS_IP_ADDRESS
TTL: 14400
```

**Example:** If your VPS IP is `123.45.67.89`:
- `@` → `123.45.67.89`
- `www` → `123.45.67.89`

### 3. Verify DNS Propagation

```bash
# Check from your server
dig your-domain.com

# Check from your server with specific nameserver
dig @8.8.8.8 your-domain.com

# Expected output should show your VPS IP address
```

**Note:** DNS propagation can take 1-48 hours, but often completes within 15 minutes with Hostinger.

### 4. Update Nginx Configuration with Your Domain

```bash
cd /var/www/library
nano docker/nginx/nginx.production.conf
```

Replace `your-domain.com` with your actual domain in both HTTP and HTTPS server blocks.

### 5. Temporary HTTP-Only Access (Before SSL)

Before setting up SSL, you can test with HTTP:

1. Temporarily comment out the HTTPS server block (lines with `listen 443`)
2. Remove the redirect in the HTTP block
3. Start containers (see next sections)
4. Visit `http://your-domain.com`
5. Then proceed with SSL setup

---

## SSL/HTTPS Setup (Hostinger)

Hostinger VPS allows free SSL certificates via Let's Encrypt.

### Option 1: Using Let's Encrypt with Certbot (Recommended)

**Prerequisites:**
- Domain DNS records pointing to your VPS ✓
- Ports 80 and 443 open in firewall ✓
- Application not yet running (or nginx container stopped)

#### 1. Install Certbot on Hostinger VPS
```bash
# Update package list
dnf update -y

# Install Certbot and required modules
dnf install -y certbot
```

#### 2. Stop Nginx Container (if running)
```bash
cd /var/www/library

# If containers are already running:
docker compose -f docker-compose.production.yml stop nginx

# Or if not started yet, skip this step
```

#### 3. Obtain SSL Certificate from Let's Encrypt

**Replace** `your-domain.com` and `your-email@domain.com` with your actual values:

```bash
certbot certonly --standalone \
  -d your-domain.com \
  -d www.your-domain.com \
  --email your-email@domain.com \
  --agree-tos \
  --non-interactive
```

**Example:**
```bash
certbot certonly --standalone \
  -d mtdl.example.com \
  -d www.mtdl.example.com \
  --email admin@example.com \
  --agree-tos \
  --non-interactive
```

**Expected output:**
```
Successfully received certificate.
Certificate is saved at: /etc/letsencrypt/live/your-domain.com/fullchain.pem
Key is saved at:         /etc/letsencrypt/live/your-domain.com/privkey.pem
```

#### 4. Copy Certificates to Docker Volume

```bash
# Create SSL directory
mkdir -p /var/www/library/docker/nginx/ssl

# Copy certificates (replace your-domain.com)
cp /etc/letsencrypt/live/your-domain.com/fullchain.pem /var/www/library/docker/nginx/ssl/
cp /etc/letsencrypt/live/your-domain.com/privkey.pem /var/www/library/docker/nginx/ssl/

# Set correct permissions
chmod 644 /var/www/library/docker/nginx/ssl/*
```

#### 5. Set Up Auto-Renewal

Certbot certificates expire every 90 days. Set up automatic renewal:

```bash
# Create renewal script
cat > /usr/local/bin/renew-ssl.sh <<'EOF'
#!/bin/bash
certbot renew --quiet
cp /etc/letsencrypt/live/*/fullchain.pem /var/www/library/docker/nginx/ssl/
cp /etc/letsencrypt/live/*/privkey.pem /var/www/library/docker/nginx/ssl/
docker compose -f /var/www/library/docker-compose.production.yml restart nginx
EOF

# Make executable
chmod +x /usr/local/bin/renew-ssl.sh

# Add to crontab (runs daily at 3 AM)
(crontab -l 2>/dev/null; echo "0 3 * * * /usr/local/bin/renew-ssl.sh >> /var/log/ssl-renewal.log 2>&1") | crontab -
```

#### 6. Test Auto-Renewal (Optional)
```bash
# Dry run to test renewal process
certbot renew --dry-run
```

### Option 2: Using Hostinger's SSL (If Available)

Some Hostinger plans provide SSL certificates through their panel. Check:
1. hPanel → **SSL** section
2. If available, download certificates
3. Upload to `/var/www/library/docker/nginx/ssl/`

### Option 3: Using Existing SSL Certificates

If you have existing SSL certificates from another provider:
```bash
mkdir -p /var/www/library/docker/nginx/ssl
cp /path/to/your/fullchain.pem /var/www/library/docker/nginx/ssl/
cp /path/to/your/privkey.pem /var/www/library/docker/nginx/ssl/
chmod 644 /var/www/library/docker/nginx/ssl/*
```

---

## Starting the Application

**Prerequisites completed:**
- ✓ .env file configured
- ✓ docker-compose.production.yml created
- ✓ nginx.production.conf created
- ✓ SSL certificates in place (if using HTTPS)

### 1. Navigate to Project Directory
```bash
cd /var/www/library
```

### 2. Build Docker Images

This will build the application container from your Dockerfile:

```bash
docker compose -f docker-compose.production.yml build --no-cache
```

**Expected:** Build process will install PHP dependencies via Composer and Node.js dependencies. This may take 5-10 minutes on first build.

### 3. Start All Containers

```bash
docker compose -f docker-compose.production.yml up -d
```

The `-d` flag runs containers in detached mode (background).

### 4. Verify All Containers are Running

```bash
docker compose -f docker-compose.production.yml ps
```

**Expected output:** You should see all services with "Up" status:
```
NAME                            STATUS
book_library_app_prod           Up
book_library_db_prod            Up
book_library_nginx_prod         Up (0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp)
book_library_redis_prod         Up
book_library_queue_prod         Up
book_library_scheduler_prod     Up
```

### 5. Check Container Logs (if any issues)
```bash
# View all logs
docker compose -f docker-compose.production.yml logs

# View specific service logs
docker compose -f docker-compose.production.yml logs app
docker compose -f docker-compose.production.yml logs nginx
```

### 6. Generate Application Encryption Key

```bash
docker compose -f docker-compose.production.yml exec app php artisan key:generate
```

This updates the `APP_KEY` in your `.env` file.

### 7. Run Database Migrations

```bash
docker compose -f docker-compose.production.yml exec app php artisan migrate --force
```

The `--force` flag is required in production. This creates all database tables.

### 8. Build Frontend Assets (if not already compiled)

```bash
docker compose -f docker-compose.production.yml exec app npm install
docker compose -f docker-compose.production.yml exec app npm run build
```

### 9. Create Storage Link

```bash
docker compose -f docker-compose.production.yml exec app php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

### 10. Create FilamentPHP Admin User

```bash
docker compose -f docker-compose.production.yml exec app php artisan make:filament-user
```

Follow the prompts to create your admin user:
- Name: (your name)
- Email: (your admin email)
- Password: (strong password)

### 11. Set Correct Storage Permissions

```bash
docker compose -f docker-compose.production.yml exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose -f docker-compose.production.yml exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

### 12. Cache Configuration (Production Optimization)

```bash
docker compose -f docker-compose.production.yml exec app php artisan config:cache
docker compose -f docker-compose.production.yml exec app php artisan route:cache
docker compose -f docker-compose.production.yml exec app php artisan view:cache
```

### 13. Verify Application is Accessible

**Via Browser:**
- Main site: `https://your-domain.com`
- Admin panel: `https://your-domain.com/admin`

**Via Command Line:**
```bash
curl -I https://your-domain.com
```

**Expected:** Should return `HTTP/2 200` or `HTTP/1.1 200`

### 14. Test Admin Login

1. Visit `https://your-domain.com/admin`
2. Log in with the credentials you created in step 10
3. You should see the FilamentPHP admin dashboard

**Congratulations!** 🎉 Your application is now live on Hostinger!

---

## Post-Deployment Tasks

### 1. Set Up Log Rotation on Host
```bash
sudo nano /etc/logrotate.d/library
```

Add:
```
/var/www/library/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    copytruncate
    su www-data www-data
}
```

### 2. Enable Docker Auto-Start on Boot
```bash
sudo systemctl enable docker
```

### 3. Configure Docker Restart Policies
The `restart: always` policy in docker-compose.production.yml ensures containers restart automatically.

---

## Maintenance & Updates

### Deploying Updates

```bash
cd /var/www/library

# Pull latest code
git pull origin main

# Rebuild and restart containers
docker compose -f docker-compose.production.yml down
docker compose -f docker-compose.production.yml build --no-cache
docker compose -f docker-compose.production.yml up -d

# Run migrations
docker compose -f docker-compose.production.yml exec app php artisan migrate --force

# Clear and cache configuration
docker compose -f docker-compose.production.yml exec app php artisan config:clear
docker compose -f docker-compose.production.yml exec app php artisan cache:clear
docker compose -f docker-compose.production.yml exec app php artisan config:cache
docker compose -f docker-compose.production.yml exec app php artisan route:cache
docker compose -f docker-compose.production.yml exec app php artisan view:cache

# Restart queue workers
docker compose -f docker-compose.production.yml restart queue
```

### Viewing Container Logs
```bash
# All containers
docker compose -f docker-compose.production.yml logs -f

# Specific container
docker compose -f docker-compose.production.yml logs -f app
docker compose -f docker-compose.production.yml logs -f nginx
docker compose -f docker-compose.production.yml logs -f queue
```

### Entering Container Shell
```bash
# App container
docker compose -f docker-compose.production.yml exec app bash

# Database container
docker compose -f docker-compose.production.yml exec db mysql -u library_user -p book_library
```

---

## Backup Strategy

### Database Backup

#### Manual Backup
```bash
docker compose -f docker-compose.production.yml exec db mysqldump \
  -u library_user -p book_library | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

#### Automated Backup Script
Create backup script:
```bash
sudo nano /usr/local/bin/backup-library.sh
```

Add:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/library"
DATE=$(date +%Y%m%d_%H%M%S)
PROJECT_DIR="/var/www/library"

mkdir -p $BACKUP_DIR

# Database backup
cd $PROJECT_DIR
docker compose -f docker-compose.production.yml exec -T db mysqldump \
  -u library_user -p'your_password' book_library | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup (storage directory)
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz -C $PROJECT_DIR storage/app/public

# Keep only last 14 days
find $BACKUP_DIR -type f -mtime +14 -delete

# Optional: Upload to remote storage (S3, etc.)
# aws s3 sync $BACKUP_DIR s3://your-bucket/library-backups/
```

Make executable and schedule:
```bash
sudo chmod +x /usr/local/bin/backup-library.sh
sudo crontab -e
```

Add:
```cron
0 2 * * * /usr/local/bin/backup-library.sh >> /var/log/library-backup.log 2>&1
```

### Volume Backup
```bash
# Backup MySQL volume
docker run --rm \
  -v library_mysql_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/mysql_volume_$(date +%Y%m%d).tar.gz /data

# Backup Redis volume
docker run --rm \
  -v library_redis_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/redis_volume_$(date +%Y%m%d).tar.gz /data
```

---

## Monitoring & Logs

### Container Health Monitoring
```bash
# Check container status
docker compose -f docker-compose.production.yml ps

# Check resource usage
docker stats

# Check specific container health
docker inspect --format='{{.State.Health.Status}}' library_app
```

### Application Logs
```bash
# Laravel logs
docker compose -f docker-compose.production.yml exec app tail -f storage/logs/laravel.log

# Nginx access logs
docker compose -f docker-compose.production.yml logs -f nginx

# Queue worker logs
docker compose -f docker-compose.production.yml logs -f queue
```

### Recommended Monitoring Tools
- **Portainer**: Docker container management UI
- **Grafana + Prometheus**: Metrics and monitoring
- **Uptime Kuma**: Uptime monitoring
- **Sentry**: Error tracking (configure in Laravel)

---

## Troubleshooting

### Container Won't Start
```bash
# Check logs
docker compose -f docker-compose.production.yml logs app

# Check Docker daemon
sudo systemctl status docker

# Rebuild container
docker compose -f docker-compose.production.yml build --no-cache app
docker compose -f docker-compose.production.yml up -d
```

### Database Connection Issues
```bash
# Test database connection
docker compose -f docker-compose.production.yml exec app php artisan tinker
>>> DB::connection()->getPdo();

# Check database is running
docker compose -f docker-compose.production.yml exec db mysql -u library_user -p
```

### Permission Issues
```bash
docker compose -f docker-compose.production.yml exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose -f docker-compose.production.yml exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

### Clear All Caches
```bash
docker compose -f docker-compose.production.yml exec app php artisan cache:clear
docker compose -f docker-compose.production.yml exec app php artisan config:clear
docker compose -f docker-compose.production.yml exec app php artisan route:clear
docker compose -f docker-compose.production.yml exec app php artisan view:clear
docker compose -f docker-compose.production.yml restart app
```

### Queue Not Processing
```bash
# Check queue worker status
docker compose -f docker-compose.production.yml logs -f queue

# Restart queue worker
docker compose -f docker-compose.production.yml restart queue
```

### High Memory Usage
```bash
# Check container resources
docker stats

# Restart containers
docker compose -f docker-compose.production.yml restart

# Prune unused Docker resources
docker system prune -a
```

---

## Security Checklist

- [ ] Environment file (.env) has secure passwords
- [ ] APP_DEBUG is set to false
- [ ] APP_ENV is set to production
- [ ] SSL certificate installed and HTTPS enforced
- [ ] Database passwords are strong (20+ characters)
- [ ] Firewall configured (UFW)
- [ ] Regular backups configured and tested
- [ ] Docker containers restart automatically
- [ ] Security headers configured in Nginx
- [ ] File upload restrictions in place (100MB limit)
- [ ] Redis protected (password if exposed)
- [ ] Sensitive files not exposed (only /public directory)
- [ ] Database not exposed to internet (no ports in docker-compose)
- [ ] Regular security updates applied

---

## Performance Optimization

### Docker Optimization
```bash
# Use BuildKit for faster builds
export DOCKER_BUILDKIT=1

# Optimize image layers in Dockerfile
# (already done in current Dockerfile)
```

### Laravel Optimization
```bash
# Cache everything
docker compose -f docker-compose.production.yml exec app php artisan config:cache
docker compose -f docker-compose.production.yml exec app php artisan route:cache
docker compose -f docker-compose.production.yml exec app php artisan view:cache

# Use Redis for everything
# (already configured in .env.production)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## Support & Monitoring

### Health Check Endpoint
```
https://your-domain.com/up
```

### Useful Commands Reference
```bash
# Start all services
docker compose -f docker-compose.production.yml up -d

# Stop all services
docker compose -f docker-compose.production.yml down

# Restart a service
docker compose -f docker-compose.production.yml restart app

# View logs
docker compose -f docker-compose.production.yml logs -f

# Execute artisan commands
docker compose -f docker-compose.production.yml exec app php artisan [command]

# Access database
docker compose -f docker-compose.production.yml exec db mysql -u library_user -p

# Backup database
docker compose -f docker-compose.production.yml exec db mysqldump -u library_user -p book_library > backup.sql
```

---

## Hostinger-Specific Notes

### Resource Management on Hostinger VPS

#### Check VPS Resources
```bash
# Check available memory
free -h

# Check disk space
df -h

# Check CPU usage
htop
# (press 'q' to quit)
```

#### Optimize for Limited Resources

If running on a smaller VPS plan (2GB RAM), consider:

```bash
# Reduce Redis memory limit in docker-compose.production.yml
# Change: --maxmemory 256mb to --maxmemory 128mb

# Reduce PHP-FPM workers
# Edit docker/php/uploads.ini and add:
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
```

### Hostinger hPanel Integration

#### File Manager Access
- hPanel provides a file manager for manual file uploads
- Useful for uploading book PDFs to `storage/app/public/books/`
- Access: hPanel → Files → File Manager → `/var/www/library/`

#### Database Access
While PHPMyAdmin is removed from production, you can:
1. Use Hostinger's hPanel database tools (if available)
2. Use command line:
   ```bash
   docker compose -f docker-compose.production.yml exec db mysql -u library_user -p
   ```
3. Install a standalone database GUI locally and connect via SSH tunnel

#### Email Configuration with Hostinger

If using Hostinger email hosting:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=your-hostinger-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
```

### Hostinger VPS Firewall Notes

Hostinger VPS (AlmaLinux/Rocky Linux) uses **firewalld**, not UFW. If issues accessing the site:

```bash
# Check firewall status
firewall-cmd --list-all

# Ensure ports are open
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload

# Or open ports directly
firewall-cmd --permanent --add-port=80/tcp
firewall-cmd --permanent --add-port=443/tcp
firewall-cmd --reload
```

### Hostinger Backup Integration

Consider using Hostinger's backup features:
- hPanel → Backups → Create Manual Backup
- Hostinger provides automatic weekly backups (check your plan)
- Download backups regularly for safekeeping

### Performance Tips for Hostinger VPS

1. **Use Hostinger's CDN** (if available on your plan)
   - Can be enabled in hPanel for static assets

2. **Enable OPcache** (already included in Dockerfile)
   - Improves PHP performance significantly

3. **Monitor Resource Usage**
   ```bash
   # View Docker container stats
   docker stats

   # If memory issues occur, restart containers
   docker compose -f docker-compose.production.yml restart
   ```

4. **Upgrade VPS Plan** if needed:
   - VPS 1: 1GB RAM (minimum, may struggle)
   - VPS 2: 2GB RAM (recommended minimum)
   - VPS 4: 4GB RAM (recommended for production)
   - VPS 8: 8GB RAM (optimal for high traffic)

### Common Hostinger VPS Issues

#### Issue: "Cannot connect to Docker daemon"
```bash
# Solution: Start Docker service
systemctl start docker
systemctl enable docker
```

#### Issue: "Permission denied" errors
```bash
# Solution: Add user to docker group
usermod -aG docker $USER
# Then logout and login again
```

#### Issue: "Port 80 already in use"
```bash
# Check what's using port 80
ss -tulpn | grep :80
# Or: netstat -tulpn | grep :80

# Hostinger sometimes has httpd (Apache) or nginx running
# Check running web servers:
systemctl status httpd
systemctl status nginx

# Stop if not needed:
systemctl stop httpd
systemctl disable httpd
# Or for nginx:
systemctl stop nginx
systemctl disable nginx
```

#### Issue: "Out of disk space"
```bash
# Check Docker disk usage
docker system df

# Clean up unused Docker resources
docker system prune -a

# Clean up old log files
find /var/log -name "*.log" -type f -mtime +30 -delete
```

### Hostinger Support

If you encounter VPS-specific issues:
- **Hostinger Live Chat**: Available 24/7 in hPanel
- **Hostinger Knowledge Base**: https://support.hostinger.com
- **VPS Tutorials**: Check Hostinger's VPS setup guides

### Quick Reference: Hostinger hPanel Links

- **Main Panel**: https://hpanel.hostinger.com
- **VPS Overview**: hPanel → VPS → Your VPS
- **SSH Access**: hPanel → VPS → SSH Access
- **Domain Management**: hPanel → Domains
- **DNS Settings**: hPanel → Domains → Your Domain → DNS
- **SSL Certificates**: hPanel → SSL (if available on your plan)
- **Email Accounts**: hPanel → Emails
- **File Manager**: hPanel → Files → File Manager

### Quick Reference: AlmaLinux/Rocky Linux Commands

**Package Management:**
```bash
dnf update -y                    # Update all packages
dnf install package-name         # Install a package
dnf remove package-name          # Remove a package
dnf search keyword               # Search for packages
```

**Firewall (firewalld):**
```bash
firewall-cmd --list-all                          # List all firewall rules
firewall-cmd --permanent --add-service=http      # Allow HTTP
firewall-cmd --permanent --add-service=https     # Allow HTTPS
firewall-cmd --permanent --add-port=8080/tcp     # Allow specific port
firewall-cmd --reload                            # Apply changes
systemctl status firewalld                       # Check firewall status
```

**SELinux:**
```bash
getenforce                      # Check SELinux status (Enforcing/Permissive/Disabled)
setenforce 0                    # Set to permissive mode (temporary)
setenforce 1                    # Set to enforcing mode (temporary)
sestatus                        # View SELinux status details
ausearch -m avc -ts recent      # View recent SELinux denials
```

**Service Management:**
```bash
systemctl start service-name    # Start a service
systemctl stop service-name     # Stop a service
systemctl restart service-name  # Restart a service
systemctl status service-name   # Check service status
systemctl enable service-name   # Enable service to start on boot
systemctl disable service-name  # Disable service from starting on boot
```

**System Information:**
```bash
cat /etc/os-release            # View OS information
uname -a                       # View kernel information
free -h                        # View memory usage
df -h                          # View disk usage
ss -tulpn                      # View open ports and services
journalctl -xe                 # View system logs
```

---

## Contact & Support

For deployment assistance or issues:
- **GitHub**: https://github.com/ShvedkoDev/book_library
- **Documentation**: See CLAUDE.md for project details
- **Hostinger Support**: Available 24/7 via hPanel live chat

---

**Last Updated**: 2025-01-28
**Version**: 4.0 (Manual Deployment + Docker VPS Guide)
**Target Hosting**: Hostinger Cloud/Shared Hosting (Manual) or VPS (Docker)
**Primary Domain**: micronesian.school
**Deployment Methods**:
- Manual deployment WITHOUT Docker (Shared/Cloud hosting) - **PRIMARY GUIDE**
- Docker deployment (VPS only - requires plan upgrade)

**Hosting Confirmation:**
- Docker requires root access (VPS only)
- Current plan: Shared/Cloud hosting
- Manual deployment recommended for immediate use
