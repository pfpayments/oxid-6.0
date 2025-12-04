

# OXID 6.X

v1.0.53, 2025-12

This repository contains the OXID  PostFinance Checkout payment module that enables the shop to process payments with [PostFinance Checkout](https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).

##### To use this extension, a [PostFinance Checkout](https://checkout.postfinance.ch/en-ch/user/signup) account is required.

## Requirements

* [Oxid](https://www.oxid-esales.com/) 6.x
* [PHP](http://php.net/) 5.6 or later

## Install Oxid 6.2+

 Run on the same path via terminal (required on oxid 6.2 upwards) this command to install the plugin: +
```
composer require postfinancecheckout/oxid-6.0
```
If the plugin still don't work you need to run these commands:
```
./vendor/bin/oe-console oe:module:install source/modules/pfc/PostFinanceCheckout
./vendor/bin/oe-console oe:module:install-configuration source/modules/pfc/PostFinanceCheckout
./vendor/bin/oe-console oe:module:activate PostFinanceCheckout
./vendor/bin/oe-console oe:module:apply-configuration
```

## Support

Support queries can be issued on the [PostFinance Checkout support site](https://www.postfinance.ch/en/business/support.html).

## Documentation

* [English](https://plugin-documentation.postfinance-checkout.ch/pfpayments/oxid-6.0/1.0.53/docs/en/documentation.html)

## License

Please see the [license file](https://github.com/pfpayments/oxid-6.0/blob/1.0.53/LICENSE) for more information.