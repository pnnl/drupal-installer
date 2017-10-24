# Drupal 7 Composer Installer
A composer installer for Drupal 7. Installs modules, themes, etc to their proper location within the Drupal filesystem.

## How to install
```bash
composer require thomscode/drupal-installer:^7.0
```

## How to use
No configuration required. Will install all Drupal components to their proper locations automatically.

## Configuration
```json
{
  "extra": {
    "drupal-installer": {
      "webroot": "docroot"
    }
  }
}
```

The Drupal webroot is configurable relative to the root of your repository. Default webroot is `docroot`.
