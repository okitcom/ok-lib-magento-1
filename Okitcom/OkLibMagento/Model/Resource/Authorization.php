<?php

class Okitcom_OkLibMagento_Model_Resource_Authorization extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('oklibmagento/authorization','id');
    }
}