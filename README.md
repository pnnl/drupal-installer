# Drupal 7 Composer Installer
[![Coverage Status](https://coveralls.io/repos/github/pnnl/drupal-installer/badge.svg?branch=master)](https://coveralls.io/github/pnnl/drupal-installer?branch=master)

A composer installer for Drupal 7. Installs modules, themes, etc to their proper location within the Drupal filesystem.

## How to install
```bash
composer require pnnl/drupal-installer:^7.0
```

## How to use
No configuration required. Will install all Drupal components to their proper locations automatically.

## Configuration
```json
{
  "extra": {
    "drupal-installer": {
      "webroot": "docroot",
      "npm-support": true,
      "bower-support": true
    }
  }
}
```

The Drupal webroot is configurable relative to the root of your repository.  
Default webroot is `docroot`.

Support `npm-asset` and `bower-asset`
(from [fxp/composer-asset-plugin](https://packagist.org/packages/fxp/composer-asset-plugin) and [hiqdev/asset-packagist](https://packagist.org/packages/hiqdev/asset-packagist))
as `drupal-library`'s.
This installs packages of type `npm-asset` and `bower-asset` into Drupal's libraries folder.  
Default: `true`. Support for NPM and Bower assets can be enabled/disabled individually.
