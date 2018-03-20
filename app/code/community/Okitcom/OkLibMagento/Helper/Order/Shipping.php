<?php

use OK\Model\Cash\Transaction;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Order_Shipping extends Okitcom_OkLibMagento_Helper_Order_Processor {


    function process(Mage_Sales_Model_Quote $quote, Transaction $transaction) {
        $shippingMethod = Mage::helper('oklibmagento/config')->getOkCashValue("default_shipping_method");
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($shippingMethod);

        $carrier = explode("_", $shippingMethod)[0];

        $rates = $shippingAddress->getGroupedAllShippingRates();
        if (isset($rates[$carrier])) {
            $rate = $rates[$carrier][0];
            $shippingAddress->setShippingMethod($rate->getCarrier() . "_" . $rate->getMethod());
        }
    }
}
