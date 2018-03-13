<?php
/**
 * Created by PhpStorm.
 * Date: 3/9/18
 */

class Okitcom_OkLibMagento_Model_Resource_Checkout_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    protected function _construct()
    {
        $this->_init('oklibmagento/checkout');
    }
}