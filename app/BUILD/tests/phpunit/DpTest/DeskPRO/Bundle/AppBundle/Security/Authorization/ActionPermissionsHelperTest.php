<?php

namespace DeskPRO\Bundle\ApiBundle\Security\Authorization;

use DeskPRO\Bundle\AppBundle\ApiTag\TagsCollector;
use DeskPRO\Bundle\AppBundle\Security\Authorization\ActionPermissionsHelper;
use DpTest\ApiTestCase;

class ActionPermissionsHelperTest extends ApiTestCase
{
    /** @var ActionPermissionsHelper */
    protected $helper;

    public function setUp()
    {
        /** @var TagsCollector $tagsCollector */
        $tagsCollector = $this->get('api_tags.tags_collector');
        $this->helper  = new ActionPermissionsHelper($tagsCollector);
    }

    /**
     * @dataProvider getCalculateData
     */
    public function testCalculateAccess($action_tags, $gathered_tags, $access)
    {
        $this->assertEquals($this->helper->calculateAccess($action_tags, $gathered_tags), $access);
    }

    public function getCalculateData()
    {
        return [
            // simple example - one tag was received from annotation, no tags in store
            // remember that there is at least one tag for any method in controller.
            [['test.tag'], ['*'], true],
            // simple example - one tag was received from annotation, no tags in store
            // remember that there is at least one tag for any method in controller.
            [['test.tag'], [], false],
            // two tags was received from annotation, one global restriction
            [['test.test2.test3', 'test.test2.test4'], ['-test.test2.*'], false],
            [['test.test2.test3', 'test.test2.test4'], ['test.test2.test3', '-test.test2.test4'], false],
            // two tags, one restricted, one allowed
            [['test.test2.test3', 'test.test2.test4'], ['-test.test2.test3', 'test.test2.test4'], false],
            // same case, but tags has different roots
            [['test.test2.test3', 'test1.test2.test4'], ['-test.test2.test3', 'test1.test2.test4'], false],
            // two tags, one allowed and one wasn't described in store. Allow. Strict allow takes precedence.
            [['test.test2.test3', 'test1.test2.test4'], ['test1.test2.test4'], true],
            [['test.test2.test3', 'test1.test2.test4'], ['test.test2.test3'], true],
            // one tag received, one tag was allowed
            [['test.test2'], ['test.test2'], true],
            // one tag received, global allowed
            [['test.test2.test3'], ['test.test2.*'], true],
            // one tag received, global allowed 2
            [['test.test2.test3'], ['test.*'], true],
            // one global allow and one subglobal deny you can allow all, except something
            [['test.test2.test3'], ['test.*', '-test.test2.*'], false],
            // one global deny and one sublobal allow, but you can not deny all except something
            [['test.test2.test3'], ['-test.*', 'test.test2.*'], false],
        ];
    }
}
