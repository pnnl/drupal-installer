<?php

/**
 * Created by PhpStorm.
 * User: will202
 * Date: 11/22/17
 * Time: 8:49 AM
 */

namespace pnnl\Tests;

use Composer\Package\AliasPackage;
use Composer\Package\Package;
use Composer\Package\Version\VersionParser;
use Composer\Semver\Constraint\Constraint;
use Composer\Util\Filesystem;

class TestCase extends \PHPUnit_Framework_TestCase
{
    private static $parser;

    protected static function getVersionParser()
    {
        if (!self::$parser) {
            self::$parser = new VersionParser();
        }

        return self::$parser;
    }

    protected function getVersionConstraint($operator, $version)
    {
        return new Constraint(
            $operator,
            self::getVersionParser()->normalize($version)
        );
    }

    protected function getPackage($name, $version)
    {
        $normVersion = self::getVersionParser()->normalize($version);

        return new Package($name, $normVersion, $version);
    }

    protected function getAliasPackage($package, $version)
    {
        $normVersion = self::getVersionParser()->normalize($version);

        return new AliasPackage($package, $normVersion, $version);
    }

    protected function ensureDirectoryExistsAndClear($directory)
    {
        $filesystem = new Filesystem();
        if (is_dir($directory)) {
            $filesystem->removeDirectory($directory);
        }
        mkdir($directory, 0777, true);
    }
}
