<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Security\Permissions;

use Application\DeskPRO\Entity\Permission;
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
        $perm1 = new Permission();
        $perm1->setName('key');
        $perm1->setValue('1');

        $perm2 = new Permission();
        $perm2->setName('setting');
        $perm2->setValue(1);

        $perm3 = new Permission();
        $perm3->setName('extra_setting');
        $perm3->setValue(0);

        $bag = new PermissionsBag([$perm1, $perm2, $perm3]);

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

        $this->assertSame(
            [
                'key'           => 1,
                'setting'       => 1,
                'extra_setting' => 0,
            ],
            $bag->toArray()
        );
    }

    public function testAllowedDepartmentIds()
    {
        // the input of the dep ids is array(dep_id => extra_array_of_permission_levels)
        $ticket_dep_perms = [
            5 => [
                'full' => 1,
            ],
            6 => [
                'full' => 1,
            ],
        ];
        $chat_dep_perms = [
            4 => [
                'full' => 1,
            ],
        ];
        $bag = new PermissionsBag([], $ticket_dep_perms, $chat_dep_perms);
        $this->assertEquals([5, 6], $bag->getAllowedTicketDepartmentIds());
        $this->assertEquals([4], $bag->getAllowedChatDepartmentIds());

        $bag2 = new PermissionsBag();
        $bag2->setAllowedTicketDepartmentIds($ticket_dep_perms);
        $bag2->setAllowedChatDepartmentIds($chat_dep_perms);
        $this->assertEquals([5, 6], $bag2->getAllowedTicketDepartmentIds());
        $this->assertEquals([4], $bag2->getAllowedChatDepartmentIds());
    }

    public function testPermissionOverride()
    {
        $perm1 = new Permission();
        $perm1->setName('key');
        $perm1->setValue('0');

        $perm2 = new Permission();
        $perm2->setName('key');
        $perm2->setValue('2');

        $perm3 = new Permission();
        $perm3->setName('key');
        $perm3->setValue('1');

        $bag = new PermissionsBag([$perm1, $perm2, $perm3]);
        $this->assertSame(['key' => 2], $bag->toArray());
    }
}
