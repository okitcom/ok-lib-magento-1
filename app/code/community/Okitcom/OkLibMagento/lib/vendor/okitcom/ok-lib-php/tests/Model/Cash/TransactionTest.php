<?php
/**
 * Created by PhpStorm.
 * Date: 9/15/17
 */

namespace OK\Tests\Model\Cash;


use OK\Model\Cash\LineItems;
use OK\Model\Cash\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{

    const TRANSACTION_RESPONSE_LINE_ITEM_JSON = "{\"account\":\"2\",\"amount\":\"11100\",\"attributes\":[{\"key\":\"address\",\"label\":\"Address\",\"required\":\"true\",\"type\":\"ADDRESS\",\"value\":\"Molslaan, 14, , , 2611RM, DELFT, Nederland\"},{\"key\":\"phone\",\"label\":\"Phone\",\"required\":\"true\",\"type\":\"PHONENUMBER\",\"value\":\"31620789955\"},{\"key\":\"name\",\"label\":\"Name\",\"required\":\"true\",\"type\":\"NAME\",\"value\":\"Hidde;Lycklama\"},{\"key\":\"email\",\"label\":\"Email\",\"required\":\"true\",\"type\":\"EMAILADDRESS\",\"value\":\"mail.hidde@gmail.com\"}],\"authorisationResult\":{\"amount\":\"8850\",\"location\":{\"lat\":\"52.341335\",\"lon\":\"4.82361\"},\"reference\":\"13419\",\"result\":\"OK\",\"timestamp\":\"2017-08-28T11:13:06+02:00\"},\"barcode\":\"2621072329110\",\"currency\":\"EUR\",\"description\":\"4x Radiant Tee, 1x Magento sample product (Push It Messenger Bag), etc.\",\"guid\":\"AJvL8Eh-Qva9YPeWBr1B2g\",\"id\":\"9671\",\"landingPageUrl\":\"https://dev.okit.com/q#tAJvL8Eh-Qva9YPeWBr1B2g\",\"lineItems\":[{\"amount\":\"0\",\"currency\":\"EUR\",\"description\":\"Radiant Tee-M-Orange\",\"id\":\"14392\",\"productCode\":\"WS12-M-Orange\",\"quantity\":\"1\",\"totalAmount\":\"0\",\"totalCurrency\":\"EUR\",\"vat\":\"0\"},{\"amount\":\"-50\",\"currency\":\"%\",\"description\":\"Better bag (19645)\",\"id\":\"14396\",\"quantity\":\"1\",\"totalAmount\":\"-2250\",\"totalCurrency\":\"EUR\",\"type\":\"Coupon\"},{\"amount\":\"4500\",\"currency\":\"EUR\",\"description\":\"Magento sample product (Push It Messenger Bag)\",\"id\":\"14393\",\"productCode\":\"24-WB04\",\"quantity\":\"1\",\"subItems\":{\"amount\":\"-50\",\"currency\":\"%\",\"description\":\"Better bag (19645)\",\"id\":\"14396\",\"quantity\":\"1\",\"totalAmount\":\"-2250\",\"totalCurrency\":\"EUR\",\"type\":\"Coupon\"},\"totalAmount\":\"4500\",\"totalCurrency\":\"EUR\",\"vat\":\"0\"},{\"amount\":\"1650\",\"currency\":\"EUR\",\"description\":\"Radiant Tee\",\"id\":\"14391\",\"productCode\":\"WS12-M-Orange\",\"quantity\":\"4\",\"totalAmount\":\"6600\",\"totalCurrency\":\"EUR\",\"vat\":\"0\"}],\"paymentMethod\":\"CREDITCARD\",\"paymentTransactions\":{\"amount\":\"8850\",\"id\":\"7851\",\"merchantAccountId\":\"11\",\"method\":\"CREDITCARD\",\"reference\":\"QH61CB48GGc27AgEyQHrdVRYBWp\",\"state\":\"Captured\",\"timestamp\":\"2017-08-28T11:12:58+02:00\",\"tokenId\":\"10052\"},\"permissions\":\"\",\"reference\":\"4\",\"service\":\"3\",\"state\":\"ClosedAndCaptured\",\"timestamp\":\"2017-08-28T11:12:12+02:00\",\"token\":\"294f27a9-4ab6-4848-b75e-a0ccf00f6799\",\"type\":\"OKNOW\"}";

    public function testDecodeTransactionResponseSubItems() {
        $result = new Transaction(TransactionTest::TRANSACTION_RESPONSE_LINE_ITEM_JSON);
        foreach ($result->lineItems->all() as $item) {
            $this->assertTrue($item->subItems == null || $item->subItems instanceof LineItems);
        }
    }


}