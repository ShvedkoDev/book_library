#!/bin/bash

# Quick cache clearing script for production
# Run this after any deployment or when you see component not found errors

echo "🧹 Clearing all caches..."

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
php artisan livewire:discover
php artisan filament:clear-cache

echo "✅ All caches cleared!"
echo "🔄 Please refresh your browser and try again."
