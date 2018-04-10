<?php
/**
 * Created by PhpStorm.
 * Date: 3/2/18
 */

class Okitcom_OkLibMagento_Model_Checkout extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('oklibmagento/checkout');
    }

    /**
     * Before save actions
     *
     * @return Okitcom_OkLibMagento_Model_Checkout
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

    /**
     * Get the Store for this checkout.
     * @return mixed
     */
    public function getStore() {
        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($this->getQuoteId());
        $store = $quote->getStore();
        return $store;
    }

}