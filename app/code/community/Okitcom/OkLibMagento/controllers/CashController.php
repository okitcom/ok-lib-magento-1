<?php

class Okitcom_OkLibMagento_CashController extends Mage_Core_Controller_Front_Action {

    public function initAction() {
        $response = Mage::helper('oklibmagento/checkout')->requestCash();

        if (isset($response["error"])) {
            $this->jsonError($response["error"]);
            return;
        }

        $this->getResponse()->setHeader(
            'Content-type',
            'application/json'
        );

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($response)
        );
    }

    public function buynowAction() {
        $quote = Mage::getModel('sales/quote');
        $quote->setStoreId(Mage::app()->getStore()->getId());

//        $cart   = Mage::getSingleton('checkout/cart');
//        $cart->setQuote($quoteObj);
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = null;
            $productId = (int) $this->getRequest()->getParam('product');
            if ($productId) {
                $_product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                if ($_product->getId()) {
                    $product = $_product;
                }
            }
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->jsonError("Product unavailable");
                return;
            }

            if ($product->isConfigurable()) {

                $request = $this->_getProductRequest($params);
                /**
                 * Hardcoded Configurable product default
                 * Set min required qty for a product if it's need
                 */
                $qty = isset($params['qty']) ? $params['qty'] : 0;
                $requestedQty = ($qty > 1) ? $qty : 1;
                $subProduct = $product->getTypeInstance(true)
                    ->getProductByAttributes($request->getSuperAttribute(), $product);

                if (!empty($subProduct)
                    && $requestedQty < ($requiredQty = $subProduct->getStockItem()->getMinSaleQty())
                ) {
                    $requestedQty = $requiredQty;
                }

                $params['qty'] = $requestedQty;
            }

            $request = new Varien_Object($params);

            $quote->addProduct($product, $request);
            if (!empty($related)) {
                $this->addProductsByIds($quote, explode(',', $related));
            }

            $quote->getBillingAddress();
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals();
            $quote->save();

            $response = Mage::helper('oklibmagento/checkout')->requestCash($quote);

            $this->getResponse()->setHeader(
                'Content-type',
                'application/json'
            );

            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode($response)
            );

        } catch (Mage_Core_Exception $e) {
            $this->jsonError($e->getMessage());

        } catch (Exception $e) {
            Mage::logException($e);
            $this->jsonError("Can't add item to shopping cart");
        }
    }

    public function callbackAction() {
        $transaction = $this->getRequest()->getParam("transaction");

        sleep(3);

        if ($transaction == null) {
            return $this->redirectWithError($this->__('Your OK transaction was not found.'));
        }

        $checkout = Mage::getModel('oklibmagento/checkout');
        $checkout->load($transaction, "external_id");
        if ($checkout == null) {
            return $this->redirectWithError($this->__('Your OK transaction was not found.'));

        }

        /** @var \OK\Service\Cash $okCashClient */
        $okCashClient = Mage::helper('oklibmagento/oklib')->getCashClient();

        try {
            /** @var \OK\Model\Cash\Transaction $okResponse */
            $okResponse = $okCashClient->get($checkout->getGuid());
        } catch (\OK\Model\Network\Exception\NetworkException $exception) {
            return $this->redirectWithError($this->__('Your OK transaction was not found.'));
        }

        $checkout->setState($okResponse->state);
        $checkout->save();

        if ($okResponse->state != Okitcom_OkLibMagento_Helper_Config::STATE_CHECKOUT_SUCCESS) {
            return $this->redirectWithError(
                $this->messageForState($okResponse->state)
            );
        }
        if ($checkout->getSalesOrderId() != null) {
            return $this->redirectWithSuccess(Mage::getModel('sales/order')->load($checkout->getSalesOrderId()));
        }

        $quote = Mage::getModel('sales/quote')->load($checkout->getQuoteId());
        if ($quote == null) {
            Mage::logException(new Okitcom_OkLibMagento_Helper_Checkout_Exception("Could not find quote on OK transaction object. Checkout: " . $checkout->getId()));
            return $this->redirectWithError($this->__('An error occurred while creating your order.'));
        }
        $order = Mage::helper('oklibmagento/order')->createOrder($quote, $okResponse);
        if ($order == null) {
            Mage::logException(new Okitcom_OkLibMagento_Helper_Checkout_Exception("Could not create order for checkout: " . $checkout->getId()));
            return $this->redirectWithError($this->__('An error occurred while creating your order.'));
        }

        $discountOk = $okResponse->authorisationResult->amount->sub($okResponse->amount);

        $checkout->setDiscount(-$discountOk->getCents());
        $checkout->setSalesOrderId($order->getId());
        $checkout->save();

        return $this->redirectWithSuccess($order);
    }

    private function redirectWithError($message) {
        Mage::getSingleton('core/session')->addError($message);
        return $this->_redirect("checkout/onepage/failure");
    }

    private function redirectWithSuccess($order) {
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $session = Mage::getSingleton('checkout/session');
        $session->setLastQuoteId($quote->getId());
        $session->setLastSuccessQuoteId($quote->getId());
        $session->setLastOrderId($order->getId());
        $session->setLastRealOrderId($order->getIncrementId());
        $session->setLastOrderStatus($order->getStatus());

        Mage::getSingleton('checkout/session')->getQuote()
            ->setReservedOrderId(null)
            ->setIsActive(0)
            ->save();
        Mage::getSingleton('checkout/session')->setQuoteId(null);

//        $freshQuote = Mage::getModel('sales/quote')
//            ->assignCustomer($order->getCustomer())
//            ->setStoreId(Mage::app()->getStore()->getId());
//        Mage::getSingleton('checkout/cart')->setQuote($freshQuote);

        return $this->_redirect("checkout/onepage/success");
    }

    /**
     * @param $state string
     * @return string localized error message
     */
    private function messageForState($state) {
        $key = "An unknown error occurred.";
        switch ($state) {
            case "NewPendingApproval":
                $key = "Your  OK transaction has not been completed. Please use your OK app to scan the QR code.";
                break;
            case "NewPendingTrigger":
            case "ApprovedPendingAuthorisation":
                $key = "Your OK transaction has not been completed. Please use your OK app to complete the transaction.";
                break;
                // Issuer issues
            case "DeclinedByIssuer":
            case "ErrorUnableToApprove":
                $key = "Your issuer was not able to fulfill the transaction. Please try again.";
                break;
            case "DeclinedByUserPendingVoid":
            case "AuthorisedPendingCapture":
                $key = "Your OK transaction is still processing.";
                break;
            case "ClosedAndVoided":
                $key = "You have declined the OK transaction in your app.";
                break;
            case "ErrorAndVoided":
                $key = "Your OK transaction has been declined.";
                break;
            case "Cancelled":
                $key = "Your OK transaction has been cancelled by the merchant.";
                break;

            case "Refunded":
            case "ClosedAndCaptured":
            case "ErrorUnableToGetAuthorisation":
                break;
        }
        return $this->__($key);
    }


    /**
     * Get request for product add to cart procedure
     *
     * @param mixed $requestInfo
     * @return Varien_Object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object();
            $request->setQty($requestInfo);
        } else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }
        return $request;
    }

    protected function jsonError($message) {
        $this->getResponse()->setHeader(
            'Content-type',
            'application/json'
        );

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode([
                "error" => $message
            ])
        );
    }

    /**
     * Adding products to cart by ids
     *
     * @param   array $productIds
     */
    public function addProductsByIds($quote, $productIds)
    {
        $allAvailable = true;
        $allAdded     = true;

        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $productId = (int) $productId;
                if (!$productId) {
                    continue;
                }
                $product = $this->_getProduct($productId);
                if ($product->getId() && $product->isVisibleInCatalog()) {
                    try {
                        $quote->addProduct($product);
                    } catch (Exception $e){
                        $allAdded = false;
                    }
                } else {
                    $allAvailable = false;
                }
            }
        }
    }

    /**
     * Get product object based on requested product information
     *
     * @param   mixed $productInfo
     * @return  Mage_Catalog_Model_Product
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Mage_Catalog_Model_Product) {
            $product = $productInfo;
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productInfo);
        }
        $currentWebsiteId = Mage::app()->getStore()->getWebsiteId();
        if (!$product
            || !$product->getId()
            || !is_array($product->getWebsiteIds())
            || !in_array($currentWebsiteId, $product->getWebsiteIds())
        ) {
            Mage::throwException(Mage::helper('checkout')->__('The product could not be found.'));
        }
        return $product;
    }

}