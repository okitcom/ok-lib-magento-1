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

//                TODO: Version statement
//                $address->setGrandTotal($address->getGrandTotal() + $discountAmount);
//                $address->setBaseGrandTotal($address->getBaseGrandTotal() + $discountAmount);

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

}