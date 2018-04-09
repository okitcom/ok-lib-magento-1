<?php
/**
 * Created by PhpStorm.
 * Date: 3/6/18
 */

class Okitcom_OkLibMagento_Helper_Customer extends Mage_Core_Helper_Abstract
{

    /**
     * @param $token
     * @param $websiteId
     * @return Mage_Customer_Model_Customer|null
     */
    public function findByToken($token, $websiteId) {
        return Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->getCollection()
            ->addAttributeToSelect(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN)
            ->addAttributeToFilter(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN, $token)
            ->getFirstItem();
    }

    public function findByEmail($email, $websiteId) {
        return $customer = Mage::getModel("customer/customer")
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);
    }

    public function findOrCreate($token, $email, $firstName, $lastName, $websiteId, $storeId) {
        $customerByToken = $this->findByToken($token, $websiteId);
        if ($customerByToken->getId()) {
            return $customerByToken;
        }
        $customerByEmail = $this->findByEmail($email, $websiteId);
        if ($customerByEmail->getId()) {
            $customerByEmail->setData(Okitcom_OkLibMagento_Helper_Config::EAV_OKTOKEN, $token);
            $customerByEmail->save();
            return $customerByEmail;
        }
        return $this->create($token, $firstName, $lastName, $email, $websiteId, $storeId);
    }

    public function create($token, $firstName, $lastName, $email, $websiteId, $storeId) {
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId($websiteId)
            ->setStoreId($storeId)
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