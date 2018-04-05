#!/usr/bin/env bash

certbot --apache -n --agree-tos --email hidde@okit.com --domains oklibmagento1.okit.io

/usr/local/bin/install-sampledata
/usr/local/bin/install-magento

cp /var/www/oklib/SetupConfig.php /var/www/html/SetupConfig.php
php /var/www/html/SetupConfig.php

/sbin/my_init

