<?php

namespace spec\DeskPRO\Component\Hierarchy;

use DeskPRO\Component\Hierarchy\Hierarchy;
use DeskPRO\Component\Hierarchy\HierarchyFormatterInterface;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HierarchyNodeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(null);
    }

    public function it_holds_any_data()
    {
        $data  = 'strings, arrays, objects, whatever';
        $depth = 0;
        $order = 0;

        $this->beConstructedWith($data, $depth, $order);

        $this->getData()->shouldBe($data);
        $this->getDepth()->shouldBe($depth);
        $this->getOrder()->shouldBe($order);
    }

    public function it_uses_the_hierarchy_formatter_to_display_itself(
        Hierarchy $hierarchy,
        HierarchyFormatterInterface $formatter
    ) {
        $hierarchy->getFormatter()->willReturn($formatter);
        $formatter->format(Argument::any())->willReturn('some formatted output');

        $this->setHierarchy($hierarchy);
        $this->__toString()->shouldReturn('some formatted output');
    }

    public function it_holds_child_nodes_and_is_countable(
        Hierarchy $hierarchy,
        HierarchyNode $child1,
        HierarchyNode $child2
    ) {
        $this->setHierarchy($hierarchy);
        $this->addChild($child1);
        $this->addChild($child2);

        $this->count()->shouldReturn(2);
    }

    public function it_maintains_children_order_and_is_traversable(
        Hierarchy $hierarchy,
        HierarchyNode $child1,
        HierarchyNode $child2
    ) {
        $hierarchy->addNode($this);
        $child1->getOrder()->willReturn(-40);
        $child2->getOrder()->willReturn(-10);

        $hierarchy->addNode($child1)->shouldBeCalled();
        $hierarchy->addNode($child2)->shouldBeCalled();

        $this->setHierarchy($hierarchy);
        $this->addChild($child1);
        $this->addChild($child2);

        $children = [];
        foreach ($this->getWrappedObject() as $child) {
            $children[] = $child;
        }

        expect($children[0])->toBe($child2);
        expect($children[1])->toBe($child1);
    }
}
