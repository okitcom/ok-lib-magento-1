<?php
/**
 * Created by PhpStorm.
 * Date: 3/23/18
 */
// Change current directory to the directory of current script
chdir(dirname(__FILE__));

require 'app/bootstrap.php';
require 'app/Mage.php';

umask(0);
Mage::app();

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

$config = [
    'web/secure/base_url' => getenv("MAGENTO_URL"),
    'web/secure/use_in_frontend' => getenv("MAGENTO_USE_SECURE"),
    'web/secure/use_in_adminhtml' => getenv("MAGENTO_USE_SECURE"),

    'okcheckout/general/environment' => getenv("OKENV"),
    'okcheckout/okcash/enabled' => '1',
    'okcheckout/okcash/okcashsecret_local' => getenv("OKCASHSECRET"),
    'okcheckout/okcash/default_shipping_method' => 'flatrate_flatrate',
    'okcheckout/okcash/default_shipping_country' => 'NL',

    'okcheckout/okopen/enabled' => '1',
    'okcheckout/okopen/okopensecret_local' => getenv("OKOPENSECRET"),

    'carriers/flatrate/type' => 'O' // flatrate per order
];

foreach ($config as $key => $value) {
    Mage::getModel('core/config')->saveConfig($key, $value);
}
Mage::getModel('core/config')->cleanCache();
Mage::app()->getCacheInstance()->flush();
