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
    public static function create(DeskproContainer $container)
    {
        if (extension_loaded('Zip')) {
            $type = 'zip';
        } elseif (extension_loaded('zlib')) {
            $type = 'pcl_zip';
        }

        switch ($type) {
            case 'zip':
                $adapter = new Zip\Adapter\ZipArchiveAdapter();
                break;

            case 'pcl_zip':
                require_once DP_ROOT.'/vendor-src/pclzip/pclzip.lib.php';
                $adapter = new Zip\Adapter\PclZipAdapter();
                break;

            default:
                throw new Zip\ZipException('Zip and zlib extensions not installed, no way to zip', 0);
        }

        $zip = new Zip\Zip($adapter);

        return $zip;
    }
}
