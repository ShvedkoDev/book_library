# Production Deployment Guide (Docker)
## Micronesian Teachers Digital Library

This guide provides step-by-step instructions for deploying the Micronesian Teachers Digital Library application to a production server using Docker and Docker Compose.

---

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Server Setup](#server-setup)
4. [Docker Installation](#docker-installation)
5. [Application Deployment](#application-deployment)
6. [Environment Configuration](#environment-configuration)
7. [Production Docker Compose](#production-docker-compose)
8. [SSL/HTTPS Setup](#sslhttps-setup)
9. [Starting the Application](#starting-the-application)
10. [Post-Deployment Tasks](#post-deployment-tasks)
11. [Maintenance & Updates](#maintenance--updates)
12. [Backup Strategy](#backup-strategy)
13. [Monitoring & Logs](#monitoring--logs)
14. [Troubleshooting](#troubleshooting)

---

## Server Requirements

### Minimum Specifications
- **CPU**: 2 cores (4 cores recommended)
- **RAM**: 4 GB (8 GB recommended)
- **Storage**: 50 GB SSD (100 GB+ recommended for book PDFs)
- **OS**: Ubuntu 22.04 LTS, Debian 11+, or CentOS/RHEL 8+

### Required Software
- **Docker**: 24.x or later
- **Docker Compose**: 2.x or later
- **Git**: For cloning repository

### Network Requirements
- Ports 80 (HTTP) and 443 (HTTPS) open to the internet
- Port 22 (SSH) for server management

---

## Pre-Deployment Checklist

### Domain & DNS
- [ ] Domain name registered
- [ ] DNS A record pointing to server IP
- [ ] SSL certificate ready or will use Let's Encrypt

### Server Access
- [ ] SSH access configured
- [ ] Firewall configured (ports 80, 443, 22)
- [ ] Non-root sudo user created

### Third-Party Services
- [ ] Email service configured (SMTP credentials ready)
- [ ] Backup solution identified
- [ ] Monitoring tools selected (optional)

---

## Server Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Basic Tools
```bash
sudo apt install -y git curl wget ufw
```

### 3. Configure Firewall
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 4. Create Application Directory
```bash
sudo mkdir -p /var/www
cd /var/www
```

---

## Docker Installation

### Install Docker

#### For Ubuntu/Debian:
```bash
# Remove old versions
sudo apt remove docker docker-engine docker.io containerd runc

# Install dependencies
sudo apt update
sudo apt install -y ca-certificates curl gnupg lsb-release

# Add Docker's official GPG key
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Set up repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Start and enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Add current user to docker group (logout/login required after this)
sudo usermod -aG docker $USER
```

#### Verify Installation:
```bash
docker --version
docker compose version
```

Expected output:
```
Docker version 24.x.x
Docker Compose version 2.x.x
```

---

## Application Deployment

### 1. Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/ShvedkoDev/book_library.git library
cd library
```

### 2. Set Directory Permissions
```bash
sudo chown -R $USER:$USER /var/www/library
chmod -R 755 /var/www/library
```

---

## Environment Configuration

### 1. Create Production Environment File
```bash
cd /var/www/library
cp .env.production .env
```

### 2. Edit Environment Variables
```bash
nano .env
```

**Important variables to configure:**

```env
# Application
APP_NAME="Micronesian Teachers Digital Library"
APP_ENV=production
APP_KEY=   # Generate later with: docker compose exec app php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (Docker service names)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=book_library
DB_USERNAME=library_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache & Session (use Redis in production)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis (Docker service name)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**See `.env.production` file for complete configuration options.**

---

## Production Docker Compose

### 1. Create Production Docker Compose File
```bash
nano docker-compose.production.yml
```

Add this configuration:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: library_app
    restart: always
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./storage/logs:/var/www/storage/logs
    networks:
      - library_network
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
    container_name: library_db
    restart: always
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf:ro
    networks:
      - library_network
    command: --default-authentication-plugin=mysql_native_password

  redis:
    image: redis:7-alpine
    container_name: library_redis
    restart: always
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    networks:
      - library_network

  nginx:
    image: nginx:alpine
    container_name: library_nginx
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
      - library_network
    depends_on:
      - app

  # Queue Worker
  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: library_queue
    restart: always
    working_dir: /var/www
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    volumes:
      - ./:/var/www
    networks:
      - library_network
    depends_on:
      - db
      - redis

  # Scheduler (Laravel Cron)
  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: library_scheduler
    restart: always
    working_dir: /var/www
    command: sh -c "while true; do php artisan schedule:run >> /dev/null 2>&1; sleep 60; done"
    volumes:
      - ./:/var/www
    networks:
      - library_network
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
  library_network:
    driver: bridge
```

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

## SSL/HTTPS Setup

### Option 1: Using Let's Encrypt (Recommended)

#### 1. Install Certbot on Host
```bash
sudo apt install -y certbot
```

#### 2. Stop Nginx Container Temporarily
```bash
docker compose -f docker-compose.production.yml stop nginx
```

#### 3. Obtain SSL Certificate
```bash
sudo certbot certonly --standalone \
  -d your-domain.com \
  -d www.your-domain.com \
  --email your-email@domain.com \
  --agree-tos \
  --no-eff-email
```

#### 4. Copy Certificates to Docker Volume
```bash
mkdir -p docker/nginx/ssl
sudo cp /etc/letsencrypt/live/your-domain.com/fullchain.pem docker/nginx/ssl/
sudo cp /etc/letsencrypt/live/your-domain.com/privkey.pem docker/nginx/ssl/
sudo chmod 644 docker/nginx/ssl/*
```

#### 5. Set Up Auto-Renewal
```bash
sudo crontab -e
```

Add this line:
```cron
0 3 * * * certbot renew --quiet --deploy-hook "cp /etc/letsencrypt/live/your-domain.com/*.pem /var/www/library/docker/nginx/ssl/ && docker compose -f /var/www/library/docker-compose.production.yml restart nginx"
```

### Option 2: Using Existing SSL Certificates

If you have existing SSL certificates:
```bash
mkdir -p docker/nginx/ssl
cp /path/to/your/fullchain.pem docker/nginx/ssl/
cp /path/to/your/privkey.pem docker/nginx/ssl/
chmod 644 docker/nginx/ssl/*
```

---

## Starting the Application

### 1. Build Docker Images
```bash
cd /var/www/library
docker compose -f docker-compose.production.yml build --no-cache
```

### 2. Start Containers
```bash
docker compose -f docker-compose.production.yml up -d
```

### 3. Verify Containers are Running
```bash
docker compose -f docker-compose.production.yml ps
```

You should see all services running:
- library_app
- library_db
- library_redis
- library_nginx
- library_queue
- library_scheduler

### 4. Generate Application Key
```bash
docker compose -f docker-compose.production.yml exec app php artisan key:generate
```

### 5. Run Database Migrations
```bash
docker compose -f docker-compose.production.yml exec app php artisan migrate --force
```

### 6. Create Admin User
```bash
docker compose -f docker-compose.production.yml exec app php artisan make:filament-user
```

### 7. Set Storage Permissions
```bash
docker compose -f docker-compose.production.yml exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose -f docker-compose.production.yml exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

### 8. Link Storage
```bash
docker compose -f docker-compose.production.yml exec app php artisan storage:link
```

### 9. Cache Configuration
```bash
docker compose -f docker-compose.production.yml exec app php artisan config:cache
docker compose -f docker-compose.production.yml exec app php artisan route:cache
docker compose -f docker-compose.production.yml exec app php artisan view:cache
```

### 10. Verify Application
Visit `https://your-domain.com` in your browser.

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

## Contact & Support

For deployment assistance or issues:
- **GitHub**: https://github.com/ShvedkoDev/book_library
- **Documentation**: See CLAUDE.md for project details

---

**Last Updated**: 2025-10-27
**Version**: 2.0 (Docker)
