<?php

use OK\Model\Network\Exception\NetworkException;

/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Model_Okcash extends Mage_Payment_Model_Method_Abstract
{

    const PAYMENT_CODE = "okcash";
    protected $_code = self::PAYMENT_CODE;
    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway                   = true;
    protected $_canOrder                    = false;
    protected $_canAuthorize                = false;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canCaptureOnce              = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = false;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = false;
    protected $_canFetchTransactionInfo     = true;
    protected $_canReviewPayment            = false;
    protected $_canCreateBillingAgreement   = true;
    protected $_canManageRecurringProfiles  = false;
    /**
     * TODO: whether a captured transaction may be voided by this gateway
     * This may happen when amount is captured, but not settled
     * @var bool
     */
    protected $_canCancelInvoice        = false;

    protected $_infoBlockType = 'oklibmagento/adminhtml_works';

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        $supported = [
            "EUR"
        ];
        return in_array($currencyCode, $supported) && parent::canUseForCurrency($currencyCode);
    }

    /**
     * Check whether payment method can be used
     * @param Mage_Sales_Model_Quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (parent::isAvailable($quote) &&
            $quote != null &&
            Mage::getModel('oklibmagento/checkout')->load($quote->getId(), "quote_id") != null) {
            return true;
        }
        return false;
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * Capture payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Okitcom_OkLibMagento_Model_Okcash
     */
    public function capture(Varien_Object $payment, $amount)
    {
        return $this->processOkPayment($payment, $amount);
    }

    /**
     * Refund capture
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Mage_Paypal_Model_Express
     */
    public function refund(Varien_Object $payment, $amount)
    {
        return $this->processOkRefund($payment, $amount);
    }

    /**
     * Process payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Okitcom_OkLibMagento_Model_Okcash
     */
    private function processOkPayment(Varien_Object $payment, $amount) {
        // this is basically a formality
        $order = $payment->getOrder();
        $checkout = Mage::getModel('oklibmagento/checkout')->load($order->getQuoteId(), "quote_id");
        if ($checkout->getState() !== Okitcom_OkLibMagento_Helper_Config::STATE_CHECKOUT_SUCCESS) {
            Mage::throwException(Mage::helper("core")->__("OK transaction state is invalid: " . $checkout->getState()));
        }

        // Match amounts
        /** @var \OK\Service\Cash $service */
        $service = Mage::helper('oklibmagento/oklib')->getCashClient();
        $okTransaction = $service->get($checkout->getGuid());
        if ($okTransaction->amount->getEuro() < $amount) {
            $data = json_encode([
                "ok_transaction_amount" => $okTransaction->amount->getEuro(),
                "capture_amount" => $amount
            ]);
            Mage::throwException("OK transaction amount was smaller than the transaction. " . $data);
        }

        $payment->setTransactionAdditionalInfo(
            "ok_transaction_id",
            $checkout->getOkTransactionId());
        $payment->setTransactionId($checkout->getOkTransactionId()
        )->setParentTransactionId($checkout->getOkTransactionId());

        // notify customer
        if (!$order->getEmailSent()) {
            $order->sendNewOrderEmail()->addStatusHistoryComment(
                Mage::helper("core")->__('Notified customer about order #%s.', $order->getIncrementId())
            )->setIsCustomerNotified(true)->save();
        }

        return $this;
    }

    /**
     * Refund capture
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Okitcom_OkLibMagento_Model_Okcash
     */
    private function processOkRefund(Varien_Object $payment, $amount) {
        $order = $payment->getOrder();
        $checkout = Mage::getModel('oklibmagento/checkout')->load($order->getId(), "sales_order_id");
        /** @var \OK\Service\Cash $service */
        $service = Mage::helper('oklibmagento/oklib')->getCashClient();
        try {
            $service->refund($checkout->getGuid(), \OK\Model\Amount::fromEuro($amount));
        } catch (NetworkException $exception) {
            Mage::throwException(Mage::helper('core')->__("Unable to refund (amount: %.2f): %s", $amount, $exception->getMessage()));
        }
        return $this;
    }

}