<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Component\Hierarchy;

use DeskPRO\Component\Hierarchy\Hierarchy;
use DeskPRO\Component\Hierarchy\HierarchyNode;

/**
 * Class HierarchyTest.
 */
class HierarchyTest extends \PHPUnit_Framework_TestCase
{
    public function test_with_out_display_order()
    {
        $data1     = new \stdClass();
        $data1->id = 1;
        $data2     = new \stdClass();
        $data2->id = 2;
        $data3     = new \stdClass();
        $data3->id = 3;

        $node1 = new HierarchyNode($data1);
        $node2 = new HierarchyNode($data2);
        $node3 = new HierarchyNode($data3);

        $hierarchy = new Hierarchy([$node1, $node2, $node3]);
        $this->assertEquals([$node1, $node2, $node3], $hierarchy->getRootNodes());
    }

    public function test_sort_by_display_order()
    {
        $data1     = new \stdClass();
        $data1->id = 1;
        $data2     = new \stdClass();
        $data2->id = 2;
        $data3     = new \stdClass();
        $data3->id = 3;

        $node1 = new HierarchyNode($data1, 0, 10);
        $node2 = new HierarchyNode($data2, 0, 5);
        $node3 = new HierarchyNode($data3, 0, 20);

        $hierarchy = new Hierarchy([$node1, $node2, $node3]);
        $this->assertEquals([$node3, $node1, $node2], $hierarchy->getRootNodes());
    }
}
