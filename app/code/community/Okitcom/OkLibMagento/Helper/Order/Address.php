<?php

use OK\Model\Attribute;
use OK\Model\Cash\Transaction;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Order_Address extends Okitcom_OkLibMagento_Helper_Order_Processor {


    function process(Mage_Sales_Model_Quote $quote, Transaction $transaction) {
        $this->mapOkAddress($quote->getShippingAddress(), $transaction);
        $this->mapOkAddress($quote->getBillingAddress(), $transaction);
    }

    private function mapOkAddress($address, Transaction $transaction) {
        $nameParts = explode(";", $transaction->attributes->name->value);
        $okAddress = $transaction->attributes->address;

        $address->setFirstname($nameParts[0]);
        $address->setLastname($nameParts[1]);
        $address->setStreet($okAddress->addressComponent(Attribute::ADDRESS_STREET)
            . " "
            . $okAddress->addressComponent(Attribute::ADDRESS_NUMBER));
        $address->setPostcode($okAddress->addressComponent(Attribute::ADDRESS_ZIP));
        $address->setCity($okAddress->addressComponent(Attribute::ADDRESS_CITY));
        $address->setCountryId("NL"); // TODO: Change
        $address->setTelephone("31620789955");
        return $address;
    }
}
