# Drupal Installer for Composer
[![Coverage Status](https://coveralls.io/repos/github/pnnl/drupal-installer/badge.svg?branch=master)](https://coveralls.io/github/pnnl/drupal-installer?branch=master)

A composer installer for Drupal. Installs modules, themes, etc to their proper location within the Drupal filesystem.

## How to install
```bash
composer require pnnl/drupal-installer:^7.0
```

Major version corresponds to major Drupal version.

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

This does conflict with [`composer/installers`](https://packagist.org/packages/composer/installers). If your project or a dependency requires [`composer/installers`](https://packagist.org/packages/composer/installers) you
can disable [`composer/installers`](https://packagist.org/packages/composer/installers)'s Drupal installer with the following `extra` config and this [patch](https://gist.githubusercontent.com/thomscode/8ad286a97ce9efbdf5829ba9e79fcb85/raw/9387aa8aea2ca3f870b61d44c38ff2e5211d271b/composer-installers.diff).

```json
{
  "extra": {
    "installer-disable": [
      "drupal"
    ]
  }
}
```

Patch can be automatically applied by composer using [`cweagans/composer-patches`](https://packagist.org/packages/cweagans/composer-patches).
