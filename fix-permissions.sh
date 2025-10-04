#!/bin/bash

# Script to fix permissions for development
# Run this on the host to fix IDE access

echo "This script will fix file permissions for IDE access"
echo "It requires sudo access to change file ownership"
echo ""

# Change ownership to current user for IDE access
echo "Changing ownership to gena:gena for all project files..."
sudo chown -R gena:gena /home/gena/book_library

# Make specific directories writable
echo "Setting proper permissions for Laravel directories..."
chmod -R 775 storage bootstrap/cache

echo ""
echo "Permissions fixed!"
echo "You can now edit files in your IDE."
echo ""
echo "Note: When running Docker, the container will use www-data internally."
echo "This is handled by the Docker volume mapping."