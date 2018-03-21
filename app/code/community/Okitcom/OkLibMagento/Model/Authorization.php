<?php
/**
 * Created by PhpStorm.
 * Date: 3/2/18
 */

class Okitcom_OkLibMagento_Model_Authorization extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('oklibmagento/authorization');
    }

    /**
     * Before save actions
     *
     * @return Okitcom_OkLibMagento_Model_Authorization
     */
    protected function _beforeSave()
    {
        if ($this->isObjectNew() && null === $this->getCreatedAt()) {
            $this->setCreatedAt(Varien_Date::now());
        }
        $this->setUpdatedAt(Varien_Date::now());
        parent::_beforeSave();
        return $this;
    }
}