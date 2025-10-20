#!/bin/bash

# Docker entrypoint script to handle permissions
# This ensures the container can write to necessary directories
# while keeping host files owned by the host user

# Create necessary directories if they don't exist
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/framework/{sessions,views,cache}
mkdir -p /var/www/bootstrap/cache

# Ensure storage and cache directories are writable
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Ensure www-data can write to these directories
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Execute the main container command directly
exec "$@"