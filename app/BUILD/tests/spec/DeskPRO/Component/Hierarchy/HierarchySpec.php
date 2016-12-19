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

namespace spec\DeskPRO\Component\Hierarchy;

use DeskPRO\Component\Hierarchy\HierarchyFormatterInterface;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HierarchySpec extends ObjectBehavior
{
    public function let(
        HierarchyFormatterInterface $formatter,
        HierarchyNode $node1,
        HierarchyNode $node2
    ) {
        $node1->getData()->willReturn(['id' => 11]);
        $node1->getOrder()->willReturn(10);
        $node1->setHierarchy(Argument::any())->shouldBeCalled();

        $node2->getData()->willReturn(['id' => 22]);
        $node2->getOrder()->willReturn(50);
        $node2->setHierarchy(Argument::any())->shouldBeCalled();

        $root_nodes = [$node2, $node1];

        $this->beConstructedWith($root_nodes, $formatter, 'data[id]');
    }

    public function it_always_has_a_formatter_and_its_root_nodes_are_ordered(
        HierarchyFormatterInterface $formatter,
        HierarchyNode $node1,
        HierarchyNode $node2
    ) {
        $this->getFormatter()->shouldReturn($formatter);
        $this->getRootNodes()->shouldBe([$node2, $node1]);
    }

    public function it_is_countable()
    {
        $this->count()->shouldReturn(2);
    }

    public function it_finds_a_root_node_by_id_via_the_property_path_defined_in_constructor(
        HierarchyNode $node2
    ) {
        $this->findNodeById(22)->shouldReturn($node2);
    }
}
