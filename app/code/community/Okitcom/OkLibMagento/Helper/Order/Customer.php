<?php

use OK\Model\Cash\Transaction;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Order_Customer extends Okitcom_OkLibMagento_Helper_Order_Processor {


    function process(Mage_Sales_Model_Quote $quote, Transaction $transaction) {
        $store = $quote->getStore();

        /** @var Okitcom_OkLibMagento_Helper_Customer $customerHelper */
        $customerHelper = Mage::helper('oklibmagento/customer');

        // Attempt a search by OK token
        $customerByOkToken = $customerHelper->findByToken($transaction->token, $store->getWebsiteId());
        if ($customerByOkToken->getId() != null) {
            $this->assign($customerByOkToken, $quote);
            return;
        }

        $customerByEmail = $customerHelper->findByEmail($transaction->attributes->get("email")->value, $store->getWebsiteId());
        if ($customerByEmail->getId() != null) {
            $customerByEmail->setData(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN, $transaction->token);
            $customerByEmail->save();
            $this->assign($customerByEmail, $quote);
            return;
        }

        $nameParts = explode(";", $transaction->attributes->get("name")->value);
        $customer = $customerHelper->create($transaction->token, $nameParts[0], $nameParts[1], $transaction->attributes->get("email")->value, $store->getWebsiteId(), $store->getId());

        if ($customer->getId() == null) {
            throw new Okitcom_OkLibMagento_Helper_Checkout_Exception("Could not create a valid customer.");
        }

        $this->assign($customer, $quote);
    }

    public function assign($customer, $quote) {
        $quote->setCustomerFirstname($customer->getFirstname());
        $quote->setCustomerLastname($customer->getLastname());
        $quote->save();
        $quote->assignCustomer($customer);
    }
}
