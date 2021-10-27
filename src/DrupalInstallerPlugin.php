<?php
/**
 * Created by PhpStorm.
 * User: thomscode
 * Date: 10/20/17
 * Time: 12:43 PM
 */

namespace pnnl\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Class DrupalInstallerPlugin
 *
 * @package pnnl\Composer
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class DrupalInstallerPlugin implements PluginInterface
{

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->installer = new DrupalInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($this->installer);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io) {
        $composer->getInstallationManager()->removeInstaller($this->installer);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer $composer, IOInterface $io) {
    }
}
