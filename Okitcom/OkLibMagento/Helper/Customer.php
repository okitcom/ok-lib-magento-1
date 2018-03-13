<?php
/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Customer extends Mage_Core_Helper_Abstract
{

    /**
     * @param $token
     * @return Mage_Customer_Model_Customer|null
     */
    public function findByToken($token) {
        return Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->getCollection()
            ->addAttributeToSelect(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN)
            ->addAttributeToFilter(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN, $token)
            ->getFirstItem();
    }

    public function findByEmail($email) {
        return $customer = Mage::getModel("customer/customer")
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);
    }

    public function findOrCreate($token, $email, $firstName, $lastName) {
        $customerByToken = $this->findByToken($token);
        if ($customerByToken->getId()) {
            return $customerByToken;
        }
        $customerByEmail = $this->findByEmail($email);
        if ($customerByEmail->getId()) {
            $customerByEmail->setData(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN, $token);
            $customerByEmail->save();
            return $customerByEmail;
        }
        return $this->create($token, $firstName, $lastName, $email);
    }

    public function create($token, $firstName, $lastName, $email) {
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->setStore(Mage::app()->getStore())
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setEmail($email)
            ->setData(
                Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN, $token
            );
        $customer->save();
        return $customer;
    }

}