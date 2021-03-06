<?php

use OK\Credentials\CashCredentials;
use OK\Credentials\Environment\BetaEnvironment;
use OK\Credentials\Environment\DevelopmentEnvironment;
use OK\Credentials\Environment\LocalEnvironment;
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
     * @param null $store
     * @return Cash
     */
    public function getCashClient($store = null) {
        Okitcom_OkLibMagento_Model_Observer::init();

        $cashSecret = $this->getSecretKey(self::SERVICE_TYPE_CASH, $store);
        return new Cash(
            new CashCredentials("", $cashSecret, $this->getEnvironment($store))
        );
    }

    /**
     * @param null $store
     * @return Open
     */
    public function getOpenClient($store = null) {
        Okitcom_OkLibMagento_Model_Observer::init();

        $openSecret = $this->getSecretKey(self::SERVICE_TYPE_OPEN, $store);
        return new Open(
            new OpenCredentials("", $openSecret, $this->getEnvironment($store))
        );
    }

    /**
     * @param null $store
     * @return \OK\Credentials\Environment\Environment current environment
     */
    public function getEnvironment($store = null) {
        $env = Mage::helper('oklibmagento/config')->getOkGeneralValue("environment", $store);
        switch ($env) {
            case "secure":
                return new ProductionEnvironment();
            case "dev":
                return new DevelopmentEnvironment();
            case "beta":
                return new BetaEnvironment();
            case "local":
                return new LocalEnvironment();
        }
        return null;
    }

    /**
     * Whether our service is enabled
     * @param $type string service
     * @param null $store
     * @return bool
     */
    public function isServiceEnabled($type, $store = null) {
        $configHelper = Mage::helper('oklibmagento/config');
        switch ($type) {
            case self::SERVICE_TYPE_OPEN:
                return boolval($configHelper->getOkOpenValue("enabled", $store));
                break;
            case self::SERVICE_TYPE_CASH:
                return boolval($configHelper->getOkCashValue("enabled", $store));
                break;
            default:
                return false;
        }
    }

    /**
     * @param $type string service type, open or cash.
     * @param null $store
     * @return null|string secret key, if any
     */
    public function getSecretKey($type, $store = null) {
        $configHelper = Mage::helper('oklibmagento/config');
        $env = $configHelper->getOkGeneralValue("environment", $store);
        $suffix = $env;
        switch ($type) {
            case self::SERVICE_TYPE_OPEN:
                return $configHelper->getOkOpenValue("okopensecret_" . $suffix, $store);
                break;
            case self::SERVICE_TYPE_CASH:
                return $configHelper->getOkCashValue("okcashsecret_" . $suffix, $store);
                break;
            default:
                return null;
        }
    }

}