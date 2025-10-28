#!/bin/bash

# Set permissions for data directory
mkdir -p /tmp
chmod 755 /tmp

# Initialize database if it doesn't exist
if [ ! -f "/tmp/data.json" ]; then
    php setup.php
fi

# Start the PHP server
exec php -S 0.0.0.0:${PORT:-8000} index.php