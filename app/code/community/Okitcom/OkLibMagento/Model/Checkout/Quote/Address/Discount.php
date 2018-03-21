<?php

class Okitcom_OkLibMagento_Model_Checkout_Quote_Address_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_code = Okitcom_OkLibMagento_Helper_Config::DISCOUNT_CODE;
    protected $label = Okitcom_OkLibMagento_Helper_Config::DISCOUNT_LABEL;

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }

        $quote = $address->getQuote();

        $checkout = Mage::helper('oklibmagento/checkout')->loadByQuoteId($quote->getId());

        if ($checkout != null
            && $checkout->getId() != null
            && $checkout->getGuid() != null) {
            /** @var \OK\Service\Cash $okCashClient */
            $okCashClient = Mage::helper('oklibmagento/oklib')->getCashClient();
            $okresponse = $okCashClient->get($checkout->getGuid());
            if ($okresponse != null
                && $okresponse->state == Okitcom_OkLibMagento_Helper_Config::STATE_CHECKOUT_SUCCESS
                && $okresponse->authorisationResult->result == "OK") {
                $discountOk = $okresponse->authorisationResult->amount->sub($okresponse->amount);
                $discountAmount = $discountOk->getEuro();

                $address->addTotalAmount($this->getCode(), $discountAmount);
                $address->addBaseTotalAmount($this->getCode(), $discountAmount);

//              In Magento 1.8.0.0 at least, the grand total is incorrectly calculated when only
//              calling the addTotalAmount methods above. We have to manually update the grand total amounts.
//              However, this is not compatible with Magento installations that DO do this correctly.
//              We are unsure what version provides the update that changed this behavior. However, our best guess
//              is Magento versions sub 1.9.

                if ($this->isVersionBelow19()) {
                    $address->setGrandTotal($address->getGrandTotal() + $discountAmount);
                    $address->setBaseGrandTotal($address->getBaseGrandTotal() + $discountAmount);
                }

                return $this;
            }

        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }

    public function getLabel() {
        return $this->label;
    }

    private function isVersionBelow19() {
        $info = Mage::getVersionInfo();
        if ($info["major"] === '1' && intval($info["minor"]) < 9) {
            return true;
        }
        return false;
    }

}