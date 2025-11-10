#!/bin/bash
################################################################################
# Build Assets and Commit to GitHub
# Run this FROM YOUR LOCAL MACHINE (WSL)
#
# This script:
# 1. Pulls latest code
# 2. Builds production assets
# 3. Commits built assets to git
# 4. Pushes to GitHub
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

print_header "Build and Commit Production Assets"

# Check we're in the right directory
print_step "1/6" "Checking environment..."
if [ ! -f "artisan" ]; then
    print_error "Not in Laravel project root"
    exit 1
fi

if [ ! -f "package.json" ]; then
    print_error "package.json not found"
    exit 1
fi

print_success "Environment OK"

# Pull latest changes
print_step "2/6" "Pulling latest code..."
git pull origin main
print_success "Code updated"

# Install npm dependencies
print_step "3/6" "Installing npm dependencies..."
if command -v npm >/dev/null 2>&1; then
    npm install
    print_success "Dependencies installed"
else
    print_error "npm not found. Install Node.js first."
    exit 1
fi

# Build production assets
print_step "4/6" "Building production assets..."
npm run build

if [ $? -eq 0 ]; then
    print_success "Build complete"
else
    print_error "Build failed"
    exit 1
fi

# Verify build output
if [ ! -d "public/build" ]; then
    print_error "Build directory not found"
    exit 1
fi

print_success "Build verified"

# Check if there are changes to commit
print_step "5/6" "Checking for changes..."
if [[ -z $(git status -s public/build public/library-assets) ]]; then
    print_warning "No asset changes to commit"
    echo ""
    echo "The build completed but no files changed."
    echo "This means assets are already up to date."
    echo ""
    exit 0
fi

echo ""
echo "Changed files:"
git status -s public/build public/library-assets
echo ""

read -p "Commit and push these changes? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Changes not committed"
    echo ""
    echo "Built assets are in public/build but not committed."
    echo "Run this script again when ready to commit."
    exit 0
fi

# Commit the changes
print_step "6/6" "Committing and pushing..."
git add public/build public/library-assets

# Create commit message
COMMIT_MSG="build: Update production assets - $(date '+%Y-%m-%d %H:%M')

Built production assets with latest changes.

Generated files:
$(git diff --cached --stat public/build public/library-assets | tail -1)

🤖 Generated with [Claude Code](https://claude.com/claude-code)
"

git commit -m "$COMMIT_MSG"
git push origin main

print_success "Changes committed and pushed!"

# Final instructions
print_header "Next Steps"

echo "Built assets have been pushed to GitHub."
echo ""
echo -e "${YELLOW}Now update production server:${NC}"
echo ""
echo -e "${GREEN}ssh your-username@micronesian.school${NC}"
echo -e "${GREEN}cd ~/app_root${NC}"
echo -e "${GREEN}./update-simple.sh${NC}"
echo ""
echo "The server will pull the changes and copy assets to public_html."
echo ""
