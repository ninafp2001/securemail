#!/bin/bash
set -e

if [ -d /data ]; then
    chown -R www-data:www-data /data
    chmod -R 775 /data
fi

mkdir -p /data/sessions /data/painel/sessions
for dir in bol hostgator hostinger kinghost terra-fisico terra-juridico uol-fisico uol-pro locaweb cpanel-webmail; do
    mkdir -p "/data/providers/$dir"
done
chown -R www-data:www-data /data 2>/dev/null || true

exec apache2-foreground
