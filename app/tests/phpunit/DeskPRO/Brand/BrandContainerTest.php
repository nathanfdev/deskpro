<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Brand
 */

namespace DpUnitTests\DeskPRO\Brand;


use Application\DeskPRO\Brand\BrandContainer;

class BrandContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $mockBrand     = \Mockery::mock('Application\DeskPRO\Entity\Brand');
        $mockSettings  = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsBag');
        $themeResolver = \Mockery::mock('Application\PortalBundle\Theme\ThemeResolver');
        $bc            = new BrandContainer($mockBrand, $mockSettings, $themeResolver);

        $this->assertSame($mockBrand, $bc->getBrand());
        $this->assertSame($mockSettings, $bc->getSettings());
    }


    public function testGetSetting()
    {
        $mockBrand     = \Mockery::mock('Application\DeskPRO\Entity\Brand');
        $mockSettings  = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsBag');
        $themeResolver = \Mockery::mock('Application\PortalBundle\Theme\ThemeResolver');
        $bc            = new BrandContainer($mockBrand, $mockSettings, $themeResolver);

        $mockSettings->shouldReceive('get')->with('setting_name', null)->andReturn('the val!')->once();

        $this->assertEquals('the val!', $bc->getSetting('setting_name'));
    }
}
