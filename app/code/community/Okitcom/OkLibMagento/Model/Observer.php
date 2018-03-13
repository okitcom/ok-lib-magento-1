<?php

class Okitcom_OkLibMagento_Model_Observer
{
    /**
     * @param Varien_Event_Observer $event
     */
    public function controllerFrontInitBefore(Varien_Event_Observer $event)
    {
        self::init();
    }

    /**
     * Add in auto loader for OkLibPhp
     */
    static function init()
    {
        $libDir = Mage::getModuleDir('', 'Okitcom_OkLibMagento') . DS . 'lib';
        // Add our vendor folder to our include path
        set_include_path(get_include_path() . PATH_SEPARATOR . $libDir . DS . 'vendor');

        // Include the autoloader for composer
        require_once($libDir . DS . 'vendor' . DS . 'autoload.php');
    }

    public function updateStatus() {
        Mage::log('Running OK transaction status check');

        /** @var \OK\Service\Cash $okCash */
        $okCash = Mage::helper('oklibmagento/oklib')->getCashClient();
        $transactions = Mage::helper('oklibmagento/checkout')->loadAllPending();
//        $this->logger->info("Found " . $transactions->count() . " tx to update");
        $updated = 0;
        $completed = 0;
        $still_pending = 0;
        /** @var Okitcom_OkLibMagento_Model_Checkout $item */
        foreach ($transactions->getItems() as $item) {
            // Get status

            $guid = $item->getGuid();
            try {
                $okResponse = $okCash->get($guid);
                if ($okResponse != null && $okResponse->state != $item->getState()) {
                    $item->setState($okResponse->state);
                    $item->save();

                    if ($okResponse->state == Okitcom_OkLibMagento_Helper_Config::STATE_CHECKOUT_SUCCESS) {
                        $this->createOrder($item, $okResponse);
                        $completed++;
                    }

                    $updated++;
                } else {
                    $still_pending++;
                }
            } catch (\Exception $e) {
                Mage::log("Could not update OK transaction with id " . $item->getId(), "error");
            }

            // Mark NewPendingTrigger transactions as closed (if older than X time)
            //$this->logger->info("Transaction ID " . $item->id . " state " . $item->state);
        }

        if ($updated > 0) {
            Mage::log("Ran update on " . $transactions->count() . " transactions. (" . $updated . " updated, " . $completed . " completed, " . $still_pending . " still pending)");
        }
    }

    private function createOrder(Okitcom_OkLibMagento_Model_Checkout $checkout, $okResponse) {
        // process
        if ($checkout->getSalesOrderId() == null) {
            // update
            $quote = Mage::helper('oklibmagento/checkout')->loadByQuoteId($checkout->loadByQuoteId());
            $order = Mage::helper('oklibmagento/order')->createOrder($quote, $okResponse);

            $discountOk = $okResponse->authorisationResult->amount->sub($okResponse->amount);
            $checkout->setDiscount(-$discountOk->getCents());
            $checkout->setSalesOrderId($order->getEntityId());
            $checkout->save();
        }
    }

}