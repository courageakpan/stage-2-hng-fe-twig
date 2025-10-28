#!/bin/bash

# Set permissions for data directory
mkdir -p /tmp
chmod 755 /tmp

# Initialize database if it doesn't exist
if [ ! -f "/tmp/data.json" ]; then
    php setup.php
fi

# Check if we're running in Apache or standalone
if [ -n "$APACHE_CONFDIR" ]; then
    # Running in Apache Docker container
    echo "Starting Apache server..."
    # Configure Apache to listen on the Railway port
    sed -i "s/Listen 80/Listen ${PORT:-8000}/" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT:-8000}>/" /etc/apache2/sites-available/000-default.conf
    exec apache2-foreground
else
    # Running standalone PHP server
    echo "Starting PHP standalone server..."
    exec php -S 0.0.0.0:${PORT:-8000} index.php
fi