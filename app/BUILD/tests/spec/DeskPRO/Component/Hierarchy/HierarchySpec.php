<?php

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
