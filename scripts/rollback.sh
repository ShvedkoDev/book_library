#!/bin/bash

################################################################################
# Rollback Script - Revert to previous commit
#
# Usage: ./scripts/rollback.sh [number_of_commits]
# Example: ./scripts/rollback.sh 1  (rollback 1 commit)
#          ./scripts/rollback.sh     (rollback 1 commit by default)
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

COMMITS=${1:-1}

if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: Run this script from app_root directory${NC}"
    exit 1
fi

echo -e "${YELLOW}⚠  WARNING: This will rollback $COMMITS commit(s)${NC}"
echo ""
echo "Current commit:"
git log -1 --oneline
echo ""
echo "Will rollback to:"
git log --oneline -1 HEAD~$COMMITS
echo ""
read -p "Are you sure you want to rollback? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Rollback cancelled."
    exit 0
fi

echo -e "${YELLOW}→${NC} Starting rollback..."

# Enable maintenance mode
echo -e "${GREEN}✓${NC} Enabling maintenance mode..."
php artisan down || true

# Rollback Git
echo -e "${GREEN}✓${NC} Rolling back Git to HEAD~$COMMITS..."
git reset --hard HEAD~$COMMITS

# Run full deployment
echo -e "${GREEN}✓${NC} Running deployment..."
./scripts/deploy-production.sh

echo ""
echo -e "${GREEN}✓ Rollback complete!${NC}"
echo "Current commit:"
git log -1 --oneline
