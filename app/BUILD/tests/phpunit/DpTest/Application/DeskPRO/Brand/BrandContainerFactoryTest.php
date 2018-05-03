<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Brand;

use DeskPRO\Bundle\PortalBundle\Brand\BrandContainerFactory;
use DpTest\DeskProTestCase;

class BrandContainerFactoryTest extends DeskProTestCase
{
    public function testConstruction()
    {
        $mockBrand            = \Mockery::mock('Application\DeskPRO\Entity\Brand');
        $mockSettings         = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsBag');
        $mockSettingsResolver = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsResolver');
        $mockEm               = \Mockery::mock('Doctrine\ORM\EntityManager');
        $mockBlobStorage      = \Mockery::mock('Application\DeskPRO\BlobStorage\DeskproBlobStorage');

        $mockSettingsResolver->shouldReceive('getBrandSettings')->with($mockBrand)->andReturn($mockSettings)->once();
        $themeResolver = \Mockery::mock('DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver');
        $factory       = new BrandContainerFactory(
            $mockSettingsResolver, $mockEm, $mockBlobStorage);

        $container = $factory->create($mockBrand);

        $this->assertSame($mockBrand, $container->getBrand());
        $this->assertSame($mockSettings, $container->getSettings());
    }
}
