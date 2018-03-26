<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DpTest\DeskProTestCase;

/**
 * Class ApiModesTest.
 */
class ApiModesTest extends DeskProTestCase
{
    /**
     * @dataProvider getConstructionParams
     *
     * @param $params
     * @param $expected
     */
    public function testConstruction($params, $expected)
    {
        $api_modes = new ApiModes($params);
        $exp       = $expected;
        sort($exp);
        $modes = $api_modes->getModes();
        sort($modes);
        $this->assertEquals($exp, $modes);
    }

    /**
     * @return array
     */
    public function getConstructionParams()
    {
        return [
            [['standard'], ['session', 'token']],
            [['all'], ['session', 'token', 'key']],
            [['standard', 'all'], ['session', 'token', 'key']],
            [['key', 'standard'], ['session', 'token', 'key']],
            [['token', 'session'], ['token', 'session']],
            [['key', 'session'], ['key', 'session']],
            [['value' => ['key', 'token']], ['key', 'token']],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParam()
    {
        $api_modes = new ApiModes(['wrong_key']);
    }
}
