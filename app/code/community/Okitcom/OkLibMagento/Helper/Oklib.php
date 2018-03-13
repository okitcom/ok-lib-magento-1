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

    /**
     * Get a OK Cash client.
     * @return Cash
     */
    public function getCashClient() {
        Okitcom_OkLibMagento_Model_Observer::init();

        $cashSecret = Mage::helper('oklibmagento/config')->getOkCashValue("okcashsecret");
        return new Cash(
            new CashCredentials("", $cashSecret, $this->getEnvironment())
        );
    }

    public function getOpenClient() {
        Okitcom_OkLibMagento_Model_Observer::init();

        $openSecret = Mage::helper('oklibmagento/config')->getOkOpenValue("okopensecret");
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

}