#!/bin/sh
set -e

# Fix permissions for writable directories
# We use chown to ensure www-data (Apache user) can write to these directories
# This runs at runtime, so it fixes permissions even for mounted volumes
echo "Fixing permissions for data, logs, and uploads..."

# Ensure directories exist
mkdir -p /var/www/html/data
mkdir -p /var/www/html/logs
mkdir -p /var/www/html/uploads/avatars

# Set ownership to www-data
chown -R www-data:www-data /var/www/html/data
chown -R www-data:www-data /var/www/html/logs
chown -R www-data:www-data /var/www/html/uploads

# Execute the main container command
exec "$@"
