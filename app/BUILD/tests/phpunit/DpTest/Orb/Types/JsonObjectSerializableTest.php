<?php

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
