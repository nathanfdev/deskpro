<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpTest\Bundle\AppBundle\CountBadge;

use DpTest\DeskProTestCase;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;

/**
 * Class CountTest
 */
class CountTest extends DeskProTestCase
{
    /**
     * @test
     */
    function it_should_be_constructable_from_grouped_by()
    {
        $count = Count::fromGroupedBy('test_grouped_by');
        $this->assertEquals('test_grouped_by', $count->getGroupedBy());
    }

    /**
     * @test
     */
    function it_should_be_constructable_from_count_value()
    {
        $count = Count::fromValue(13);
        $this->assertEquals(13, $count->getCount());
    }

    /**
     * @test
     */
    function it_should_create_and_add_nested_counts()
    {
        $count = new Count(42);

        $count->addNested(1, 1);
        $count->addNested(2, 2);

        $this->assertCount(2, $count->getNested());
    }

    /**
     * @test
     */
    function it_should_add_nested_Count_instances()
    {
        $count = new Count(42);

        $count->addNestedInstance(new Count());
        $count->addNestedInstance(new Count());

        $this->assertCount(2, $count->getNested());
    }

    /**
     * @test
     */
    function it_should_increase_its_value()
    {
        $count = new Count();
        $count->add(3);
        $count->add(5);
        $this->assertEquals(3 + 5, $count->getCount());
    }
}