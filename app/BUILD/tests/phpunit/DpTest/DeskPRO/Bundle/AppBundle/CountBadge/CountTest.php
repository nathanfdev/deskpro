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
