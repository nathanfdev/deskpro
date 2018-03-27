<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Brand;

use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DpTest\DeskProTestCase;

class BrandContainerTest extends DeskProTestCase
{
    public function testGetters()
    {
        $mockBrand    = \Mockery::mock('Application\DeskPRO\Entity\Brand');
        $mockSettings = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsBag');
        $bc           = new BrandContainer($mockBrand, $mockSettings);

        $this->assertSame($mockBrand, $bc->getBrand());
        $this->assertSame($mockSettings, $bc->getSettings());
    }

    public function testGetSetting()
    {
        $mockBrand    = \Mockery::mock('Application\DeskPRO\Entity\Brand');
        $mockSettings = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsBag');
        $bc           = new BrandContainer($mockBrand, $mockSettings);

        $mockSettings->shouldReceive('get')->with('setting_name', null)->andReturn('the val!')->once();

        $this->assertEquals('the val!', $bc->getSetting('setting_name'));
    }
}
