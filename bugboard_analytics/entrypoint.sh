#!/bin/bash
set -e

# Ensure data directory exists and is writable by www-data
mkdir -p /var/www/data
chown -R www-data:www-data /var/www/data
chmod 750 /var/www/data

# Start Apache in the foreground
exec apache2-foreground
