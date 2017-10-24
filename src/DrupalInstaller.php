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

    /** @const string EXTRA */
    const EXTRA = "drupal-installer";

    /** @const string ROOT */
    const ROOT = "webroot";

    /** @const int CORE */
    const CORE = 0;

    /** @const int DRUSH */
    const DRUSH = 1;

    /** @const int LIBRARY */
    const LIBRARY = 2;

    /** @const int MODULE */
    const MODULE = 3;

    /** @const int THEME */
    const THEME = 4;

    /** @const int PROFILE */
    const PROFILE = 5;


    /** @var array $types - types supported by this installer */
    protected $types;

    /** @var array $config */
    protected $config;

    /** @var PackageInterface $package */
    protected $package;

    /**
     * DrupalInstaller constructor.
     *
     * @param IOInterface $io
     * @param Composer $composer
     * @param string $type
     * @param Filesystem|null $filesystem
     * @param BinaryInstaller|null $binaryInstaller
     */
    public function __construct(
      IOInterface $io,
      Composer $composer,
      $type = 'drupal',
      Filesystem $filesystem = null,
      BinaryInstaller $binaryInstaller = null
    ) {
        parent::__construct($io, $composer, $type, $filesystem,
          $binaryInstaller);

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

        // Load configuration from composer.json
        $extra = $this->composer->getPackage()->getExtra();
        $this->config = empty($extra[self::EXTRA]) ? [] : $extra[self::EXTRA];
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
    }

    /**
     * @param int $type - Type of package (class constant)
     *
     * @return string - base path for package
     * @throws \Exception
     */
    protected function getBase($type)
    {
        $base = $this->getWebroot();

        if ($type != self::CORE && $type != self::PROFILE) {
            $base .= "/sites/all";
        }

        return $base;
    }

    /**
     * @param string $key
     * @param null|string $default
     *
     * @return mixed|null
     */
    protected function getConfig($key, $default = null)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        } else {
            return $default;
        }
    }

    /**
     * Retrieve just the package name from the packages "prettyName"
     *  prettyName = "vendor/name"
     *
     * @return string
     */
    protected function getPackageName()
    {
        $name = $this->package->getPrettyName();
        if (strpos($name, '/') !== false) {
            list(, $name) = explode('/', $name);
        }
        return $name;
    }

    /**
     * Get the target path after the root for the package
     *
     * @param string $type
     * @param bool $custom
     *
     * @return string
     * @throws \Exception
     */
    protected function getTargetPath($type, $custom = false)
    {
        switch ($type) {
            case self::CORE:
                $path = '';
                break;
            case self::DRUSH:
                $path = "drush";
                break;
            case self::LIBRARY:
                $path = "libraries";
                break;
            case self::MODULE:
                $path = "modules";
                break;
            case self::THEME:
                $path = "themes";
                break;
            case self::PROFILE:
                $path = "profiles";
                break;
            default:
                throw new \Exception("Unsupported package type: $type");
        }

        if ($type === self::MODULE || $type === self::THEME) {
            $path .= $custom ? "/custom" : "/contrib";
        }

        if ($type !== self::CORE) {
            $name = $this->getPackageName();
            $path .= "/$name";
        }

        return $path;
    }

    /**
     * Get the webroot specified in the "extra" composer config
     * Default: docroot
     *
     * @return string
     */
    protected function getWebroot()
    {
        return $this->getConfig(self::ROOT, "docroot");
    }
}
