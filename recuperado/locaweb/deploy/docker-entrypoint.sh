#!/bin/bash
set -e

# Volume Fly monta /data como root; Apache (www-data) precisa escrever audit/stats.
if [ -d /data ]; then
    chown -R www-data:www-data /data
    chmod -R 775 /data
fi

mkdir -p /data/sessions var/data var/rate
chown -R www-data:www-data var/data var/rate 2>/dev/null || true
chmod -R 775 var/data var/rate 2>/dev/null || true

exec apache2-foreground
