<?php

class Okitcom_OkLibMagento_Block_Checkout_Discount extends Mage_Sales_Block_Order_Totals
{
    public function initTotals(){
        $order = $this->getParentBlock()->getOrder();
        $checkout = Mage::getModel('oklibmagento/checkout')->load($order->getId(), 'sales_order_id');
        if($checkout != null &&
            $checkout->getDiscount() != null &&
            $checkout->getDiscount() > 0) {
            $amount = -$checkout->getDiscount() / 100.0;
            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code' => Okitcom_OkLibMagento_Helper_Config::DISCOUNT_CODE,
                'value' => $amount,
                'base_value' => $amount,
                'label' => Okitcom_OkLibMagento_Helper_Config::DISCOUNT_LABEL,
            )),'discount');
        }
    }
}