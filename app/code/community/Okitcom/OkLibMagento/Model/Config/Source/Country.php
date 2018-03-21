<?php

class Okitcom_OkLibMagento_Model_Config_Source_Country {

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray() {
        return [
            [
                "value" => 'NL',
                "label" => 'Nederland'
            ]
        ];
    }
}