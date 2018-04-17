[![Build Status](https://travis-ci.org/okitcom/ok-lib-magento-1.svg?branch=master)](https://travis-ci.org/okitcom/ok-lib-magento-1)
# OK Lib
This is the OK Lib plugin for Magento 1.x. This module provides OK Cash and OK Open functionality.

## Installation
* Clone this repository in a subfolder
* Copy the files into the magento installation, using a tool like `rsync`
* Refresh any caches
* Go to site configuration `Sales -> OK`
* Enable the OK Cash service, OK Open service, or both, by entering the API keys from OK Works. For OK Cash, select a shipping method to use by default in OK transactions.

The commands of a typical installation may look as follows:
```bash
git clone https://github.com/okitcom/ok-lib-magento-1
rsync -a ok-lib-magento-1/app/ magento/app/
rsync -a ok-lib-magento-1/skin/ magento/skin/
php -r 'require "magento/app/Mage.php"; Mage::app()->getCacheInstance()->flush();'
```

## Using the OK Cash webhook
The OK Cash service provides a functionality to notify the website when a transaction has reached a final state in the form of a webhook.

The plugin automatically provides an endpoint in the website to listen for callbacks from OK. However, the url of this endpoint must be provided in the Cash service in OK Works. The url must use `https` and looks as follows:
```
Template: {base_url_secure}/oklib/callback/cash
Example:  https://oklibmagento1.okit.io/oklib/callback/cash
```

## Features
* OK Cash checkout from cart, which, when successful, will create a normal, paid, magento order, including:
    * Correctly processing OK discounts, alongside magento cart discounts
    * Store orders on customer object, matching by OK token
    * Display of discount in admin
    * Order fulfillment and cancellation using a cron script
    * Refund orders using credit memo's in the admin dashboard
    * Button template blocks to be included on any relevant page
    * 'Buy now' functionality, to instantly buy a single product without a cart.
    
* OK Open, let users log without having to create an account.


## Blocks
The plug-in includes three button types in the form of front-end blocks. Buttons can be placed in the following locations.
* OK Open (oklibmagento/authorization_button)
    * In menu bar
    * Account sign in page
* OK Cash
    * On product page (buy now) (oklibmagento/checkout_buynow)
    * On catalog page (buy now) (oklibmagento/checkout_buynow)
    * In cart (oklibmagento/checkout_button)
    * On checkout page (oklibmagento/checkout_button)
        * As text link
    * In minicart (oklibmagento/checkout_button)

OK can also be initiated using a text link instead of a button

## Logs
The cron script logs can be found `oklib.log` file.

## Changelog
__0.3.0__ Include support for the OK Cash webhook functionality

__0.2.3__ Fix bugs with OK API key management per store 

__0.2.2__ Fix bugs with customer creation over multiple stores in cron

__0.2.1__ Fix javascript event bugs, add support for docker deployment of plugin

__0.2.0__ Support for multiple stores

__0.1.0__ Initial release

## Known issues
* Magento coupon codes can only be applied in OK transactions if they are applicable to logged in as well as not logged in customers.
* Shipping price must be per-order, not per-item.
* Discounts cannot be applied to shipping