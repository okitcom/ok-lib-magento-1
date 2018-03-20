<?php
/**
 * Created by PhpStorm.
 * Date: 8/28/17
 */

class Okitcom_OkLibMagento_Block_Adminhtml_Works extends Mage_Payment_Block_Info
{

    /**
     * Set block template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('oklibmagento/payment_info.phtml');
    }


    /**
     * Prepare credit card related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $checkout = $this->getOrderOK();
        if ($checkout->getId() == null) {
            return $transport;
        }
        $data = array();
        $data["Transaction"] = $checkout->getOkTransactionId();
        return $transport->setData(array_merge($data, $transport->getData()));
    }

    /**
     * @return bool whether this order was paid using OK.
     */
    private function orderIsOK() {
        return $this->getOrderOK() != null;
    }

    /**
     * Get OK checkout for this order.
     * @return null|\Okitcom_OkLibMagento_Model_Checkout if any
     */
    private function getOrderOK() {
        $orderId = $this->getInfo()->getOrder()->getId();
        if ($orderId != null) {
            $ok = Mage::getModel('oklibmagento/checkout')->load($orderId, 'sales_order_id');
            return $ok;
        }
        return null;
    }

    /**
     * Get the url of this transaction in OK Works.
     * @return string
     */
    public function getOkWorksUrl() {
        $checkout = $this->getOrderOK();
        if ($checkout == null) {
            return null;
        }
        $env = Mage::helper('oklibmagento/config')->getOkGeneralValue("environment");
        $baseUrl = "okit.com";
        switch ($env) {
            case "beta":
                $baseUrl = "beta.okit.io";
                break;
            case "dev":
                $baseUrl = "dev.okit.io";
                break;
            default:
                break;
        }
        return 'https://' . $baseUrl . '/okworks/#/transactions/' . $checkout->getOkTransactionId();
    }

}