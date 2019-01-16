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

        $this->assertEquals(date('Ymd'), $info->getDefaultVersion());
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
}
