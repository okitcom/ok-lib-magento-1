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

    protected $_isInCatalog = false;

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();

        $quote = ($this->_isInCatalog)
            ? null : Mage::getSingleton('checkout/session')->getQuote();
        $maximumTransactionAmount = Mage::helper('oklibmagento/config')->getOkCashValue("transaction_amount_max") / 100.0;
        if ($this->_isInCatalog) {
            /** @var Mage_Catalog_Model_Product $currentProduct */
            $currentProduct = Mage::registry('current_product');
            if (!is_null($currentProduct)) {
                $price = (float)$currentProduct->getFinalPrice();
                $typeInstance = $currentProduct->getTypeInstance();
                if (empty($price) && !$currentProduct->isSuper() && !$typeInstance->canConfigure($currentProduct)) {
                    $this->_shouldRender = false;
                    return $result;
                }
                if ($price > $maximumTransactionAmount) {
                    $this->_shouldRender = false;
                    return $result;
                }
            }

            return $result;
        }

        // validate minimum quote amount and validate quote for zero grandtotal
        if (null !== $quote && (!$quote->validateMinimumAmount()
                || (!$quote->getGrandTotal() && !$quote->hasNominalItems()))) {
            $this->_shouldRender = false;
            return $result;
        }
        // check payment method availability
//        $methodInstance = Mage::helper('payment')->getMethodInstance($this->_paymentMethodCode);
//        if (!$methodInstance || !$methodInstance->isAvailable($quote)) {
//            $this->_shouldRender = false;
//            return $result;
//        }
        if (null !== $quote && $quote->getGrandTotal() > $maximumTransactionAmount) {
            $this->_shouldRender = false;
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
        if (!$this->_shouldRender) {
            return '';
        }
        return parent::_toHtml();
    }
}
