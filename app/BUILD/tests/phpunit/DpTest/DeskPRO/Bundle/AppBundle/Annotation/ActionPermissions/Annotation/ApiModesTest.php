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

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation;

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
        $this->assertEquals(sort($expected), sort($api_modes->getModes()));
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
