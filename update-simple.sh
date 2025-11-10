#!/bin/bash
################################################################################
# Simple Production Update Script
# Run this ON THE SERVER in ~/app_root
#
# This script:
# 1. Updates code from GitHub
# 2. Copies built assets from app_root/public to public_html
# 3. No scp/rsync needed - everything via git!
################################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo ""
    echo -e "${BLUE}════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}════════════════════════════════════════════════════════${NC}"
    echo ""
}

print_step() {
    echo -e "${BLUE}[$1]${NC} $2"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_header "Micronesian Teachers Digital Library - Simple Update"

# Confirmation
read -p "Update production site? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Update cancelled."
    exit 0
fi

# Check we're in the right place
print_step "1/12" "Checking environment..."
if [ ! -f "artisan" ]; then
    print_error "artisan not found. Run this from ~/app_root"
    exit 1
fi
print_success "In correct directory"

# Create backup directory
print_step "2/12" "Creating backup..."
BACKUP_DIR=~/backups/$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"
cp .env "$BACKUP_DIR/.env.backup"
print_success "Backup: $BACKUP_DIR"

# Backup database
print_step "3/12" "Backing up database..."
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)

if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ]; then
    echo "Enter database password (or Ctrl+C to skip):"
    mysqldump -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_DIR/database.sql" 2>/dev/null && print_success "Database backed up" || print_warning "Skipped database backup"
else
    print_warning "Could not read DB credentials"
fi

# Show current version
print_step "4/12" "Current version:"
CURRENT_COMMIT=$(git rev-parse --short HEAD)
git log -1 --oneline

# Enable maintenance mode
print_step "5/12" "Enabling maintenance mode..."
php artisan down --message="Updating library - back in 5 minutes!" --refresh=15
print_success "Maintenance mode enabled"

# Stash local changes if any
print_step "6/12" "Checking for local changes..."
if [[ -n $(git status -s) ]]; then
    print_warning "Local changes detected, stashing..."
    git stash save "Auto-stash before update $(date +%Y%m%d-%H%M%S)"
    print_success "Changes stashed"
else
    print_success "No local changes"
fi

# Pull latest code
print_step "7/12" "Pulling latest code from GitHub..."
git pull origin main
print_success "Code updated to: $(git rev-parse --short HEAD)"

# Update Composer
print_step "8/12" "Updating Composer dependencies..."
if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --optimize-autoloader --no-interaction
    print_success "Dependencies updated"
else
    print_warning "Composer not found, trying composer.phar..."
    if [ -f "composer.phar" ]; then
        php composer.phar install --no-dev --optimize-autoloader --no-interaction
        print_success "Dependencies updated"
    else
        print_error "Composer not available"
    fi
fi

# Run migrations
print_step "9/12" "Running database migrations..."
php artisan migrate --force
print_success "Migrations complete"

# Copy built assets to public_html
print_step "10/12" "Copying built assets to public_html..."

# Check if public_html exists
if [ ! -d ~/public_html ]; then
    print_error "~/public_html not found!"
    exit 1
fi

# Copy build folder
if [ -d "public/build" ]; then
    echo "  → Copying build folder..."
    cp -r public/build ~/public_html/
    print_success "Build folder copied"
else
    print_warning "No build folder found in public/"
fi

# Copy library-assets
if [ -d "public/library-assets" ]; then
    echo "  → Copying library-assets..."
    cp -r public/library-assets ~/public_html/
    print_success "Library assets copied"
else
    print_warning "No library-assets folder found"
fi

# Copy index.php and .htaccess
echo "  → Copying public files..."
cp public/index.php ~/public_html/ 2>/dev/null || print_warning "index.php not copied"
cp public/.htaccess ~/public_html/ 2>/dev/null || print_warning ".htaccess not copied"

print_success "Assets copied to public_html"

# Clear caches
print_step "11/12" "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

# Rebuild caches
echo "  → Rebuilding optimized caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
print_success "Caches rebuilt"

# Disable maintenance mode
print_step "12/12" "Bringing site back online..."
php artisan up
print_success "Site is live!"

# Summary
print_header "Update Complete!"

echo "Previous version: $CURRENT_COMMIT"
echo "Current version:  $(git rev-parse --short HEAD)"
echo "Backup location:  $BACKUP_DIR"
echo ""
echo -e "${GREEN}✓ Update successful!${NC}"
echo ""
echo "Next steps:"
echo "  1. Visit: https://micronesian.school"
echo "  2. Hard refresh: Ctrl+Shift+R"
echo "  3. Test search and book pages"
echo "  4. Check admin: https://micronesian.school/admin"
echo "  5. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
echo "If issues occur, rollback with:"
echo "  git reset --hard $CURRENT_COMMIT"
echo "  php artisan migrate:rollback"
echo "  php artisan cache:clear"
echo "  php artisan up"
echo ""
