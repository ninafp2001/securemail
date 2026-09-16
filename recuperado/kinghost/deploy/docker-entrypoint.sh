#!/bin/bash
set -e

if [ -d /data ]; then
    chown -R www-data:www-data /data
    chmod -R 775 /data
fi

mkdir -p /data/sessions
chown -R www-data:www-data /data 2>/dev/null || true

exec apache2-foreground
