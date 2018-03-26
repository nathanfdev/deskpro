<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use DpTest\DeskProTestCase;

/**
 * Class ApiTagsTest.
 */
class ApiTagsTest extends DeskProTestCase
{
    /**
     * @dataProvider getConstructionParams
     *
     * @param $params
     * @param $expected
     */
    public function testConstruction($params, $expected)
    {
        $api_tags = new ApiTags($params);
        sort($expected);

        $got = $api_tags->getTags();
        sort($got);

        $this->assertEquals($expected, $got);
    }

    /**
     * @return array
     */
    public function getConstructionParams()
    {
        return [
            [['test.tag'], ['test.tag']],
            [['test.tag', 'test.tag.2'], ['test.tag', 'test.tag.2']],
        ];
    }
}
