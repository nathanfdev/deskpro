<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use Application\DeskPRO\DependencyInjection\SystemServices\ZipperService;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\BundleFileHandlingStrategyZip;
use DpTest\DeskProTestCase;

class BundleFileHandlingStrategyZipTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function instanceThrowsExceptionWhenUnsuitableExtensionsUsed()
    {
        $noExtensionsException = null;
        try {
            $noAvailableExtensions = [];
            BundleFileHandlingStrategyZip::instance($noAvailableExtensions);
        } catch (\DomainException $e) {
            $noExtensionsException = $e;
        }
        $this->assertNotEmpty($noExtensionsException, 'should throw exception if no extensions are available');

        $noSuitableException = null;
        try {
            $noAvailableExtensions = ["some-unsupported exception"];
            BundleFileHandlingStrategyZip::instance($noAvailableExtensions);
        } catch (\DomainException $e) {
            $noSuitableException = $e;
        }
        $this->assertNotEmpty($noSuitableException, 'should throw exception if no suitable extension is available');
    }

    public function instanceCreatesNewObjects()
    {
        $instance = BundleFileHandlingStrategyZip::instance([ZipperService::EXTENSION_ZIP]);
        $this->assertNotEmpty($instance, sprintf('should use extension %s when it is the only available', ZipperService::EXTENSION_ZIP));

        $instance = BundleFileHandlingStrategyZip::instance([ZipperService::EXTENSION_ZLIB]);
        $this->assertNotEmpty($instance, sprintf('should use extension %s when it is the only available', ZipperService::EXTENSION_ZLIB));

        $instance = BundleFileHandlingStrategyZip::instance([ZipperService::EXTENSION_ZLIB, ZipperService::EXTENSION_ZIP]);
        $this->assertTrue($instance->usesStrategy(ZipperService::EXTENSION_ZIP), sprintf('should prefer extension %s when it is the only available', ZipperService::EXTENSION_ZIP));
    }
}
