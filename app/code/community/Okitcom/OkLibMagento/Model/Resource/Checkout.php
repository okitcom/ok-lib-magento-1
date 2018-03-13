<?php

class Okitcom_OkLibMagento_Model_Resource_Checkout extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('oklibmagento/checkout','id');
    }
}