<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
        $mockModeStorage      = \Mockery::mock('DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage');

        $mockSettingsResolver->shouldReceive('getBrandSettings')->with($mockBrand)->andReturn($mockSettings)->once();
        $themeResolver = \Mockery::mock('DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver');
        $factory       = new BrandContainerFactory(
            $mockSettingsResolver, $themeResolver, $mockEm, $mockBlobStorage, $mockModeStorage);

        $container = $factory->create($mockBrand);

        $this->assertSame($mockBrand, $container->getBrand());
        $this->assertSame($mockSettings, $container->getSettings());
    }
}
