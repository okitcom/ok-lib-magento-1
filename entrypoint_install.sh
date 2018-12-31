#!/usr/bin/env bash
echo "[entry point] start"

while ! mysqladmin ping -h"$MYSQL_HOST" --silent; do
    echo "[entry point] waiting for db to start"
    sleep 1
done
echo "[entry point] mysql started"

echo "[entry point] Installing magento"
/usr/local/bin/install-magento

echo "[entry point] Run OK config"
php /var/www/html/SetupConfig.php

echo "[entry point] Add cron"
cat <(crontab -l) <(echo "* * * * * sh /var/www/html/cron.sh") | crontab -

echo "[entry point] done"
exec "$@"