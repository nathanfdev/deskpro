<?php

namespace spec\DeskPRO\Component\Hierarchy\Formatter;

use DeskPRO\Component\Hierarchy\HierarchyNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DashesFormatterSpec extends ObjectBehavior
{
    function it_formats_node_with_dashes(
        HierarchyNode $node
    )
    {
        $node->getData()->willReturn('my data');
        $node->getDepth()->willReturn(1);
        $this->format($node)->shouldReturn('-- my data');
    }

    function it_formats_node_with_more_dashes_when_deeper(
        HierarchyNode $node
    )
    {
        $node->getData()->willReturn('my data');
        $node->getDepth()->willReturn(3);
        $this->format($node)->shouldReturn('------ my data');
    }
}
