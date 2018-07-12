<?php

/**
 * DeskPRO.
 */

namespace DpTest\Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\DependencyInjection\SystemServices\ZipperService;
use DpTest\DeskProTestCase;
use Orb\Zip\Adapter\ZipArchiveAdapter;

class ZipperServiceDummy extends ZipperService
{
    /**
     * Returns all supported extensions
     *
     * @return array|string[]
     */
    public static function getInstalledExtensions()
    {
        return [ZipperService::EXTENSION_ZIP, ZipperService::EXTENSION_ZLIB];
    }
}
class ZipperServiceTest extends DeskProTestCase
{
    /**
     * @throws \Orb\Zip\ZipException
     */
    public function testCreatePrefersZipArchiveExtension()
    {
        $container = new DeskproContainer();
        $zip = ZipperServiceDummy::create($container);
        $this->assertEquals(
            ZipArchiveAdapter::class,
            $zip->getAdapterType(),
            sprintf('% extension should be preferred when available', ZipperService::EXTENSION_ZIP)
        );
    }

    /**
     * @throws \Orb\Zip\ZipException
     */
    public function testCreateZipAdapterPrefersZipArchiveExtension()
    {
        $container = new DeskproContainer();
        $adapter = ZipperService::createZipAdapter($container, [ ZipperService::EXTENSION_ZLIB, ZipperService::EXTENSION_ZIP]);
        $this->assertTrue(
        $adapter instanceof ZipArchiveAdapter,
        sprintf('% extension should be preferred when available', ZipperService::EXTENSION_ZIP)
        );
    }
}
