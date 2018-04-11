<?php
/**
 * Created by PhpStorm.
 * Date: 3/20/18
 */

class Okitcom_OkLibMagento_CallbackController extends Mage_Core_Controller_Front_Action {

    private $headerVersion = "X-Auth-Version";
    private $headerTimestamp = "X-Auth-Timestamp";
    private $headerSignature = "X-Auth-Signature";

    public function cashAction() {
        if (!Mage::helper('oklibmagento/oklib')->isServiceEnabled(Okitcom_OkLibMagento_Helper_Oklib::SERVICE_TYPE_CASH)) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setBody(
                "Page not found"
            );
        }

        try {
            $this->verifySignature();
        } catch (Okitcom_OkLibMagento_Helper_Callback_Exception $exception) {
            Mage::logException($exception);
            $this->log("Invalid signature for OK Cash callback.");

            $this->respondNotOK("Invalid signature");
            return;
        }

        $guid = $this->getRequest()->getPost("guid");
        $state = $this->getRequest()->getPost("state");

        /** @var Okitcom_OkLibMagento_Model_Checkout $checkout */
        $checkout = Mage::getModel('oklibmagento/checkout')->load($guid, "guid");
        if ($checkout->getId() == null) {
            $this->respondNotOK("Transaction not found");
            return;
        }

        /** @var \OK\Service\Cash $okCash */
        $okCash = Mage::helper('oklibmagento/oklib')->getCashClient($checkout->getStore());
        try {
            $okResponse = $okCash->get($guid);
            if ($okResponse != null) {

                $checkout->setState($okResponse->state);
                $checkout->save();

                if ($okResponse->state == Okitcom_OkLibMagento_Helper_Config::STATE_CHECKOUT_SUCCESS) {
                    Mage::helper('oklibmagento/checkout')->createOrder($checkout, $okResponse);
                }

                $this->respondOK("Transaction processed");
            } else {
                $this->respondNotOK("Could not get transaction state");
            }

        } catch (\Exception $e) {
            Mage::logException($e);
            $this->log("Could not update OK transaction with id " . $checkout->getId() . ". Message: " . $e->getMessage());
            $this->respondNotOK("Could not create order");
            return;
        }

    }

    private function respondOK($message) {
        $this->getResponse()->setBody(
            $message
        );
    }

    private function respondNotOK($message) {
        $this->getResponse()->setHeader('HTTP/1.1','400 Bad Request');
        $this->getResponse()->setBody(
            $message
        );
    }

    /**
     * @throws Okitcom_OkLibMagento_Helper_Callback_Exception when validation failed.
     */
    private function verifySignature() {
        $request = $this->getRequest();
        $okSignature = $request->getHeader($this->headerSignature);

        $method = $request->getMethod();
        $timestamp = $request->getHeader($this->headerTimestamp);

        $path = $request->getPathInfo();
        $queryString = http_build_query($request->getQuery());
        $fullPath = $path . "?" . $queryString;
        $content = $request->getRawBody();

        if ($okSignature === false || $method === false || $timestamp === false || $path === false || $queryString === false) {
            $maxLength = 128;
            $data = json_encode([
                "okSignature" => substr($okSignature, 0, $maxLength),
                "method" => substr($method, 0, $maxLength),
                "timestamp" => substr($timestamp, 0, $maxLength),
                "path" => substr($path, 0, $maxLength),
                "queryString" => substr($queryString, 0, $maxLength)
            ]);
            throw new Okitcom_OkLibMagento_Helper_Callback_Exception("Signature is invalid. " . $data);
        }

        $unixTimestamp = strtotime($timestamp);
        $acceptableInterval = 60; // 1 minute

        if (abs(time() - $unixTimestamp) > $acceptableInterval) {
            throw new Okitcom_OkLibMagento_Helper_Callback_Exception("Timestamp is too different.");
        }

        $data = $method . "\n" . $timestamp . "\n" . $fullPath;

        if ($content !== false) {
            $data .= "\n" . $content;
        }

        $cashSecret = Mage::helper('oklibmagento/oklib')->getSecretKey(Okitcom_OkLibMagento_Helper_Oklib::SERVICE_TYPE_CASH);

        $calculatedSignature = base64_encode(hash_hmac('sha256', $data, $cashSecret, true));

        if ($calculatedSignature == null) {
            throw new Okitcom_OkLibMagento_Helper_Callback_Exception("Calculated signature is null.");
        }

        if ($calculatedSignature !== $okSignature) {
            throw new Okitcom_OkLibMagento_Helper_Callback_Exception("Signatures do not match");
        }

//        var_dump($okSignature);
//        var_dump($method);
//        var_dump($timestamp);
//        var_dump($fullPath);
//        var_dump($content);
//        var_dump($data);
    }

    private function log($message) {
        Mage::log($message, null, Okitcom_OkLibMagento_Helper_Config::LOGFILE);
    }


}