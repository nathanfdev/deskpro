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

namespace spec\DeskPRO\Component\Hierarchy\Formatter;

use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use PhpSpec\ObjectBehavior;

class ParentListFormatterSpec extends ObjectBehavior
{
    public function let(
        HierarchyNode $node1,
        HierarchyNode $node2,
        HierarchyNode $node3
    ) {
        $cat1 = new NewsCategory();
        $cat1->setTitle('General');
        $cat2 = new NewsCategory();
        $cat2->setTitle('Specific');
        $cat3 = new NewsCategory();
        $cat3->setTitle('More Specific');

        $node1->getData()->willReturn($cat1);
        $node1->getParents()->willReturn([]);

        $node2->getData()->willReturn($cat2);

        $node3->getData()->willReturn($cat3);
        $node3->getParents()->willReturn([$node2]);
    }

    public function it_formats_plain_when_no_parents(
        HierarchyNode $node1
    ) {
        $this->format($node1)->shouldReturn('General');
    }

    public function it_formats_a_hierarchy_of_parents_when_parents_exist(
        HierarchyNode $node3
    ) {
        $this->format($node3)->shouldReturn('Specific > More Specific');
    }
}
