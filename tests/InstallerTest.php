<?php
/**
 * Created by PhpStorm.
 * User: will202
 * Date: 11/7/17
 * Time: 11:56 AM
 */

namespace pnnl\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\Downloader\DownloadManager;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryInterface;
use pnnl\Composer\DrupalInstaller;
use pnnl\Composer\DrupalInstallerPlugin;
use Symfony\Component\Filesystem\Filesystem;

final class InstallerTest extends TestCase
{

    /** @var Composer $composer */
    private $composer;

    /** @var Config $config */
    private $config;

    /** @var string $vendorDir */
    private $vendorDir;

    /** @var string $binDir */
    private $binDir;

    /** @var DownloadManager $downloadManager */
    private $downloadManager;

    /** @var RepositoryInterface $repoMock */
    private $repoMock;

    /** @var IOInterface $ioMock */
    private $ioMock;

    /** @var InstallationManager|\PHPUnit_Framework_MockObject_MockObject $imMock */
    private $imMock;

    /** @var Filesystem $filesystem */
    private $filesystem;

    public function setUp()
    {
        $realPath = realpath(sys_get_temp_dir());

        $this->filesystem = new Filesystem();

        $this->composer = new Composer();
        $this->config = new Config();
        $this->composer->setConfig($this->config);

        $this->vendorDir = $realPath . DIRECTORY_SEPARATOR . "baton-test-vendor";
        $this->ensureDirectoryExistsAndClear($this->vendorDir);

        $this->binDir = $realPath . DIRECTORY_SEPARATOR . 'baton-test-bin';
        $this->ensureDirectoryExistsAndClear($this->binDir);

        $this->config->merge(
            [
                'config' => [
                    'vendor-dir' => $this->vendorDir,
                    'bin-dir'    => $this->binDir,
                ],
            ]
        );

        // Setup classes to mock
        $downloadClass = "Composer\Downloader\DownloadManager";
        $repoClass = "Composer\Repository\InstalledRepositoryInterface";
        $ioClass = "Composer\IO\IOInterface";
        $imClass = "Composer\Installer\InstallationManager";

        // Create mock objects
        $this->downloadManager = $this->getMockBuilder($downloadClass)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repoMock = $this->createMock($repoClass);
        $this->ioMock = $this->createMock($ioClass);
        $this->imMock = $this->createMock($imClass);

        // Setup Composer managers
        $this->composer->setDownloadManager($this->downloadManager);
        $this->composer->setInstallationManager($this->imMock);

        $consumerPackage = new RootPackage("foo/bar", "1.0.0", "1.0.0");
        $this->composer->setPackage($consumerPackage);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->vendorDir);
        $this->filesystem->remove($this->binDir);
    }

    /**
     * testSupports
     *
     * @param string $type - type of composer package to check
     * @param bool   $expected - The expected support value
     * @param bool   $alterConfig - Should config be altered
     *
     * @return void
     *
     * @covers       \pnnl\Composer\DrupalInstaller::__construct()
     * @covers       \pnnl\Composer\DrupalInstaller::getConfig()
     * @covers       \pnnl\Composer\DrupalInstaller::supports()
     * @dataProvider dataForTestSupport
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function testSupports($type, $expected, $alterConfig = false)
    {
        if ($alterConfig) {
            $this->alterConfig();
        }
        $installer = new DrupalInstaller($this->ioMock, $this->composer);
        $errorMessage = sprintf('Failed to show support for %s', $type);
        $this->assertSame(
            $expected,
            $installer->supports($type),
            $errorMessage
        );
    }

    /**
     * dataForTestSupport
     *
     * @return array
     */
    public function dataForTestSupport()
    {
        return [
            ["drupal-core", true],
            ["drupal-drush", true],
            ["drupal-library", true],
            ["drupal-module", true],
            ["drupal-custom-module", true],
            ["drupal-theme", true],
            ["drupal-custom-theme", true],
            ["drupal-profile", true],
            ["drupal-custom-profile", true],
            ["npm-asset", true],
            ["bower-asset", true],
            ["npm-asset", false, true],
            ["bower-asset", false, true],
            ["drupal", false],
            ["drush", false],
            ["module", false],
            ["theme", false],
            ["profile", false],
            ["library", false],
            ["package", false],
            ["metapackage", false],
            ["project", false],
            ["composer-plugin", false],
        ];
    }

    /**
     * testInstallPath
     *
     * @param string $type - Type of composer package
     * @param string $path - Expected return path
     * @param string $name - Package name
     * @param string $version - Package version
     * @param bool   $alterConfig - Should config be altered
     * @throws \Exception
     *
     * @covers       \pnnl\Composer\DrupalInstaller::__construct()
     * @covers       \pnnl\Composer\DrupalInstaller::getBase()
     * @covers       \pnnl\Composer\DrupalInstaller::getConfig()
     * @covers       \pnnl\Composer\DrupalInstaller::getInstallPath()
     * @covers       \pnnl\Composer\DrupalInstaller::getPackageName()
     * @covers       \pnnl\Composer\DrupalInstaller::getTargetPath()
     * @covers       \pnnl\Composer\DrupalInstaller::isPackageCustom()
     *
     * @dataProvider dataforTestInstallPath
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function testInstallPath(
        $type,
        $path,
        $name,
        $version = '1.0.0',
        $alterConfig = false
    ) {
        // Alter the configuration to change the docroot and disable optional types
        if ($alterConfig) {
            $this->alterConfig();
        }
        // Expect an exception in these cases
        $expectException = (
            'package' == $type ||
            (
                $alterConfig &&
                strpos($type, '-asset') !== false
            )
        );

        if ($expectException) {
            $this->expectException(\Exception::class);
        }

        // Test the conditions
        $installer = new DrupalInstaller($this->ioMock, $this->composer);
        $package = new Package($name, $version, $version);

        $package->setType($type);
        $result = $installer->getInstallPath($package);
        $this->assertEquals($path, $result);
    }

    /**
     * dataForTestInstallPath
     *
     * @return array
     */
    public function dataForTestInstallPath()
    {
        return [
            ["drupal-core", 'docroot/', 'drupal/drupal', '7.0.0'],
            [
                "drupal-drush",
                'docroot/sites/all/drush/drush',
                'drush/drush',
                '8.0.0',
            ],
            [
                "drupal-library",
                'docroot/sites/all/libraries/my_library',
                'pnnl/my_library',
            ],
            [
                "drupal-module",
                'docroot/sites/all/modules/contrib/my_module',
                'pnnl/my_module',
            ],
            [
                "drupal-custom-module",
                'docroot/sites/all/modules/custom/my_custom_module',
                'pnnl/my_custom_module',
            ],
            [
                "drupal-theme",
                'docroot/sites/all/themes/contrib/my_theme',
                'pnnl/my_theme',
            ],
            [
                "drupal-custom-theme",
                'docroot/sites/all/themes/custom/my_custom_theme',
                'pnnl/my_custom_theme',
            ],
            [
                "drupal-profile",
                'docroot/profiles/my_profile',
                'pnnl/my_profile',
            ],
            [
                "drupal-custom-profile",
                'docroot/profiles/my_custom_profile',
                'pnnl/my_custom_profile',
            ],
            [
                "npm-asset",
                'docroot/sites/all/libraries/my_npm_asset',
                'npm-asset/my_npm_asset',
            ],
            [
                "bower-asset",
                'docroot/sites/all/libraries/my_bower_asset',
                'bower-asset/my_bower_asset',
            ],
            [
                "npm-asset",
                'docroot/sites/all/libraries/my_npm_asset',
                'npm-asset/my_npm_asset',
                '1.0.0',
                true,
            ],
            [
                "bower-asset",
                'docroot/sites/all/libraries/my_bower_asset',
                'bower-asset/my_bower_asset',
                '1.0.0',
                true,
            ],
            ["package", "vendor/pnnl/my_package", "pnnl/my_package"],
        ];
    }

    /**
     * testActivate
     *
     * @covers \pnnl\Composer\DrupalInstallerPlugin::activate()
     */
    public function testActivate()
    {
        $this->imMock->expects($this->once())->method('addInstaller');
        $plugin = new DrupalInstallerPlugin();
        $plugin->activate($this->composer, $this->ioMock);
    }

    /**
     * Alter config to check configurable settings
     */
    private function alterConfig()
    {
        $settings = [
            "webroot"       => "drupal",
            "npm-support"   => false,
            "bower-support" => false,
        ];
        $config['config']['extra']['drupal-installer'] = $settings;
        $this->config->merge($config);
    }
}
