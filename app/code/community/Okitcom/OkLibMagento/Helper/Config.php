<?php
/**
 * Created by PhpStorm.
 * Date: 3/5/18
 */

class Okitcom_OkLibMagento_Helper_Config extends Mage_Core_Helper_Abstract
{

    const OK_SECTION = "okcheckout";

    const OK_GROUP_GENERAL = "general";
    const OK_GROUP_CASH = "okcash";
    const OK_GROUP_OPEN = "okopen";

    const EAV_OKTOKEN = "oktoken";

    const DISCOUNT_CODE = "oklibmagento";
    const DISCOUNT_LABEL = "Discount (OK)";
    const PAYMENT_METHOD_CODE = "okcash";

    const DEFAULT_LOCALE = "en_GB";

    const DATE_DB_FORMAT = 'Y-m-d H:i:s';
    const DATE_PENDING_OFFSET = ' -1 day'; // One day in the past
//    const PENDING_STATES = [
//        "NewPendingTrigger", "NewPendingApproval"
//    ];
    const STATE_CHECKOUT_SUCCESS = "ClosedAndCaptured";
    const STATE_AUTHORIZATION_SUCCESS = "Processed";
    const STATE_AUTHORIZATION_SUCCESS_OK = "OK";

    const STATE_CHECKOUT_CANCELLED = "Cancelled";
    const STATE_CHECKOUT_UNSCANNED = "NewPendingTrigger";

    const LOGFILE = "oklib.log";

    public function getOkGeneralValue($key) {
        return $this->getValue(self::OK_SECTION, self::OK_GROUP_GENERAL, $key);
    }

    public function getOkCashValue($key) {
        return $this->getValue(self::OK_SECTION, self::OK_GROUP_CASH, $key);
    }

    public function getOkOpenValue($key) {
        return $this->getValue(self::OK_SECTION, self::OK_GROUP_OPEN, $key);
    }

    public function getOkValue($group, $key) {
        return $this->getValue(self::OK_SECTION, $group, $key);
    }

    public function getValue($section, $group, $key) {
        return Mage::getStoreConfig(
            $section . '/' . $group . '/' . $key,
            Mage::app()->getStore()
        );
    }

}