<?php

use OK\Credentials\CashCredentials;
use OK\Credentials\Environment\BetaEnvironment;
use OK\Credentials\Environment\DevelopmentEnvironment;
use OK\Credentials\Environment\ProductionEnvironment;
use OK\Credentials\OpenCredentials;
use OK\Service\Cash;
use OK\Service\Open;

/**
 * Created by PhpStorm.
 * Date: 3/5/18
 */

class Okitcom_OkLibMagento_Helper_Oklib extends Mage_Core_Helper_Abstract
{

    const SERVICE_TYPE_CASH = "cash";
    const SERVICE_TYPE_OPEN = "open";

    /**
     * Get a OK Cash client.
     * @return Cash
     */
    public function getCashClient() {
        Okitcom_OkLibMagento_Model_Observer::init();

        $cashSecret = $this->getSecretKey(self::SERVICE_TYPE_CASH);
        return new Cash(
            new CashCredentials("", $cashSecret, $this->getEnvironment())
        );
    }

    public function getOpenClient() {
        Okitcom_OkLibMagento_Model_Observer::init();

        $openSecret = $this->getSecretKey(self::SERVICE_TYPE_OPEN);
        return new Open(
            new OpenCredentials("", $openSecret, $this->getEnvironment())
        );
    }

    /**
     * @return \OK\Credentials\Environment\Environment current environment
     */
    public function getEnvironment() {
        $env = Mage::helper('oklibmagento/config')->getOkGeneralValue("environment");
        switch ($env) {
            case "secure":
                return new ProductionEnvironment();
            case "dev":
                return new DevelopmentEnvironment();
            case "beta":
                return new BetaEnvironment();
        }
        return null;
    }

    /**
     * Whether our service is enabled
     * @param $type string service
     * @return bool
     */
    public function isServiceEnabled($type) {
        $configHelper = Mage::helper('oklibmagento/config');
        switch ($type) {
            case self::SERVICE_TYPE_OPEN:
                return boolval($configHelper->getOkOpenValue("enabled"));
                break;
            case self::SERVICE_TYPE_CASH:
                return boolval($configHelper->getOkCashValue("enabled"));
                break;
            default:
                return false;
        }
    }

    /**
     * @param $type string service type, open or cash.
     * @return null|string secret key, if any
     */
    public function getSecretKey($type) {
        $configHelper = Mage::helper('oklibmagento/config');
        $env = $configHelper->getOkGeneralValue("environment");
        $suffix = $env;
        switch ($type) {
            case self::SERVICE_TYPE_OPEN:
                return $configHelper->getOkOpenValue("okopensecret_" . $suffix);
                break;
            case self::SERVICE_TYPE_CASH:
                return $configHelper->getOkCashValue("okcashsecret_" . $suffix);
                break;
            default:
                return null;
        }
    }

}