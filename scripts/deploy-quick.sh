#!/bin/bash

################################################################################
# Quick Deployment Script - For minor updates (no dependency changes)
#
# Use this for:
# - Code changes only
# - View/template updates
# - Minor bug fixes
#
# Do NOT use this for:
# - Database migrations
# - New composer/npm packages
# - Major updates
#
# Usage: ./scripts/deploy-quick.sh
################################################################################

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${GREEN}✓${NC} $1"
}

info() {
    echo -e "${BLUE}→${NC} $1"
}

if [ ! -f "artisan" ]; then
    echo "Error: Run this script from app_root directory"
    exit 1
fi

PUBLIC_HTML="../public_html"

info "Quick deployment starting..."

# Pull latest code (includes pre-built assets)
log "Pulling from GitHub..."
git pull origin $(git rev-parse --abbrev-ref HEAD)

# Copy pre-built assets to public_html
log "Copying build files..."
cp -f public/index.php "$PUBLIC_HTML/"
cp -rf public/build "$PUBLIC_HTML/"
cp -rf public/css "$PUBLIC_HTML/" 2>/dev/null || true
cp -rf public/js "$PUBLIC_HTML/" 2>/dev/null || true

# Clear caches
log "Clearing caches..."
php artisan view:clear
php artisan cache:clear

log "Quick deployment complete!"
git log -1 --pretty=format:"Deployed: %h - %s"
echo ""
