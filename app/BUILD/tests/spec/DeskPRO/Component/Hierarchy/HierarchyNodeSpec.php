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
        $child1->getOrder()->willReturn(-40);
        $child2->getOrder()->willReturn(-10);

        $child1->setHierarchy($hierarchy)->shouldBeCalled();
        $child2->setHierarchy($hierarchy)->shouldBeCalled();

        $this->setHierarchy($hierarchy);
        $hierarchy->addNode($child1);
        $hierarchy->addNode($child2);

        $children = [];
        foreach ($this->getWrappedObject() as $child) {
            $children[] = $child;
        }

        expect($children[0])->toBe($child2);
        expect($children[1])->toBe($child1);
    }
}
