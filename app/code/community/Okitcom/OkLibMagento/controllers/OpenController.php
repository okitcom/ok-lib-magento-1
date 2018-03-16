<?php

use OK\Builder\AttributeBuilder;
use OK\Builder\AuthorisationRequestBuilder;
use OK\Model\Attribute;

class Okitcom_OkLibMagento_OpenController extends Mage_Core_Controller_Front_Action {

    public function initAction() {
        Okitcom_OkLibMagento_Model_Observer::init();

        if (!Mage::helper('oklibmagento/oklib')->isServiceEnabled(Okitcom_OkLibMagento_Helper_Oklib::SERVICE_TYPE_OPEN)) {
            return $this->jsonError(Mage::helper('core')->__("Could not initiate OK Open: Service is not enabled."));
        }

        $externalIdentifier = Mage::helper('core')->getRandomString(32);

        $authorization = Mage::getModel('oklibmagento/authorization');
        $authorization->setExternalId($externalIdentifier);
        $authorization->save();

        $redirectUrl = Mage::getUrl('oklib/open/callback', [
            "_secure" => true,
            "authorization" => $externalIdentifier
        ]);

        $okOpenClient = Mage::helper('oklibmagento/oklib')->getOpenClient();

        $authorisationRequest = (new AuthorisationRequestBuilder())
            ->setPermissions("TriggerPaymentInitiation")
            ->setAction("SignupLogin")
            ->setRedirectUrl($redirectUrl)
            ->setReference("Online")
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
                    ->setKey("phone")
                    ->setLabel("Phone")
                    ->setType(Attribute::TYPE_PHONENUMBER)
                    ->setRequired(false)
                    ->build()
            )->build();

        try {
            $response = $okOpenClient->request($authorisationRequest);
        } catch (\OK\Model\Network\Exception\NetworkException $exception) {
            Mage::logException($exception);
            return $this->jsonError(Mage::helper('core')->__("Could not initiate OK Open."));
        }

        $authorization->setGuid($response->guid);
        $authorization->setOkTransactionId($response->id);
        $authorization->setState($response->state);
        $authorization->save();

        $this->getResponse()->setHeader(
            'Content-type',
            'application/json'
        );

        if (!isset($response->guid)) {
            Mage::throwException($this->__("Could not initiate request with OK."));
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode([
                "guid" => $response->guid,
                "culture" => Mage::helper('oklibmagento/checkout')->getLocale(),
                "environment" => Mage::helper('oklibmagento/checkout')->getOkLibEnvironment(),
                "initiation" => false
            ])
        );
    }

    public function callbackAction() {
        $authorizationIdentifier = $this->getRequest()->getParam("authorization");

        sleep(3);

        if ($authorizationIdentifier == null) {
            return $this->redirectWithError($this->__('Your OK login could not be processed.'));
        }

        $authorization = Mage::getModel('oklibmagento/authorization');
        $authorization->load($authorizationIdentifier, "external_id");
        if ($authorization == null) {
            return $this->redirectWithError($this->__('Your OK login could not be processed.'));
        }

        /** @var \OK\Service\Open $okOpenClient */
        $okOpenClient = Mage::helper('oklibmagento/oklib')->getOpenClient();

        try {
            /** @var \OK\Model\Open\AuthorisationRequest $okResponse */
            $okResponse = $okOpenClient->get($authorization->getGuid());
        } catch (\OK\Model\Network\Exception\NetworkException $exception) {
            return $this->redirectWithError($this->__('Your OK login could not be processed.'));
        }

        // Only process once
        $shouldProcess = $authorization->getState() != Okitcom_OkLibMagento_Helper_Config::STATE_AUTHORIZATION_SUCCESS;
        $authorization->setState($okResponse->state);
        $authorization->save();

        if (!$shouldProcess) {
            return $this->redirectWithError($this->__('Your OK login has expired. Please try again.'));
        }

        if ($okResponse->state != Okitcom_OkLibMagento_Helper_Config::STATE_AUTHORIZATION_SUCCESS) {
            return $this->redirectWithError(
                $this->messageForState($okResponse->state)
            );
        }
        if ($okResponse->authorisationResult->result != Okitcom_OkLibMagento_Helper_Config::STATE_AUTHORIZATION_SUCCESS_OK) {
            return $this->redirectWithError($this->__("You have declined the OK login in your app."));
        }

        $customerHelper = Mage::helper('oklibmagento/customer');
        $nameParts = explode(";", $okResponse->attributes->get("name")->value);

        $store = Mage::app()->getStore();
        $customer = $customerHelper->findOrCreate(
            $okResponse->token,
            $okResponse->attributes->get("email"),
            $nameParts[0],
            $nameParts[1],
            $store->getWebsiteId(),
            $store->getId()
        );

        $customerSession = Mage::getSingleton('customer/session');

        $customerSession->setCustomerAsLoggedIn($customer);
        $beforeUrl = $customerSession->getBeforeAuthUrl();
        $url =  $beforeUrl ? $beforeUrl : Mage::helper('customer')->getLoginUrl();

        Mage::getSingleton('core/session')->addSuccess($this->__("Succesfully logged in with OK."));
        return $this->_redirectUrl($url);

    }

    private function redirectWithError($message) {
        Mage::getSingleton('core/session')->addError($message);
        return $this->_redirect("customer/account/login");
    }

    /**
     * @param $state string
     * @return string localized error message
     */
    private function messageForState($state) {
        $key = "An unknown error occurred.";
        switch ($state) {
            case "NewPendingApproval":
                $key = "Your OK transaction has not been completed. Please use your OK app to scan the QR code.";
                break;
            case "NewPendingTrigger":
                $key = "Your OK transaction has not been completed. Please use your OK app to complete the transaction.";
                break;

            case "Processing":
                $key = "Your OK transaction is still processing.";
                break;
            case "Processed":
            case "Error":
                break;
        }
        return $this->__($key);
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


}