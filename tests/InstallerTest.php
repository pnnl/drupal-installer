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
use Composer\IO\IOInterface;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryInterface;
use pnnl\Composer\DrupalInstaller;
use Symfony\Component\Filesystem\Filesystem;

final class InstallerTest extends TestCase
{

    /**
     * TODO:
     * Assert supports
     *    Add all types
     *
     * Assert paths are correct
     *
     * Assert config updates appropriately
     *  Change Docroot
     *  Disable Bower and NPM asset support
     */

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

    /** @var RepositoryInterface $repository */
    private $repository;

    /** @var IOInterface $ioMock */
    private $ioMock;

    /** @var Filesystem $filesystem */
    private $filesystem;

    public function setUp()
    {
        $this->filesystem = new Filesystem();

        $this->composer = new Composer();
        $this->config = new Config();
        $this->composer->setConfig($this->config);

        $this->vendorDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . "baton-test-vendor";
        $this->ensureDirectoryExistsAndClear($this->vendorDir);

        $this->binDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'baton-test-bin';
        $this->ensureDirectoryExistsAndClear($this->binDir);

        $this->config->merge([
            'config' => [
                'vendor-dir' => $this->vendorDir,
                'bin-dir' => $this->binDir,
            ],
        ]);

        $this->downloadManager = $this->getMockBuilder("Composer\Downloader\DownloadManager")
            ->disableOriginalConstructor()
            ->getMock();
        $this->composer->setDownloadManager($this->downloadManager);

        $this->repository = $this->createMock("Composer\Repository\InstalledRepositoryInterface");
        $this->ioMock = $this->createMock("Composer\IO\IOInterface");

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
     * @param $type
     * @param $expected
     *
     * @return void
     * @dataProvider dataForTestSupport
     */
    public function testSupports($type, $expected)
    {
        $installer = new DrupalInstaller($this->ioMock, $this->composer);
        $errorMessage = sprintf('Failed to show support for %s', $type);
        $this->assertSame($expected, $installer->supports($type), $errorMessage);
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
}
