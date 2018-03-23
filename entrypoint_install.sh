#!/usr/bin/env bash

/usr/local/bin/install-sampledata
/usr/local/bin/install-magento

php /var/www/html/SetupConfig.php

/sbin/my_init

