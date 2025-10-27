# Production Deployment Guide
## Micronesian Teachers Digital Library

This guide provides step-by-step instructions for deploying the Micronesian Teachers Digital Library application to a production server.

---

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Server Setup](#server-setup)
4. [Application Deployment](#application-deployment)
5. [Environment Configuration](#environment-configuration)
6. [Database Setup](#database-setup)
7. [File Storage Configuration](#file-storage-configuration)
8. [Web Server Configuration](#web-server-configuration)
9. [SSL/HTTPS Setup](#sslhttps-setup)
10. [Post-Deployment Tasks](#post-deployment-tasks)
11. [Maintenance & Updates](#maintenance--updates)
12. [Troubleshooting](#troubleshooting)

---

## Server Requirements

### Minimum Specifications
- **CPU**: 2 cores
- **RAM**: 4 GB (8 GB recommended)
- **Storage**: 50 GB SSD (100 GB+ recommended for book PDFs)
- **OS**: Ubuntu 22.04 LTS or later

### Required Software
- **PHP**: 8.2 or higher
- **Composer**: 2.x
- **Node.js**: 20.x LTS
- **NPM**: 10.x
- **MySQL**: 8.0 or higher
- **Nginx**: 1.18+ or Apache 2.4+
- **Redis**: 7.x (optional but recommended)
- **Supervisor**: For queue workers

### Required PHP Extensions
```bash
php8.2-cli
php8.2-common
php8.2-mysql
php8.2-xml
php8.2-xmlrpc
php8.2-curl
php8.2-gd
php8.2-imagick
php8.2-cli
php8.2-dev
php8.2-imap
php8.2-mbstring
php8.2-opcache
php8.2-soap
php8.2-zip
php8.2-intl
php8.2-bcmath
php8.2-redis
```

---

## Pre-Deployment Checklist

### Domain & DNS
- [ ] Domain name registered
- [ ] DNS A record pointing to server IP
- [ ] SSL certificate obtained (Let's Encrypt recommended)

### Server Access
- [ ] SSH access configured
- [ ] Firewall configured (ports 80, 443, 22)
- [ ] Non-root sudo user created

### Third-Party Services
- [ ] Email service configured (SMTP or service like SendGrid, Mailgun)
- [ ] Backup solution in place
- [ ] Monitoring tools set up (optional)

---

## Server Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Required Software

#### Install PHP 8.2
```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-curl php8.2-gd php8.2-mbstring php8.2-opcache php8.2-zip \
    php8.2-intl php8.2-bcmath php8.2-redis
```

#### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

#### Install Node.js & NPM
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

#### Install MySQL
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

#### Install Redis (Optional but Recommended)
```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

#### Install Nginx
```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

#### Install Supervisor
```bash
sudo apt install -y supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

### 3. Create Application User
```bash
sudo adduser --system --group --home /var/www library
sudo usermod -aG www-data library
```

---

## Application Deployment

### 1. Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/ShvedkoDev/book_library.git library
sudo chown -R library:library /var/www/library
cd library
```

### 2. Install PHP Dependencies
```bash
sudo -u library composer install --optimize-autoloader --no-dev
```

### 3. Install Node Dependencies and Build Assets
```bash
sudo -u library npm ci
sudo -u library npm run build
```

### 4. Set Permissions
```bash
sudo chown -R library:www-data /var/www/library
sudo chmod -R 755 /var/www/library
sudo chmod -R 775 /var/www/library/storage
sudo chmod -R 775 /var/www/library/bootstrap/cache
```

---

## Environment Configuration

### 1. Create Production Environment File
```bash
cd /var/www/library
sudo -u library cp .env.production .env
```

### 2. Edit Environment Variables
```bash
sudo -u library nano .env
```

**See `.env.production` file for complete configuration template.**

### 3. Generate Application Key
```bash
sudo -u library php artisan key:generate
```

### 4. Cache Configuration
```bash
sudo -u library php artisan config:cache
sudo -u library php artisan route:cache
sudo -u library php artisan view:cache
```

---

## Database Setup

### 1. Create Database and User
```bash
sudo mysql
```

```sql
CREATE DATABASE book_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'library_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON book_library.* TO 'library_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Run Migrations
```bash
cd /var/www/library
sudo -u library php artisan migrate --force
```

### 3. Seed Database (if needed)
```bash
sudo -u library php artisan db:seed --force
```

### 4. Create Admin User
```bash
sudo -u library php artisan make:filament-user
```

---

## File Storage Configuration

### 1. Create Storage Directories
```bash
sudo mkdir -p /var/www/library/storage/app/public/books
sudo mkdir -p /var/www/library/storage/app/public/thumbnails
sudo chown -R library:www-data /var/www/library/storage
sudo chmod -R 775 /var/www/library/storage
```

### 2. Link Storage
```bash
sudo -u library php artisan storage:link
```

### 3. Configure File Upload Limits

Edit PHP configuration:
```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Update these values:
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 512M
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

---

## Web Server Configuration

### Nginx Configuration

Create site configuration:
```bash
sudo nano /etc/nginx/sites-available/library
```

Add this configuration:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/library/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    # Max upload size
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/library /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## SSL/HTTPS Setup

### Using Let's Encrypt (Recommended)

#### 1. Install Certbot
```bash
sudo apt install -y certbot python3-certbot-nginx
```

#### 2. Obtain SSL Certificate
```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

#### 3. Auto-Renewal
Certbot sets up automatic renewal. Verify it:
```bash
sudo certbot renew --dry-run
```

---

## Post-Deployment Tasks

### 1. Configure Queue Workers

Create supervisor configuration:
```bash
sudo nano /etc/supervisor/conf.d/library-worker.conf
```

Add this configuration:
```ini
[program:library-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/library/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=library
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/library/storage/logs/worker.log
stopwaitsecs=3600
```

Start workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start library-worker:*
```

### 2. Configure Cron Jobs

Edit crontab:
```bash
sudo -u library crontab -e
```

Add Laravel scheduler:
```cron
* * * * * cd /var/www/library && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Set Up Log Rotation

Create log rotation config:
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
    create 0640 library www-data
    sharedscripts
}
```

### 4. Configure Firewall
```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow 22/tcp
sudo ufw enable
```

### 5. Enable Opcache

Edit PHP configuration:
```bash
sudo nano /etc/php/8.2/fpm/conf.d/10-opcache.ini
```

Add/uncomment:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

---

## Maintenance & Updates

### Deploying Updates

```bash
cd /var/www/library

# Put application in maintenance mode
sudo -u library php artisan down

# Pull latest code
sudo -u library git pull origin main

# Update dependencies
sudo -u library composer install --optimize-autoloader --no-dev
sudo -u library npm ci
sudo -u library npm run build

# Run migrations
sudo -u library php artisan migrate --force

# Clear and cache configuration
sudo -u library php artisan config:clear
sudo -u library php artisan cache:clear
sudo -u library php artisan config:cache
sudo -u library php artisan route:cache
sudo -u library php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart library-worker:*

# Bring application back online
sudo -u library php artisan up
```

### Database Backup

#### Manual Backup
```bash
mysqldump -u library_user -p book_library > backup_$(date +%Y%m%d_%H%M%S).sql
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
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u library_user -p'your_password' book_library | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/library/storage/app/public

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

Make executable and add to cron:
```bash
sudo chmod +x /usr/local/bin/backup-library.sh
sudo crontab -e
```

Add:
```cron
0 2 * * * /usr/local/bin/backup-library.sh
```

---

## Troubleshooting

### Application Errors

#### Check Laravel Logs
```bash
sudo tail -f /var/www/library/storage/logs/laravel.log
```

#### Check PHP-FPM Errors
```bash
sudo tail -f /var/log/php8.2-fpm.log
```

#### Check Nginx Errors
```bash
sudo tail -f /var/log/nginx/error.log
```

### Permission Issues
```bash
cd /var/www/library
sudo chown -R library:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Clear All Caches
```bash
sudo -u library php artisan cache:clear
sudo -u library php artisan config:clear
sudo -u library php artisan route:clear
sudo -u library php artisan view:clear
sudo systemctl restart php8.2-fpm
```

### Queue Not Processing
```bash
sudo supervisorctl status
sudo supervisorctl restart library-worker:*
```

### Database Connection Issues
- Verify database credentials in `.env`
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `mysql -u library_user -p book_library`

---

## Security Checklist

- [ ] Environment file (.env) has secure passwords
- [ ] APP_DEBUG is set to false
- [ ] APP_ENV is set to production
- [ ] SSL certificate installed and HTTPS enforced
- [ ] Database user has minimal required privileges
- [ ] Firewall configured (UFW or iptables)
- [ ] Regular backups configured
- [ ] Server security updates enabled
- [ ] Failed2ban installed (optional but recommended)
- [ ] Rate limiting configured in application
- [ ] Security headers configured in Nginx
- [ ] File upload restrictions in place

---

## Support & Monitoring

### Recommended Monitoring Tools
- **Server Monitoring**: Netdata, Prometheus + Grafana
- **Error Tracking**: Sentry, Bugsnag
- **Uptime Monitoring**: UptimeRobot, Pingdom
- **Log Management**: Papertrail, Loggly

### Health Check Endpoint
The application provides a health check endpoint at:
```
https://your-domain.com/up
```

---

## Contact & Support

For deployment assistance or issues:
- **GitHub**: https://github.com/ShvedkoDev/book_library
- **Documentation**: See CLAUDE.md for project details

---

**Last Updated**: 2025-10-27
**Version**: 1.0
