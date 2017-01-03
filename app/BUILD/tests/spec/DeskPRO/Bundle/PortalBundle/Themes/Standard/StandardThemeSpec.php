<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Themes\Standard;

use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Themes\Standard\StandardTheme
 */
class StandardThemeSpec extends ObjectBehavior
{
    public function it_is_a_theme()
    {
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface');
    }

    public function it_has_the_correct_id_and_name()
    {
        $this->getId()->shouldReturn('standard');
        $this->getName()->shouldReturn('Standard');
    }

    public function it_is_a_child_of_the_base_theme()
    {
        $this->getParentId()->shouldReturn('base');
    }

    public function it_can_resolve_a_tag_which_will_recursively_climb_the_parent_tree(
        Tag $tag1,
        Tag $tag2,
        ThemeInterface $base_theme
    ) {
        // base holds tag 2
        $tag2->getName()->willReturn('second_tag');
        $base_theme->resolveTag('second_tag')->willReturn($tag2);
        $this->setParent($base_theme);

        // standard holds tag 1
        $tag1->getName()->willReturn('first_tag');
        $this->setTags([$tag1]);
        $this->getTags()->shouldBe([
            'first_tag' => $tag1,
        ]);

        // both can be resolved from standard, transparently
        $this->resolveTag('first_tag')->shouldReturn($tag1);
        $this->resolveTag('second_tag')->shouldReturn($tag2);
    }
}
