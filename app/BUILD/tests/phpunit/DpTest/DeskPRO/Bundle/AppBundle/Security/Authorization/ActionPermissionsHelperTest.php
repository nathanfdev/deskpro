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

namespace DeskPRO\Bundle\ApiBundle\Security\Authorization;

use DpTest\ApiTestCase;

class ActionPermissionsHelperTest extends ApiTestCase
{
    /** @var ActionPermissionsHelper */
    protected $helper;

    public function setUp()
    {
        $this->helper = new ActionPermissionsHelper();
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
            // two tags, one allowed and one wasn't described in store. Deny.
            [['test.test2.test3', 'test1.test2.test4'], ['test1.test2.test4'], true],
            [['test.test2.test3', 'test1.test2.test4'], ['test.test2.test3'], true],
            // one tag received, one tag was allowed
            [['test.test2'], ['test.test2'], true],
            // one tag received, global allowed
            [['test.test2.test3'], ['test.test2.*'], true],
            // one tag received, global allowed 2
            [['test.test2.test3'], ['test.*'], true],
        ];
    }
}
