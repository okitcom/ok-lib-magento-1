<?php

use OK\Model\Cash\Transaction;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

abstract class Okitcom_OkLibMagento_Helper_Order_Processor extends Mage_Core_Helper_Abstract
{

    abstract function process(Mage_Sales_Model_Quote $quote, Transaction $transaction);

}