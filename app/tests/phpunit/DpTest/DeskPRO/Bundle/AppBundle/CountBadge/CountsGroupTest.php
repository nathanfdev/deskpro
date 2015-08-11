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
use DeskPRO\Bundle\AppBundle\CountBadge\CountsGroup;
use DeskPRO\Bundle\AppBundle\CountBadge\GroupedCount;

/**
 * Class CountsGroupTest
 */
class CountsGroupTest extends DeskProTestCase
{
    /**
     * @test
     */
    function it_should_be_instantiable_with_grouped_by_value()
    {
        $group = new CountsGroup($grouped_by = 'grouped_by_property_name');
        $this->assertEquals($grouped_by, $group->getGroupedBy());
    }

    /**
     * @test
     */
    function it_should_be_instantiable_with_grouped_by_value_and_array_of_nested_counts()
    {
        $counts = [new GroupedCount('group_name', $value = 42)];
        $group = new CountsGroup('grouped_by_property_name', $counts);
        $this->assertEquals($counts, $group->getCounts());
    }

    /**
     * @test
     */
    function it_should_add_nested_counts()
    {
        $group = new CountsGroup('grouped_by_property_name', []);

        $group->add(new GroupedCount('group_name', 1));
        $group->add(new GroupedCount('group_name', 2));

        $this->assertCount(2, $group->getCounts());
    }
}