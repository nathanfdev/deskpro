<?php

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\AppBundle\CountBadge;

use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DpTest\DeskProTestCase;

/**
 * Class CountTest.
 */
class CountTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_constructable_from_count_value()
    {
        $count = Count::fromValue(13);
        $this->assertEquals(13, $count->getCount());
    }

    /**
     * @test
     */
    public function it_should_create_and_add_nested_counts()
    {
        $count = Count::fromValue(42);

        $count->addNested(1, 1, 'type');
        $count->addNested(2, 2, 'type');

        $this->assertCount(2, $count->getNested());
    }

    /**
     * @test
     */
    public function it_should_add_nested_Count_instances()
    {
        $count = Count::fromValue(42);

        $count->addNestedInstance(Count::fromValue(1));
        $count->addNestedInstance(Count::fromValue(2));

        $this->assertCount(2, $count->getNested());
    }

    /**
     * @test
     */
    public function it_should_increase_its_value()
    {
        $count = Count::fromValue(0);
        $count->add(3);
        $count->add(5);
        $this->assertEquals(3 + 5, $count->getCount());
    }
}
