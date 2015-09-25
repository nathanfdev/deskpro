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

/**
 * DeskPRO.
 */
namespace DpTest\DeskPRO\Bundle\AppBundle\Security\Permissions;

use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsBag;
use DpTest\DeskProTestCase;

class PermissionsBagTest extends DeskProTestCase
{
    public function testIsLikeAnArrayObject()
    {
        $bag = new PermissionsBag();

        $this->assertInstanceOf('\ArrayAccess', $bag);
        $this->assertInstanceOf('\IteratorAggregate', $bag);
        $this->assertInstanceOf('\Countable', $bag);
        $this->assertInstanceOf('\Serializable', $bag);
    }

    public function testGetWillReturnABoolIfHasPermissionFalseOtherwise()
    {
        $inputArray = array(
            'key'           => '1',
            'setting'       => 1,
            'extra_setting' => 0,
        );

        $bag = new PermissionsBag($inputArray);

        $this->assertEquals(true, $bag->get('key'));
        $this->assertEquals(true, $bag->get('setting'));
        $this->assertEquals(false, $bag->get('extra_setting'));

        $this->assertEquals(false, $bag->get('does_not_exist'));

        $this->assertEquals(true, $bag['key']);
        $this->assertEquals(true, $bag['setting']);
        $this->assertEquals(false, $bag['extra_setting']);

        $this->assertSame(3, $bag->count());
        $this->assertSame(3, count($bag));

        $this->assertTrue($bag->has('key'));
        $this->assertFalse($bag->has('non_existant-key'));

        $this->assertSame($inputArray, $bag->toArray(), 'can get the settings as an array');
    }

    public function testAllowedDepartmentIds()
    {
        // the input of the dep ids is array(dep_id => extra_array_of_permission_levels)
        $ticket_dep_perms = array(
            5 => array(
                'full' => 1,
            ),
            6 => array(
                'full' => 1,
            ),
        );
        $chat_dep_perms = array(
            4 => array(
                'full' => 1,
            ),
        );
        $bag = new PermissionsBag(array(), $ticket_dep_perms, $chat_dep_perms);
        $this->assertEquals(array(5, 6), $bag->getAllowedTicketDepartmentIds());
        $this->assertEquals(array(4), $bag->getAllowedChatDepartmentIds());

        $bag2 = new PermissionsBag();
        $bag2->setAllowedTicketDepartmentIds($ticket_dep_perms);
        $bag2->setAllowedChatDepartmentIds($chat_dep_perms);
        $this->assertEquals(array(5, 6), $bag2->getAllowedTicketDepartmentIds());
        $this->assertEquals(array(4), $bag2->getAllowedChatDepartmentIds());
    }
}
