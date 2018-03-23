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
    'okcheckout/general/environment' => getenv("OKENV"),
    'okcheckout/okcash/enabled' => '1',
    'okcheckout/okcash/okcashsecret_beta' => getenv("OKCASHSECRET"),
    'okcheckout/okcash/default_shipping_method' => 'flatrate_flatrate',
    'okcheckout/okcash/default_shipping_country' => 'NL',

    'okcheckout/okopen/enabled' => '1',
    'okcheckout/okopen/okopensecret_beta' => getenv("OKOPENSECRET"),

    'carriers/flatrate/type' => 'O' // flatrate per order
];

foreach ($config as $key => $value) {
    Mage::getModel('core/config')->saveConfig($key, $value);
}
Mage::getModel('core/config')->cleanCache();