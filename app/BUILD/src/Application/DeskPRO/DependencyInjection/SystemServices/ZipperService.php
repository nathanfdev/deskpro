<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Orb\Zip;

class ZipperService
{
    const EXTENSION_ZIP = 'Zip';

    const EXTENSION_ZLIB = 'zlib';

    /**
     * Requires the bundled version of http://www.phpconcept.net/pclzip
     */
    public static function requirePclzip()
    {
        require_once DP_ROOT.'/vendor-src/pclzip/pclzip.lib.php';
    }

    /**
     * Returns the names of installed zip extensions
     *
     * @return array|string[]
     */
    public static function getInstalledExtensions()
    {
        $extensions = [ZipperService::EXTENSION_ZIP, ZipperService::EXTENSION_ZLIB];
        $availableExtensions = array_filter($extensions, function ($extension) {
            return extension_loaded($extension);
        });

        return $availableExtensions;
    }

    /**
     * @param DeskproContainer $container
     * @return Zip\Zip
     * @throws Zip\ZipException
     */
    public static function create( DeskproContainer $container)
    {
        $extensions = static::getInstalledExtensions();
        $adapter = ZipperService::createZipAdapter($container, $extensions);
        return new Zip\Zip($adapter);
    }

    /**
     * @param DeskproContainer $container
     * @param array $extensions list of installed extensions
     * @return Zip\Adapter\PclZipAdapter|Zip\Adapter\ZipArchiveAdapter
     * @throws Zip\ZipException
     */
    public static function createZipAdapter(DeskproContainer $container, array $extensions)
    {
        if (in_array(ZipperService::EXTENSION_ZIP, $extensions)) {
            return $adapter = new Zip\Adapter\ZipArchiveAdapter();
        }

        if (in_array(ZipperService::EXTENSION_ZLIB, $extensions)) {
            ZipperService::requirePclzip();
            return $adapter = new Zip\Adapter\PclZipAdapter();
        }

        throw new Zip\ZipException('Zip and zlib extensions not installed, no way to zip', 0);
    }
}
