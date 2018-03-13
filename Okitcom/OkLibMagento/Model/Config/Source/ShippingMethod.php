<?php
/**
 * Created by PhpStorm.
 * Date: 8/15/17
 */


class Okitcom_OkLibMagento_Model_Config_Source_ShippingMethod
{

    private function getShippingMethods($isMultiSelect = false) {

        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        $options = array();

        foreach($methods as $carrierCode => $carrierModel)
        {
            $methods = array();
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $carrierCode . '_' . $methodCode;
                    $methods[] = array('value' => $code, 'label' => $method);

                }
                $carrierTitle = Mage::getStoreConfig("carriers/$carrierCode/title");

                $options[] = array('value' => $methods, 'label' => $carrierTitle);
            }

        }

        if($isMultiSelect)
        {
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray($isMultiSelect = false) {
        return $this->getShippingMethods(true);
    }
}