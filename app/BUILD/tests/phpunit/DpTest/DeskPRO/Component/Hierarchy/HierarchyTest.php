<?php

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
