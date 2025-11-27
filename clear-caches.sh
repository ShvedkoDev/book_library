#!/bin/bash

# Quick cache clearing script for production
# Run this after any deployment or when you see component not found errors

echo "🧹 Clearing all caches..."

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Clear compiled views and optimize
php artisan optimize:clear

# Clear Filament cache
php artisan filament:clear-cache 2>/dev/null || echo "   Filament cache cleared (if available)"

# Discover packages (includes Livewire components)
php artisan package:discover --ansi

echo ""
echo "✅ All caches cleared!"
echo "🔄 Please refresh your browser and try again."
