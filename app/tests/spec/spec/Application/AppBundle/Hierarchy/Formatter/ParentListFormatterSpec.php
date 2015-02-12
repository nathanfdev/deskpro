<?php

namespace spec\Application\AppBundle\Hierarchy\Formatter;

use Application\AppBundle\Hierarchy\HierarchyNode;
use Application\DeskPRO\Entity\NewsCategory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParentListFormatterSpec extends ObjectBehavior
{
    function let(
        HierarchyNode $node1,
        HierarchyNode $node2,
        HierarchyNode $node3
    )
    {
        $cat1 = new NewsCategory();
        $cat1->setTitle('General');
        $cat2 = new NewsCategory();
        $cat2->setTitle('Specific');
        $cat3 = new NewsCategory();
        $cat3->setTitle('More Specific');

        $node1->getData()->willReturn($cat1);
        $node1->getParents()->willReturn(array());

        $node2->getData()->willReturn($cat2);

        $node3->getData()->willReturn($cat3);
        $node3->getParents()->willReturn(array($node2));

    }

    function it_formats_plain_when_no_parents(
        HierarchyNode $node1
    )
    {
        $this->format($node1)->shouldReturn('General');
    }

    function it_formats_a_hierarchy_of_parents_when_parents_exist(
        HierarchyNode $node3
    )
    {
        $this->format($node3)->shouldReturn('Specific > More Specific');
    }
}
