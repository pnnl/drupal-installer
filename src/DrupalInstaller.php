<?php
/**
 * Created by PhpStorm.
 * User: thomscode
 * Date: 10/20/17
 * Time: 12:45 PM
 */

namespace pnnl\Composer;


use Composer\Composer;
use Composer\Installer\BinaryInstaller;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;

/**
 * Class DrupalInstaller
 *
 * @package pnnl\Composer
 */
class DrupalInstaller extends LibraryInstaller
{

    /** @const string EXTRA - key in extra used as config root for this installer */
    const EXTRA = "drupal-installer";

    /** @const string ROOT - webroot key used in composer.json */
    const ROOT = "webroot";

    /** @const string NPM - npm support key used in composer.json */
    const NPM = "npm-support";

    /** @const string BOWER - bower support key used in composer.json */
    const BOWER = "bower-support";

    /** @const string NPM_TYPE - type key from npm asset package */
    const NPM_TYPE = "npm-asset";

    /** @const string BOWER_TYPE - type key from bower asset package */
    const BOWER_TYPE = "bower-asset";

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

    /** @var string $webroot - The webroot specified in the config. Default: docroot */
    protected $webroot;

    /** @var bool $npm - Support npm-asset type. Default: true */
    protected $npm;

    /** @var bool $bower - Support bower-asset type. Default: true */
    protected $bower;

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

        // Retrieve configuration values into class
        $this->webroot = $this->getConfig(self::ROOT, "docroot");
        $this->npm = $this->getConfig(self::NPM, true);
        $this->bower = $this->getConfig(self::BOWER, true);

        // Add additional supported types
        if ($this->npm) {
            $this->types[] = self::NPM_TYPE;
        }
        if ($this->bower) {
            $this->types[] = self::BOWER_TYPE;
        }
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
        // Load the current package
        $this->package = $package;
        $packageType = $this->package->getType();

        $custom = (strpos($packageType, 'custom') !== false) ? true : false;

        switch ($packageType) {
            case "drupal-core":
                $type = self::CORE;
                break;
            case "drupal-drush":
                $type = self::DRUSH;
                break;
            case "drupal-library":
            case self::NPM_TYPE:
            case self::BOWER_TYPE:
                // NPM_TYPE and BOWER_TYPE will only be referenced if already supported.
                $type = self::LIBRARY;
                break;
            case "drupal-custom-module":
            case "drupal-module":
                $type = self::MODULE;
                break;
            case "drupal-custom-theme":
            case "drupal-theme":
                $type = self::THEME;
                break;
            case "drupal-custom-profile":
            case "drupal-profile":
                $type = self::PROFILE;
                break;
            default:
                throw new \Exception("Unsupported type: $packageType");
                break;
        }

        $base = $this->getBase($type);
        $target = $this->getTargetPath($type, $custom);

        $path = (!empty($target)) ? "$base/$target" : $base;

        return $path;
    }

    /**
     * @param int $type - Type of package (class constant)
     *
     * @return string - base path for package
     * @throws \Exception
     */
    protected function getBase($type)
    {
        $base = $this->webroot;

        if ($type != self::CORE && $type != self::PROFILE) {
            $base .= "/sites/all";
        }

        return $base;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed|null
     */
    protected function getConfig($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
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
}
