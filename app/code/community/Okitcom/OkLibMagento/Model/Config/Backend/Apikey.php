<?php
/**
 * Created by PhpStorm.
 * Date: 3/18/18
 */

class Okitcom_OkLibMagento_Model_Config_Backend_Apikey extends Mage_Core_Model_Config_Data
{

    protected function _beforeSave() {
        $this->setValue(trim($this->getValue()));
        return parent::_beforeSave();
    }

}