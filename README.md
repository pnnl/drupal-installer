# Drupal Installer for Composer
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/PNNL/Drupal-Installer/blob/master/LICENSE.md)
[![Travis CI](https://travis-ci.org/pnnl/drupal-installer.svg?branch=master)](https://travis-ci.org/pnnl/drupal-installer)
[![Coverage Status](https://coveralls.io/repos/github/pnnl/drupal-installer/badge.svg?branch=master)](https://coveralls.io/github/pnnl/drupal-installer?branch=master)

A composer installer for Drupal. Installs modules, themes, etc to their proper location within the Drupal filesystem.

## How to install
```bash
composer require pnnl/drupal-installer:^8.0
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



Supports `npm-asset` and `bower-asset`
(from [fxp/composer-asset-plugin](https://packagist.org/packages/fxp/composer-asset-plugin) and [hiqdev/asset-packagist](https://packagist.org/packages/hiqdev/asset-packagist))
as `drupal-library`'s.
This installs packages of type `npm-asset` and `bower-asset` into Drupal's libraries folder.
Default: `true`. Support for NPM and Bower assets can be enabled/disabled individually.

This does conflict with [`composer/installers`](https://packagist.org/packages/composer/installers). If your project or a dependency requires [`composer/installers`](https://packagist.org/packages/composer/installers) you can disable [`composer/installers`](https://packagist.org/packages/composer/installers)'s Drupal installer with the following `extra` config and this the latest dev version of `composer/installers`.

```json
{
  "extra": {
    "installer-disable": [
      "drupal"
    ]
  }
}
```

You can use this version of `composer/installers` by including it as follows in your `composer.json` file.

```json
{
  "require": {
    "composer/installers": "dev-master as 1.5.x-dev"
  }
}
```

## Disclaimer
This material was prepared as an account of work sponsored by an agency of the United States Government.  Neither the United States Government nor the United States Department of Energy, nor Battelle, nor any of their employees, nor any jurisdiction or organization that has cooperated in the development of these materials, makes any warranty, express or implied, or assumes any legal liability or responsibility for the accuracy, completeness, or usefulness or any information, apparatus, product, software, or process disclosed, or represents that its use would not infringe privately owned rights.

Reference herein to any specific commercial product, process, or service by trade name, trademark, manufacturer, or otherwise does not necessarily constitute or imply its endorsement, recommendation, or favoring by the United States Government or any agency thereof, or Battelle Memorial Institute. The views and opinions of authors expressed herein do not necessarily state or reflect those of the United States Government or any agency thereof.

<p align="center">
PACIFIC NORTHWEST NATIONAL LABORATORY<br />
<em>operated by</em><br />
BATTELLE<br />
<em>for the</em><br />
UNITED STATES DEPARTMENT OF ENERGY<br />
<em>under Contract DE-AC05-76RL01830</em><br />
</p>
