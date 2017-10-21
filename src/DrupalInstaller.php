<?php
/**
 * Created by PhpStorm.
 * User: thomscode
 * Date: 10/20/17
 * Time: 12:45 PM
 */

namespace thomscode\Composer;


use Composer\Composer;
use Composer\Installer\BinaryInstaller;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;

class DrupalInstaller extends LibraryInstaller
{
    /** @var array $types - types supported by this installer */
    protected $types;

    public function __construct(
      \Composer\IO\IOInterface $io,
      \Composer\Composer $composer,
      $type = 'drupal',
      \Composer\Util\Filesystem $filesystem = null,
      \Composer\Installer\BinaryInstaller $binaryInstaller = null
    ) {
        $this->types = [
          "drupal-core",
          "drupal-drush",
          "drupal-library",
          "drupal-module",
          "drupal-custom-module",
          "drupal-theme",
          "drupal-custom-theme",
          "drupal-profile",
          "drupal-custom-profile",
        ];

        parent::__construct($io, $composer, $type, $filesystem,
          $binaryInstaller);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return in_array($packageType, $this->types);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        // TODO: Define default base path
        // TODO: Define how Extra is defined in composer.json
        // TODO: Retrieve extra config for base path
        // TODO: allow user to define custom path (maybe)
        $basePath = $this->composer->getConfig()->get("extra");

        switch ($package->getType()) {
            case "drupal-core":
                return $basePath;
            case "drupal-drush":
                return "drush/contrib";
            case "drupal-library":
                return "$basePath/libraries/";
            case "drupal-module":
                return "$basePath/modules/contrib";
            case "drupal-custom-module":
                return "$basePath/modules/custom";
            case "drupal-theme":
                return "$basePath/themes/contrib";
            case "drupal-custom-theme":
                return "$basePath/themes/custom";
            case "drupal-profile":
                return "$basePath/profiles/contrib";
            case "drupal-custom-profile":
                return "$basePath/profiles/custom";
            default:
                return parent::getInstallPath($package);
        }
    }
}
