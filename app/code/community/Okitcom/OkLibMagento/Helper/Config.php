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
    const DATE_PENDING_OFFSET = ' -3 days'; // One day in the past
//    const PENDING_STATES = [
//        "NewPendingTrigger", "NewPendingApproval"
//    ];
    const STATE_OK_EXTERNAL_ERROR = "OkReturnedError";
    const STATE_CHECKOUT_SUCCESS = "ClosedAndCaptured";
    const STATE_AUTHORIZATION_SUCCESS = "Processed";
    const STATE_AUTHORIZATION_SUCCESS_OK = "OK";

    const STATE_CHECKOUT_CANCELLED = "Cancelled";
    const STATE_CHECKOUT_UNSCANNED = "NewPendingTrigger";

    const LOGFILE = "oklib.log";

    public function getOkGeneralValue($key, $store = null) {
        return $this->getValue(self::OK_SECTION, self::OK_GROUP_GENERAL, $key, $store);
    }

    public function getOkCashValue($key, $store = null) {
        return $this->getValue(self::OK_SECTION, self::OK_GROUP_CASH, $key, $store);
    }

    public function getOkOpenValue($key, $store = null) {
        return $this->getValue(self::OK_SECTION, self::OK_GROUP_OPEN, $key, $store);
    }

    public function getOkValue($group, $key, $store = null) {
        return $this->getValue(self::OK_SECTION, $group, $key, $store);
    }

    public function getValue($section, $group, $key, $store = null) {
        if ($store == null) {
            // Try the default
            $store = Mage::app()->getStore();
        }
        return Mage::getStoreConfig(
            $section . '/' . $group . '/' . $key,
            $store
        );
    }

}