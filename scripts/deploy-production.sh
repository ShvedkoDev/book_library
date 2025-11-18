#!/bin/bash

################################################################################
# Production Deployment Script for Micronesian Teachers Digital Library
#
# This script should be run from the app_root directory on the production server
# Usage: ./scripts/deploy-production.sh
################################################################################

set -e  # Exit on any error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_ROOT=$(pwd)
PUBLIC_HTML="../public_html"
LOG_FILE="deploy-$(date +%Y%m%d-%H%M%S).log"

# Functions
log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

# Verify we're in the correct directory
if [ ! -f "artisan" ]; then
    error "artisan file not found. Please run this script from the app_root directory."
fi

log "=========================================="
log "Starting Production Deployment"
log "=========================================="
log "App Root: $APP_ROOT"
log "Public HTML: $PUBLIC_HTML"

# Step 1: Enable maintenance mode
log "Step 1: Enabling maintenance mode..."
php artisan down --render="errors::503" --retry=60 || warning "Could not enable maintenance mode"

# Step 2: Pull latest code from GitHub
log "Step 2: Pulling latest code from GitHub..."
git fetch origin || error "Failed to fetch from GitHub"
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
log "Current branch: $CURRENT_BRANCH"
git pull origin "$CURRENT_BRANCH" || error "Failed to pull from GitHub"

# Step 3: Install/Update Composer dependencies
log "Step 3: Installing Composer dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction || error "Composer install failed"
else
    php composer.phar install --no-dev --optimize-autoloader --no-interaction || error "Composer install failed"
fi

# Step 4: Run database migrations
log "Step 4: Running database migrations..."
php artisan migrate --force || error "Migration failed"

# Step 5: Clear and rebuild caches
log "Step 5: Clearing caches..."
php artisan config:clear || warning "Config clear failed"
php artisan cache:clear || warning "Cache clear failed"
php artisan view:clear || warning "View clear failed"
php artisan route:clear || warning "Route clear failed"

# Step 6: Verify pre-built assets exist
log "Step 6: Verifying pre-built assets..."
if [ ! -d "public/build" ]; then
    warning "public/build directory not found. Assets should be built locally and committed to Git."
fi
log "✓ Using pre-built assets from repository"

# Step 7: Copy build files to public_html
log "Step 7: Copying build files to public_html..."

# Ensure public_html exists
if [ ! -d "$PUBLIC_HTML" ]; then
    error "public_html directory not found at $PUBLIC_HTML"
fi

# Copy index.php (Laravel entry point)
if [ -f "public/index.php" ]; then
    cp -f public/index.php "$PUBLIC_HTML/index.php" || error "Failed to copy index.php"
    log "✓ Copied index.php"
fi

# Copy .htaccess if it exists
if [ -f "public/.htaccess" ]; then
    cp -f public/.htaccess "$PUBLIC_HTML/.htaccess" || warning "Failed to copy .htaccess"
    log "✓ Copied .htaccess"
fi

# Copy robots.txt if it exists
if [ -f "public/robots.txt" ]; then
    cp -f public/robots.txt "$PUBLIC_HTML/robots.txt" || warning "Failed to copy robots.txt"
    log "✓ Copied robots.txt"
fi

# Copy favicon if it exists
if [ -f "public/favicon.ico" ]; then
    cp -f public/favicon.ico "$PUBLIC_HTML/favicon.ico" || warning "Failed to copy favicon.ico"
    log "✓ Copied favicon.ico"
fi

# Copy build directory (Vite compiled assets)
if [ -d "public/build" ]; then
    rm -rf "$PUBLIC_HTML/build"
    cp -rf public/build "$PUBLIC_HTML/build" || error "Failed to copy build directory"
    log "✓ Copied build directory"
fi

# Copy css directory
if [ -d "public/css" ]; then
    rm -rf "$PUBLIC_HTML/css"
    cp -rf public/css "$PUBLIC_HTML/css" || warning "Failed to copy css directory"
    log "✓ Copied css directory"
fi

# Copy js directory
if [ -d "public/js" ]; then
    rm -rf "$PUBLIC_HTML/js"
    cp -rf public/js "$PUBLIC_HTML/js" || warning "Failed to copy js directory"
    log "✓ Copied js directory"
fi

# Copy library-assets directory
if [ -d "public/library-assets" ]; then
    rm -rf "$PUBLIC_HTML/library-assets"
    cp -rf public/library-assets "$PUBLIC_HTML/library-assets" || warning "Failed to copy library-assets directory"
    log "✓ Copied library-assets directory"
fi

# Copy ui-test directory if it exists
if [ -d "public/ui-test" ]; then
    rm -rf "$PUBLIC_HTML/ui-test"
    cp -rf public/ui-test "$PUBLIC_HTML/ui-test" || warning "Failed to copy ui-test directory"
    log "✓ Copied ui-test directory"
fi

# Copy admin-assets if it exists
if [ -d "public/admin-assets" ]; then
    rm -rf "$PUBLIC_HTML/admin-assets"
    cp -rf public/admin-assets "$PUBLIC_HTML/admin-assets" || warning "Failed to copy admin-assets directory"
    log "✓ Copied admin-assets directory"
fi

# Step 8: Ensure storage symlink is correct
log "Step 8: Verifying storage symlink..."
if [ ! -L "$PUBLIC_HTML/storage" ]; then
    log "Creating storage symlink..."
    ln -sf "$APP_ROOT/storage/app/public" "$PUBLIC_HTML/storage" || warning "Failed to create storage symlink"
fi
log "✓ Storage symlink verified"

# Step 9: Optimize Laravel
log "Step 9: Optimizing Laravel..."
php artisan config:cache || warning "Config cache failed"
php artisan route:cache || warning "Route cache failed"
php artisan view:cache || warning "View cache failed"
php artisan event:cache || warning "Event cache failed"

# Step 10: Set proper permissions
log "Step 10: Setting proper permissions..."
# Set permissions for storage and bootstrap/cache
chmod -R 775 storage bootstrap/cache || warning "Failed to set permissions"
log "✓ Permissions set"

# Step 11: Run queue restart (if using queues)
if grep -q "QUEUE_CONNECTION" .env && [ "$(grep QUEUE_CONNECTION .env | cut -d '=' -f2)" != "sync" ]; then
    log "Step 11: Restarting queue workers..."
    php artisan queue:restart || warning "Queue restart failed"
fi

# Step 12: Disable maintenance mode
log "Step 12: Disabling maintenance mode..."
php artisan up || warning "Could not disable maintenance mode"

# Final summary
log "=========================================="
log "Deployment completed successfully!"
log "=========================================="
log "Deployment log saved to: $LOG_FILE"

# Display last commit info
log ""
log "Latest deployed commit:"
git log -1 --pretty=format:"%h - %an, %ar : %s" || true
log ""

info "Please verify the application is working correctly at your production URL"
