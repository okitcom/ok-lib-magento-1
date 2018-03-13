<?php

use OK\Model\Amount;
use OK\Model\Cash\Transaction;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Order_LineItem extends Okitcom_OkLibMagento_Helper_Order_Processor {


    function process(Mage_Sales_Model_Quote $quote, Transaction $transaction) {
        $quoteItems = $quote->getAllItems();

        $lineItems = $transaction->lineItems->all();
        foreach ($lineItems as $lineItem) {
            if (isset($lineItem->subItems) && $lineItem->subItems != null) {
                $quoteItem = $this->findQuoteProduct($lineItem->productCode, $quoteItems);

                if ($quoteItem != null) {
                    $discount = Amount::fromCents(0);
                    foreach ($lineItem->subItems->all() as $subItem) {
                        if (isset($subItem->type) && $subItem->type == "Coupon") {
                            // calculate discount
                            $discount = $discount->sub($subItem->totalAmount);
                        }
                    }

                    $quoteItem->setDiscountAmount($quoteItem->getDiscountAmount() + $discount->getEuro());
                    $quoteItem->setBaseDiscountAmount($quoteItem->getBaseDiscountAmount() + $discount->getEuro());
                    $quoteItem->save();
                }

            }
        }
    }

    /**
     * Find a product by SKU
     * @param $sku string identifier
     * @param $items Mage_Sales_Model_Quote_Item[] list of items
     * @return Mage_Sales_Model_Quote_Item|null item
     */
    function findQuoteProduct($sku, $items) {
        foreach ($items as $item) {
            if ($item->getSku() === $sku) {
                return $item;
            }
        }
        return null;
    }
}
