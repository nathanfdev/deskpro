<?php

namespace spec\Application\AppBundle\Hierarchy;

use Application\AppBundle\Hierarchy\HierarchyFormatterInterface;
use Application\AppBundle\Hierarchy\HierarchyNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HierarchySpec extends ObjectBehavior
{
    function let(
        HierarchyFormatterInterface $formatter,
        HierarchyNode $node1,
        HierarchyNode $node2
    )
    {
        $node1->getData()->willReturn(array('id' => 11));
        $node1->getOrder()->willReturn(1);
        $node1->setHierarchy(Argument::any())->shouldBeCalled();

        $node2->getData()->willReturn(array('id' => 22));
        $node2->getOrder()->willReturn(2);
        $node2->setHierarchy(Argument::any())->shouldBeCalled();

        $root_nodes = array($node2, $node1);

        $this->beConstructedWith($root_nodes, $formatter, 'data[id]');
    }

    function it_always_has_a_formatter_and_its_root_nodes_are_ordered(
        HierarchyFormatterInterface $formatter,
        HierarchyNode $node1,
        HierarchyNode $node2
    )
    {
        $this->getFormatter()->shouldReturn($formatter);
        $this->getRootNodes()->shouldBeLike(array($node2, $node1));
    }

    function it_is_countable()
    {
        $this->count()->shouldReturn(2);
    }

    function it_finds_a_root_node_by_id_via_the_property_path_defined_in_constructor(
        HierarchyNode $node2
    )
    {
        $this->findNodeById(22)->shouldReturn($node2);
    }
}
