<?php

namespace spec\Application\AppBundle\Hierarchy;

use Application\AppBundle\Hierarchy\Hierarchy;
use Application\AppBundle\Hierarchy\HierarchyFormatterInterface;
use Application\AppBundle\Hierarchy\HierarchyNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HierarchyNodeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(null);
    }

    function it_holds_any_data()
    {
        $data = 'strings, arrays, objects, whatever';
        $depth = 0;
        $order = 0;

        $this->beConstructedWith($data, $depth, $order);

        $this->getData()->shouldBe($data);
        $this->getDepth()->shouldBe($depth);
        $this->getOrder()->shouldBe($order);
    }

    function it_uses_the_hierarchy_formatter_to_display_itself(
        Hierarchy $hierarchy,
        HierarchyFormatterInterface $formatter
    )
    {
        $hierarchy->getFormatter()->willReturn($formatter);
        $formatter->format(Argument::any())->willReturn('some formatted output');

        $this->setHierarchy($hierarchy);
        $this->__toString()->shouldReturn('some formatted output');
    }

    function it_holds_child_nodes_and_is_countable(
        Hierarchy $hierarchy,
        HierarchyNode $child1,
        HierarchyNode $child2
    )
    {
        $this->setHierarchy($hierarchy);
        $this->addChild($child1);
        $this->addChild($child2);

        $this->count()->shouldReturn(2);
    }

    function it_maintains_children_order_and_is_traversable(
        Hierarchy $hierarchy,
        HierarchyNode $child1,
        HierarchyNode $child2
    )
    {

        $child1->getOrder()->willReturn(15);
        $child2->getOrder()->willReturn(16);

        $child1->setHierarchy($hierarchy)->shouldBeCalled();
        $child2->setHierarchy($hierarchy)->shouldBeCalled();

        $this->setHierarchy($hierarchy);
        $this->addChild($child1);
        $this->addChild($child2);

        $children = array();
        foreach ($this->getWrappedObject() as $child) {
            $children[] = $child;
        }

        expect($children)->toBeLike(array($child2, $child1));
    }
}
