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

namespace DpTest\Orb\Types;

use DpTest\DeskProTestCase;
use Orb\Types\JsonObjectSerializer;

class JsonObjectSerializableTest extends DeskProTestCase
{
    public function setup()
    {
        require __DIR__.'/JsonObjectSerializableData.php';
    }

    public function testObjectSerialize()
    {
        $obj1 = new JsonObjectSerializableData(1, 2, 3, 'y', 'z');
        $obj2 = new JsonObjectSerializableData(1, 2, 3, 'y', 'z');

        $s1 = JsonObjectSerializer::serialize($obj1);
        $s2 = JsonObjectSerializer::serialize($obj2);
        $this->assertEquals($s1, $s2, 'Two obejcts with the same data have the same encoded string');

        $new_obj1 = JsonObjectSerializer::unserialize($s1);
        $this->assertInstanceOf(
            'DpTest\\Orb\\Types\\JsonObjectSerializableData',
            $new_obj1,
            'Unserialized object should return to the same type'
        );
        $this->assertEquals(
            $new_obj1->__toString(),
            $obj1->__toString(),
            'Unserialized object should have same data as original'
        );
    }
}
