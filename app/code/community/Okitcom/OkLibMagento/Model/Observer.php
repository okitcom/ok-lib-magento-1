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
        $this->log('Running OK transaction status check');

        $transactions = Mage::helper('oklibmagento/checkout')->loadAllPending();
        $this->log("Found " . $transactions->count() . " tx to update");
        $updated = 0;
        $completed = 0;
        $canceled = 0;
        $still_pending = 0;

        /** @var Okitcom_OkLibMagento_Model_Checkout $item */
        foreach ($transactions->getItems() as $item) {

            $store = $item->getStore();
            if ($store == null || !Mage::helper('oklibmagento/oklib')->isServiceEnabled(Okitcom_OkLibMagento_Helper_Oklib::SERVICE_TYPE_CASH, $store)) {
                $this->log("OK Cash service is disabled for this store. (checkout id: " . $item->getId() . ")");
                return; // Don't run if OK Cash is disabled
            }

            /** @var \OK\Service\Cash $okCash */
            $okCash = Mage::helper('oklibmagento/oklib')->getCashClient($store);
            $cancelAfterMinutes = Mage::helper('oklibmagento/config')->getOkCashValue("cancel_after", $store);

            $guid = $item->getGuid();
            try {
                $okResponse = $okCash->get($guid);
                if ($okResponse != null) {

                    if ($okResponse->state != $item->getState()) {
                        $item->setState($okResponse->state);
                        $item->save();

                        if ($okResponse->state == Okitcom_OkLibMagento_Helper_Config::STATE_CHECKOUT_SUCCESS) {
                            Mage::helper('oklibmagento/checkout')->createOrder($item, $okResponse);
                            $completed++;
                        }

                        $updated++;
                    } else {
                        $still_pending++;
                    }

                } else {
                    $still_pending++;
                }

            } catch (\Exception $e) {
                Mage::logException($e);
                $this->log("Could not update OK transaction with id " . $item->getId() . ". Message: " . $e->getMessage());
            }

            if (isset($cancelAfterMinutes) && $cancelAfterMinutes != 0 && $item->getState() === Okitcom_OkLibMagento_Helper_Config::STATE_CHECKOUT_UNSCANNED) {
                $age = time() - strtotime($item->getCreatedAt());
                if ($age > $cancelAfterMinutes * 60.0) {
                    try {
                        $okCash->cancel($guid);

                        $item->setState(Okitcom_OkLibMagento_Helper_Config::STATE_CHECKOUT_CANCELLED);
                        $item->save();

                        $canceled++;
                        $still_pending--;
                    } catch (\OK\Model\Network\Exception\NetworkException $exception) {
                        Mage::logException(new Okitcom_OkLibMagento_Helper_Checkout_Exception("Could not cancel OK transaction: " . $item->getId()));
                    }
                }
            }

            // Mark NewPendingTrigger transactions as closed (if older than X time)
            //$this->logger->info("Transaction ID " . $item->id . " state " . $item->state);
        }

        if ($still_pending > 0 || $updated > 0 || $canceled > 0) {
            $this->log("Ran update on " . $transactions->count() . " transactions. (" . $updated . " updated, " . $completed . " completed, " . $canceled . " canceled, " . $still_pending . " still pending)");
        }
    }

    private function log($message) {
        Mage::log($message, null, Okitcom_OkLibMagento_Helper_Config::LOGFILE);
    }

}