<?php

class Okitcom_OkLibMagento_Model_Config_Source_Environment {

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray() {
        return [
            [
                "value" => 'secure',
                "label" => 'Production'
            ],
            [
                "value" => 'beta',
                "label" => 'Beta'
            ],
        ];
    }
}