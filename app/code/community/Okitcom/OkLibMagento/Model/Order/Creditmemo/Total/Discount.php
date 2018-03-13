<?php

class Okitcom_OkLibMagento_Model_Order_Creditmemo_Total_Discount extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {

        // We need to override this, because the "Discount" on invoices is calculated by adding up all the line item discounts.
        // We want to have a separate line for OKs

        $order = $creditmemo->getOrder();
        $checkout = Mage::getModel('oklibmagento/checkout')->load($order->getId(), 'sales_order_id');

        if ($checkout != null && $checkout->getId() != null && $checkout->getDiscount() != null) {

            $okDiscountAmount = $checkout->getDiscount() / 100.0;
        }

        $creditmemo->setDiscountAmount($creditmemo->getDiscountAmount() + $okDiscountAmount);
        $creditmemo->setBaseDiscountAmount($creditmemo->getBaseDiscountAmount() + $okDiscountAmount);

        return $this;
    }

    public function getLabel() {
        return "OK";
    }
}