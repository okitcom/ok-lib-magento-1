<?php

use OK\Builder\AttributeBuilder;
use OK\Builder\LineItemBuilder;
use OK\Builder\TransactionBuilder;
use OK\Model\Amount;
use OK\Model\Attribute;

/**
 * Created by PhpStorm.
 * Date: 3/3/18
 */

class Okitcom_OkLibMagento_Helper_Checkout extends Mage_Core_Helper_Abstract
{

    protected $configHelper;

    /**
     * Okitcom_OkLibMagento_Helper_Checkout constructor.
     */
    public function __construct() {
        $this->configHelper = Mage::helper('oklibmagento/config');
    }


    public function requestCash($quote = null) {
        Okitcom_OkLibMagento_Model_Observer::init();

        if ($quote == null) {
            $quote = Mage::getModel('checkout/cart')->getQuote();
        }
        if ($quote == null) {
            Mage::throwException($this->__("Cart is empty."));
        }

        $shippingMethod = $this->calculateShippingPrice($quote);
        $shippingPrice = $shippingMethod["price"];
        $shippingMethodName = $shippingMethod["label"];

        $totalAmount = $quote->getBaseSubtotalWithDiscount() + $shippingPrice;
        if ($totalAmount == 0) {
            Mage::throwException($this->__("Total amount is zero."));
        }

        $externalIdentifier = Mage::helper('core')->getRandomString(32);

        $checkout = Mage::getModel('oklibmagento/checkout');
        $checkout->setExternalId($externalIdentifier);
        $checkout->setQuoteId($quote->getId());
        $checkout->save();

        $redirectUrl = Mage::getUrl('oklib/cash/callback', [
            "_secure" => true,
            "transaction" => $externalIdentifier
        ]);

        $transactionBuilder = (new TransactionBuilder())
            ->setReference($quote->getId())
            ->setRedirectUrl($redirectUrl)
            ->setAmount(Amount::fromEuro($totalAmount))
            ->setPermissions("TriggerPaymentInitiation")
            ->addAttribute(
                (new AttributeBuilder())
                    ->setKey("name")
                    ->setLabel("Name")
                    ->setType(Attribute::TYPE_NAME)
                    ->setRequired(true)
                    ->build()
            )
            ->addAttribute(
                (new AttributeBuilder())
                    ->setKey("email")
                    ->setLabel("Email")
                    ->setType(Attribute::TYPE_EMAIL)
                    ->setRequired(true)
                    //->setVerified(true)
                    ->build()
            )
            ->addAttribute(
                (new AttributeBuilder())
                    ->setKey("address")
                    ->setLabel("Address")
                    ->setType(Attribute::TYPE_ADDRESS)
                    ->setRequired(true)
                    ->build()
            )
            ->addAttribute(
                (new AttributeBuilder())
                    ->setKey("phone")
                    ->setLabel("Phone")
                    ->setType(Attribute::TYPE_PHONENUMBER)
                    ->setRequired(true)
                    //->setVerified(true)
                    ->build()
            );

        foreach ($quote->getAllItems() as $item) {
//            print $item->getQty() . " " . $item->getName() . " " . $item->getPrice() . " - " . $item->getDiscountAmount() . " Calc: " . Amount::fromEuro($item->getPrice() - ($item->getDiscountAmount() / $item->getQty()))->getEuro() . " VAT: ". $item->getRowTotalInclTax() . "<br />";
            $itemPrice = $item->getRowTotalInclTax();
            $tax = $itemPrice - $item->getRowTotal();
            if ($tax == null) {
                $tax = 0;
            }
            $lineItemBuilder = (new LineItemBuilder())
                ->setQuantity($item->getQty())
                ->setProductCode($item->getSku())
                ->setDescription($item->getName())
                ->setAmount(Amount::fromEuro($itemPrice))
                ->setVat(Amount::fromEuro($tax)->getCents())
                ->setCurrency("EUR");
            if ($item->getDiscountAmount() > 0) {
                $lineItemBuilder->addSubItem(
                    (new LineItemBuilder())
                        ->setQuantity(1)
                        ->setProductCode("DISCOUNT")
                        ->setAmount(Amount::fromEuro(- $item->getDiscountAmount()))
                        ->setVat(0)
                        ->setDescription("Magento discount")
                        ->setCurrency("EUR")
                        ->build()
                );
            }
            $transactionBuilder->addLineItem(
                $lineItemBuilder->build()
            );
        }

        if ($shippingPrice > 0) {
            // Add shipping as a line item.
            $transactionBuilder->addLineItem(
                (new LineItemBuilder())
                    ->setQuantity(1)
                    ->setProductCode("shipping_" . $shippingMethodName)
                    ->setDescription("Shipping")
                    ->setAmount(Amount::fromEuro($shippingPrice))
                    ->setVat(0)
                    ->setCurrency("EUR")
                    ->build()
            );
        }

        $initiation = false;
        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            $token = $customerSession->getCustomer()->getData(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN);
            if ($token != null) {
                $transactionBuilder->setInitiationToken($token);
                $initiation = true;
            }
        }

//        var_dump($transactionBuilder->build());
//        die();

        $okCash = Mage::helper('oklibmagento/oklib')->getCashClient();
        try {
            $response = $okCash->request($transactionBuilder->build());
        } catch (\OK\Model\Network\Exception\NetworkException $exception) {
            Mage::logException($exception);
            return [
                "error" => Mage::helper('core')->__("Your transaction exceeds the maximum amount that is supported by OK.")
            ];
        }

        $checkout->setGuid($response->guid);
        $checkout->setOkTransactionId($response->id);
        $checkout->setState($response->state);
        $checkout->save();

        return [
            "guid" => $response->guid,
            "culture" => $this->getLocale(),
            "environment" => $this->getOkLibEnvironment(),
            "initiation" => $initiation
        ];
    }

    private function calculateShippingPrice($quote) {
        $shippingMethod = $this->configHelper->getOkCashValue("default_shipping_method");
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setShippingMethod($shippingMethod)
            ->setCollectShippingRates(true)
            ->collectShippingRates();

        $carrier = explode("_", $shippingMethod)[0];

        $shippingPrice = 0;
        $rates = $shippingAddress->getGroupedAllShippingRates();
        if (isset($rates[$carrier])) {
            $shippingPrice = $rates[$carrier][0]->getPrice();
        }
        return [
            "price" =>$shippingPrice,
            "label" => $shippingMethod
        ];
    }

    public function getOkLibEnvironment() {
        $env = $this->configHelper->getOkGeneralValue("environment");
        $map = [
            "secure",
            "dev",
            "alpha",
            "test",
            "beta"
        ];
        $default = "secure";
        if (!in_array($env, $map)) {
            return $default;
        }
        return $env;
    }

    public function getLocale() {
        $code = Mage::app()->getLocale()->getLocaleCode();
        $defaultLocales = array("nl_NL", "en_GB");
        if (!in_array($code, $defaultLocales)) {
            $code = Okitcom_OkLibMagento_Helper_Config::DEFAULT_LOCALE;
        }
        return str_replace("_", "-", $code);
    }

    public function loadByQuoteId($quoteId) {
        $checkout = Mage::getModel('oklibmagento/checkout');
        return $checkout->getCollection()
            ->setOrder('id', 'DESC')
            ->addFieldToFilter('quote_id', $quoteId)
            ->getFirstItem();
    }

    public function loadAllPending() {
        $fromDate = date(Okitcom_OkLibMagento_Helper_Config::DATE_DB_FORMAT, strtotime(Okitcom_OkLibMagento_Helper_Config::DATE_PENDING_OFFSET));
        $pendingStates = [
            "NewPendingTrigger", "NewPendingApproval"
        ];
        return Mage::getModel('oklibmagento/checkout')
            ->getCollection()
            ->addFieldToFilter("state", $pendingStates)
            ->addFieldToFilter("updated_at", [
                "from" => $fromDate
            ])
            ->load();
    }

}