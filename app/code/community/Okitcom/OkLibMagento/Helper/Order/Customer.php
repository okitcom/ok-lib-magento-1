<?php

use OK\Model\Cash\Transaction;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Order_Customer extends Okitcom_OkLibMagento_Helper_Order_Processor {


    function process(Mage_Sales_Model_Quote $quote, Transaction $transaction) {
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        /** @var Okitcom_OkLibMagento_Helper_Customer $customerHelper */
        $customerHelper = Mage::helper('oklibmagento/customer');

        // Attempt a search by OK token
        $customerByOkToken = $customerHelper->findByToken($transaction->token);
        if ($customerByOkToken->getId() != null) {
            $quote->assignCustomer($customerByOkToken);
            return;
        }

        $customerByEmail = $customerHelper->findByEmail($transaction->attributes->get("email")->value);
        if ($customerByEmail->getId() != null) {
            $customerByEmail->setData(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN, $transaction->token);
            $customerByEmail->save();
            $quote->assignCustomer($customerByEmail);
            return;
        }

        $store = $quote->getStore();
        $nameParts = explode(";", $transaction->attributes->get("name")->value);
        $customer = $customerHelper->create($transaction->token, $nameParts[0], $nameParts[1], $transaction->attributes->get("email")->value, $store->getWebsiteId(), $store->getId());

        if ($customer->getId() == null) {
            throw new Okitcom_OkLibMagento_Helper_Checkout_Exception("Could not create a valid customer.");
        }

        $quote->assignCustomer($customer);

    }
}
