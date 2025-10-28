#!/bin/bash

# Set permissions (skip if already set)
mkdir -p /tmp 2>/dev/null || true

# Start PHP server
exec php -S 0.0.0.0:${PORT:-8000} index.php