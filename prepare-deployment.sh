#!/bin/bash
# Deployment Preparation Script
# Prepares the Laravel application for manual deployment to Hostinger

set -e  # Exit on error

echo "🚀 Preparing application for deployment..."
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found. Are you in the project root?${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Installing Composer dependencies (production mode)...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${GREEN}✓ Composer dependencies installed${NC}"
echo ""

echo -e "${YELLOW}Step 2: Building frontend assets...${NC}"
npm install
npm run build

echo -e "${GREEN}✓ Frontend assets built${NC}"
echo ""

echo -e "${YELLOW}Step 3: Clearing development caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 4: Creating deployment archive...${NC}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ARCHIVE_NAME="library-deploy-${TIMESTAMP}.tar.gz"

# Create archive excluding development files
tar --exclude='node_modules' \
    --exclude='.git' \
    --exclude='.env' \
    --exclude='.env.example' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/debugbar' \
    --exclude='tests' \
    --exclude='.phpunit.result.cache' \
    --exclude='phpunit.xml' \
    --exclude='docker' \
    --exclude='docker-compose.yml' \
    --exclude='Dockerfile' \
    --exclude='*.md' \
    --exclude='prepare-deployment.sh' \
    -czf "${ARCHIVE_NAME}" .

echo -e "${GREEN}✓ Archive created: ${ARCHIVE_NAME}${NC}"
echo ""

# Calculate archive size
ARCHIVE_SIZE=$(du -h "${ARCHIVE_NAME}" | cut -f1)

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✅ Deployment package ready!${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📦 Archive: ${ARCHIVE_NAME}"
echo "📊 Size: ${ARCHIVE_SIZE}"
echo ""
echo "Next steps:"
echo "1. Upload this archive to your Hostinger server via SFTP"
echo "2. SSH into your server and extract:"
echo "   cd domains/micronesian.school/public_html"
echo "   tar -xzf ${ARCHIVE_NAME}"
echo "   rm ${ARCHIVE_NAME}"
echo ""
echo "3. Follow the deployment checklist in DEPLOYMENT_CHECKLIST.md"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Restore development dependencies
echo ""
echo -e "${YELLOW}Restoring development dependencies...${NC}"
composer install

echo -e "${GREEN}✓ Development environment restored${NC}"
echo ""
echo "🎉 Done!"
