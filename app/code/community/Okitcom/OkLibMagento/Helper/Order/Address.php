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

        $customer = $quote->getCustomer();
        $address = $quote->getShippingAddress()->exportCustomerAddress();
        if (!$this->customerHasAddress($customer, $address)) {

            if ($customer->getPrimaryBillingAddress() == null) {
                $address->setIsPrimaryBilling(true);
            }
            if ($customer->getPrimaryShippingAddress() == null) {
                $address->setIsPrimaryShipping(true);
            }
            $customer->addAddress($address);
        }
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
        $address->setTelephone($transaction->attributes->phone->value);
        return $address;
    }

    private function customerHasAddress($customer, $address) {
        foreach ($customer->getAddresses() as $customerAddress) {
            if ($this->serializeAddress($address) == $this->serializeAddress($customerAddress)) {
                return true;
            }
        }
        return false;
    }

    function serializeAddress($address)  {
        return serialize(
            array(
                'firstname' => $address->getFirstname(),
                'lastname'  => $address->getLastname(),
                'street'    => $address->getStreet(),
                'city'      => $address->getCity(),
                'postcode'  => $address->getPostcode(),
                'country_id'=> $address->getCountryId(),
            )
        );
    }
}
