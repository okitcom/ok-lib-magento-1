<?php
/**
 * Created by PhpStorm.
 * Date: 8/28/17
 */

class Okitcom_OkLibMagento_Block_Adminhtml_Works extends Mage_Core_Block_Template
{
    /**
     * @return bool whether this order was paid using OK.
     */
    public function orderIsOK() {
        return $this->getOrderOK() != null;
    }

    /**
     * Get OK checkout for this order.
     * @return null|\Okitcom_OkLibMagento_Model_Checkout if any
     */
    public function getOrderOK() {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId != null) {
            $ok = Mage::getModel('oklibmagento/checkout')->load($orderId, 'order_id');
            return $ok;
        }
        return null;
    }

    /**
     * Get the url of this transaction in OK Works.
     * @return string
     */
    public function getWorksUrl() {
        return 'https://dev.okit.io/';
//        return $this->_checkoutHelper->getWorksUrl($this->getOrderOK());
    }

}