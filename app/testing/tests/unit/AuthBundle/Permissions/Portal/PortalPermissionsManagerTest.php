<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace DpUnitTests\AuthBundle\Permissions\Portal;


use Application\AuthBundle\Permissions\PortalPermissionsManager;

class PortalPermissionsManagerTest extends \DpUnitTestCase
{
    public function testCacheKeyFromPerson()
    {
        $mockSettingsResolver = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsResolver');
        $mockSettingsBag = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsBag');
        $mockSettingsResolver->shouldReceive('getGlobalSettings')->andReturn($mockSettingsBag);
        $mockSettingsBag->shouldReceive('get')->with(PortalPermissionsManager::CACHE_TIMESTAMP_SETTING_NAME)->andReturn(time());
        $mockConn = \Mockery::mock('Doctrine\DBAL\Connection');
        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');

        $mockPerson = \Mockery::mock('Application\DeskPRO\Entity\Person');
        $mockUsergroup1 = \Mockery::mock('Application\DeskPRO\Entity\Usergroup');
        $mockUsergroup1->shouldReceive('offsetGet')->andReturn(1);
        $mockUsergroup2 = \Mockery::mock('Application\DeskPRO\Entity\Usergroup');
        $mockUsergroup2->shouldReceive('offsetGet')->andReturn(8);
        $mockPerson->shouldReceive('getUsergroups')->andReturn(array($mockUsergroup2, $mockUsergroup1));

        $portPerm = new PortalPermissionsManager($mockSettingsResolver, $mockConn, $mockCache);
        $cacheKey1 = $portPerm->getCacheKeyForPerson($mockPerson);
        $cacheKey2 = $portPerm->getCacheKeyForUsergroups(array($mockUsergroup1, $mockUsergroup2));
        $cacheKey3 = $portPerm->getCacheKeyForUsergroupIds(array(8, 1));

        $this->assertTrue($cacheKey1 === $cacheKey2, 'getCacheForPerson and getCacheForUsergroups work the same');
        $this->assertTrue($cacheKey2 === $cacheKey3, 'getCacheForUsergroups and getCacheForUsergroupIds work the same');
        $this->assertTrue($cacheKey3 === $cacheKey1, 'getCacheForUsergroupIds and getCacheForPerson work the same');
    }
}
