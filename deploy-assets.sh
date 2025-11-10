#!/bin/bash
################################################################################
# Deploy Built Assets to Production (Part 2)
# Run this FROM YOUR LOCAL MACHINE (WSL) after server update
#
# This script:
# 1. Pulls latest code locally
# 2. Builds production assets
# 3. Uploads to production server
################################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration - UPDATE THESE VALUES
SERVER_USER="your-username"              # Your SSH username
SERVER_HOST="micronesian.school"         # Your server hostname or IP
PROJECT_PATH="/home/gena/book_library"   # Local project path

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
print_header "Deploy Assets to Production"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Not in Laravel project root. Navigating to project..."
    cd "$PROJECT_PATH" || exit 1
fi

# Check configuration
if [ "$SERVER_USER" = "your-username" ] || [ "$SERVER_HOST" = "micronesian.school" ]; then
    print_warning "Please edit this script and set SERVER_USER and SERVER_HOST"
    echo ""
    echo "Edit the file: $0"
    echo "Update these lines near the top:"
    echo "  SERVER_USER=\"your-actual-username\""
    echo "  SERVER_HOST=\"your-server.com\""
    echo ""
    read -p "Continue anyway with default values? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_warning "Deployment cancelled. Please update configuration."
        exit 0
    fi
fi

# Confirm deployment
echo "Deploying to: ${SERVER_USER}@${SERVER_HOST}"
read -p "Continue? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Deployment cancelled."
    exit 0
fi

# Step 1: Pull latest code
print_step "1/5" "Pulling latest code..."
if git pull origin main; then
    print_success "Code updated"
else
    print_warning "Git pull failed or no changes"
fi

# Step 2: Install npm dependencies
print_step "2/5" "Installing npm dependencies..."
if command -v npm >/dev/null 2>&1; then
    npm install
    print_success "Dependencies installed"
else
    print_error "npm not found. Please install Node.js"
    exit 1
fi

# Step 3: Build production assets
print_step "3/5" "Building production assets..."
npm run build

if [ $? -eq 0 ]; then
    print_success "Assets built successfully"
else
    print_error "Build failed"
    exit 1
fi

# Verify build output
if [ ! -d "public/build" ]; then
    print_error "Build directory not found at public/build"
    exit 1
fi

print_success "Build directory verified"

# Step 4: Upload to production
print_header "Uploading to Production"

# Check if rsync is available
if ! command -v rsync >/dev/null 2>&1; then
    print_warning "rsync not found. Installing rsync is recommended for faster uploads."
    echo "Install with: sudo apt-get install rsync"
    echo ""
    read -p "Use scp instead? (slower) (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        USE_SCP=1
    else
        print_error "Cannot upload without rsync or scp"
        exit 1
    fi
fi

if [ "${USE_SCP:-0}" -eq 1 ]; then
    # Upload with scp
    print_step "4/5" "Uploading with scp (this may take a while)..."

    scp -r public/build/* "${SERVER_USER}@${SERVER_HOST}:~/public_html/build/" && \
    scp -r public/library-assets/* "${SERVER_USER}@${SERVER_HOST}:~/public_html/library-assets/" && \
    scp public/index.php "${SERVER_USER}@${SERVER_HOST}:~/public_html/" && \
    scp public/.htaccess "${SERVER_USER}@${SERVER_HOST}:~/public_html/"

    if [ $? -eq 0 ]; then
        print_success "Files uploaded via scp"
    else
        print_error "Upload failed"
        exit 1
    fi
else
    # Upload with rsync (recommended)
    print_step "4/5" "Uploading build folder..."
    rsync -avz --progress \
        --exclude='*.map' \
        --delete \
        public/build/ \
        "${SERVER_USER}@${SERVER_HOST}:~/public_html/build/"

    if [ $? -ne 0 ]; then
        print_error "Build upload failed"
        exit 1
    fi
    print_success "Build folder uploaded"

    print_step "4/5" "Uploading library assets..."
    rsync -avz --progress \
        --delete \
        public/library-assets/ \
        "${SERVER_USER}@${SERVER_HOST}:~/public_html/library-assets/"

    if [ $? -ne 0 ]; then
        print_error "Library assets upload failed"
        exit 1
    fi
    print_success "Library assets uploaded"

    print_step "4/5" "Uploading public files..."
    rsync -avz \
        public/index.php \
        public/.htaccess \
        "${SERVER_USER}@${SERVER_HOST}:~/public_html/"

    if [ $? -ne 0 ]; then
        print_warning "Public files upload failed (may not be critical)"
    else
        print_success "Public files uploaded"
    fi
fi

# Step 5: Clear server caches
print_step "5/5" "Clearing server caches..."
echo ""
echo "Connecting to server to clear caches..."

ssh "${SERVER_USER}@${SERVER_HOST}" << 'ENDSSH'
cd ~/app_root
php artisan view:clear
php artisan cache:clear
echo "✓ Caches cleared on server"
ENDSSH

if [ $? -eq 0 ]; then
    print_success "Server caches cleared"
else
    print_warning "Could not clear server caches (you may need to do this manually)"
fi

# Success summary
print_header "Deployment Complete!"

echo "Assets have been deployed to production:"
echo "  • Build folder → ~/public_html/build/"
echo "  • Library assets → ~/public_html/library-assets/"
echo "  • Public files → ~/public_html/"
echo ""
echo -e "${GREEN}✓ Asset deployment successful!${NC}"
echo ""
echo "Next steps:"
echo "  1. Visit: https://micronesian.school"
echo "  2. Hard refresh browser: Ctrl+Shift+R"
echo "  3. Check CSS/JS are loading correctly"
echo "  4. Test functionality"
echo ""
echo "If assets don't load:"
echo "  ssh ${SERVER_USER}@${SERVER_HOST}"
echo "  cd ~/app_root"
echo "  php artisan view:clear"
echo "  php artisan cache:clear"
echo ""
