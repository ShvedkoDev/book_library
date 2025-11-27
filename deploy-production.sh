#!/bin/bash

# Production Deployment Script for Micronesian Teachers Digital Library
# This script handles cache clearing and optimization after deployment

echo "🚀 Starting production deployment tasks..."

# Clear all caches
echo "🧹 Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Clear Livewire component cache (important for Filament pages)
echo "⚡ Clearing Livewire cache..."
php artisan livewire:discover

# Clear Filament cache
echo "📋 Clearing Filament cache..."
php artisan filament:clear-cache
php artisan filament:optimize

# Optimize for production
echo "⚙️ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize Composer autoloader
echo "🎯 Optimizing Composer autoloader..."
composer dump-autoload --optimize --no-dev

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/views

# Restart queue workers if they exist
echo "👷 Restarting queue workers..."
php artisan queue:restart 2>/dev/null || echo "   No queue workers to restart"

echo "✅ Production deployment complete!"
echo ""
echo "📝 Next steps:"
echo "   1. Test the admin panel: https://micronesian.school/admin"
echo "   2. Test the library: https://micronesian.school/library"
echo "   3. Check error logs if issues persist: storage/logs/laravel.log"
