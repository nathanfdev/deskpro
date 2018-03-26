<?php

namespace DpTest\DeskPRO\Bundle\ApiBundle\Request;

use DeskPRO\Bundle\ApiBundle\Request\ApiVersionInfo;

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
