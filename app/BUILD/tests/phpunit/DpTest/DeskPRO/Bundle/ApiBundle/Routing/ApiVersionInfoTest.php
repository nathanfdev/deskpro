<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\ApiBundle\Routing;

use DeskPRO\Bundle\ApiBundle\Routing\ApiVersionInfo;

/**
 * Class ApiVersionInfoTest.
 */
class ApiVersionInfoTest extends \PHPUnit_Framework_TestCase
{
    public function test_default_latest_version()
    {
        $info = new ApiVersionInfo('latest', [
            '20150101',
            '20160101',
            '20170401',
        ]);

        $this->assertEquals('20170401', $info->getDefaultVersion());
    }

    public function test_default_concrete_version()
    {
        $info = new ApiVersionInfo('20160101', [
            '20150101',
            '20160101',
            '20170401',
        ]);

        $this->assertEquals('20160101', $info->getDefaultVersion());
    }

    /**
     * @dataProvider lowerVersionsProvider
     *
     * @param string $checkVersion
     * @param string $expectedLowerVersions
     */
    public function test_lower_versions($checkVersion, $expectedLowerVersions)
    {
        $info = new ApiVersionInfo('latest', [
            '20150101',
            '20160101',
            '20170401',
        ]);

        $this->assertEquals($expectedLowerVersions, $info->getLowerVersions($checkVersion));
    }

    /**
     * @return array
     */
    public function lowerVersionsProvider()
    {
        return [
            ['20170401', [
                '20150101',
                '20160101',
                '20170401',
            ]],
            ['20160101', [
                '20150101',
                '20160101',
            ]],
            ['20150101', [
                '20150101',
            ]],
            ['20140101', []],
        ];
    }

    /**
     * @dataProvider closesVersionProvider
     *
     * @param string $checkVersion
     * @param string $expectedClosestVersion
     */
    public function test_closest_versions($checkVersion, $expectedClosestVersion)
    {
        $info = new ApiVersionInfo('latest', [
            '20150101',
            '20160101',
            '20170401',
        ]);

        $this->assertEquals($expectedClosestVersion, $info->getClosestVersion($checkVersion));
    }

    /**
     * @return array
     */
    public function closesVersionProvider()
    {
        return [
            ['20170402', '20170401'],
            ['20170401', '20170401'],
            ['20160102', '20160101'],
            ['20160101', '20160101'],
            ['20150102', '20150101'],
            ['20150101', '20150101'],
            ['20140101', '20150101'],
        ];
    }
}
