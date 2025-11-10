#!/bin/bash
################################################################################
# Production Update Script
# Micronesian Teachers Digital Library
#
# This script automates the update process for production deployments.
# Usage: ./update-production.sh
################################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Welcome banner
echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║     Micronesian Teachers Digital Library                      ║"
echo "║     Production Update Script                                  ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Confirmation
read -p "This will update the production site. Continue? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Update cancelled by user."
    exit 0
fi

# Step 1: Check we're in the right directory
print_status "Checking environment..."
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Are you in the Laravel project root?"
    exit 1
fi

if [ ! -f "composer.json" ]; then
    print_error "composer.json not found. Are you in the Laravel project root?"
    exit 1
fi

print_success "Environment check passed"

# Step 2: Check Git status
print_status "Checking Git status..."
if ! command_exists git; then
    print_error "Git is not installed"
    exit 1
fi

CURRENT_COMMIT=$(git rev-parse --short HEAD)
print_status "Current commit: ${CURRENT_COMMIT}"

# Check for uncommitted changes
if [[ -n $(git status -s) ]]; then
    print_warning "You have uncommitted local changes"
    git status -s
    read -p "Stash these changes? (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git stash save "Auto-stash before update $(date +%Y%m%d-%H%M%S)"
        print_success "Changes stashed"
    fi
fi

# Step 3: Create backup
print_status "Creating backup..."
BACKUP_DIR=~/backups/$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"

# Backup .env
cp .env "$BACKUP_DIR/.env.backup"
print_success ".env backed up to $BACKUP_DIR"

# Backup database
print_status "Backing up database..."
if command_exists php && [ -f "artisan" ]; then
    php artisan db:backup 2>/dev/null || print_warning "Could not backup database via artisan"
fi

# Note: Add manual mysqldump if artisan backup doesn't work
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)

if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ]; then
    print_status "Creating database dump..."
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_DIR/database_backup.sql" 2>/dev/null || print_warning "Manual database backup failed (may require password)"
fi

print_success "Backup completed at $BACKUP_DIR"

# Step 4: Enable maintenance mode
print_status "Enabling maintenance mode..."
php artisan down --message="Updating library with latest features. Back soon!" --refresh=15
print_success "Site in maintenance mode"

# Step 5: Fetch and show changes
print_status "Fetching latest changes..."
git fetch origin

print_status "Changes to be applied:"
git log HEAD..origin/main --oneline --no-merges | head -10

read -p "Apply these changes? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Update cancelled. Bringing site back online..."
    php artisan up
    exit 0
fi

# Step 6: Pull changes
print_status "Pulling latest code..."
if git pull origin main; then
    print_success "Code updated successfully"
else
    print_error "Git pull failed"
    print_warning "Bringing site back online..."
    php artisan up
    exit 1
fi

# Step 7: Update Composer dependencies
print_status "Updating Composer dependencies..."
if command_exists composer; then
    composer install --no-dev --optimize-autoloader --no-interaction
    print_success "Dependencies updated"
else
    print_warning "Composer not found. Skipping dependency update."
fi

# Step 8: Run migrations
print_status "Running database migrations..."
php artisan migrate --force
print_success "Migrations completed"

# Step 9: Update storage link
print_status "Updating storage link..."
php artisan storage:link 2>/dev/null || print_warning "Storage link already exists"

# Step 10: Clear all caches
print_status "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

# Step 11: Rebuild optimized caches
print_status "Rebuilding optimized caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Caches rebuilt"

# Step 12: Optimize application
print_status "Optimizing application..."
php artisan optimize
print_success "Application optimized"

# Step 13: Set permissions
print_status "Setting file permissions..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || print_warning "Could not set permissions (may need sudo)"

# Step 14: Disable maintenance mode
print_status "Disabling maintenance mode..."
php artisan up
print_success "Site is back online!"

# Step 15: Check for errors in log
print_status "Checking recent logs..."
if [ -f "storage/logs/laravel.log" ]; then
    RECENT_ERRORS=$(tail -50 storage/logs/laravel.log | grep -i "error" | wc -l)
    if [ "$RECENT_ERRORS" -gt 0 ]; then
        print_warning "Found $RECENT_ERRORS error(s) in recent logs"
        echo "Last 10 errors:"
        tail -50 storage/logs/laravel.log | grep -i "error" | tail -10
    else
        print_success "No recent errors in logs"
    fi
fi

# Final summary
echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║     Update Completed Successfully!                            ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
print_status "Previous commit: ${CURRENT_COMMIT}"
print_status "Current commit:  $(git rev-parse --short HEAD)"
print_status "Backup location: ${BACKUP_DIR}"
echo ""
print_status "Next steps:"
echo "  1. Test the site: https://micronesian.school"
echo "  2. Check admin panel: https://micronesian.school/admin"
echo "  3. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
print_success "Update complete! 🎉"
echo ""

# Optional: Show rollback instructions
print_status "If something is wrong, you can rollback with:"
echo "  git reset --hard ${CURRENT_COMMIT}"
echo "  php artisan migrate:rollback"
echo "  php artisan cache:clear && php artisan up"
echo ""
