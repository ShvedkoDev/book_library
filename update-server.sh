#!/bin/bash
################################################################################
# Server-Side Update Script (Part 1)
# Run this ON THE PRODUCTION SERVER in ~/app_root
#
# This script handles the server-side code update.
# After this, you'll upload built assets from your local machine.
################################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Print functions
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

# Main script
print_header "Micronesian Teachers Digital Library - Server Update"

# Confirmation
read -p "Update production code? This will enable maintenance mode. (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Update cancelled."
    exit 0
fi

# Check environment
print_step "1/10" "Checking environment..."
if [ ! -f "artisan" ]; then
    print_error "artisan not found. Are you in ~/app_root?"
    exit 1
fi
print_success "Environment OK"

# Create backup
print_step "2/10" "Creating backup..."
BACKUP_DIR=~/backups/$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"
cp .env "$BACKUP_DIR/.env.backup"
print_success "Backup directory: $BACKUP_DIR"

# Backup database
print_step "3/10" "Backing up database..."
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)

if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ]; then
    mysqldump -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_DIR/database.sql" 2>/dev/null && print_success "Database backed up" || print_warning "Database backup requires password (enter it now or skip)"
else
    print_warning "Could not read database credentials from .env"
fi

# Show current version
print_step "4/10" "Current version:"
CURRENT_COMMIT=$(git rev-parse --short HEAD)
echo "  → $CURRENT_COMMIT $(git log -1 --format=%s)"

# Enable maintenance mode
print_step "5/10" "Enabling maintenance mode..."
php artisan down --message="Updating library with new features! Back in 10 minutes." --refresh=15
print_success "Site in maintenance mode"

# Pull changes
print_step "6/10" "Fetching latest changes..."
git fetch origin

echo ""
echo "Changes to be applied:"
git log HEAD..origin/main --oneline --no-merges | head -10
echo ""

read -p "Apply these updates? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Update cancelled. Bringing site back online..."
    php artisan up
    exit 0
fi

print_step "7/10" "Pulling latest code..."
git pull origin main
print_success "Code updated to: $(git rev-parse --short HEAD)"

# Update dependencies
print_step "8/10" "Updating Composer dependencies..."
if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --optimize-autoloader --no-interaction
    print_success "Dependencies updated"
else
    print_warning "Composer not found in PATH, trying php composer.phar..."
    if [ -f "composer.phar" ]; then
        php composer.phar install --no-dev --optimize-autoloader --no-interaction
        print_success "Dependencies updated"
    else
        print_error "Composer not found. Please install dependencies manually."
    fi
fi

# Run migrations
print_step "9/10" "Running database migrations..."
php artisan migrate --force
print_success "Migrations complete"

# Storage link
print_step "10/10" "Updating storage link..."
php artisan storage:link 2>/dev/null || print_warning "Storage link already exists"

# Instructions for asset upload
echo ""
print_header "IMPORTANT: Upload Built Assets Now"

echo -e "${YELLOW}From your LOCAL machine (WSL), run these commands:${NC}"
echo ""
echo -e "${GREEN}cd /home/gena/book_library${NC}"
echo -e "${GREEN}./deploy-assets.sh${NC}"
echo ""
echo -e "${YELLOW}Or manually:${NC}"
echo -e "${GREEN}npm install && npm run build${NC}"
echo -e "${GREEN}rsync -avz public/build/ $(whoami)@$(hostname):~/public_html/build/${NC}"
echo -e "${GREEN}rsync -avz public/library-assets/ $(whoami)@$(hostname):~/public_html/library-assets/${NC}"
echo ""
echo -e "${BLUE}Press ENTER after you've uploaded the assets...${NC}"
read

# Clear caches
echo ""
print_step "Final" "Clearing and rebuilding caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
print_success "Caches rebuilt"

# Disable maintenance mode
echo ""
print_step "Final" "Bringing site back online..."
php artisan up
print_success "Site is live!"

# Summary
echo ""
print_header "Update Complete!"

echo "Previous version: $CURRENT_COMMIT"
echo "Current version:  $(git rev-parse --short HEAD)"
echo "Backup location:  $BACKUP_DIR"
echo ""
echo -e "${GREEN}✓ Server-side update complete${NC}"
echo ""
echo "Next steps:"
echo "  1. Test site: https://micronesian.school"
echo "  2. Test search and books"
echo "  3. Check admin panel: https://micronesian.school/admin"
echo "  4. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
echo "If issues occur, rollback with:"
echo "  git reset --hard $CURRENT_COMMIT"
echo "  php artisan migrate:rollback"
echo "  php artisan cache:clear && php artisan up"
echo ""
