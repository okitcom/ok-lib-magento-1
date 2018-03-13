<?php

class Okitcom_OkLibMagento_Block_Authorization_Button extends Mage_Core_Block_Template
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     */
    protected $_shouldRender = true;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_paymentMethodCode = Okitcom_OkLibMagento_Helper_Config::PAYMENT_METHOD_CODE;

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();

        $this->_shouldRender = Mage::helper('oklibmagento/config')->getOkOpenValue("secret") != null;

        return $result;
    }

    /**
     * Render the block if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
//        if (!$this->_shouldRender) {
//            return '';
//        }
        return parent::_toHtml();
    }
}
