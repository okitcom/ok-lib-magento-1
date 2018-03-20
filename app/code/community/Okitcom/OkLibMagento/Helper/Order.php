<?php

use OK\Model\Cash\Transaction;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Order extends Mage_Core_Helper_Abstract
{

    /**
     * Creates an order from a quote and ok transaction
     * @param Mage_Sales_Model_Quote $quote
     * @param Transaction $transaction ok transaction
     */
    public function createOrder(Mage_Sales_Model_Quote $quote, Transaction $transaction) {
        $quote->setCurrency();

        /** @var Okitcom_OkLibMagento_Helper_Order_Processor[] $processors */
        $processors = [
            new Okitcom_OkLibMagento_Helper_Order_Customer(),
            new Okitcom_OkLibMagento_Helper_Order_Address(),
            new Okitcom_OkLibMagento_Helper_Order_Shipping(),
            new Okitcom_OkLibMagento_Helper_Order_Payment(),
            new Okitcom_OkLibMagento_Helper_Order_LineItem()
        ];
        foreach ($processors as $processor) {
            $processor->process($quote, $transaction);
        }

        $quote->collectTotals()->save();

//        $checkout = Mage::getModel('oklibmagento/checkout')->load($transaction->id, "ok_transaction_id");
//        $checkout->sales_order_id = $quote->reserveOrderId()->getReservedOrderId();
//        $checkout->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        $quote->setIsActive(false)->save();

        $order = $service->getOrder();

        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));

        return $order;
    }


}