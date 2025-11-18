#!/bin/bash

################################################################################
# Pre-Deployment Readiness Check
#
# Verifies the server environment is ready for deployment
# Usage: ./scripts/check-deployment-readiness.sh
################################################################################

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

ERRORS=0
WARNINGS=0

check_pass() {
    echo -e "${GREEN}✓${NC} $1"
}

check_fail() {
    echo -e "${RED}✗${NC} $1"
    ((ERRORS++))
}

check_warn() {
    echo -e "${YELLOW}⚠${NC} $1"
    ((WARNINGS++))
}

echo "=================================="
echo "Deployment Readiness Check"
echo "=================================="
echo ""

# Check we're in the right directory
echo "📂 Checking directory structure..."
if [ -f "artisan" ]; then
    check_pass "In app_root directory"
else
    check_fail "Not in app_root directory (artisan not found)"
fi

if [ -d "../public_html" ]; then
    check_pass "public_html directory exists"
else
    check_fail "public_html directory not found"
fi

# Check Git
echo ""
echo "🔧 Checking Git..."
if command -v git &> /dev/null; then
    check_pass "Git is installed"

    if git rev-parse --git-dir > /dev/null 2>&1; then
        check_pass "Git repository initialized"

        BRANCH=$(git rev-parse --abbrev-ref HEAD)
        check_pass "Current branch: $BRANCH"

        if git diff-index --quiet HEAD --; then
            check_pass "No uncommitted changes"
        else
            check_warn "Uncommitted changes detected"
        fi

        git fetch origin &> /dev/null || check_warn "Could not fetch from origin"

    else
        check_fail "Not a Git repository"
    fi
else
    check_fail "Git not installed"
fi

# Check PHP
echo ""
echo "🐘 Checking PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    check_pass "PHP installed: $PHP_VERSION"

    # Check required extensions
    REQUIRED_EXTENSIONS=("pdo" "mbstring" "openssl" "tokenizer" "xml" "ctype" "json")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -q "^$ext$"; then
            check_pass "PHP extension: $ext"
        else
            check_fail "Missing PHP extension: $ext"
        fi
    done
else
    check_fail "PHP not installed"
fi

# Check Composer
echo ""
echo "📦 Checking Composer..."
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version 2>/dev/null | head -n1)
    check_pass "Composer installed: $COMPOSER_VERSION"
elif [ -f "composer.phar" ]; then
    check_pass "composer.phar found"
else
    check_fail "Composer not found"
fi

# Check pre-built assets
echo ""
echo "📦 Checking pre-built assets..."
if [ -d "public/build" ]; then
    check_pass "Pre-built assets exist (public/build)"
else
    check_warn "public/build not found. Build assets locally before pushing to Git."
fi

# Check Laravel files
echo ""
echo "🔧 Checking Laravel files..."
if [ -f ".env" ]; then
    check_pass ".env file exists"

    if grep -q "APP_KEY=base64:" .env; then
        check_pass "APP_KEY is set"
    else
        check_fail "APP_KEY not set (run: php artisan key:generate)"
    fi

    if grep -q "APP_ENV=production" .env; then
        check_pass "APP_ENV=production"
    else
        check_warn "APP_ENV is not 'production'"
    fi

    if grep -q "APP_DEBUG=false" .env; then
        check_pass "APP_DEBUG=false"
    else
        check_warn "APP_DEBUG should be 'false' in production"
    fi
else
    check_fail ".env file not found"
fi

if [ -f "composer.json" ]; then
    check_pass "composer.json exists"
else
    check_fail "composer.json not found"
fi


# Check directories and permissions
echo ""
echo "📁 Checking directories and permissions..."
DIRS=("storage" "storage/logs" "storage/framework" "storage/app" "bootstrap/cache")
for dir in "${DIRS[@]}"; do
    if [ -d "$dir" ]; then
        if [ -w "$dir" ]; then
            check_pass "$dir is writable"
        else
            check_fail "$dir exists but is not writable"
        fi
    else
        check_fail "$dir directory not found"
    fi
done

# Check database connection
echo ""
echo "🗄️  Checking database connection..."
if php artisan db:show &> /dev/null; then
    check_pass "Database connection successful"
else
    check_warn "Could not connect to database (check credentials)"
fi

# Check public_html contents
echo ""
echo "🌐 Checking public_html..."
PUBLIC_HTML="../public_html"
if [ -f "$PUBLIC_HTML/index.php" ]; then
    check_pass "index.php exists in public_html"
else
    check_warn "index.php not found in public_html"
fi

if [ -L "$PUBLIC_HTML/storage" ]; then
    check_pass "storage symlink exists"
    TARGET=$(readlink -f "$PUBLIC_HTML/storage")
    if [ -d "$TARGET" ]; then
        check_pass "storage symlink points to valid directory"
    else
        check_warn "storage symlink target doesn't exist"
    fi
else
    check_warn "storage symlink not found in public_html"
fi

# Summary
echo ""
echo "=================================="
echo "Summary"
echo "=================================="

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed! Ready for deployment.${NC}"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠ $WARNINGS warning(s) found. Review before deploying.${NC}"
    exit 0
else
    echo -e "${RED}✗ $ERRORS error(s) and $WARNINGS warning(s) found.${NC}"
    echo -e "${RED}Please fix errors before deploying.${NC}"
    exit 1
fi
