<?php

use OK\Model\Cash\Transaction;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Order_Payment extends Okitcom_OkLibMagento_Helper_Order_Processor {


    function process(Mage_Sales_Model_Quote $quote, Transaction $transaction) {
        $quote->setPaymentMethod(Okitcom_OkLibMagento_Model_Okcash::PAYMENT_CODE); //payment method
        $quote->setInventoryProcessed(false);
        $quote->getPayment()->importData(['method' => Okitcom_OkLibMagento_Model_Okcash::PAYMENT_CODE]);
    }
}
