#!/bin/bash

################################################################################
# Local Build and Commit Helper
#
# This script should be run on your LOCAL machine (not production server)
# It builds assets and prepares them for deployment
#
# Usage: ./scripts/build-and-commit.sh "Your commit message"
################################################################################

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

log() {
    echo -e "${GREEN}✓${NC} $1"
}

info() {
    echo -e "${BLUE}→${NC} $1"
}

warn() {
    echo -e "${YELLOW}⚠${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
    exit 1
}

# Check if commit message provided
if [ -z "$1" ]; then
    error "Commit message required. Usage: ./scripts/build-and-commit.sh \"Your message\""
fi

COMMIT_MSG="$1"

# Check we're in project root
if [ ! -f "artisan" ]; then
    error "Run this script from the project root directory"
fi

# Check Node.js and npm are installed
if ! command -v npm &> /dev/null; then
    error "npm not found. Install Node.js and npm first."
fi

info "Starting build and commit process..."
echo ""

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
    warn "You have uncommitted changes. These will be included in the commit."
    git status --short
    echo ""
    read -p "Continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Cancelled."
        exit 0
    fi
fi

# Install/update dependencies if needed
if [ -f "package.json" ]; then
    log "Checking npm dependencies..."
    npm install
fi

# Clean old build directory and check if we have permission
if [ -d "public/build" ]; then
    info "Cleaning old build files..."
    if ! rm -rf public/build 2>/dev/null; then
        warn "Cannot remove old build files (likely created by Docker as root)"

        # Check if Docker is available
        if command -v docker-compose &> /dev/null && docker-compose ps app &> /dev/null 2>&1; then
            info "Running build inside Docker container..."
            docker-compose exec -T app sh -c "cd /var/www && rm -rf public/build && NODE_ENV=production npm run build"
            log "Build completed in Docker"
            # Skip the host build below
            HOST_BUILD=false
        else
            error "Cannot clean build directory. Either:\n  1. Run: sudo rm -rf public/build\n  2. Or start Docker and re-run this script"
        fi
    fi
fi

# Build production assets on host (if not already built in Docker)
if [ "${HOST_BUILD:-true}" = "true" ]; then
    log "Building production assets..."
    NODE_ENV=production npm run build
fi

# Check if build was successful
if [ ! -d "public/build" ]; then
    error "Build failed - public/build directory not found"
fi

log "Build successful!"
echo ""

# Add all changes including build files
info "Adding files to git..."
git add .

# Show what will be committed
echo ""
info "Files to be committed:"
git status --short
echo ""

# Commit
log "Creating commit..."
git commit -m "$COMMIT_MSG"

log "Commit created successfully!"
echo ""

# Show commit info
git log -1 --oneline

echo ""
info "Next steps:"
echo "  1. Push to GitHub: ${GREEN}git push origin main${NC}"
echo "  2. Deploy on server: ${GREEN}./scripts/deploy-production.sh${NC}"
echo ""
warn "Don't forget to push your changes!"
