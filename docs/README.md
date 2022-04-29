

# OXID 6.X

v1.0.33, 2022-4

This repository contains the OXID  PostFinance Checkout payment module that enables the shop to process payments with [PostFinance Checkout](https://www.postfinance.ch/checkout).

##### To use this extension, a [PostFinance Checkout](https://www.postfinance.ch/checkout) account is required.

## Requirements

* [Oxid](https://www.oxid-esales.com/) 6.0, 6.1, 6.2
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

Support queries can be issued on the [PostFinance Checkout support site](https://www.postfinance.ch/en/business/support/written-contact/contact-form.html).

## Documentation

* [English](https://plugin-documentation.postfinance-checkout.ch/pfpayments/oxid-6.0/1.0.33/docs/en/documentation.html)

## License

Please see the [license file](https://github.com/pfpayments/oxid-6.0/blob/1.0.33/LICENSE) for more information.