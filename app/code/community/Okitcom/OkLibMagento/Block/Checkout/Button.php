<?php

class Okitcom_OkLibMagento_Block_Checkout_Button extends Mage_Core_Block_Template
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

        $isInCatalog = $this->getIsInCatalogProduct();
        $quote = ($isInCatalog || '' == $this->getIsQuoteAllowed())
            ? null : Mage::getSingleton('checkout/session')->getQuote();

        // validate minimum quote amount and validate quote for zero grandtotal
        if (null !== $quote && (!$quote->validateMinimumAmount()
                || (!$quote->getGrandTotal() && !$quote->hasNominalItems()))) {
            $this->_shouldRender = false;
            return $result;
        }

        // check payment method availability
        $methodInstance = Mage::helper('payment')->getMethodInstance($this->_paymentMethodCode);
        if (!$methodInstance || !$methodInstance->isAvailable($quote)) {
//            $this->_shouldRender = false;
            return $result;
        }

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
