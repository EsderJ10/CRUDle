#!/bin/sh
set -e

# Check if PUID/PGID are passed and modify www-data if so
if [ ! -z "$PUID" ]; then
    if [ ! -z "$PGID" ]; then
        groupmod -o -g "$PGID" www-data
    fi
    usermod -o -u "$PUID" www-data
fi

# Ensure directories exist
mkdir -p /var/www/html/data
mkdir -p /var/www/html/logs
mkdir -p /var/www/html/uploads/avatars

# Fix permissions
if [ "$(stat -c '%U' /var/www/html/uploads)" != "www-data" ]; then
    chown -R www-data:www-data /var/www/html/uploads
fi

if [ "$(stat -c '%U' /var/www/html/data)" != "www-data" ]; then
    chown -R www-data:www-data /var/www/html/data
fi

if [ "$(stat -c '%U' /var/www/html/logs)" != "www-data" ]; then
    chown -R www-data:www-data /var/www/html/logs
fi

# Execute the command
exec "$@"
