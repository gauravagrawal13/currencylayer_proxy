CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration

INTRODUCTION
------------

The Currencylayer Proxy module creates ability to use currency layer service/api.

REQUIREMENTS
------------

This module requires no modules outside of Drupal core.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit:
   https://www.drupal.org/docs/extending-drupal/installing-drupal-modules
   for further information.

CONFIGURATION
-------------

The module has no menu or modifiable settings. There is no configuration.

Simply visit /api/currency to access default Exchange Rates & Currency
data.

Example of filtering results with parameters:
/api/currency?start_date=2023-01-01&end_date=2023-03-06&source=USD&currencies=GBP,EUR

REFERENCES
-------------
https://apilayer.com/marketplace/currency_data-api?live_demo=show#endpoints
https://apilayer.com/marketplace/currency_data-api?live_demo=show#errors
