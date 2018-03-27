<?php

/**
 * DeskPRO.
 */

namespace DpTest\Orb\Serializer;

use DpTest\DeskProTestCase;
use Orb\Serializer\Serializer\ArraySerializer;
use Orb\Serializer\SerializerRegistry;

class SerializerRegistryTest extends DeskProTestCase
{
    public function testAFullSerializationFlowArrayToArray()
    {
        $serializer = new SerializerRegistry(
            [
                new ArraySerializer(),
            ]
        );

        $arr = ['data', 'in' => 'here'];
        $this->assertEquals($arr, $serializer->serialize($arr));
    }

    public function testSerializerRegistryCallsFirstSupportedSerializer()
    {
        $registry = new SerializerRegistry();

        $data = $view = $format = 'string';

        $serializer1 = \Mockery::mock('Orb\Serializer\SerializerInterface');
        $serializer1->shouldReceive('supports')->with($data, $view, $format)->andReturn(false);

        $serializer2 = \Mockery::mock('Orb\Serializer\SerializerInterface');
        $serializer2->shouldReceive('supports')->with($data, $view, $format)->andReturn(true);
        $serializer2->shouldReceive('serialize')->with($data, $view, $format)->andReturn(['string']);

        $registry->addSerializer($serializer1);
        $registry->addSerializer($serializer2);

        $result = $registry->serialize($data, $view, $format);

        $this->assertEquals(['string'], $result);
    }
}
